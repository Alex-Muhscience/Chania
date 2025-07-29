<?php
require_once __DIR__ . '/../../shared/Core/Utilities.php';
class ContactService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function submitContactForm($data) {
        // Validate required fields
        $requiredFields = ['name', 'email', 'subject', 'message'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        // Sanitize data
        $sanitizedData = [
            'name' => htmlspecialchars(trim($data['name'])),
            'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            'subject' => htmlspecialchars(trim($data['subject'])),
            'message' => htmlspecialchars(trim($data['message']))
        ];

        // Save to database
        $stmt = $this->db->prepare("
            INSERT INTO contacts (name, email, subject, message, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");

$result = $stmt->execute([
            $sanitizedData['name'],
            $sanitizedData['email'],
            $sanitizedData['subject'],
            $sanitizedData['message'],
            $_SERVER['REMOTE_ADDR']
        ]);

        // Log activity
        if ($result) {
            Utilities::logActivity([
                'user_id' => null, // or replace with actual user ID if available
                'action' => 'Submit Contact Form',
                'entity_type' => 'Contact',
                'details' => json_encode($sanitizedData),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            ]);
        }

        return $result;
}
?>