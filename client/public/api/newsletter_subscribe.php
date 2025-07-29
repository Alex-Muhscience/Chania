require_once '../../includes/ClientActivityLogger.php';

<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If JSON input is empty, try regular POST data
    if (empty($input)) {
        $input = $_POST;
    }
    
    $email = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $name = trim($input['name'] ?? '');
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please provide a valid email address']);
        exit;
    }
    
    // Connect to database
    $db = Database::getInstance()->getConnection();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        if ($existing['status'] === 'subscribed') {
            echo json_encode(['success' => false, 'message' => 'This email is already subscribed to our newsletter']);
            exit;
        } else {
            // Reactivate subscription
            $stmt = $db->prepare("UPDATE newsletter_subscribers SET status = 'subscribed', subscribed_at = NOW() WHERE email = ?");
            $stmt->execute([$email]);
            echo json_encode(['success' => true, 'message' => 'Welcome back! Your subscription has been reactivated']);
            exit;
        }
    }
    
    // Add new subscription
    $stmt = $db->prepare("INSERT INTO newsletter_subscribers (email, name, status, subscribed_at) VALUES (?, ?, 'subscribed', NOW())");
    $result = $stmt->execute([$email, $name]);
    
    if ($result) {
        // Log the subscription for admin tracking
        $logger = new ClientActivityLogger();
        $logger->logNewsletterSubscription($email, $name);
        $logger->logToAdminLogs($email, 'Newsletter subscription');

        echo json_encode([
            'success' => true, 
            'message' => 'Thank you for subscribing! You\'ll receive our latest updates and news.'
        ]);
    } else {
        throw new Exception('Failed to process subscription');
    }
    
} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while processing your subscription. Please try again later.'
    ]);
}
?>
