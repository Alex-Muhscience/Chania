<?php
require_once '../../shared/Core/Database.php';
require_once '../classes/Faq.php';
require_once '../../shared/Core/User.php';

// Start session
session_start();

$database = new Database();
$db = $database->connect();
$user = new User($db);

if (!$user->hasPermission($_SESSION['user_id'], 'faqs') && !$user->hasPermission($_SESSION['user_id'], '*')) {
    die('Access denied. You do not have permission to delete FAQs.');
}

if (!isset($_GET['id'])) {
    header('Location: ../public/faqs.php');
    exit;
}

$faq = new Faq($db);

if ($faq->delete($_GET['id'])) {
    $_SESSION['success'] = 'FAQ deleted successfully.';
} else {
    $_SESSION['error'] = 'Failed to delete FAQ.';
}

header('Location: ../public/faqs.php');
exit;
?>
