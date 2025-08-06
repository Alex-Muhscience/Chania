<?php
require_once __DIR__ . '/../../shared/Core/Environment.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Load environment variables
Environment::load();

// Session security (must be set before session_start())
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
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

// Language System
$supported_languages = ['en', 'fr', 'es', 'de', 'pt', 'sw', 'ar', 'zh'];

// Set default language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Change language if requested
if (isset($_GET['lang']) && in_array($_GET['lang'], $supported_languages)) {
    $_SESSION['lang'] = $_GET['lang'];
    // Redirect to remove lang parameter from URL
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    if (!empty($_GET)) {
        $params = $_GET;
        unset($params['lang']);
        if (!empty($params)) {
            $redirect_url .= '?' . http_build_query($params);
        }
    }
    header('Location: ' . $redirect_url);
    exit;
}

// Load language file
$current_lang = $_SESSION['lang'];
$lang_file = __DIR__ . "/../languages/{$current_lang}.php";
if (file_exists($lang_file)) {
    $lang_data = include $lang_file;
} else {
    // Fallback to English if language file doesn't exist
    $lang_data = include __DIR__ . "/../languages/en.php";
}

// Language function
function lang($key, $default = null) {
    global $lang_data;
    return $lang_data[$key] ?? $default ?? $key;
}

// Get language name and flag
function getLanguageInfo($lang_code) {
    $languages = [
        'en' => ['name' => 'English', 'flag' => '🇺🇸'],
        'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
        'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
        'de' => ['name' => 'Deutsch', 'flag' => '🇩🇪'],
        'pt' => ['name' => 'Português', 'flag' => '🇵🇹'],
        'sw' => ['name' => 'Kiswahili', 'flag' => '🇰🇪'],
        'ar' => ['name' => 'العربية', 'flag' => '🇸🇦'],
        'zh' => ['name' => '中文', 'flag' => '🇨🇳']
    ];
    return $languages[$lang_code] ?? $languages['en'];
}

// Site Settings Helper Functions (using both site_settings and system_settings tables)
function getSiteSetting($key, $default = '') {
    global $db;
    static $settings_cache = [];
    
    // Check if setting is already cached
    if (isset($settings_cache[$key])) {
        return $settings_cache[$key];
    }
    
    try {
        // Try site_settings table first
        $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = :key");
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            // Fallback to system_settings table
            $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = :key");
            $stmt->execute(['key' => $key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $value = $result ? $result['setting_value'] : $default;
        $settings_cache[$key] = $value;
        
        return $value;
    } catch (Exception $e) {
        return $default;
    }
}

function getAllSiteSettings() {
    global $db;
    static $all_settings_cache = null;
    
    if ($all_settings_cache !== null) {
        return $all_settings_cache;
    }
    
    try {
        $settings = [];
        
        // Get from site_settings table
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM site_settings");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Get from system_settings table
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM system_settings");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            // Don't override site_settings with system_settings
            if (!isset($settings[$row['setting_key']])) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
        
        $all_settings_cache = $settings;
        return $settings;
    } catch (Exception $e) {
        return [];
    }
}

// Image URL Helper Functions
function getLogoUrl($default = '') {
    $logo_url = getSiteSetting('default_logo', $default);
    return !empty($logo_url) ? $logo_url : ASSETS_URL . 'images/logo.png';
}

function getFaviconUrl($default = '') {
    $favicon_url = getSiteSetting('default_favicon', $default);
    return !empty($favicon_url) ? $favicon_url : ASSETS_URL . 'images/favicon.ico';
}

function getHeroBackgroundUrl($default = '') {
    $hero_bg_url = getSiteSetting('hero_background_url', $default);
    return !empty($hero_bg_url) ? $hero_bg_url : ASSETS_URL . 'images/hero-bg.jpg';
}
?>
