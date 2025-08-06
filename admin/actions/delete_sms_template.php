<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/User.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to perform this action.";
    Utilities::redirect('/admin/public/login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

if (!$userModel->hasPermission($_SESSION['user_id'], 'sms') && !$userModel->hasPermission($_SESSION['user_id'], '*')) {
    $_SESSION['error'] = "You don't have permission to perform this action.";
    Utilities::redirect('/admin/public/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $template_id = $_POST['id'];
    
    // Check if template is being used in any SMS campaigns
    $stmt = $db->prepare("SELECT COUNT(*) FROM sms_campaigns WHERE template_id = ?");
    $stmt->execute([$template_id]);
    $usageCount = $stmt->fetchColumn();
    
    if ($usageCount > 0) {
        $_SESSION['error'] = "Cannot delete template: It is being used in {$usageCount} SMS campaign(s).";
    } else {
        // Proceed with deletion
        $stmt = $db->prepare("DELETE FROM sms_templates WHERE id = ?");
        if ($stmt->execute([$template_id])) {
            $_SESSION['message'] = "SMS template deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete SMS template.";
        }
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

Utilities::redirect('/admin/public/sms_templates.php');
exit();
?>
