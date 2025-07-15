<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

session_start();

// Define constants using environment variables if available
define('BASE_URL', 
    getenv('APP_BASE_URL') ?: 'http://localhost/chania/');
define('UPLOAD_PATH', 
    getenv('APP_UPLOAD_PATH') ?: __DIR__ . '/../../uploads/');
const MAX_UPLOAD_SIZE = 5 * 1024 * 1024; // 5MB

// Initialize database connection
try {
    $db = (new Database())->connect();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>