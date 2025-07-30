<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/SecurityLogger.php';

// Check for admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<div class='alert alert-danger'>Access denied. You must be an administrator to view this page.</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$db = (new Database())->connect();
$logger = new SecurityLogger($db);

// TODO: Implement pagination and filtering
$logs = $logger->getLogs();

?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Security Audit Logs</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Security Logs</li>
    </ol>

    <div class="card shadow mb-4">
        <div class="card-header">
            <i class="fas fa-shield-alt"></i>
            All Security Events
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Timestamp</th>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Event Type</th>
                            <th>Severity</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['id']) ?></td>
                                <td><?= htmlspecialchars($log['created_at']) ?></td>
                                <td><?= htmlspecialchars($log['user_id']) ?></td>
                                <td><?= htmlspecialchars($log['username']) ?></td>
                                <td><?= htmlspecialchars($log['event_type']) ?></td>
                                <td><?= htmlspecialchars($log['severity']) ?></td>
                                <td><?= htmlspecialchars($log['description']) ?></td>
                                <td><?= htmlspecialchars($log['ip_address']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
