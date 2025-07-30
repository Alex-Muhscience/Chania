<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/User.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

if (!$userModel->hasPermission($_SESSION['user_id'], 'reports') && !$userModel->hasPermission($_SESSION['user_id'], '*')) {
    $_SESSION['error'] = "You don't have permission to access this resource.";
    header('Location: index.php');
    exit();
}

// Fetch event data for the report
$stmt = $db->query("
    SELECT e.*, 
           COUNT(er.id) as registration_count,
           COUNT(CASE WHEN er.status = 'confirmed' THEN 1 END) as confirmed_count
    FROM events e 
    LEFT JOIN event_registrations er ON e.id = er.event_id
    GROUP BY e.id
    ORDER BY e.event_date DESC
");
$events = $stmt->fetchAll();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Event Engagement Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="reports.php">Reports</a></li>
        <li class="breadcrumb-item active">Event Engagement</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            All Events with Statistics
        </div>
        <div class="card-body">
            <table id="datatablesSimple">
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>Event Type</th>
                        <th>Registrations</th>
                        <th>Confirmed</th>
                        <th>Confirmation Rate</th>
                        <th>Status</th>
                        <th>Event Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= htmlspecialchars($event['event_type']) ?></td>
                            <td><?= $event['registration_count'] ?></td>
                            <td><?= $event['confirmed_count'] ?></td>
                            <td>
                                <?php
                                $confirmationRate = $event['registration_count'] > 0 
                                    ? round(($event['confirmed_count'] / $event['registration_count']) * 100, 1) 
                                    : 0;
                                ?>
                                <?= $confirmationRate ?>%
                            </td>
                            <td>
                                <span class="badge badge-<?= $event['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $event['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td><?= $event['event_date'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
