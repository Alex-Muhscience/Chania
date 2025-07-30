<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants using environment variables if available
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/chania/client/public/');
}
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', __DIR__ . '/../../uploads/');
}
if (!defined('ASSETS_URL')) {
    define('ASSETS_URL', BASE_URL . '../assets/');
}
if (!defined('MAX_UPLOAD_SIZE')) {
    define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
}

// Site settings
define('SITE_NAME', 'Chania Skills for Africa');
define('SITE_DESCRIPTION', 'Empowering communities through skills development and training programs');
define('CONTACT_EMAIL', 'info@skillsforafrica.org');
define('CONTACT_PHONE', '+254 700 000 000');

// Initialize database connection if not already connected
if (!isset($db)) {
    try {
        $db = (new Database())->connect();
    } catch (Exception $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Session security (only set if session is not active)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
}

// Utility functions
function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = 'M j, Y \a\t g:i A') {
    return date($format, strtotime($datetime));
}

function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function generateSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}

function truncateText($text, $limit = 150) {
    if (strlen($text) <= $limit) {
        return $text;
    }
    return substr($text, 0, $limit) . '...';
}
?>
