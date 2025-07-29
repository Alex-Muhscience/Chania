<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants using environment variables if available
if (!defined('BASE_URL')) {
    define('BASE_URL', 
        rtrim(getenv('APP_BASE_URL') ?: 'http://localhost/chania', '/'));
}
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', 
        getenv('APP_UPLOAD_PATH') ?: __DIR__ . '/../../uploads/');
}
if (!defined('MAX_UPLOAD_SIZE')) {
    define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
}

// Initialize database connection if not already connected
if (!isset($db)) {
    try {
        $db = (new Database())->connect();
    } catch (Exception $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Error reporting (disable in production)
// For production, uncomment the lines below and comment out the development ones
// ini_set('display_errors', 0);
// error_reporting(0);
// For development:
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Note: Security headers moved to .htaccess file to avoid header issues

// Session security
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
}
?>