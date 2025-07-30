<?php
// Configure session before starting for action files
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit();
}

require_once '../../shared/Core/Database.php';
require_once '../../shared/Core/EmailTemplate.php';
require_once '../../shared/Core/User.php';

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

// Check for permission
if (!$userModel->hasPermission($_SESSION['user_id'], 'templates') && !$userModel->hasPermission($_SESSION['user_id'], '*')) {
    $_SESSION['error'] = "You don't have permission to delete email templates.";
    header('Location: ../public/email_templates.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid template ID.';
    header('Location: ../public/email_templates.php');
    exit();
}

$emailTemplate = new EmailTemplate($db);

$template = $emailTemplate->getById($_GET['id']);
if (!$template) {
    $_SESSION['error'] = 'Template not found.';
    header('Location: ../public/email_templates.php');
    exit();
}

if ($emailTemplate->delete($_GET['id'])) {
    $_SESSION['message'] = 'Email template "' . htmlspecialchars($template['name']) . '" deleted successfully.';
} else {
    $_SESSION['error'] = 'Failed to delete template. Please try again.';
}

header('Location: ../public/email_templates.php');
exit();
?>
