<?php
/**
 * Contact Form API Endpoint
 * Handles contact form submissions from the client
 */

require_once __DIR__ . '/../client/includes/config.php';
require_once __DIR__ . '/../shared/Core/Database.php';
require_once __DIR__ . '/../shared/Core/ApiConfig.php';

header('Content-Type: application/json');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(ApiConfig::createResponse(
        ApiConfig::STATUS_ERROR,
        'Method not allowed. Please use POST.',
        null,
        'METHOD_NOT_ALLOWED'
    ));
    exit;
}

$inputData = json_decode(file_get_contents('php://input'), true);

// Validate input manually since it's JSON
if (!$inputData) {
    http_response_code(400);
    echo json_encode(ApiConfig::createResponse(
        ApiConfig::STATUS_ERROR,
        'Invalid JSON data provided.',
        null,
        'INVALID_JSON'
    ));
    exit;
}

// Check required fields
$requiredFields = ['name', 'email', 'subject', 'message'];
$missingFields = [];
foreach ($requiredFields as $field) {
    if (!isset($inputData[$field]) || empty(trim($inputData[$field]))) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    http_response_code(400);
    echo json_encode(ApiConfig::createResponse(
        ApiConfig::STATUS_ERROR,
        'Missing required fields: ' . implode(', ', $missingFields),
        null,
        'MISSING_FIELDS'
    ));
    exit;
}

// Validate email format
if (!filter_var(trim($inputData['email']), FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(ApiConfig::createResponse(
        ApiConfig::STATUS_ERROR,
        'Please provide a valid email address.',
        null,
        'INVALID_EMAIL'
    ));
    exit;
}

$name = trim($inputData['name']);
$email = trim($inputData['email']);
$phone = trim($inputData['phone'] ?? '');
$inquiryType = trim($inputData['inquiry_type'] ?? '');
$subject = trim($inputData['subject']);
$message = trim($inputData['message']);
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

try {
    $db = (new Database())->connect();

    // Insert contact inquiry
    $stmt = $db->prepare("INSERT INTO contacts (name, email, phone, inquiry_type, subject, message, ip_address, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $email, $phone, $inquiryType, $subject, $message, $ipAddress]);

    // Get the contact ID for logging
    $contactId = $db->lastInsertId();
    
    // Log activity for admin synchronization
    ApiConfig::logActivity($db, 'CONTACT_SUBMIT', 'contacts', $contactId, "New contact inquiry from {$name} ({$email})");
    
    // Send response
    echo json_encode(ApiConfig::createResponse(
        ApiConfig::STATUS_SUCCESS,
        'Thank you for your inquiry. We will get back to you soon!'
    ));

} catch (Exception $e) {
    error_log('Contact submission error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(ApiConfig::createResponse(
        ApiConfig::STATUS_ERROR,
        'An error occurred while submitting your contact. Please try again later.',
        null,
        'CONTACT_ERROR'
    ));
}
?>
