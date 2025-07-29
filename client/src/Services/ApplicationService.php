<?php
require_once __DIR__ . '/../Models/ApplicationModel.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

class ApplicationService {
    private $model;

    public function __construct($db) {
        $this->model = new ApplicationModel($db);
    }

    public function submitApplication($data) {
        // Validate required fields
        $requiredFields = [
            'program_id', 'first_name', 'last_name', 'email',
            'phone', 'address', 'education', 'motivation'
        ];

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
            'program_id' => (int)$data['program_id'],
            'first_name' => htmlspecialchars(trim($data['first_name'])),
            'last_name' => htmlspecialchars(trim($data['last_name'])),
            'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            'phone' => htmlspecialchars(trim($data['phone'])),
            'address' => htmlspecialchars(trim($data['address'])),
            'education' => htmlspecialchars(trim($data['education'])),
            'motivation' => htmlspecialchars(trim($data['motivation']))
        ];

        // Optional fields
        if (!empty($data['experience'])) {
            $sanitizedData['experience'] = htmlspecialchars(trim($data['experience']));
        }

$result = $this->model->submitApplication($sanitizedData);
        
        // Log activity
        if ($result) {
            Utilities::logActivity([
                'user_id' => null, // or replace with actual user ID if available
                'action' => 'Submit Application',
                'entity_type' => 'Application',
                'entity_id' => $result, // retrieve the application ID from the result if available
                'details' => json_encode($sanitizedData),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            ]);
        }

        return $result;
    }

    public function getApplicationById($id) {
        return $this->model->getApplicationById($id);
    }
}
?>