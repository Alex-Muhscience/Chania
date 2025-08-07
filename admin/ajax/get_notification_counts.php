<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';

// Configure session settings before starting session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

try {
    $db = (new Database())->connect();
    
    // Get current notification counts from database
    $counts = [];
    
    // Pending applications count
    $stmt = $db->query("SELECT COUNT(*) FROM applications WHERE status = 'pending' AND deleted_at IS NULL");
    $counts['applications'] = (int) $stmt->fetchColumn();
    
    // Unread contacts count
    $stmt = $db->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0");
    $counts['contacts'] = (int) $stmt->fetchColumn();
    
    // Event registrations count (all registered)
    $stmt = $db->query("SELECT COUNT(*) FROM event_registrations WHERE status = 'registered'");
    $counts['event_registrations'] = (int) $stmt->fetchColumn();
    
    // Recent newsletter subscriptions (last 24 hours)
    $stmt = $db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status = 'subscribed'");
    $counts['newsletter_subscriptions'] = (int) $stmt->fetchColumn();
    
    // Total notifications
    $counts['total'] = $counts['applications'] + $counts['contacts'] + $counts['event_registrations'] + $counts['newsletter_subscriptions'];
    
    echo json_encode([
        'success' => true,
        'counts' => $counts
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch notification counts',
        'message' => $e->getMessage()
    ]);
}
?>
