<?php
require_once '../../shared/Core/Database.php';
require_once '../../shared/Core/Utilities.php';
require_once '../../shared/Core/ApiConfig.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = [];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed. Use POST.'
        ];
        echo json_encode($response);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Fallback to POST data if no JSON
    if (!$input) {
        $input = $_POST;
    }

    $event_id = (int)($input['event_id'] ?? 0);
    $full_name = trim($input['full_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $organization = trim($input['organization'] ?? '');
    $special_requirements = trim($input['special_requirements'] ?? '');

    // Validation
    $errors = [];
    if (empty($event_id)) $errors[] = 'Event ID is required';
    if (empty($full_name)) $errors[] = 'Full name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email address is required';
    }
    if (empty($phone)) $errors[] = 'Phone number is required';

    if (!empty($errors)) {
        http_response_code(400);
        $response = [
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors
        ];
        echo json_encode($response);
        exit;
    }

    $db = (new Database())->connect();

    // Check if event exists and is available for registration
    $event_stmt = $db->prepare("
        SELECT e.*, 
               (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status IN ('confirmed', 'pending')) as current_registrations
        FROM events e 
        WHERE e.id = ? AND e.event_date >= CURDATE() AND e.deleted_at IS NULL
    ");
    $event_stmt->execute([$event_id]);
    $event = $event_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        http_response_code(404);
        $response = [
            'status' => 'error',
            'message' => 'Event not found or registration has closed'
        ];
        echo json_encode($response);
        exit;
    }

    // Check capacity
    if ($event['max_attendees'] && $event['current_registrations'] >= $event['max_attendees']) {
        http_response_code(409);
        $response = [
            'status' => 'error',
            'message' => 'This event is fully booked'
        ];
        echo json_encode($response);
        exit;
    }

    // Check if already registered
    $check_stmt = $db->prepare("SELECT COUNT(*) FROM event_registrations WHERE email = ? AND event_id = ?");
    $check_stmt->execute([$email, $event_id]);
    
    if ($check_stmt->fetchColumn() > 0) {
        http_response_code(409);
        $response = [
            'status' => 'error',
            'message' => 'You are already registered for this event'
        ];
        echo json_encode($response);
        exit;
    }

    // Split full name
    $name_parts = explode(' ', $full_name, 2);
    $first_name = $name_parts[0];
    $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

    // Insert registration
    $stmt = $db->prepare("
        INSERT INTO event_registrations 
        (event_id, full_name, first_name, last_name, email, phone, organization, special_requirements, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW())
    ");
    
    if ($stmt->execute([$event_id, $full_name, $first_name, $last_name, $email, $phone, $organization, $special_requirements])) {
        $registration_id = $db->lastInsertId();
        
        // Log the activity for real-time notifications
        Utilities::logActivity('event_registration', $registration_id, "New event registration: {$full_name} for {$event['title']}");
        
        http_response_code(201);
        $response = [
            'status' => 'success',
            'message' => 'Registration successful! You will receive a confirmation email shortly.',
            'data' => [
                'registration_id' => $registration_id,
                'event' => [
                    'id' => $event['id'],
                    'title' => $event['title'],
                    'date' => $event['event_date'],
                    'location' => $event['location']
                ]
            ]
        ];
    } else {
        http_response_code(500);
        $response = [
            'status' => 'error',
            'message' => 'Failed to process registration. Please try again.'
        ];
    }

} catch (Exception $e) {
    error_log("Event registration API error: " . $e->getMessage());
    http_response_code(500);
    $response = [
        'status' => 'error',
        'message' => 'An internal error occurred. Please try again later.'
    ];
}

echo json_encode($response);
?>
