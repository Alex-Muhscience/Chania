<?php
require_once __DIR__ . '/../../shared/Core/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Only GET requests allowed']);
    exit;
}

$program_id = $_GET['program_id'] ?? '';

if (empty($program_id)) {
    echo json_encode(['success' => false, 'message' => 'Program ID is required']);
    exit;
}

try {
    $db = (new Database())->connect();
    
    $stmt = $db->prepare("
        SELECT id, title, start_date, end_date, max_participants, online_fee, physical_fee
        FROM program_schedules 
        WHERE program_id = ? AND is_active = 1 AND deleted_at IS NULL 
        ORDER BY start_date ASC
    ");
    $stmt->execute([$program_id]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates for display
    foreach ($schedules as &$schedule) {
        $schedule['start_date'] = date('M j, Y', strtotime($schedule['start_date']));
        $schedule['end_date'] = date('M j, Y', strtotime($schedule['end_date']));
    }
    
    echo json_encode([
        'success' => true,
        'schedules' => $schedules
    ]);
    
} catch (Exception $e) {
    error_log('Get schedules error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading schedules'
    ]);
}
?>
