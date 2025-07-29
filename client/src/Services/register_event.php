<?php
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

// Verify CSRF token
if (!Utilities::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Validate required fields
$requiredFields = ['name', 'email', 'phone', 'event_id'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Sanitize inputs
$data = Database::sanitize($_POST);

// Validate email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Check if event exists
$stmt = $db->prepare("SELECT id FROM events WHERE id = ?");
$stmt->execute([$data['event_id']]);
if (!$stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid event']);
    exit;
}

try {
    // Save registration
    $stmt = $db->prepare("
        INSERT INTO event_registrations (
            event_id, full_name, email, phone, organization, 
            subscribe_newsletter, ip_address
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['event_id'],
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['organization'] ?? null,
        isset($data['newsletter']) ? 1 : 0,
        $_SERVER['REMOTE_ADDR']
    ]);

    // TODO: Send confirmation email

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful'
    ]);
} catch (PDOException $e) {
    error_log("Event Registration Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to register. Please try again.']);
}
?>