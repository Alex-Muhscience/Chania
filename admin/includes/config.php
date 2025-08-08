<?php
// Admin Panel Configuration
define('ADMIN_TITLE', 'Euroafrique Corporate Skills - Admin');
define('ADMIN_VERSION', '2.0.0');
define('BASE_URL', 'http://localhost/chania/');
define('ADMIN_URL', BASE_URL . '/admin');
define('UPLOAD_PATH', __DIR__ . '/../../uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');
define('LOGS_PATH', __DIR__ . '/../../logs');

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'chania_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security Settings
define('SESSION_TIMEOUT', 7200); // 2 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('PASSWORD_MIN_LENGTH', 8);
define('REMEMBER_ME_DURATION', 2592000); // 30 days

// File Upload Settings
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'avi', 'mov', 'wmv']);

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@euroafriquecorporateskills.com');
define('SMTP_FROM_NAME', 'Euroafrique Corporate Skills');

// Application Settings
define('ITEMS_PER_PAGE', 20);
define('DASHBOARD_RECENT_ITEMS', 10);
define('EXPORT_LIMIT', 5000);
define('TIMEZONE', 'Africa/Nairobi');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/error.log');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Session configuration (moved to session management files to avoid conflicts)
?>