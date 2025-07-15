<?php
class ResponseHandler {
    public static function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public static function errorResponse($message, $statusCode = 400) {
        self::jsonResponse([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }

    public static function successResponse($data = null, $message = '') {
        $response = ['success' => true];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data) {
            $response['data'] = $data;
        }

        self::jsonResponse($response);
    }

    public static function redirect($url, $statusCode = 303) {
        header("Location: $url", true, $statusCode);
        exit;
    }
}
?>