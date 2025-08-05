<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/SecurityLogger.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Store the current URL for redirect after login
    if (isset($_SERVER['REQUEST_URI'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    }
    
    // Redirect to login page
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $loginUrl = $protocol . $host . '/chania/admin/public/login.php';
    
    header('Location: ' . $loginUrl);
    exit;
}

// Check for admin permissions
if (!isset($_SESSION['permissions']) || !in_array('*', $_SESSION['permissions'])) {
    $_SESSION['flash_message'] = 'Access denied. You must be an administrator to view security logs.';
    $_SESSION['flash_type'] = 'error';
    header('Location: ' . BASE_URL . '/admin/public/index.php');
    exit;
}

try {
    $db = (new Database())->connect();
    $logger = new SecurityLogger($db);
    
    // Implement pagination
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 50;
    $offset = ($page - 1) * $limit;
    
    // Get filters
    $eventType = $_GET['event_type'] ?? '';
    $severity = $_GET['severity'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    // Get logs with filters and pagination
    $logs = $logger->getLogs($limit, $offset, $eventType, $severity, $dateFrom, $dateTo);
    $totalLogs = $logger->getLogsCount($eventType, $severity, $dateFrom, $dateTo);
    $totalPages = ceil($totalLogs / $limit);
    
    // Get unique event types and severities for filters
    $eventTypes = $logger->getUniqueEventTypes();
    $severities = ['low', 'medium', 'high', 'critical'];
    
} catch (Exception $e) {
    error_log('Security logs error: ' . $e->getMessage());
    $_SESSION['flash_message'] = 'Error loading security logs. Please try again later.';
    $_SESSION['flash_type'] = 'error';
    $logs = [];
    $totalLogs = 0;
    $totalPages = 0;
    $eventTypes = [];
}

// Include header after authentication checks
require_once __DIR__ . '/../includes/header.php';
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
