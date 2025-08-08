<?php
/**
 * Chania Skills for Africa - Production Configuration
 * HostAfrica Deployment Configuration File
 */

// ==============================================
// PRODUCTION ENVIRONMENT SETUP
// ==============================================

// Set error reporting for production
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set timezone
date_default_timezone_set('Africa/Nairobi');

// ==============================================
// DATABASE CONFIGURATION
// ==============================================
// UPDATE THESE WITH YOUR HOSTAFRICA DATABASE DETAILS

define('DB_HOST', 'your-hostafrica-db-host');          // e.g., 'localhost' or 'mysql.yourdomain.com'
define('DB_NAME', 'chania_skills_africa');              // Your database name
define('DB_USER', 'your-db-username');                  // Database username
define('DB_PASS', 'your-secure-db-password');           // Database password
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// ==============================================
// APPLICATION CONFIGURATION
// ==============================================

define('APP_NAME', 'Chania Skills for Africa');
define('APP_VERSION', '2.0.0');
define('APP_ENV', 'production');
define('APP_DEBUG', false);

// URL Configuration - UPDATE WITH YOUR DOMAIN
define('BASE_URL', 'https://yourdomain.com/');          // Your actual domain
define('ADMIN_URL', BASE_URL . 'admin/');
define('API_URL', BASE_URL . 'api/');

// ==============================================
// FILE PATHS
// ==============================================

define('ROOT_PATH', __DIR__);
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');
define('ASSETS_PATH', ROOT_PATH . '/assets/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('LOGS_PATH', ROOT_PATH . '/logs/');
define('CACHE_PATH', ROOT_PATH . '/cache/');
define('BACKUP_PATH', ROOT_PATH . '/backups/');

// ==============================================
// SECURITY SETTINGS
// ==============================================

define('SECRET_KEY', 'your-unique-secret-key-here');    // Generate a unique 64-character key
define('ENCRYPTION_KEY', 'your-encryption-key-here');   // Generate a unique 32-character key
define('JWT_SECRET', 'your-jwt-secret-key-here');       // Generate a unique JWT secret

// Session Configuration
define('SESSION_TIMEOUT', 7200);                        // 2 hours
define('SESSION_NAME', 'chania_session');
define('COOKIE_DOMAIN', '.yourdomain.com');             // Your domain
define('COOKIE_SECURE', true);                          // Set to true for HTTPS
define('COOKIE_HTTPONLY', true);
define('COOKIE_SAMESITE', 'Strict');

// Security Settings
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOGIN_LOCKOUT_TIME', 1800);                     // 30 minutes
define('PASSWORD_MIN_LENGTH', 12);
define('REMEMBER_ME_DURATION', 2592000);                // 30 days
define('ENABLE_CSRF_PROTECTION', true);
define('ENABLE_RATE_LIMITING', true);
define('FORCE_HTTPS', true);

// ==============================================
// EMAIL CONFIGURATION
// ==============================================

define('MAIL_HOST', 'mail.yourdomain.com');             // Your mail server
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'info@yourdomain.com');         // Your email
define('MAIL_PASSWORD', 'your-email-password');         // Your email password
define('MAIL_ENCRYPTION', 'tls');                       // 'tls' or 'ssl'
define('MAIL_FROM_EMAIL', 'noreply@yourdomain.com');
define('MAIL_FROM_NAME', 'Chania Skills for Africa');

// Alternative Gmail Configuration (uncomment if using Gmail)
/*
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-gmail@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_ENCRYPTION', 'tls');
*/

// ==============================================
// FILE UPLOAD SETTINGS
// ==============================================

define('MAX_FILE_SIZE', 10485760);                      // 10MB in bytes
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'avi', 'mov', 'wmv']);
define('MAX_IMAGE_WIDTH', 1920);
define('MAX_IMAGE_HEIGHT', 1080);
define('IMAGE_QUALITY', 85);

// ==============================================
// APPLICATION SETTINGS
// ==============================================

