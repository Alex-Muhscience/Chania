<?php
require_once __DIR__ . '/../../../shared/includes/config.php';

header('Content-Type: application/json');

// Verify CSRF token
if (!Utilities::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Validate required fields
$requiredFields = [
    'first_name', 'last_name', 'email', 'phone', 'address',
    'program_id', 'education', 'motivation', 'agree_terms'
];

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

// Check if the program exists
$stmt = $db->prepare("SELECT id FROM programs WHERE id = ?");
$stmt->execute([$data['program_id']]);
if (!$stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid program selected']);
    exit;
}

try {
    // Insert application
    $stmt = $db->prepare("
        INSERT INTO applications (
            program_id, first_name, last_name, email, phone, address,
            education, experience, motivation, ip_address
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['program_id'],
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'],
        $data['address'],
        $data['education'],
        $data['experience'] ?? null,
        $data['motivation'],
        $_SERVER['REMOTE_ADDR']
    ]);

    $applicationId = $db->lastInsertId();

    // TODO: Send email notifications (implement with PHPMailer)

    echo json_encode([
        'success' => true,
        'referenceId' => 'APP-' . str_pad($applicationId, 6, '0', STR_PAD_LEFT)
    ]);
} catch (PDOException $e) {
    error_log("Application Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to submit application. Please try again.']);
}
?>