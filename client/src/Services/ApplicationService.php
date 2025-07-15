<?php
require_once __DIR__ . '/../Models/ApplicationModel.php';

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

        return $this->model->submitApplication($sanitizedData);
    }

    public function getApplicationById($id) {
        return $this->model->getApplicationById($id);
    }
}
?>