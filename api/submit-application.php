<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['program_id', 'name', 'email', 'phone', 'country', 'preferred_session', 'experience_level', 'motivation'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Missing required fields',
        'missing_fields' => $missing_fields
    ]);
    exit();
}

// Validate email format
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit();
}

// Validate terms acceptance
if (empty($input['terms_accepted']) || $input['terms_accepted'] !== true) {
    http_response_code(400);
    echo json_encode(['error' => 'Terms and conditions must be accepted']);
    exit();
}

try {
    // Database connection
    $pdo = new PDO('mysql:host=localhost;dbname=chania_db;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Check if program exists
    $stmt = $pdo->prepare('SELECT id, title FROM programs WHERE id = ? AND status = "active"');
    $stmt->execute([$input['program_id']]);
    $program = $stmt->fetch();

    if (!$program) {
        http_response_code(404);
        echo json_encode(['error' => 'Program not found or not active']);
        exit();
    }

    // Check for duplicate applications (same email for same program)
    $stmt = $pdo->prepare('SELECT id FROM applications WHERE program_id = ? AND email = ?');
    $stmt->execute([$input['program_id'], $input['email']]);
    $existing_application = $stmt->fetch();

    if ($existing_application) {
        http_response_code(409);
        echo json_encode(['error' => 'You have already applied for this program']);
        exit();
    }

    // Generate application reference number
    $reference_number = 'APP-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    // Split name into first and last name
    $name_parts = explode(' ', trim($input['name']), 2);
    $first_name = $name_parts[0];
    $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

    // Insert application using actual database structure
    $stmt = $pdo->prepare('
        INSERT INTO applications (
            program_id, application_number, first_name, last_name, email, phone, country, 
            address, education_details, motivation, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending")
    ');

    $stmt->execute([
        $input['program_id'],
        $reference_number,
        $first_name,
        $last_name,
        $input['email'],
        $input['phone'],
        $input['country'],
        'Not provided', // address
        'Not provided', // education_details  
        $input['motivation']
    ]);

    $application_id = $pdo->lastInsertId();

    // Send confirmation email (basic implementation)
    $to = $input['email'];
    $subject = 'Program Application Confirmation - ' . $program['title'];
    $message = "
        Dear {$input['name']},
        
        Thank you for your application to the {$program['title']} program.
        
        Application Details:
        - Reference Number: {$reference_number}
        - Program: {$program['title']}
        - Preferred Session: {$input['preferred_session']}
        - Application Date: " . date('F j, Y') . "
        
        Your application is currently being reviewed. We will contact you within 2-3 business days with further information.
        
        If you have any questions, please contact us at info@chania.edu.
        
        Best regards,
        Chania Admissions Team
    ";
    
    $headers = [
        'From: noreply@chania.edu',
        'Reply-To: info@chania.edu',
        'Content-Type: text/plain; charset=UTF-8'
    ];

    // Attempt to send email (suppress errors in case mail server isn't configured)
    @mail($to, $subject, $message, implode("\r\n", $headers));

    // Log the application for admin notification
    error_log("New program application: ID {$application_id}, Reference {$reference_number}, Program: {$program['title']}, Applicant: {$input['name']} ({$input['email']})");

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully',
        'application_id' => $application_id,
        'reference_number' => $reference_number,
        'program_title' => $program['title']
    ]);

} catch (PDOException $e) {
    error_log('Database error in application submission: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('Error in application submission: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while processing your application']);
}
?>
