<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Media.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Ensure user is logged in
Utilities::requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

try {
    $media = new Media($db);
    
    // Get additional form data
    $alt_text = $_POST['alt_text'] ?? '';
    $description = $_POST['description'] ?? '';
    $folder = $_POST['folder'] ?? 'general';
    
    // Create upload path with folder
    $upload_path = __DIR__ . '/../../uploads/media/' . $folder . '/';
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
    
    // Upload the file
    $mediaId = $media->upload($_FILES['file'], $upload_path);
    
    // Update the media record with additional information
    if ($mediaId && ($alt_text || $description)) {
        $stmt = $db->prepare("UPDATE media_library SET alt_text = ?, description = ?, folder = ? WHERE id = ?");
        $stmt->execute([$alt_text, $description, $folder, $mediaId]);
    }
    
    // Get the uploaded file info
    $uploadedFile = $media->getById($mediaId);
    
    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully!',
        'file' => $uploadedFile
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error uploading file: ' . $e->getMessage()
    ]);
}
?>
