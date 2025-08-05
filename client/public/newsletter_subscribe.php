<?php
/**
 * Newsletter Subscription Endpoint
 * Handles newsletter subscriptions with comprehensive validation and admin synchronization
 */

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load configuration
try {
    require_once '../includes/config.php';
    require_once __DIR__ . '/../../shared/Core/ApiConfig.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(ApiConfig::createResponse(
        ApiConfig::STATUS_ERROR,
        'System configuration error. Please try again later.',
        null,
        'CONFIG_ERROR'
    ));
    exit;
}

/**
 * Validate email address with comprehensive checks
 */
function validateEmail($email) {
    $email = trim($email);
    
    // Basic validation
    if (empty($email)) {
        return ['valid' => false, 'message' => 'Email address is required.'];
    }
    
    // Format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => 'Please enter a valid email address.'];
    }
    
    // Length validation
    if (strlen($email) > 100) {
        return ['valid' => false, 'message' => 'Email address is too long.'];
    }
    
    // Domain validation (basic)
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return ['valid' => false, 'message' => 'Invalid email format.'];
    }
    
    return ['valid' => true, 'email' => $email];
}

/**
 * Log activity for admin panel synchronization
 */
function logNewsletterActivity($db, $action, $subscriberId, $email, $ip) {
    try {
        // Check if admin_logs table exists
        $stmt = $db->query("SHOW TABLES LIKE 'admin_logs'");
        if ($stmt->rowCount() > 0) {
            $logStmt = $db->prepare("
                INSERT INTO admin_logs (user_id, action, entity_type, entity_id, details, ip_address, created_at, source)
                VALUES (NULL, ?, 'newsletter_subscribers', ?, ?, ?, NOW(), 'system')
            ");
            $logStmt->execute([
                $action,
                $subscriberId,
                "Newsletter action: {$action} for {$email}",
                $ip
            ]);
        }
    } catch (Exception $e) {
        error_log('Newsletter activity logging error: ' . $e->getMessage());
        // Don't fail the main operation if logging fails
    }
}

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'Invalid request.',
    'timestamp' => date('Y-m-d H:i:s')
];

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Method not allowed. Please use POST.';
    echo json_encode($response);
    exit;
}

// Get and validate email
$email = $_POST['email'] ?? $_GET['email'] ?? '';
$validation = validateEmail($email);

if (!$validation['valid']) {
    http_response_code(400);
    $response['message'] = $validation['message'];
    echo json_encode($response);
    exit;
}

$email = $validation['email'];
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$source = $_POST['source'] ?? $_GET['source'] ?? 'website_footer';

try {
    // Start transaction
    $db->beginTransaction();
    
    // Check if email already exists (including soft-deleted records)
    $checkStmt = $db->prepare("
        SELECT id, status, deleted_at, subscribed_at 
        FROM newsletter_subscribers 
        WHERE email = ?
    ");
    $checkStmt->execute([$email]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        if ($existing['deleted_at'] === null && $existing['status'] === 'subscribed') {
            // Already subscribed and active
            $response['status'] = 'info';
            $response['message'] = 'You are already subscribed to our newsletter!';
            $response['subscribed_date'] = $existing['subscribed_at'];
        } elseif ($existing['deleted_at'] !== null || $existing['status'] !== 'subscribed') {
            // Reactivate subscription
            $updateStmt = $db->prepare("
                UPDATE newsletter_subscribers 
                SET status = 'subscribed', 
                    subscribed_at = NOW(), 
                    unsubscribed_at = NULL,
                    deleted_at = NULL,
                    ip_address = ?,
                    user_agent = ?,
                    source = ?,
                    updated_at = NOW()
                WHERE email = ?
            ");
            
            if ($updateStmt->execute([$ip, $userAgent, $source, $email])) {
                $response['status'] = 'success';
                $response['message'] = 'Welcome back! Your newsletter subscription has been reactivated.';
                logNewsletterActivity($db, 'NEWSLETTER_RESUBSCRIBE', $existing['id'], $email, $ip);
            } else {
                throw new Exception('Failed to reactivate subscription');
            }
        }
    } else {
        // New subscription
        $insertStmt = $db->prepare("
            INSERT INTO newsletter_subscribers 
            (email, status, subscribed_at, ip_address, user_agent, source, created_at, updated_at) 
            VALUES (?, 'subscribed', NOW(), ?, ?, ?, NOW(), NOW())
        ");
        
        if ($insertStmt->execute([$email, $ip, $userAgent, $source])) {
            $subscriberId = $db->lastInsertId();
            $response['status'] = 'success';
            $response['message'] = 'Thank you for subscribing to our newsletter! You will receive updates about our programs and events.';
            $response['subscriber_id'] = $subscriberId;
            
            // Log for admin panel
            logNewsletterActivity($db, 'NEWSLETTER_SUBSCRIBE', $subscriberId, $email, $ip);
        } else {
            throw new Exception('Failed to create subscription');
        }
    }
    
    // Commit transaction
    $db->commit();
    
} catch (PDOException $e) {
    // Rollback transaction
    $db->rollback();
    
    error_log('Newsletter subscription database error: ' . $e->getMessage());
    
    http_response_code(500);
    $response['status'] = 'error';
    $response['message'] = 'A database error occurred. Please try again later.';
    $response['error_code'] = 'DB_ERROR';
    
} catch (Exception $e) {
    // Rollback transaction
    $db->rollback();
    
    error_log('Newsletter subscription error: ' . $e->getMessage());
    
    http_response_code(500);
    $response['status'] = 'error';
    $response['message'] = 'An error occurred while processing your subscription. Please try again later.';
    $response['error_code'] = 'PROCESSING_ERROR';
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
?>
