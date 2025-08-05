<?php
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/Program.php';

class EnhancedProgramsController extends BaseController {
    private $programModel;

    public function __construct() {
        parent::__construct();
        $this->programModel = new Program($this->db);
        $this->setPageTitle('Enhanced Programs Management');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Enhanced Programs Management']
        ]);
    }

    public function index() {
        // Check permissions - CRITICAL SECURITY CHECK
        if (!$this->hasPermission('programs') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to manage programs.');
            header('Location: index.php');
            exit;
        }

        // Handle POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }

        // Get filters and pagination
        $search = $_GET['search'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $whereClause = '';
            $params = [];

            if ($search) {
                $whereClause = "WHERE p.title LIKE ? OR p.description LIKE ?";
                $searchTerm = "%$search%";
                $params = [$searchTerm, $searchTerm];
            }

            // Get total count
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM programs p $whereClause");
            $countStmt->execute($params);
            $totalPrograms = $countStmt->fetchColumn();

            // Get programs with schedule counts
            $stmt = $this->db->prepare("
                SELECT p.*, 
                       COUNT(ps.id) as schedule_count,
                       pi.introduction,
                       pi.objectives
                FROM programs p 
                LEFT JOIN program_schedules ps ON p.id = ps.program_id AND ps.deleted_at IS NULL
                LEFT JOIN program_info pi ON p.id = pi.program_id
                $whereClause 
                GROUP BY p.id 
                ORDER BY p.created_at DESC 
                LIMIT $limit OFFSET $offset
            ");
            $stmt->execute($params);
            $programs = $stmt->fetchAll();

            $totalPages = ceil($totalPrograms / $limit);

            $this->renderView(__DIR__ . '/../views/enhanced_programs/index.php', [
                'programs' => $programs,
                'search' => $search,
                'pagination' => [
                    'page' => $page,
                    'totalPages' => $totalPages,
                    'totalPrograms' => $totalPrograms,
                    'limit' => $limit
                ]
            ]);

        } catch (PDOException $e) {
            error_log("Enhanced Programs fetch error: " . $e->getMessage());
            $this->addError('Error loading programs.');
            $this->renderView(__DIR__ . '/../views/enhanced_programs/index.php', [
                'programs' => [],
                'search' => $search,
                'pagination' => [
                    'page' => 1,
                    'totalPages' => 0,
                    'totalPrograms' => 0,
                    'limit' => $limit
                ]
            ]);
        }
    }

    public function add() {
        // Check permissions
        if (!$this->hasPermission('programs') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to add programs.');
            header('Location: index.php');
            exit;
        }

        $this->setPageTitle('Add New Enhanced Program');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Enhanced Programs Management', 'url' => BASE_URL . '/admin/enhanced_programs.php'],
            ['title' => 'Add New Program']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProgramCreation();
        }

        $this->renderView(__DIR__ . '/../views/enhanced_programs/add.php', []);
    }

    public function edit() {
        // Check permissions
        if (!$this->hasPermission('programs') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to edit programs.');
            header('Location: index.php');
            exit;
        }

        $programId = intval($_GET['id'] ?? 0);
        if (!$programId) {
            $this->redirect(BASE_URL . '/admin/enhanced_programs.php', 'Invalid program ID.');
        }

        $this->setPageTitle('Edit Enhanced Program');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Enhanced Programs Management', 'url' => BASE_URL . '/admin/enhanced_programs.php'],
            ['title' => 'Edit Program']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProgramUpdate($programId);
        }

        try {
            // Get program data with schedules, curriculum, and info
            $programData = $this->getProgramWithDetails($programId);
            
            if (!$programData['program']) {
                $this->redirect(BASE_URL . '/admin/enhanced_programs.php', 'Program not found.');
            }

            $this->renderView(__DIR__ . '/../views/enhanced_programs/edit.php', $programData);

        } catch (Exception $e) {
            $this->redirect(BASE_URL . '/admin/enhanced_programs.php', 'Error loading program: ' . $e->getMessage());
        }
    }

    private function getProgramWithDetails($programId) {
        // Get program basic info
        $stmt = $this->db->prepare("SELECT * FROM programs WHERE id = ?");
        $stmt->execute([$programId]);
        $program = $stmt->fetch();

        // Get program info
        $stmt = $this->db->prepare("SELECT * FROM program_info WHERE program_id = ?");
        $stmt->execute([$programId]);
        $programInfo = $stmt->fetch();

        // Get program schedules
        $stmt = $this->db->prepare("SELECT * FROM program_schedules WHERE program_id = ? AND deleted_at IS NULL ORDER BY start_date ASC");
        $stmt->execute([$programId]);
        $schedules = $stmt->fetchAll();

        // Get program curriculum
        $stmt = $this->db->prepare("SELECT * FROM program_curriculum WHERE program_id = ? ORDER BY day_number ASC, session_order ASC");
        $stmt->execute([$programId]);
        $curriculum = $stmt->fetchAll();

        return [
            'program' => $program,
            'program_info' => $programInfo,
            'schedules' => $schedules,
            'curriculum' => $curriculum
        ];
    }

    private function handleActions() {
        $action = $_POST['action'] ?? '';
        $programId = $_POST['program_id'] ?? '';

        try {
            switch ($action) {
                case 'delete':
                    if ($programId) {
                        // Soft delete program and related data
                        $this->db->beginTransaction();
                        
                        $stmt = $this->db->prepare("UPDATE programs SET deleted_at = NOW() WHERE id = ?");
                        $stmt->execute([$programId]);
                        
                        $stmt = $this->db->prepare("UPDATE program_schedules SET deleted_at = NOW() WHERE program_id = ?");
                        $stmt->execute([$programId]);
                        
                        $this->db->commit();
                        $this->setSuccess("Program deleted successfully.");
                    }
                    break;

                case 'toggle_status':
                    if ($programId) {
                        $stmt = $this->db->prepare("UPDATE programs SET is_active = NOT is_active WHERE id = ?");
                        $stmt->execute([$programId]);
                        $this->setSuccess("Program status updated successfully.");
                    }
                    break;
            }

            $this->redirect($_SERVER['PHP_SELF']);

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Enhanced Program management error: " . $e->getMessage());
            $this->addError("An error occurred while processing your request.");
        }
    }

    private function handleProgramCreation() {
        $requiredFields = ['title', 'description', 'duration'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        if (empty($errors)) {
            try {
                $this->db->beginTransaction();
                
                // Create basic program
                $programData = $this->sanitizeInput($_POST);
                $stmt = $this->db->prepare("
                    INSERT INTO programs (title, description, duration, fee, is_active, created_at) 
                    VALUES (?, ?, ?, ?, 1, NOW())
                ");
                $result = $stmt->execute([
                    $programData['title'],
                    $programData['description'],
                    $programData['duration'],
                    $programData['fee'] ?? 0
                ]);
                
                $programId = $this->db->lastInsertId();
                
                // Create program info
                if (!empty($programData['introduction']) || !empty($programData['objectives'])) {
                    $stmt = $this->db->prepare("
                        INSERT INTO program_info (program_id, introduction, objectives, target_audience, prerequisites, 
                                                course_content, general_notes, certification_details, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $programId,
                        $programData['introduction'] ?? '',
                        $programData['objectives'] ?? '',
                        $programData['target_audience'] ?? '',
                        $programData['prerequisites'] ?? '',
                        $programData['course_content'] ?? '',
                        $programData['general_notes'] ?? '',
                        $programData['certification_details'] ?? ''
                    ]);
                }
                
                $this->db->commit();
                
                if ($result) {
                    $this->redirect(BASE_URL . '/admin/enhanced_programs.php', 'Program created successfully.');
                } else {
                    $this->addError('Failed to create program.');
                }
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->addError('Error creating program: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    private function handleProgramUpdate($programId) {
        $requiredFields = ['title', 'description', 'duration'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        if (empty($errors)) {
            try {
                $this->db->beginTransaction();
                
                $programData = $this->sanitizeInput($_POST);
                
                // Update basic program
                $stmt = $this->db->prepare("
                    UPDATE programs 
                    SET title = ?, description = ?, duration = ?, fee = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $result = $stmt->execute([
                    $programData['title'],
                    $programData['description'],
                    $programData['duration'],
                    $programData['fee'] ?? 0,
                    $programId
                ]);
                
                // Update or create program info
                $stmt = $this->db->prepare("SELECT id FROM program_info WHERE program_id = ?");
                $stmt->execute([$programId]);
                $infoExists = $stmt->fetchColumn();
                
                if ($infoExists) {
                    $stmt = $this->db->prepare("
                        UPDATE program_info 
                        SET introduction = ?, objectives = ?, target_audience = ?, prerequisites = ?, 
                            course_content = ?, general_notes = ?, certification_details = ?, updated_at = NOW()
                        WHERE program_id = ?
                    ");
                    $stmt->execute([
                        $programData['introduction'] ?? '',
                        $programData['objectives'] ?? '',
                        $programData['target_audience'] ?? '',
                        $programData['prerequisites'] ?? '',
                        $programData['course_content'] ?? '',
                        $programData['general_notes'] ?? '',
                        $programData['certification_details'] ?? '',
                        $programId
                    ]);
                } else {
                    $stmt = $this->db->prepare("
                        INSERT INTO program_info (program_id, introduction, objectives, target_audience, prerequisites, 
                                                course_content, general_notes, certification_details, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $programId,
                        $programData['introduction'] ?? '',
                        $programData['objectives'] ?? '',
                        $programData['target_audience'] ?? '',
                        $programData['prerequisites'] ?? '',
                        $programData['course_content'] ?? '',
                        $programData['general_notes'] ?? '',
                        $programData['certification_details'] ?? ''
                    ]);
                }
                
                $this->db->commit();
                
                if ($result) {
                    $this->redirect(BASE_URL . '/admin/enhanced_programs.php', 'Program updated successfully.');
                } else {
                    $this->addError('Failed to update program.');
                }
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->addError('Error updating program: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    public function schedules() {
        $programId = intval($_GET['program_id'] ?? 0);
        if (!$programId) {
            $this->redirect(BASE_URL . '/admin/enhanced_programs.php', 'Invalid program ID.');
        }

        // Check permissions
        if (!$this->hasPermission('programs') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied.');
            header('Location: index.php');
            exit;
        }

        $this->setPageTitle('Manage Program Schedules');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleScheduleActions($programId);
        }

        try {
            // Get program info
            $stmt = $this->db->prepare("SELECT title FROM programs WHERE id = ?");
            $stmt->execute([$programId]);
            $program = $stmt->fetch();

            if (!$program) {
                $this->redirect(BASE_URL . '/admin/enhanced_programs.php', 'Program not found.');
            }

            // Get schedules
            $stmt = $this->db->prepare("SELECT * FROM program_schedules WHERE program_id = ? AND deleted_at IS NULL ORDER BY start_date ASC");
            $stmt->execute([$programId]);
            $schedules = $stmt->fetchAll();

            $this->renderView(__DIR__ . '/../views/enhanced_programs/schedules.php', [
                'program' => $program,
                'program_id' => $programId,
                'schedules' => $schedules
            ]);

        } catch (Exception $e) {
            $this->redirect(BASE_URL . '/admin/enhanced_programs.php', 'Error loading schedules: ' . $e->getMessage());
        }
    }

    private function handleScheduleActions($programId) {
        $action = $_POST['action'] ?? '';

        try {
            switch ($action) {
                case 'add_schedule':
                    $this->addSchedule($programId);
                    break;
                case 'delete_schedule':
                    $scheduleId = $_POST['schedule_id'] ?? '';
                    if ($scheduleId) {
                        $stmt = $this->db->prepare("UPDATE program_schedules SET deleted_at = NOW() WHERE id = ? AND program_id = ?");
                        $stmt->execute([$scheduleId, $programId]);
                        $this->setSuccess("Schedule deleted successfully.");
                    }
                    break;
            }
        } catch (Exception $e) {
            error_log("Schedule management error: " . $e->getMessage());
            $this->addError("Error managing schedule: " . $e->getMessage());
        }
    }

    private function addSchedule($programId) {
        $requiredFields = ['delivery_mode', 'start_date', 'end_date', 'title'];
        $scheduleData = $this->sanitizeInput($_POST);
        $errors = [];

        // Validation
        foreach ($requiredFields as $field) {
            if (empty($scheduleData[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
            }
        }

        // Validate that at least one fee is provided
        if (empty($scheduleData['online_fee']) && empty($scheduleData['physical_fee'])) {
            $errors[] = "At least one fee (online or physical) must be specified.";
        }

        if (empty($errors)) {
            $stmt = $this->db->prepare("
                INSERT INTO program_schedules (
                    program_id, title, description, delivery_mode, start_date, end_date, 
                    start_time, end_time, location, venue_details, timezone, 
                    online_fee, physical_fee, currency, max_participants, 
                    registration_deadline, instructor_name, instructor_email, 
                    meeting_link, meeting_password, requirements, materials_included,
                    is_active, is_open_for_registration, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, NOW())
            ");
            
            $stmt->execute([
                $programId,
                $scheduleData['title'],
                $scheduleData['description'] ?? '',
                $scheduleData['delivery_mode'],
                $scheduleData['start_date'],
                $scheduleData['end_date'],
                $scheduleData['start_time'] ?? '09:00:00',
                $scheduleData['end_time'] ?? '17:00:00',
                $scheduleData['location'] ?? '',
                $scheduleData['venue_details'] ?? '',
                $scheduleData['timezone'] ?? 'Africa/Nairobi',
                $scheduleData['online_fee'] ?: 0,
                $scheduleData['physical_fee'] ?: 0,
                $scheduleData['currency'] ?? 'KES',
                $scheduleData['max_participants'] ?: null,
                $scheduleData['registration_deadline'] ?: null,
                $scheduleData['instructor_name'] ?? '',
                $scheduleData['instructor_email'] ?? '',
                $scheduleData['meeting_link'] ?? '',
                $scheduleData['meeting_password'] ?? '',
                $scheduleData['requirements'] ?? '',
                $scheduleData['materials_included'] ?? ''
            ]);
            
            $this->setSuccess("Schedule added successfully.");
        } else {
            foreach ($errors as $error) {
                $this->addError($error);
            }
        }
    }
}
?>
