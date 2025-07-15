<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$format = $_GET['format'] ?? 'csv';
$search = $_GET['search'] ?? '';

try {
    $db = (new Database())->connect();

    $whereClause = '';
    $params = [];

    if ($search) {
        $whereClause = "WHERE title LIKE ? OR description LIKE ?";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm];
    }

    $stmt = $db->prepare("SELECT id, title, description, event_date, start_time, end_time, location, max_participants, registration_deadline, is_active, created_at FROM events $whereClause ORDER BY event_date DESC");
    $stmt->execute($params);
    $events = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Events export error: " . $e->getMessage());
    $_SESSION['error'] = "Error exporting events.";
    Utilities::redirect('/admin/events.php');
}

if ($format === 'csv') {
    $filename = 'events_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // CSV Header
    fputcsv($output, [
        'ID',
        'Title',
        'Description',
        'Event Date',
        'Start Time',
        'End Time',
        'Location',
        'Max Participants',
        'Registration Deadline',
        'Status',
        'Created At'
    ]);

    // CSV Data
    foreach ($events as $event) {
        fputcsv($output, [
            $event['id'],
            $event['title'],
            $event['description'],
            $event['event_date'],
            $event['start_time'],
            $event['end_time'],
            $event['location'],
            $event['max_participants'] ?: 'Unlimited',
            $event['registration_deadline'] ?: 'No deadline',
            $event['is_active'] ? 'Active' : 'Inactive',
            $event['created_at']
        ]);
    }

    fclose($output);
    exit;
}

elseif ($format === 'json') {
    $filename = 'events_' . date('Y-m-d') . '.json';

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo json_encode($events, JSON_PRETTY_PRINT);
    exit;
}

else {
    $_SESSION['error'] = "Invalid export format.";
    Utilities::redirect('/admin/events.php');
}
?>