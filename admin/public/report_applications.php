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

// Fetch application data for the report
$stmt = $db->query("
    SELECT a.*, p.title as program_title 
    FROM applications a 
    LEFT JOIN programs p ON a.program_id = p.id
    ORDER BY a.submitted_at DESC
");
$applications = $stmt->fetchAll();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Application Analytics Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="reports.php">Reports</a></li>
        <li class="breadcrumb-item active">Application Analytics</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            All Applications
        </div>
        <div class="card-body">
            <table id="datatablesSimple">
                <thead>
                    <tr>
                        <th>Application #</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Program</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $application): ?>
                        <tr>
                            <td><?= htmlspecialchars($application['application_number']) ?></td>
                            <td><?= htmlspecialchars($application['first_name'] . ' ' . $application['last_name']) ?></td>
                            <td><?= htmlspecialchars($application['email']) ?></td>
                            <td><?= htmlspecialchars($application['program_title']) ?></td>
                            <td>
                                <span class="badge badge-<?= $application['status'] === 'approved' ? 'success' : ($application['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($application['status']) ?>
                                </span>
                            </td>
                            <td><?= $application['submitted_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
