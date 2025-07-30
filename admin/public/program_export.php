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

    $stmt = $db->prepare("SELECT id, title, description, duration, fee, start_date, end_date, max_participants, is_active, created_at FROM programs $whereClause ORDER BY created_at DESC");
    $stmt->execute($params);
    $programs = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Programs export error: " . $e->getMessage());
    $_SESSION['error'] = "Error exporting programs.";
    Utilities::redirect('/admin/programs.php');
}

if ($format === 'csv') {
    $filename = 'programs_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // CSV Header
    fputcsv($output, [
        'ID',
        'Title',
        'Description',
        'Duration',
        'Fee',
        'Start Date',
        'End Date',
        'Max Participants',
        'Status',
        'Created At'
    ]);

    // CSV Data
    foreach ($programs as $program) {
        fputcsv($output, [
            $program['id'],
            $program['title'],
            $program['description'],
            $program['duration'],
            $program['fee'] ? '$' . number_format($program['fee'], 2) : 'Free',
            $program['start_date'] ?: 'Not set',
            $program['end_date'] ?: 'Not set',
            $program['max_participants'] ?: 'Unlimited',
            $program['is_active'] ? 'Active' : 'Inactive',
            $program['created_at']
        ]);
    }

    fclose($output);
    exit;
}

elseif ($format === 'json') {
    $filename = 'programs_' . date('Y-m-d') . '.json';

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo json_encode($programs, JSON_PRETTY_PRINT);
    exit;
}

else {
    $_SESSION['error'] = "Invalid export format.";
    Utilities::redirect('/admin/programs.php');
}
?>