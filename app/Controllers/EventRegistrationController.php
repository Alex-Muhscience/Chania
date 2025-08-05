<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\NotificationService;

class EventRegistrationController
{
    private $db;
    private $notificationService;

    public function __construct()
    {
        $this->db = (new Database())->connect();
        $this->notificationService = new NotificationService();
    }

    public function register()
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
        $requiredFields = ['event_id', 'name', 'email', 'phone'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                http_response_code(400);
                echo json_encode(['error' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
                return;
            }
        }

        $eventId = (int)$input['event_id'];
        $name = trim($input['name']);
        $email = trim($input['email']);
        $phone = trim($input['phone']);

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            return;
        }

        try {
            // Check if event exists
            $stmt = $this->db->prepare("SELECT id, title FROM events WHERE id = ? AND is_active = 1");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch();

            if (!$event) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid event selected']);
                return;
            }

            // Check for existing registration
            $stmt = $this->db->prepare("SELECT id FROM event_registrations WHERE email = ? AND event_id = ?");
            $stmt->execute([$email, $eventId]);

            if ($stmt->fetch()) {
                echo json_encode([
                    'success' => false,
                    'message' => 'You have already registered for this event'
                ]);
                return;
            }

            // Create new registration
            $stmt = $this->db->prepare("
                INSERT INTO event_registrations (
                    event_id, name, email, phone, status,
                    registered_at, created_at, updated_at
                ) VALUES (?, ?, ?, ?, 'pending', NOW(), NOW(), NOW())
            ");
            
            $stmt->execute([$eventId, $name, $email, $phone]);
            $registrationId = $this->db->lastInsertId();

            // Send real-time notification to admin
            $this->notificationService->sendNotification([
                'type' => 'event_registration',
                'title' => 'New Event Registration',
                'message' => $name . ' registered for ' . $event['title'],
                'data' => [
                    'registration_id' => $registrationId,
                    'event_id' => $eventId,
                    'event_title' => $event['title'],
                    'registrant_name' => $name,
                    'email' => $email
                ]
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Your registration has been submitted successfully',
                'registration_id' => $registrationId
            ]);

        } catch (\PDOException $e) {
            error_log('Event registration error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database error occurred']);
        }
    }
}
