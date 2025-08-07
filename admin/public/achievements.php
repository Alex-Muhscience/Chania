<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../controllers/AchievementsController.php';

session_start();

// Require authentication
Utilities::requireLogin();

$controller = new AchievementsController();

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
    case 'toggle_status':
        $controller->toggleStatus();
        break;
    case 'toggle_featured':
        $controller->toggleFeatured();
        break;
    default:
        $controller->index();
        break;
}
?>
