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

// Fetch program data for the report
$stmt = $db->query("
    SELECT p.*, 
           COUNT(a.id) as application_count,
           COUNT(CASE WHEN a.status = 'approved' THEN 1 END) as approved_count
    FROM programs p 
    LEFT JOIN applications a ON p.id = a.program_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$programs = $stmt->fetchAll();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Program Performance Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="reports.php">Reports</a></li>
        <li class="breadcrumb-item active">Program Performance</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            All Programs with Statistics
        </div>
        <div class="card-body">
            <table id="datatablesSimple">
                <thead>
                    <tr>
                        <th>Program Title</th>
                        <th>Category</th>
                        <th>Applications</th>
                        <th>Approved</th>
                        <th>Approval Rate</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programs as $program): ?>
                        <tr>
                            <td><?= htmlspecialchars($program['title']) ?></td>
                            <td><?= htmlspecialchars($program['category']) ?></td>
                            <td><?= $program['application_count'] ?></td>
                            <td><?= $program['approved_count'] ?></td>
                            <td>
                                <?php
                                $approvalRate = $program['application_count'] > 0 
                                    ? round(($program['approved_count'] / $program['application_count']) * 100, 1) 
                                    : 0;
                                ?>
                                <?= $approvalRate ?>%
                            </td>
                            <td>
                                <span class="badge badge-<?= $program['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $program['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td><?= $program['created_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
