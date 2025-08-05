<?php
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/Program.php';

class ProgramsController extends BaseController {
    private $programModel;

    public function __construct() {
        parent::__construct();
        $this->programModel = new Program($this->db);
        $this->setPageTitle('Programs Management');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Programs Management']
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
                $whereClause = "WHERE title LIKE ? OR description LIKE ?";
                $searchTerm = "%$search%";
                $params = [$searchTerm, $searchTerm];
            }

            // Get total count
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM programs $whereClause");
            $countStmt->execute($params);
            $totalPrograms = $countStmt->fetchColumn();

            // Get programs
            $stmt = $this->db->prepare("SELECT * FROM programs $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
            $stmt->execute($params);
            $programs = $stmt->fetchAll();

            $totalPages = ceil($totalPrograms / $limit);

            $this->renderView(__DIR__ . '/../views/programs/index.php', [
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
            error_log("Programs fetch error: " . $e->getMessage());
            $this->addError('Error loading programs.');
            $this->renderView(__DIR__ . '/../views/programs/index.php', [
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

    private function handleActions() {
        $action = $_POST['action'] ?? '';
        $programId = $_POST['program_id'] ?? '';

        try {
            switch ($action) {
                case 'delete':
                    if ($programId) {
                        $stmt = $this->db->prepare("DELETE FROM programs WHERE id = ?");
                        $stmt->execute([$programId]);
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
            error_log("Program management error: " . $e->getMessage());
            $this->addError("An error occurred while processing your request.");
        }
    }

    public function add() {
        // Check permissions
        if (!$this->hasPermission('programs') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to add programs.');
            header('Location: index.php');
            exit;
        }

        $this->setPageTitle('Add New Program');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Programs Management', 'url' => BASE_URL . '/admin/programs.php'],
            ['title' => 'Add New Program']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProgramCreation();
        }

        $this->renderView(__DIR__ . '/../views/programs/add.php', []);
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
            $this->redirect(BASE_URL . '/admin/programs.php', 'Invalid program ID.');
        }

        $this->setPageTitle('Edit Program');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Programs Management', 'url' => BASE_URL . '/admin/programs.php'],
            ['title' => 'Edit Program']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProgramUpdate($programId);
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM programs WHERE id = ?");
            $stmt->execute([$programId]);
            $program = $stmt->fetch();
            
            if (!$program) {
                $this->redirect(BASE_URL . '/admin/programs.php', 'Program not found.');
            }

            $this->renderView(__DIR__ . '/../views/programs/edit.php', [
                'program' => $program
            ]);

        } catch (Exception $e) {
            $this->redirect(BASE_URL . '/admin/programs.php', 'Error loading program: ' . $e->getMessage());
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
                
                // Create program info if provided
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
                    $this->redirect(BASE_URL . '/admin/programs.php', 'Program created successfully.');
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
                    $this->redirect(BASE_URL . '/admin/programs.php', 'Program updated successfully.');
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

    public function export() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM programs ORDER BY created_at DESC");
            $stmt->execute();
            $programs = $stmt->fetchAll();

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="programs_export_' . date('Y-m-d') . '.csv"');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Title', 'Description', 'Duration', 'Fee', 'Status', 'Created']);

            foreach ($programs as $program) {
                fputcsv($output, [
                    $program['id'],
                    $program['title'],
                    $program['description'],
                    $program['duration'],
                    $program['fee'],
                    $program['is_active'] ? 'Active' : 'Inactive',
                    $program['created_at']
                ]);
            }

            fclose($output);
            exit;

        } catch (Exception $e) {
            error_log("Program export error: " . $e->getMessage());
            $this->redirect(BASE_URL . '/admin/programs.php', 'Error exporting programs.');
        }
    }
}
?>
