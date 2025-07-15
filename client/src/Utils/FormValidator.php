<?php
class FormValidator {
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validatePhone($phone) {
        // Basic phone number validation (adjust based on requirements)
        return preg_match('/^[0-9\-\+\(\)\s]{7,20}$/', $phone);
    }

    public static function validateRequired($fields, $data) {
        $errors = [];

        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[] = "The $field field is required.";
            }
        }

        return $errors;
    }

    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self, 'sanitizeInput'], $data);
        }

        return htmlspecialchars(strip_tags(trim($data)));
    }

    public static function validateCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        return true;
    }
}
?>