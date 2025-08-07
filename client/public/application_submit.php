<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
    exit;
}

// Get form data
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$program_id = $_POST['program_id'] ?? '';
$mode = $_POST['mode'] ?? $_POST['delivery_mode'] ?? ''; // Handle both field names
$schedule_id = $_POST['schedule_id'] ?? null;
$address = $_POST['address'] ?? '';
$message = $_POST['motivation'] ?? $_POST['reason'] ?? '';
$newsletter_subscription = isset($_POST['newsletter']) && $_POST['newsletter'] === 'on';
$education_level = $_POST['education_level'] ?? '';
$current_occupation = $_POST['current_occupation'] ?? '';

// Validate required fields
$errors = [];
if (empty(trim($_POST['first_name']))) $errors[] = 'First name is required';
if (empty(trim($_POST['last_name']))) $errors[] = 'Last name is required';
if (empty($email)) $errors[] = 'Email is required';
if (empty($phone)) $errors[] = 'Phone number is required';
if (empty($program_id)) $errors[] = 'Program selection is required';
if (empty($mode)) $errors[] = 'Preferred mode is required';

if (!empty($errors)) {
echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Insert into database
try {
    // Start transaction
    $db->beginTransaction();
    
    // Generate application number
    $application_number = 'APP-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $db->prepare("
        INSERT INTO applications (
            program_id, schedule_id, preferred_delivery_mode, application_number, first_name, last_name, email, phone, 
            address, education_details, motivation, status, ip_address, submitted_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
    ");
    
    // Use the individual name fields
    $firstName = trim($first_name);
    $lastName = trim($last_name);
    
    // Prepare education details
    $education_details = $education_level ? "Education Level: $education_level" : 'Not provided';
    if ($current_occupation) {
        $education_details .= ", Current Occupation: $current_occupation";
    }
    
    $stmt->execute([
        $program_id,
        $schedule_id,
        $mode,
        $application_number,
        $firstName,
        $lastName,
        $email,
        $phone,
        $address,
        $education_details,
        $message, // motivation
        $_SERVER['REMOTE_ADDR'] ?? ''
    ]);
    
    // Handle newsletter subscription if requested
    if ($newsletter_subscription) {
        try {
            // Check if email already exists in newsletter
            $checkStmt = $db->prepare("
                SELECT id, status, deleted_at 
                FROM newsletter_subscribers 
                WHERE email = ?
            ");
            $checkStmt->execute([$email]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                if ($existing['deleted_at'] !== null || $existing['status'] !== 'subscribed') {
                    // Reactivate subscription
                    $updateStmt = $db->prepare("
                        UPDATE newsletter_subscribers 
                        SET status = 'subscribed', 
                            subscribed_at = NOW(), 
                            unsubscribed_at = NULL,
                            deleted_at = NULL,
                            ip_address = ?,
                            user_agent = ?,
                            source = 'application_form',
                            updated_at = NOW()
                        WHERE email = ?
                    ");
                    $updateStmt->execute([
                        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                        $email
                    ]);
                }
                // If already subscribed and active, do nothing
            } else {
                // New newsletter subscription
                $insertNewsletterStmt = $db->prepare("
                    INSERT INTO newsletter_subscribers 
                    (email, status, subscribed_at, ip_address, user_agent, source, created_at, updated_at) 
                    VALUES (?, 'subscribed', NOW(), ?, ?, 'application_form', NOW(), NOW())
                ");
                $insertNewsletterStmt->execute([
                    $email,
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]);
            }
        } catch (Exception $newsletterError) {
            // Log newsletter error but don't fail the application
            error_log('Newsletter subscription error during application: ' . $newsletterError->getMessage());
        }
    }
    
    // Commit transaction
    $db->commit();
    
    // Prepare success message
    $success_message = 'Your application has been submitted successfully! Application number: ' . $application_number;
    if ($newsletter_subscription) {
        $success_message .= ' You have also been subscribed to our newsletter for updates.';
    }
    
    echo json_encode(['success' => true, 'message' => $success_message]);
    exit;
    
} catch (Exception $e) {
    // Rollback transaction
    if ($db->inTransaction()) {
        $db->rollback();
    }
    
    error_log('Application submission error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'There was an error submitting your application. Please try again.']);
    exit;
}
