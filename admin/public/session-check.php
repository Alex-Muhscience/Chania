<?php
require_once __DIR__ . '/../../shared/Core/SessionManager.php';
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'check':
        // Check session status
        $sessionInfo = SessionManager::getSessionInfo();
        if ($sessionInfo) {
            echo json_encode([
                'status' => 'active',
                'remaining_time' => $sessionInfo['remaining_time'],
                'timeout_warning' => $sessionInfo['timeout_warning'],
                'user' => [
                    'id' => $sessionInfo['user_id'],
                    'username' => $sessionInfo['username'],
                    'role' => $sessionInfo['role']
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'expired',
                'redirect' => BASE_URL . '/admin/public/login.php'
            ]);
        }
        break;
        
    case 'extend':
        // Extend session
        $remainingTime = SessionManager::extendSession();
        if ($remainingTime > 0) {
            echo json_encode([
                'status' => 'extended',
                'remaining_time' => $remainingTime
            ]);
        } else {
            echo json_encode([
                'status' => 'expired',
                'redirect' => BASE_URL . '/admin/public/login.php'
            ]);
        }
        break;
        
    case 'logout':
        // Manual logout
        SessionManager::logout('Manual logout via AJAX');
        echo json_encode([
            'status' => 'logged_out',
            'redirect' => BASE_URL . '/admin/public/login.php'
        ]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
