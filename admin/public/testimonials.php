<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../controllers/TestimonialsController.php';

$controller = new TestimonialsController();

$action = $_GET['action'] ?? $_POST['action'] ?? 'index';

switch ($action) {
    case 'add':
        $controller->add();
        break;
    case 'edit':
        $controller->edit();
        break;
    case 'delete':
        $controller->delete();
        break;
    case 'toggle_featured':
        $controller->toggleFeatured();
        break;
    case 'toggle_active':
        $controller->toggleActive();
        break;
    default:
        $controller->index();
        break;
}
?>
