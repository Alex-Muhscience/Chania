<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\NotificationService;

class ContactController
{
    private $db;
    private $notificationService;

    public function __construct()
    {
        $this->db = (new Database())->connect();
        $this->notificationService = new NotificationService();
    }

    public function submit()
    {
        header('Content-Type: application/json');
        
        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Get input data
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $requiredFields = ['name', 'email', 'subject', 'message'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                http_response_code(400);
                echo json_encode(['error' => ucfirst($field) . ' is required']);
                return;
            }
        }

        $name = trim($input['name']);
        $email = trim($input['email']);
        $phone = trim($input['phone'] ?? '');
        $subject = trim($input['subject']);
        $message = trim($input['message']);
        $inquiryType = trim($input['inquiry_type'] ?? 'general');

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            return;
        }

        try {
            // Create new contact inquiry
            $stmt = $this->db->prepare("
                INSERT INTO contact_inquiries (
                    name, email, phone, subject, message, inquiry_type,
                    status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'new', NOW(), NOW())
            ");
            
            $stmt->execute([$name, $email, $phone, $subject, $message, $inquiryType]);
            $contactId = $this->db->lastInsertId();

            // Send real-time notification to admin
            $this->notificationService->sendNotification([
                'type' => 'contact_inquiry',
                'title' => 'New Contact Inquiry',
                'message' => $name . ' sent a message: ' . substr($subject, 0, 50) . '...',
                'data' => [
                    'contact_id' => $contactId,
                    'name' => $name,
                    'email' => $email,
                    'subject' => $subject,
                    'inquiry_type' => $inquiryType
                ]
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Your message has been sent successfully. We will get back to you soon.',
                'contact_id' => $contactId
            ]);

        } catch (\PDOException $e) {
            error_log('Contact submission error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database error occurred']);
        }
    }
}
