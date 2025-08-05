<?php
require_once '../includes/config.php';

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = (int)($_POST['event_id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $special_requirements = trim($_POST['special_requirements'] ?? '');

    // Basic validation
    $errors = [];
    if (empty($event_id)) $errors[] = 'Event is required';
    if (empty($full_name)) $errors[] = 'Full name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($phone)) $errors[] = 'Phone number is required';

    if (empty($errors)) {
        try {
            // Check if event exists and is not full
            $event_stmt = $db->prepare("
                SELECT e.*, 
                       (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = 'confirmed') as current_registrations
                FROM events e 
                WHERE e.id = ? AND e.event_date >= CURDATE() AND e.deleted_at IS NULL
            ");
            $event_stmt->execute([$event_id]);
            $event = $event_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event) {
                $response['status'] = 'error';
                $response['message'] = 'Event not found or registration has closed.';
            } else if ($event['max_participants'] && $event['current_registrations'] >= $event['max_participants']) {
                $response['status'] = 'error';
                $response['message'] = 'Sorry, this event is fully booked.';
            } else {
                // Check if already registered
                $check_stmt = $db->prepare("SELECT COUNT(*) FROM event_registrations WHERE email = ? AND event_id = ?");
                $check_stmt->execute([$email, $event_id]);
                
                if ($check_stmt->fetchColumn() > 0) {
                    $response['status'] = 'error';
                    $response['message'] = 'You are already registered for this event.';
                } else {
                    // Split full name into first and last name
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
                        
                        $response['status'] = 'success';
                        $response['message'] = 'Registration successful! You will receive a confirmation email shortly.';
                        $response['registration_id'] = $registration_id;
                        $response['event_info'] = [
                            'title' => $event['title'],
                            'date' => $event['event_date'],
                            'time' => $event['event_time'],
                            'location' => $event['location']
                        ];
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Error processing registration. Please try again later.';
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Event registration failed: " . $e->getMessage());
            $response['status'] = 'error';
            $response['message'] = 'Database error. Please try again later.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Please fix the following errors:';
        $response['errors'] = $errors;
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method. Please use POST.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
