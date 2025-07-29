<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Set JSON response header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Validate required fields
    $required_fields = ['program_id', 'program_title', 'full_name', 'email', 'phone', 'country'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
        ]);
        exit;
    }
    
    // Validate email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Check if terms are accepted
    if (empty($_POST['terms_accepted'])) {
        echo json_encode(['success' => false, 'message' => 'You must agree to the terms and conditions']);
        exit;
    }
    
    // Verify program exists and is active
    $program_sql = "SELECT id, title FROM programs WHERE id = ? AND is_active = 1 AND deleted_at IS NULL";
    $program_stmt = $db->prepare($program_sql);
    $program_stmt->execute([$_POST['program_id']]);
    $program = $program_stmt->fetch();
    
    if (!$program) {
        echo json_encode(['success' => false, 'message' => 'Program not found or no longer available']);
        exit;
    }
    
    // Check if session is valid (if provided)
    $session_id = !empty($_POST['preferred_session']) ? (int)$_POST['preferred_session'] : null;
    if ($session_id) {
        $session_sql = "SELECT id, start_date, location, is_open_for_registration 
                        FROM program_sessions 
                        WHERE id = ? AND program_id = ? AND is_active = 1 AND deleted_at IS NULL";
        $session_stmt = $db->prepare($session_sql);
        $session_stmt->execute([$session_id, $_POST['program_id']]);
        $session = $session_stmt->fetch();
        
        if (!$session || !$session['is_open_for_registration']) {
            echo json_encode(['success' => false, 'message' => 'Selected session is not available for registration']);
            exit;
        }
    }
    
    // Sanitize input data - mapping to actual database columns
    $data = [
        'program_id' => (int)$_POST['program_id'],
        'first_name' => trim(explode(' ', $_POST['full_name'])[0]), // Extract first name
        'last_name' => trim(str_replace(explode(' ', $_POST['full_name'])[0], '', $_POST['full_name'])), // Extract last name
        'email' => trim(strtolower($_POST['email'])),
        'phone' => trim($_POST['phone']),
        'country' => trim($_POST['country']),
        'address' => !empty($_POST['address']) ? trim($_POST['address']) : 'Not provided',
        'education_details' => !empty($_POST['education_details']) ? trim($_POST['education_details']) : 'Not provided',
        'motivation' => !empty($_POST['motivation']) ? trim($_POST['motivation']) : '',
        'status' => 'pending',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    // Check for duplicate applications (same email + program in last 24 hours)
    $duplicate_sql = "SELECT id FROM applications 
                     WHERE email = ? AND program_id = ? 
                     AND submitted_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $duplicate_stmt = $db->prepare($duplicate_sql);
    $duplicate_stmt->execute([$data['email'], $data['program_id']]);
    
    if ($duplicate_stmt->fetch()) {
        echo json_encode([
            'success' => false, 
            'message' => 'You have already applied for this program within the last 24 hours'
        ]);
        exit;
    }
    
    // Generate application number
    $application_number = 'APP-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Insert application using actual database structure
    $insert_sql = "INSERT INTO applications 
                   (program_id, application_number, first_name, last_name, email, phone, country, 
                    address, education_details, motivation, status, ip_address, user_agent) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    
    try {
        // Log the SQL and data for debugging
        error_log("DEBUG: About to prepare SQL: " . $insert_sql);
        error_log("DEBUG: Data to insert: " . print_r($data, true));
        
        $insert_stmt = $db->prepare($insert_sql);
        if (!$insert_stmt) {
            $error_info = $db->errorInfo();
            error_log("Failed to prepare INSERT statement: " . print_r($error_info, true));
            throw new Exception('Failed to prepare application statement');
        }
        
        error_log("DEBUG: Statement prepared successfully");
        
        $execute_params = [
            $data['program_id'],
            $application_number,
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['country'],
            $data['address'],
            $data['education_details'],
            $data['motivation'],
            $data['status'],
            $data['ip_address'],
            $data['user_agent']
        ];
        
        error_log("DEBUG: Execute params: " . print_r($execute_params, true));
        
        $success = $insert_stmt->execute($execute_params);
        
        if (!$success) {
            $error_info = $insert_stmt->errorInfo();
            error_log("Failed to execute INSERT: " . print_r($error_info, true));
            error_log("Data being inserted: " . print_r($data, true));
            // Also output to response for immediate debugging
            echo json_encode([
                'success' => false,
                'message' => 'Database execution failed',
                'debug' => [
                    'error_info' => $error_info,
                    'data' => $data,
                    'sql' => $insert_sql
                ]
            ]);
            exit;
        }
        
        error_log("DEBUG: INSERT executed successfully");
        
    } catch (PDOException $e) {
        error_log("PDO Exception during INSERT: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        error_log("Data being inserted: " . print_r($data, true));
        // Also output to response for immediate debugging
        echo json_encode([
            'success' => false,
            'message' => 'PDO Exception occurred',
            'debug' => [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'data' => $data,
                'sql' => $insert_sql
            ]
        ]);
        exit;
    }
    
    $application_id = $db->lastInsertId();
    
    // Send notification email (optional - implement if needed)
    // sendApplicationNotification($data, $application_id);
    
    // Log successful application
    error_log("New program application submitted: ID {$application_id}, Email: {$data['email']}, Program ID: {$data['program_id']}");
    
    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully!',
        'application_id' => $application_id
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in program application: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred: ' . $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
} catch (Exception $e) {
    error_log("Error in program application: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}

// Optional: Function to send notification emails
function sendApplicationNotification($data, $application_id) {
    // Implement email notification logic here
    // You could send emails to:
    // 1. The applicant (confirmation)
    // 2. Admin/program coordinator (new application alert)
    
    $admin_email = "admin@skillsforafrica.com"; // Configure this
    $subject = "New Program Application: " . $data['program_title'];
    
    $message = "
    New application received:
    
    Application ID: {$application_id}
    Program: {$data['program_title']}
    Applicant: {$data['full_name']}
    Email: {$data['email']}
    Phone: {$data['phone']}
    Country: {$data['country']}
    Attendance Type: " . ucfirst($data['attendance_type']) . "
    Experience Level: " . ucfirst($data['experience_level']) . "
    
    Motivation:
    {$data['motivation']}
    
    Application Date: {$data['application_date']}
    ";
    
    // Use mail() function or preferred email service
    // mail($admin_email, $subject, $message);
}
?>
