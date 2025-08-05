<?php
require_once '../../includes/config.php';
header('Content-Type: application/json');

$response = ["status" => "error", "message" => "An unexpected error occurred."];

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name'], $data['email'], $data['phone'], $data['program_id'], $data['mode'])) {
        throw new Exception('Missing required fields.');
    }

    $stmt = $db->prepare("INSERT INTO applications (name, email, phone, program_id, mode, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['program_id'],
        $data['mode'],
    ]);

    $response = ["status" => "success", "message" => "Application submitted successfully."];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

