<?php
/**
 * Secure Admin Access Controller
 * This script provides an additional security layer for admin access
 * It validates multiple security parameters before allowing access to admin panel
 */

// Start session with secure parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_start();

// Security configuration
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 300); // 5 minutes in seconds
// Load environment variables for production
if (file_exists(__DIR__ . '/../.env.production')) {
    $env_file = __DIR__ . '/../.env.production';
} else {
    $env_file = __DIR__ . '/../.env';
}

if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, '"');
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
    }
}

define('ADMIN_ACCESS_KEY', $_ENV['ADMIN_ACCESS_KEY'] ?? 'change-this-to-very-secure-key-123!');

// Function to log security events
function logSecurityEvent($event, $details = '') {
    $logFile = __DIR__ . '/logs/security.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logEntry = "[{$timestamp}] {$event} - IP: {$ip} - User Agent: {$userAgent} - Details: {$details}" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Function to check if IP is rate limited
function isRateLimited($ip) {
    $attempts = $_SESSION['login_attempts'][$ip] ?? 0;
    $lastAttempt = $_SESSION['last_attempt'][$ip] ?? 0;
    
    if ($attempts >= MAX_LOGIN_ATTEMPTS && (time() - $lastAttempt) < LOCKOUT_TIME) {
        return true;
    }
    
    // Reset attempts if lockout period has passed
    if ((time() - $lastAttempt) >= LOCKOUT_TIME) {
        unset($_SESSION['login_attempts'][$ip]);
        unset($_SESSION['last_attempt'][$ip]);
    }
    
    return false;
}

// Function to record failed attempt
function recordFailedAttempt($ip) {
    $_SESSION['login_attempts'][$ip] = ($_SESSION['login_attempts'][$ip] ?? 0) + 1;
    $_SESSION['last_attempt'][$ip] = time();
}

// Get client IP
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '';

// Check if running from command line
if (php_sapi_name() === 'cli') {
    echo "This script is designed to run via web server, not command line.\n";
    echo "Please access it through your web browser at: https://www.euroafriquecorporateskills.com/admin/secure-admin-access.php\n";
    exit(1);
}

// Basic security checks
$securityChecks = [
    'user_agent' => !empty($_SERVER['HTTP_USER_AGENT'] ?? ''),
    'rate_limit' => !isRateLimited($clientIP),
    'method' => in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'POST']),
    'referrer' => true // You can add specific referrer checks here if needed
];

// Check if all security checks pass
$securityPassed = array_reduce($securityChecks, function($carry, $item) {
    return $carry && $item;
}, true);

if (!$securityPassed) {
    logSecurityEvent('SECURITY_CHECK_FAILED', 'Failed basic security validation');
    recordFailedAttempt($clientIP);
    http_response_code(403);
    exit('Access Denied');
}

// Handle access key validation
if (isset($_GET['key']) || isset($_POST['key'])) {
    $providedKey = $_GET['key'] ?? $_POST['key'] ?? '';
    
    if (hash_equals(ADMIN_ACCESS_KEY, $providedKey)) {
        // Valid access key provided
        $_SESSION['admin_access_granted'] = true;
        $_SESSION['admin_access_time'] = time();
        logSecurityEvent('ADMIN_ACCESS_GRANTED', 'Valid access key provided');
        
        // Redirect to admin public directory
        header('Location: public/');
        exit;
    } else {
        // Invalid access key
        logSecurityEvent('INVALID_ACCESS_KEY', 'Invalid access key attempted: ' . $providedKey);
        recordFailedAttempt($clientIP);
        http_response_code(403);
        exit('Invalid Access Key');
    }
}

// Check if admin access was previously granted and is still valid (1 hour timeout)
if (isset($_SESSION['admin_access_granted']) && 
    isset($_SESSION['admin_access_time']) && 
    (time() - $_SESSION['admin_access_time']) < 3600) {
    
    // Access still valid, redirect to admin
    header('Location: public/');
    exit;
}

// If no valid access, show access form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Admin Access</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .access-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #5a67d8;
        }
        .warning {
            background: #fed7d7;
            border: 1px solid #feb2b2;
            color: #c53030;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="access-form">
        <h2>Admin Access Required</h2>
        
        <?php if (isRateLimited($clientIP)): ?>
            <div class="warning">
                Too many failed attempts. Please wait <?php echo ceil((LOCKOUT_TIME - (time() - $_SESSION['last_attempt'][$clientIP])) / 60); ?> minutes before trying again.
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="key">Access Key:</label>
                    <input type="password" id="key" name="key" required>
                </div>
                <button type="submit">Grant Access</button>
            </form>
        <?php endif; ?>
        
        <p style="margin-top: 1rem; font-size: 0.875rem; color: #666; text-align: center;">
            Unauthorized access attempts are logged and monitored.
        </p>
    </div>
</body>
</html>
