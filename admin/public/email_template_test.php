<?php
// Configure session before starting for AJAX requests
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../../shared/Core/Database.php';
require_once '../../shared/Core/EmailTemplate.php';
require_once '../../shared/Core/User.php';

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

// Check for permission
if (!$userModel->hasPermission($_SESSION['user_id'], 'templates') && !$userModel->hasPermission($_SESSION['user_id'], '*')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You do not have permission to access this resource.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['template_id']) || !isset($input['email'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$emailTemplate = new EmailTemplate($db);

$template = $emailTemplate->getById($input['template_id']);
if (!$template) {
    echo json_encode(['success' => false, 'message' => 'Template not found']);
    exit();
}

// Parse variables from JSON
$variables = [];
if (!empty($input['variables'])) {
    $variableData = json_decode($input['variables'], true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $variables = $variableData;
    }
}

try {
    $success = $emailTemplate->sendEmail($input['email'], $template['name'], $variables);
    
    if ($success) {
        echo json_encode([
            'success' => true, 
            'message' => 'Test email sent successfully to ' . $input['email']
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to send test email. Please check your mail configuration.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
