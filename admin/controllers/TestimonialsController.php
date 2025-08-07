<?php
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/Testimonial.php';

class TestimonialsController extends BaseController {
    private $testimonialManager;

    public function __construct() {
        parent::__construct();
        $this->testimonialManager = new Testimonial($this->db);
        $this->setPageTitle('Testimonials Management');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Testimonials Management']
        ]);
    }

    public function index() {
        // CRITICAL SECURITY CHECK - Check permissions
        if (!$this->hasPermission('testimonials') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to manage testimonials.');
            header('Location: index.php');
            exit;
        }

        // Handle bulk actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['bulk_action'])) {
            $this->handleBulkActions();
        }

        // Get filters and pagination
        $filters = [
            'search' => $_GET['search'] ?? '',
            'program_id' => isset($_GET['program_id']) && $_GET['program_id'] !== '' ? intval($_GET['program_id']) : null,
            'featured' => isset($_GET['featured']) && $_GET['featured'] !== '' ? intval($_GET['featured']) : ''
        ];
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        try {
            // Get testimonials with filters
            $testimonials = $this->testimonialManager->getAll($perPage, $offset, $filters['search'], $filters['program_id'], $filters['featured']);
            $totalTestimonials = $this->testimonialManager->getTotalCount($filters['search'], $filters['program_id'], $filters['featured']);
            $totalPages = ceil($totalTestimonials / $perPage);

            // Get programs for filter dropdown
            $programs = $this->testimonialManager->getPrograms();

            // Get statistics
            $stats = $this->testimonialManager->getStats();

            $this->render('testimonials/index', [
                'testimonials' => $testimonials,
                'programs' => $programs,
                'stats' => $stats,
                'filters' => $filters,
                'pagination' => [
                    'page' => $page,
                    'totalPages' => $totalPages,
                    'totalTestimonials' => $totalTestimonials,
                    'perPage' => $perPage
                ]
            ]);

        } catch (Exception $e) {
            error_log("Testimonials fetch error: " . $e->getMessage());
            $this->addError('Error loading testimonials.');
            $this->render('testimonials/index', [
                'testimonials' => [],
                'programs' => [],
                'stats' => ['total' => 0, 'featured' => 0, 'approved' => 0, 'avg_rating' => 0],
                'filters' => $filters,
                'pagination' => [
                    'page' => 1,
                    'totalPages' => 0,
                    'totalTestimonials' => 0,
                    'perPage' => $perPage
                ]
            ]);
        }
    }

    public function add() {
        // Check permissions
        if (!$this->hasPermission('testimonials') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to add testimonials.');
            header('Location: index.php');
            exit;
        }

        $this->setPageTitle('Add New Testimonial');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Testimonials Management', 'url' => BASE_URL . '/admin/testimonials.php'],
            ['title' => 'Add New Testimonial']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleTestimonialCreation();
        }

        try {
            $programs = $this->testimonialManager->getPrograms();
            $this->render('testimonials/add', [
                'programs' => $programs
            ]);
        } catch (Exception $e) {
            $this->addError('Error loading form data: ' . $e->getMessage());
            $this->render('testimonials/add', [
                'programs' => []
            ]);
        }
    }

    public function edit() {
        // Check permissions
        if (!$this->hasPermission('testimonials') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to edit testimonials.');
            header('Location: index.php');
            exit;
        }

        $testimonialId = intval($_GET['id'] ?? 0);
        if (!$testimonialId) {
            $this->redirect(BASE_URL . '/admin/testimonials.php', 'Invalid testimonial ID.');
        }

        $this->setPageTitle('Edit Testimonial');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Testimonials Management', 'url' => BASE_URL . '/admin/testimonials.php'],
            ['title' => 'Edit Testimonial']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleTestimonialUpdate($testimonialId);
        }

        try {
            $testimonial = $this->testimonialManager->getById($testimonialId);
            if (!$testimonial) {
                $this->redirect(BASE_URL . '/admin/testimonials.php', 'Testimonial not found.');
            }

            $programs = $this->testimonialManager->getPrograms();
            $this->render('testimonials/edit', [
                'testimonial' => $testimonial,
                'programs' => $programs
            ]);

        } catch (Exception $e) {
            $this->redirect(BASE_URL . '/admin/testimonials.php', 'Error loading testimonial: ' . $e->getMessage());
        }
    }

    public function delete() {
        // Check permissions
        if (!$this->hasPermission('testimonials') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to delete testimonials.');
            header('Location: index.php');
            exit;
        }

        $testimonialId = intval($_GET['id'] ?? 0);
        if (!$testimonialId) {
            $this->redirect(BASE_URL . '/admin/testimonials.php', 'Invalid testimonial ID.');
        }

        try {
            if ($this->testimonialManager->delete($testimonialId)) {
                $this->logAdminAction('DELETE_TESTIMONIAL', 'testimonials', $testimonialId);
                $this->setSuccess('Testimonial deleted successfully.');
            } else {
                $this->addError('Failed to delete testimonial.');
            }
        } catch (Exception $e) {
            $this->addError('Error deleting testimonial: ' . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/testimonials.php');
    }

    public function toggleFeatured() {
        // Check permissions
        if (!$this->hasPermission('testimonials') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied.');
            header('Location: index.php');
            exit;
        }

        $testimonialId = intval($_GET['id'] ?? 0);
        if (!$testimonialId) {
            $this->redirect(BASE_URL . '/admin/testimonials.php', 'Invalid testimonial ID.');
        }

        try {
            if ($this->testimonialManager->toggleFeatured($testimonialId)) {
                $this->logAdminAction('TOGGLE_TESTIMONIAL_FEATURED', 'testimonials', $testimonialId);
                $this->setSuccess('Testimonial featured status updated.');
            } else {
                $this->addError('Failed to update testimonial.');
            }
        } catch (Exception $e) {
            $this->addError('Error updating testimonial: ' . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/testimonials.php');
    }

    public function toggleActive() {
        // Check permissions
        if (!$this->hasPermission('testimonials') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied.');
            header('Location: index.php');
            exit;
        }

        $testimonialId = intval($_GET['id'] ?? 0);
        if (!$testimonialId) {
            $this->redirect(BASE_URL . '/admin/testimonials.php', 'Invalid testimonial ID.');
        }

        try {
            if ($this->testimonialManager->toggleActive($testimonialId)) {
                $this->logAdminAction('TOGGLE_TESTIMONIAL_ACTIVE', 'testimonials', $testimonialId);
                $this->setSuccess('Testimonial approval status updated.');
            } else {
                $this->addError('Failed to update testimonial.');
            }
        } catch (Exception $e) {
            $this->addError('Error updating testimonial: ' . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/testimonials.php');
    }

    private function handleBulkActions() {
        $action = $_POST['bulk_action'] ?? '';
        $selectedIds = $_POST['selected_testimonials'] ?? [];

        if (empty($selectedIds)) {
            $this->addError('No testimonials selected.');
            return;
        }

        try {
            switch ($action) {
                case 'delete':
                    if ($this->testimonialManager->bulkDelete($selectedIds)) {
                        $this->logAdminAction('BULK_DELETE_TESTIMONIALS', 'testimonials', null, ['ids' => $selectedIds]);
                        $this->setSuccess(count($selectedIds) . ' testimonial(s) deleted successfully.');
                    } else {
                        $this->addError('Failed to delete selected testimonials.');
                    }
                    break;
                default:
                    $this->addError('Invalid bulk action.');
            }
        } catch (Exception $e) {
            $this->addError('Error performing bulk action: ' . $e->getMessage());
        }
    }

    private function handleTestimonialCreation() {
        $requiredFields = ['authorName', 'authorTitle', 'content', 'rating'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        if (empty($errors)) {
            try {
                $testimonialData = $this->sanitizeInput($_POST);
                
                // Handle image upload
                $imagePath = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->handleImageUpload($_FILES['image'], 'testimonials');
                    if ($uploadResult['success']) {
                        $imagePath = $uploadResult['path'];
                    } else {
                        $this->addError($uploadResult['error']);
                        return;
                    }
                }

                $testimonialData['imagePath'] = $imagePath;
                $testimonialData['isFeatured'] = isset($testimonialData['isFeatured']) ? 1 : 0;
                $testimonialData['isActive'] = isset($testimonialData['isActive']) ? 1 : 0;
                $testimonialData['videoUrl'] = !empty($testimonialData['videoUrl']) ? $testimonialData['videoUrl'] : null;
                $testimonialData['programId'] = !empty($testimonialData['programId']) ? $testimonialData['programId'] : null;

                $result = $this->testimonialManager->create($testimonialData);
                
                if ($result) {
                    $this->logAdminAction('CREATE_TESTIMONIAL', 'testimonials', $result);
                    $this->redirect(BASE_URL . '/admin/testimonials.php', 'Testimonial created successfully.');
                } else {
                    $this->addError('Failed to create testimonial.');
                }
            } catch (Exception $e) {
                $this->addError('Error creating testimonial: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    private function handleTestimonialUpdate($testimonialId) {
        $requiredFields = ['authorName', 'authorTitle', 'content', 'rating'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        if (empty($errors)) {
            try {
                $testimonialData = $this->sanitizeInput($_POST);
                
                // Handle image upload and removal
                $currentTestimonial = $this->testimonialManager->getById($testimonialId);
                $imagePath = $currentTestimonial['image_path'];
                
                // Handle image removal
                if (isset($testimonialData['removeImage']) && $testimonialData['removeImage']) {
                    if ($imagePath && file_exists(__DIR__ . '/../../uploads/' . $imagePath)) {
                        unlink(__DIR__ . '/../../uploads/' . $imagePath);
                    }
                    $imagePath = null;
                }
                
                // Handle new image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->handleImageUpload($_FILES['image'], 'testimonials');
                    if ($uploadResult['success']) {
                        $imagePath = $uploadResult['path'];
                        // Delete old image if exists
                        if ($currentTestimonial['image_path'] && file_exists(__DIR__ . '/../../uploads/' . $currentTestimonial['image_path'])) {
                            unlink(__DIR__ . '/../../uploads/' . $currentTestimonial['image_path']);
                        }
                    } else {
                        $this->addError($uploadResult['error']);
                        return;
                    }
                }

                $testimonialData['imagePath'] = $imagePath;
                $testimonialData['isFeatured'] = isset($testimonialData['isFeatured']) ? 1 : 0;
                $testimonialData['isActive'] = isset($testimonialData['isActive']) ? 1 : 0;
                $testimonialData['videoUrl'] = !empty($testimonialData['videoUrl']) ? $testimonialData['videoUrl'] : null;
                $testimonialData['programId'] = !empty($testimonialData['programId']) ? $testimonialData['programId'] : null;

                $result = $this->testimonialManager->update($testimonialId, $testimonialData);
                
                if ($result) {
                    $this->logAdminAction('UPDATE_TESTIMONIAL', 'testimonials', $testimonialId);
                    $this->redirect(BASE_URL . '/admin/testimonials.php', 'Testimonial updated successfully.');
                } else {
                    $this->addError('Failed to update testimonial.');
                }
            } catch (Exception $e) {
                $this->addError('Error updating testimonial: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
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

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'error' => 'File is too large. Maximum size is 5MB.'];
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
}
?>
