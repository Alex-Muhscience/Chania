<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../controllers/TeamMembersController.php';

$controller = new TeamMembersController();

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
    default:
        $controller->index();
        break;
}
?>

