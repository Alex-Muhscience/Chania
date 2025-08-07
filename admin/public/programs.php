<?php
require_once __DIR__ . '/../controllers/EnhancedProgramsController.php';

session_start();

try {
    $controller = new EnhancedProgramsController();
    
    // Determine which action to perform
    $action = $_GET['action'] ?? 'index';
    
    switch ($action) {
        case 'add':
            $controller->add();
            break;
        case 'edit':
            $controller->edit();
            break;
        case 'schedules':
            $controller->schedules();
            break;
        case 'delete_gallery_image':
            $controller->deleteGalleryImage();
            break;
        case 'add_gallery_images':
            $controller->addGalleryImages();
            break;
        default:
            $controller->index();
            break;
    }
    
} catch (Exception $e) {
    error_log("Enhanced Programs page error: " . $e->getMessage());
    
    // Redirect to error page or show error message
    $pageTitle = "Error";
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
        ['title' => 'Error']
    ];
    
    require_once __DIR__ . '/../includes/header.php';
    ?>
    <div class="alert alert-danger">
        <h4>An Error Occurred</h4>
        <p>Sorry, we encountered an error while processing your request. Please try again later.</p>
        <a href="<?= BASE_URL ?>/admin/" class="btn btn-primary">Return to Dashboard</a>
    </div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
}
?>