define('ITEMS_PER_PAGE', 20);
define('DASHBOARD_RECENT_ITEMS', 10);
define('EXPORT_LIMIT', 5000);
define('CACHE_LIFETIME', 3600);                         // 1 hour
define('API_RATE_LIMIT', 100);                          // requests per minute

// ==============================================
// EXTERNAL SERVICES
// ==============================================

// Google Services
define('GOOGLE_ANALYTICS_ID', '');                      // Your GA tracking ID
define('GOOGLE_MAPS_API_KEY', '');                      // Your Maps API key
define('RECAPTCHA_SITE_KEY', '');                       // Your reCAPTCHA site key
define('RECAPTCHA_SECRET_KEY', '');                     // Your reCAPTCHA secret key

// Payment Gateways
define('STRIPE_PUBLIC_KEY', '');                        // Your Stripe public key
define('STRIPE_SECRET_KEY', '');                        // Your Stripe secret key
define('PAYPAL_CLIENT_ID', '');                         // Your PayPal client ID
define('PAYPAL_CLIENT_SECRET', '');                     // Your PayPal client secret

// M-Pesa (Kenya Mobile Payments)
define('MPESA_CONSUMER_KEY', '');                       // Your M-Pesa consumer key
define('MPESA_CONSUMER_SECRET', '');                    // Your M-Pesa consumer secret
define('MPESA_SHORTCODE', '');                          // Your M-Pesa shortcode
define('MPESA_PASSKEY', '');                            // Your M-Pesa passkey

// ==============================================
// SOCIAL MEDIA & CONTACT
// ==============================================

define('CONTACT_EMAIL', 'info@yourdomain.com');
define('CONTACT_PHONE', '+254724213764');
define('WHATSAPP_NUMBER', '+254724213764');
define('OFFICE_ADDRESS', 'Chania, Thika, Kenya');

define('FACEBOOK_URL', '');                             // Your Facebook page
define('TWITTER_URL', '');                              // Your Twitter profile
define('LINKEDIN_URL', '');                             // Your LinkedIn profile
define('INSTAGRAM_URL', '');                            // Your Instagram profile
define('YOUTUBE_URL', '');                              // Your YouTube channel

// ==============================================
// PERFORMANCE & CACHING
// ==============================================

define('ENABLE_CACHING', true);
define('CACHE_TYPE', 'file');                           // 'file', 'redis', 'memcached'
define('ENABLE_GZIP_COMPRESSION', true);
define('ENABLE_BROWSER_CACHING', true);
define('STATIC_CACHE_LIFETIME', 604800);                // 1 week for static assets

// CDN Configuration (if using CDN)
define('CDN_ENABLED', false);
define('CDN_URL', '');                                  // Your CDN URL
define('CDN_ASSETS_URL', '');                           // CDN URL for assets

// ==============================================
// LOGGING & MONITORING
// ==============================================

define('LOG_LEVEL', 'error');                          // 'debug', 'info', 'warning', 'error'
define('LOG_MAX_SIZE', 52428800);                       // 50MB
define('LOG_MAX_FILES', 10);

define('ENABLE_SYSTEM_MONITORING', true);
define('MONITOR_DISK_SPACE', true);
define('MONITOR_MEMORY_USAGE', true);
define('MONITOR_DATABASE_PERFORMANCE', true);

// ==============================================
// BACKUP SETTINGS
// ==============================================

define('BACKUP_ENABLED', true);
define('BACKUP_FREQUENCY', 'daily');                    // 'hourly', 'daily', 'weekly'
define('BACKUP_RETENTION_DAYS', 30);
define('BACKUP_COMPRESS', true);
define('BACKUP_INCLUDE_UPLOADS', true);

// ==============================================
// MAINTENANCE MODE
// ==============================================

define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'Website is temporarily under maintenance. Please check back soon.');
define('MAINTENANCE_ALLOWED_IPS', []);                  // Add IPs that can access during maintenance

// ==============================================
// DATABASE CONNECTION CLASS
// ==============================================

