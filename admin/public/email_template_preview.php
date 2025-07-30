<?php
// Configure session before starting for AJAX requests
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo '<div class="alert alert-danger">Unauthorized access</div>';
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
    echo '<div class="alert alert-danger">You do not have permission to access this resource.</div>';
    exit();
}

if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">Template ID required</div>';
    exit();
}

$emailTemplate = new EmailTemplate($db);

$template = $emailTemplate->getById($_GET['id']);
if (!$template) {
    echo '<div class="alert alert-danger">Template not found</div>';
    exit();
}

// Sample variables for preview
$sampleVariables = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'date' => date('F j, Y'),
    'program_title' => 'Digital Skills Training',
    'application_id' => 'APP-2024-001',
    'submission_date' => date('F j, Y'),
    'applicant_name' => 'Jane Smith',
    'next_steps' => 'Please check your email for further instructions and join our orientation session next Monday.',
    'participant_name' => 'Alice Johnson',
    'event_title' => 'Web Development Workshop',
    'event_date' => date('F j, Y', strtotime('+7 days')),
    'event_time' => '2:00 PM - 5:00 PM',
    'event_location' => 'Community Center Room A',
    'contact_name' => 'Michael Brown',
    'message' => 'I am interested in learning more about your digital literacy programs. Could you please send me more information about upcoming courses?'
];

try {
    $rendered = $emailTemplate->renderTemplate($template['name'], $sampleVariables);
    ?>
    <div class="mb-3">
        <h6>Subject:</h6>
        <div class="p-2 bg-light border rounded">
            <?php echo htmlspecialchars($rendered['subject']); ?>
        </div>
    </div>
    
    <div class="mb-3">
        <h6>Email Body:</h6>
        <div class="p-3 bg-light border rounded" style="max-height: 400px; overflow-y: auto;">
            <?php echo $rendered['body']; ?>
        </div>
    </div>
    
    <div class="small text-muted">
        <strong>Note:</strong> This preview uses sample data. Actual emails will use real variables passed when sending.
    </div>
    <?php
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error rendering template: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
