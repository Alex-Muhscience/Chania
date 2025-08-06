<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Media.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Ensure user is logged in
Utilities::requireLogin();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid media ID']);
    exit;
}

try {
    $media = new Media($db);
    
    switch ($action) {
        case 'view':
            $mediaFile = $media->getById($id);
            if (!$mediaFile) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Media file not found']);
                exit;
            }
            
            // Get uploader information
            $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$mediaFile['uploaded_by']]);
            $uploader = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'file' => array_merge($mediaFile, [
                    'uploader_name' => $uploader['username'] ?? 'Unknown',
                    'formatted_size' => $media->formatFileSize($mediaFile['file_size']),
                    'upload_date' => date('M j, Y \a\t g:i A', strtotime($mediaFile['created_at']))
                ])
            ]);
            break;
            
        case 'delete':
            if ($media->delete($id)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Media file deleted successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete media file'
                ]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
