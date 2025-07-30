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

// Fetch SMS campaign data for the report
// This is a placeholder as we don't have a campaigns table yet
$campaigns = [];
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">SMS Campaign Analytics</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="reports.php">Reports</a></li>
        <li class="breadcrumb-item active">SMS Campaign Analytics</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            SMS Campaigns
        </div>
        <div class="card-body">
            <div class="alert alert-info">SMS campaign tracking is not yet implemented.</div>
            <table id="datatablesSimple">
                <thead>
                    <tr>
                        <th>Campaign Name</th>
                        <th>Sent Date</th>
                        <th>Recipients</th>
                        <th>Delivery Rate</th>
                        <th>Response Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaigns as $campaign): ?>
                        <tr>
                            <td>...</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
