<?php
require_once __DIR__ . '/../../client/includes/config.php';
require_once __DIR__ . '/../../shared/Core/Media.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Ensure user is logged in
Utilities::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['mediaFile'])) {
    try {
        $media = new Media($db);
        $mediaId = $media->upload($_FILES['mediaFile']);
        
        $_SESSION['success_message'] = 'Media uploaded successfully!';
        header('Location: ' . BASE_URL . '/admin/public/media.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error uploading media: ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/admin/public/media.php');
        exit();
    }
} else {
    $_SESSION['error_message'] = 'No file uploaded or invalid request.';
    header('Location: ' . BASE_URL . '/admin/public/media.php');
    exit();
}
?>
