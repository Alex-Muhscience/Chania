<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\NotificationService;

class CourseApplicationController
{
    private $db;
    private $notificationService;

    public function __construct()
    {
        $this->db = (new Database())->connect();
        $this->notificationService = new NotificationService();
    }

    public function apply()
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
        $requiredFields = ['program_id', 'first_name', 'last_name', 'email', 'phone'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                http_response_code(400);
                echo json_encode(['error' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
                return;
            }
        }

        $programId = (int)$input['program_id'];
        $firstName = trim($input['first_name']);
        $lastName = trim($input['last_name']);
        $email = trim($input['email']);
        $phone = trim($input['phone']);
        $address = trim($input['address'] ?? '');
        $reason = trim($input['reason'] ?? '');

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            return;
        }

        try {
            // Check if program exists
            $stmt = $this->db->prepare("SELECT id, title FROM programs WHERE id = ? AND is_active = 1");
            $stmt->execute([$programId]);
            $program = $stmt->fetch();

            if (!$program) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid program selected']);
                return;
            }

            // Check for duplicate application
            $stmt = $this->db->prepare("
                SELECT id FROM applications 
                WHERE email = ? AND program_id = ? AND status != 'rejected'
            ");
            $stmt->execute([$email, $programId]);
            
            if ($stmt->fetch()) {
                echo json_encode([
                    'success' => false,
                    'message' => 'You have already applied for this program'
                ]);
                return;
            }

            // Create new application
            $stmt = $this->db->prepare("
                INSERT INTO applications (
                    program_id, first_name, last_name, email, phone, address, reason,
                    status, submitted_at, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW(), NOW())
            ");
            
            $stmt->execute([
                $programId, $firstName, $lastName, $email, $phone, $address, $reason
            ]);
            
            $applicationId = $this->db->lastInsertId();

            // Send real-time notification to admin
            $this->notificationService->sendNotification([
                'type' => 'course_application',
                'title' => 'New Course Application',
                'message' => $firstName . ' ' . $lastName . ' applied for ' . $program['title'],
                'data' => [
                    'application_id' => $applicationId,
                    'program_id' => $programId,
                    'program_title' => $program['title'],
                    'applicant_name' => $firstName . ' ' . $lastName,
                    'email' => $email
                ]
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Application submitted successfully',
                'application_id' => $applicationId
            ]);

        } catch (\PDOException $e) {
            error_log('Course application error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database error occurred']);
        }
    }
}