class ProductionDatabase {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET . ';port=' . DB_PORT;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci",
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_TIMEOUT => 30
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            if (APP_DEBUG) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection error. Please try again later.");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// ==============================================
// SECURITY HEADERS
// ==============================================

function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Content type sniffing prevention
    header('X-Content-Type-Options: nosniff');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // HTTPS enforcement
    if (FORCE_HTTPS && !isset($_SERVER['HTTPS'])) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
    
    // Content Security Policy
    if (defined('ENABLE_CONTENT_SECURITY_POLICY') && ENABLE_CONTENT_SECURITY_POLICY) {
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://www.gstatic.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ";
        $csp .= "font-src 'self' https://fonts.gstatic.com; ";
        $csp .= "img-src 'self' data: https:; ";
        $csp .= "media-src 'self' https:; ";
        $csp .= "connect-src 'self' https:;";
        
        header('Content-Security-Policy: ' . $csp);
    }
}

// Apply security headers
setSecurityHeaders();

// ==============================================
// SESSION CONFIGURATION
// ==============================================

function configureSession() {
    ini_set('session.cookie_lifetime', SESSION_TIMEOUT);
    ini_set('session.cookie_httponly', COOKIE_HTTPONLY ? 1 : 0);
    ini_set('session.cookie_secure', COOKIE_SECURE ? 1 : 0);
    ini_set('session.cookie_samesite', COOKIE_SAMESITE);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.name', SESSION_NAME);
    
    if (defined('COOKIE_DOMAIN')) {
        ini_set('session.cookie_domain', COOKIE_DOMAIN);
    }
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Session timeout check
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}

// Configure session
configureSession();

// ==============================================
// ERROR HANDLING
// ==============================================

function productionErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = "[" . date('Y-m-d H:i:s') . "] Error: $errstr in $errfile on line $errline\n";
    error_log($error_message, 3, LOGS_PATH . '/error.log');
    
    if (APP_DEBUG) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px; border-radius: 4px;'>";
        echo "<strong>Error:</strong> $errstr<br>";
        echo "<strong>File:</strong> $errfile<br>";
        echo "<strong>Line:</strong> $errline";
        echo "</div>";
    }
    
    return true;
}

set_error_handler('productionErrorHandler');

// ==============================================
// UTILITY FUNCTIONS
// ==============================================

function getDbConnection() {
    return ProductionDatabase::getInstance()->getConnection();
}

function isMaintenanceMode() {
    return MAINTENANCE_MODE;
}

function isAllowedDuringMaintenance() {
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
    return in_array($clientIP, MAINTENANCE_ALLOWED_IPS);
}

function logActivity($action, $details = '') {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'details' => $details,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    error_log(json_encode($log_entry) . "\n", 3, LOGS_PATH . '/activity.log');
}

// ==============================================
// MAINTENANCE MODE CHECK
// ==============================================

if (isMaintenanceMode() && !isAllowedDuringMaintenance()) {
    http_response_code(503);
    header('Retry-After: 3600'); // Retry after 1 hour
    
    echo '<!DOCTYPE html>';
    echo '<html><head><title>Under Maintenance</title>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<style>body{font-family:Arial,sans-serif;text-align:center;padding:50px;background:#f5f5f5;}';
    echo '.maintenance{background:white;padding:50px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);max-width:500px;margin:0 auto;}';
    echo 'h1{color:#DA2525;margin-bottom:20px;}p{color:#666;line-height:1.6;}</style>';
    echo '</head><body>';
    echo '<div class="maintenance">';
    echo '<h1>Under Maintenance</h1>';
    echo '<p>' . MAINTENANCE_MESSAGE . '</p>';
    echo '<p><small>We apologize for any inconvenience.</small></p>';
    echo '</div></body></html>';
    exit;
}

// ==============================================
// INITIALIZATION COMPLETE
// ==============================================

// Log system startup
logActivity('system_start', 'Production configuration loaded');

?>
