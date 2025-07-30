
<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../controllers/FilesController.php';

$controller = new FilesController();

$action = $_GET['action'] ?? $_POST['action'] ?? 'index';

switch ($action) {
    case 'upload':
        $controller->upload();
        break;
    case 'delete':
        $controller->delete();
        break;
    default:
        $controller->index();
        break;
}
