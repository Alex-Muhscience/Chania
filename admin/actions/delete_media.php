<?php
require_once __DIR__ . '/../../client/includes/config.php';
require_once __DIR__ . '/../../shared/Core/Media.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Ensure user is logged in
Utilities::requireLogin();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    try {
        $media = new Media($db);
        $media->delete($_GET['id']);
        
        $_SESSION['success_message'] = 'Media deleted successfully!';
        header('Location: ' . BASE_URL . '/admin/public/media.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error deleting media: ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/admin/public/media.php');
        exit();
    }
} else {
    $_SESSION['error_message'] = 'Invalid media ID.';
    header('Location: ' . BASE_URL . '/admin/public/media.php');
    exit();
}
?>
