<?php
require_once __DIR__ . '/../../client/includes/config.php';
require_once __DIR__ . '/../../shared/Core/Page.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Ensure user is logged in
Utilities::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $pageManager = new Page($db);
        $page = $pageManager->getById($_POST['id']);
        
        if (!$page) {
            $_SESSION['error_message'] = 'Page not found.';
        } else {
            $pageManager->delete($_POST['id']);
            $_SESSION['success_message'] = 'Page "' . $page['title'] . '" deleted successfully!';
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error deleting page: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = 'Invalid request.';
}

header('Location: ' . BASE_URL . '/admin/public/pages.php');
exit();
?>
