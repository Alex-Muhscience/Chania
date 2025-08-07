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
            ['title' => 'Enhanced Programs Management', 'url' => BASE_URL . '/admin/programs.php'],
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
            $this->redirect(BASE_URL . '/admin/programs.php', 'Invalid program ID.');
        }

        $this->setPageTitle('Edit Enhanced Program');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Enhanced Programs Management', 'url' => BASE_URL . '/admin/programs.php'],
            ['title' => 'Edit Program']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProgramUpdate($programId);
        }

        try {
            // Get program data with schedules, curriculum, and info
            $programData = $this->getProgramWithDetails($programId);
            
            if (!$programData['program']) {
                $this->redirect(BASE_URL . '/admin/programs.php', 'Program not found.');
            }

            $this->renderView(__DIR__ . '/../views/enhanced_programs/edit.php', $programData);

        } catch (Exception $e) {
            $this->redirect(BASE_URL . '/admin/programs.php', 'Error loading program: ' . $e->getMessage());
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

        // Merge program_info fields into the main program array for form display
        if ($program && $programInfo) {
            $program = array_merge($program, $programInfo);
        }

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
                    
                case 'delete_gallery_image':
                    $this->deleteGalleryImageAjax();
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
                
                // Handle main image upload
                $imagePath = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->handleImageUpload($_FILES['image'], 'programs');
                    if ($uploadResult['success']) {
                        $imagePath = $uploadResult['path'];
                    } else {
                        $this->db->rollBack();
                        $this->addError($uploadResult['error']);
                        return;
                    }
                }
                
                // Handle gallery images upload
                $galleryImages = [];
                if (isset($_FILES['gallery_images'])) {
                    for ($i = 0; $i < count($_FILES['gallery_images']['name']); $i++) {
                        if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $_FILES['gallery_images']['name'][$i],
                                'type' => $_FILES['gallery_images']['type'][$i],
                                'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                                'error' => $_FILES['gallery_images']['error'][$i],
                                'size' => $_FILES['gallery_images']['size'][$i]
                            ];
                            $uploadResult = $this->handleImageUpload($file, 'programs/gallery');
                            if ($uploadResult['success']) {
                                $galleryImages[] = $uploadResult['path'];
                            }
                        }
                    }
                }
                
                $stmt = $this->db->prepare("
                    INSERT INTO programs (title, slug, description, short_description, category, duration, 
                                        difficulty_level, fee, max_participants, start_date, end_date,
                                        image_path, gallery_images, video_url, brochure_path,
                                        instructor_name, location, is_featured, is_active, is_online,
                                        tags, meta_title, meta_description, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, NOW())
                ");
                $result = $stmt->execute([
                    $programData['title'],
                    $this->generateSlug($programData['title']),
                    $programData['description'],
                    $programData['short_description'] ?? '',
                    $programData['category'] ?? 'General',
                    $programData['duration'],
                    $programData['difficulty_level'] ?? 'beginner',
                    $programData['fee'] ?? 0,
                    $programData['max_participants'] ?? null,
                    $programData['start_date'] ?? null,
                    $programData['end_date'] ?? null,
                    $imagePath,
                    !empty($galleryImages) ? json_encode($galleryImages) : null,
                    $programData['video_url'] ?? null,
                    null, // brochure_path - can be added later
                    $programData['instructor_name'] ?? null,
                    $programData['location'] ?? null,
                    isset($programData['is_featured']) ? 1 : 0,
                    isset($programData['is_online']) ? 1 : 0,
                    $programData['tags'] ?? null,
                    $programData['meta_title'] ?? null,
                    $programData['meta_description'] ?? null
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
                
                // Get current program data
                $stmt = $this->db->prepare("SELECT * FROM programs WHERE id = ?");
                $stmt->execute([$programId]);
                $currentProgram = $stmt->fetch();
                
                if (!$currentProgram) {
                    throw new Exception('Program not found.');
                }
                
                // Handle main image upload or removal
                $imagePath = $currentProgram['image_path'];
                
                // Check if image should be removed
                if (isset($programData['remove_image']) && $programData['remove_image'] == '1') {
                    // Delete current image file if exists
                    if ($imagePath) {
                        $currentImagePath = __DIR__ . '/../../uploads/' . $imagePath;
                        if (file_exists($currentImagePath)) {
                            unlink($currentImagePath);
                        }
                    }
                    $imagePath = null;
                }
                
                // Handle new image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    // Delete old image if exists
                    if ($imagePath) {
                        $oldImagePath = __DIR__ . '/../../uploads/' . $imagePath;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    $uploadResult = $this->handleImageUpload($_FILES['image'], 'programs');
                    if ($uploadResult['success']) {
                        $imagePath = $uploadResult['path'];
                    } else {
                        $this->db->rollBack();
                        $this->addError($uploadResult['error']);
                        return;
                    }
                }
                
                // Handle gallery images
                $currentGallery = json_decode($currentProgram['gallery_images'] ?? '[]', true);
                $galleryImages = $currentGallery;
                
                // Add new gallery images
                if (isset($_FILES['gallery_images'])) {
                    $newGalleryImages = [];
                    for ($i = 0; $i < count($_FILES['gallery_images']['name']); $i++) {
                        if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $_FILES['gallery_images']['name'][$i],
                                'type' => $_FILES['gallery_images']['type'][$i],
                                'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                                'error' => $_FILES['gallery_images']['error'][$i],
                                'size' => $_FILES['gallery_images']['size'][$i]
                            ];
                            $uploadResult = $this->handleImageUpload($file, 'programs/gallery');
                            if ($uploadResult['success']) {
                                $newGalleryImages[] = $uploadResult['path'];
                            }
                        }
                    }
                    
                    if (!empty($newGalleryImages)) {
                        $galleryImages = array_merge($galleryImages, $newGalleryImages);
                    }
                }
                
                // Update main program table with all fields
                $stmt = $this->db->prepare("
                    UPDATE programs 
                    SET title = ?, slug = ?, description = ?, category = ?, duration = ?, 
                        difficulty_level = ?, fee = ?, max_participants = ?, start_date = ?, end_date = ?,
                        image_path = ?, gallery_images = ?, video_url = ?,
                        instructor_name = ?, location = ?, is_featured = ?, is_online = ?,
                        tags = ?, meta_title = ?, meta_description = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $result = $stmt->execute([
                    $programData['title'],
                    $this->generateSlug($programData['title']),
                    $programData['description'],
                    $programData['category'] ?? 'General',
                    $programData['duration'],
                    $programData['difficulty_level'] ?? 'beginner',
                    $programData['fee'] ?? 0,
                    $programData['max_participants'] ?: null,
                    $programData['start_date'] ?: null,
                    $programData['end_date'] ?: null,
                    $imagePath,
                    !empty($galleryImages) ? json_encode(array_values($galleryImages)) : null,
                    $programData['video_url'] ?: null,
                    $programData['instructor_name'] ?: null,
                    $programData['location'] ?: null,
                    isset($programData['is_featured']) ? 1 : 0,
                    isset($programData['is_online']) ? 1 : 0,
                    $programData['tags'] ?: null,
                    $programData['meta_title'] ?: null,
                    $programData['meta_description'] ?: null,
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

    public function schedules() {
        $programId = intval($_GET['program_id'] ?? 0);
        if (!$programId) {
            $this->redirect(BASE_URL . '/admin/programs.php', 'Invalid program ID.');
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
                $this->redirect(BASE_URL . '/admin/programs.php', 'Program not found.');
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
            $this->redirect(BASE_URL . '/admin/programs.php', 'Error loading schedules: ' . $e->getMessage());
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

        // Validate that both fees are provided (requirement: all courses available in both modes)
        if (empty($scheduleData['online_fee']) || $scheduleData['online_fee'] <= 0) {
            $errors[] = "Online fee is required and must be greater than 0.";
        }
        if (empty($scheduleData['physical_fee']) || $scheduleData['physical_fee'] <= 0) {
            $errors[] = "Physical fee is required and must be greater than 0.";
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
    
    private function handleImageUpload($file, $subfolder = '') {
        $uploadDir = __DIR__ . '/../../uploads/' . ($subfolder ? $subfolder . '/' : '');
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.'];
        }

        // Validate file size (10MB max for programs)
        if ($file['size'] > 10 * 1024 * 1024) {
            return ['success' => false, 'error' => 'File is too large. Maximum size is 10MB.'];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'path' => ($subfolder ? $subfolder . '/' : '') . $filename];
        } else {
            return ['success' => false, 'error' => 'Failed to upload file.'];
        }
    }
    
    private function generateSlug($title) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');
        
        // Check if slug exists and make it unique
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM programs WHERE slug = ?");
            $stmt->execute([$slug]);
            $count = $stmt->fetchColumn();
            
            if ($count == 0) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    public function deleteGalleryImage() {
        // Check permissions
        if (!$this->hasPermission('programs') && !$this->hasPermission('*')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }
        
        $programId = intval($_POST['program_id'] ?? 0);
        $imageIndex = intval($_POST['image_index'] ?? -1);
        
        if (!$programId || $imageIndex < 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
            exit;
        }
        
        try {
            // Get current gallery images
            $stmt = $this->db->prepare("SELECT gallery_images FROM programs WHERE id = ?");
            $stmt->execute([$programId]);
            $program = $stmt->fetch();
            
            if (!$program) {
                echo json_encode(['success' => false, 'error' => 'Program not found']);
                exit;
            }
            
            $galleryImages = json_decode($program['gallery_images'] ?? '[]', true);
            
            if (isset($galleryImages[$imageIndex])) {
                $imagePath = __DIR__ . '/../../uploads/' . $galleryImages[$imageIndex];
                
                // Remove from array
                $removedImage = $galleryImages[$imageIndex];
                unset($galleryImages[$imageIndex]);
                $galleryImages = array_values($galleryImages); // Re-index array
                
                // Update database
                $stmt = $this->db->prepare("UPDATE programs SET gallery_images = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([!empty($galleryImages) ? json_encode($galleryImages) : null, $programId]);
                
                // Delete physical file
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                
                echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Image not found']);
            }
            
        } catch (Exception $e) {
            error_log("Gallery image deletion error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to delete image']);
        }
        
        exit;
    }
    
    public function addGalleryImages() {
        // Check permissions
        if (!$this->hasPermission('programs') && !$this->hasPermission('*')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }
        
        $programId = intval($_POST['program_id'] ?? 0);
        
        if (!$programId) {
            echo json_encode(['success' => false, 'error' => 'Invalid program ID']);
            exit;
        }
        
        try {
            // Get current gallery images
            $stmt = $this->db->prepare("SELECT gallery_images FROM programs WHERE id = ?");
            $stmt->execute([$programId]);
            $program = $stmt->fetch();
            
            if (!$program) {
                echo json_encode(['success' => false, 'error' => 'Program not found']);
                exit;
            }
            
            $currentGallery = json_decode($program['gallery_images'] ?? '[]', true);
            $newImages = [];
            
            // Handle new image uploads
            if (isset($_FILES['gallery_images'])) {
                for ($i = 0; $i < count($_FILES['gallery_images']['name']); $i++) {
                    if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['gallery_images']['name'][$i],
                            'type' => $_FILES['gallery_images']['type'][$i],
                            'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                            'error' => $_FILES['gallery_images']['error'][$i],
                            'size' => $_FILES['gallery_images']['size'][$i]
                        ];
                        $uploadResult = $this->handleImageUpload($file, 'programs/gallery');
                        if ($uploadResult['success']) {
                            $newImages[] = $uploadResult['path'];
                        }
                    }
                }
            }
            
            if (!empty($newImages)) {
                $updatedGallery = array_merge($currentGallery, $newImages);
                
                // Update database
                $stmt = $this->db->prepare("UPDATE programs SET gallery_images = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([json_encode($updatedGallery), $programId]);
                
                echo json_encode([
                    'success' => true, 
                    'message' => count($newImages) . ' image(s) added successfully',
                    'new_images' => $newImages
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No valid images uploaded']);
            }
            
        } catch (Exception $e) {
            error_log("Gallery image addition error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to add images']);
        }
        
        exit;
    }
}
?>
