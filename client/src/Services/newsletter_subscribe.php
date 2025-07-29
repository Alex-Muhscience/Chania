<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../includes/config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    $email = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $name = trim($input['name'] ?? '');
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Please provide a valid email address']);
        exit;
    }

    // Check if email already exists
    $stmt = $db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['status'] === 'subscribed') {
            echo json_encode(['success' => false, 'message' => 'This email is already subscribed to our newsletter']);
            exit;
        } else if ($existing['status'] === 'unsubscribed') {
            // Reactivate subscription
            $updateStmt = $db->prepare("
                UPDATE newsletter_subscribers 
                SET status = 'subscribed', name = ?, subscribed_at = NOW(), ip_address = ?
                WHERE id = ?
            ");
            $updateStmt->execute([$name, $_SERVER['REMOTE_ADDR'], $existing['id']]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Welcome back! Your subscription has been reactivated.'
            ]);
            exit;
        }
    }

    // Insert new subscription
    $insertStmt = $db->prepare("
        INSERT INTO newsletter_subscribers (email, name, status, subscribed_at, ip_address) 
        VALUES (?, ?, 'subscribed', NOW(), ?)
    ");
    
    if ($insertStmt->execute([$email, $name, $_SERVER['REMOTE_ADDR']])) {
        echo json_encode([
            'success' => true, 
            'message' => 'Thank you for subscribing! You will receive our latest updates.'
        ]);
    } else {
        throw new Exception('Failed to save subscription');
    }

} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Sorry, there was an error processing your subscription. Please try again.'
    ]);
}
?>
