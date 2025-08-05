<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\NotificationService;

class NewsletterController
{
    private $db;
    private $notificationService;

    public function __construct()
    {
        $this->db = (new Database())->connect();
        $this->notificationService = new NotificationService();
    }

    public function subscribe()
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
        if (!isset($input['email']) || empty(trim($input['email']))) {
            http_response_code(400);
            echo json_encode(['error' => 'Email is required']);
            return;
        }

        $email = trim($input['email']);
        $name = trim($input['name'] ?? '');

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            return;
        }

        try {
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
            $stmt->execute([$email]);
            $existing = $stmt->fetch();

            if ($existing) {
                if ($existing['status'] === 'subscribed') {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Email already subscribed'
                    ]);
                    return;
                } else {
                    // Reactivate subscription
                    $stmt = $this->db->prepare("
                        UPDATE newsletter_subscribers 
                        SET status = 'subscribed', subscribed_at = NOW(), updated_at = NOW()
                        WHERE email = ?
                    ");
                    $stmt->execute([$email]);
                    $subscriberId = $existing['id'];
                }
            } else {
                // Create new subscription
                $stmt = $this->db->prepare("
                    INSERT INTO newsletter_subscribers (email, name, status, subscribed_at, created_at, updated_at)
                    VALUES (?, ?, 'subscribed', NOW(), NOW(), NOW())
                ");
                $stmt->execute([$email, $name]);
                $subscriberId = $this->db->lastInsertId();
            }

            // Send real-time notification to admin
            $this->notificationService->sendNotification([
                'type' => 'newsletter_subscription',
                'title' => 'New Newsletter Subscription',
                'message' => ($name ?: $email) . ' subscribed to the newsletter',
                'data' => [
                    'subscriber_id' => $subscriberId,
                    'email' => $email,
                    'name' => $name
                ]
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Successfully subscribed to newsletter',
                'subscriber_id' => $subscriberId
            ]);

        } catch (\PDOException $e) {
            error_log('Newsletter subscription error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database error occurred']);
        }
    }
}
