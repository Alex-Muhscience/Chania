<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start([        'name' => 'ADMIN_SESSION',
        'cookie_lifetime' => SESSION_TIMEOUT,
        'cookie_secure' => false, // Set to false for localhost HTTP
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Check for brute force attempts
if (isset($_SESSION['login_attempts'])) {
    if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        if (time() - $_SESSION['last_login_attempt'] < LOGIN_LOCKOUT_TIME) {
            $_SESSION['error_message'] = "Too many login attempts. Please try again later.";
            Utilities::redirect('/admin/login.php');
        } else {
            // Reset attempts after timeout
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_login_attempt']);
        }
    }
}

// Redirect to log in if not authenticated
if (!Utilities::isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'] ?? '/admin/index.php';
    Utilities::redirect(BASE_URL . 'admin/login.php');
    exit;
}

// Check for admin role for sensitive pages
$allowedRoles = ['admin'];
$currentPage = basename($_SERVER['PHP_SELF']);
$restrictedPages = ['users.php', 'logs.php', 'settings.php'];

if (in_array($currentPage, $restrictedPages) && !in_array($_SESSION['role'], $allowedRoles)) {
    $_SESSION['error_message'] = "You don't have permission to access this page.";
    Utilities::redirect('/admin/index.php');
    exit;
}

// CSRF protection
$csrfToken = Utilities::generateCsrfToken();

// Database connection
try {
    $db = (new Database())->connect();
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Get current user info
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
}

// Logout functionality
if (isset($_GET['logout'])) {
    // Log the logout action
    $logStmt = $db->prepare("
        INSERT INTO admin_logs (user_id, action, ip_address)
        VALUES (?, ?, ?)
    ");
    $logStmt->execute([
        $_SESSION['user_id'],
        'logout',
        $_SERVER['REMOTE_ADDR']
    ]);

    session_destroy();
    Utilities::redirect('/admin/login.php');
    exit;
}

// Activity logging middleware
function logAdminAction($action, $entityType = null, $entityId = null) {
    global $db;

    $stmt = $db->prepare("
        INSERT INTO admin_logs (user_id, action, entity_type, entity_id, ip_address)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $action,
        $entityType,
        $entityId,
        $_SERVER['REMOTE_ADDR']
    ]);
}
?>