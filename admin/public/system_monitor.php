
<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require admin role
Utilities::requireRole('admin');

$pageTitle = "System Monitor";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'System Monitor']
];

// Get system information
$systemInfo = [
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
    'server_port' => $_SERVER['SERVER_PORT'] ?? 'Unknown',
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'timezone' => date_default_timezone_get(),
    'current_time' => date('Y-m-d H:i:s'),
    'disk_free_space' => disk_free_space('.'),
    'disk_total_space' => disk_total_space('.'),
];

// Get database information
try {
    $db = (new Database())->connect();
    
    // Database size
    $stmt = $db->prepare("
        SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
        FROM information_schema.tables 
        WHERE table_schema = ?
    ");
    $stmt->execute([DB_NAME]);
    $dbSize = $stmt->fetchColumn();
    
    // Table information
    $stmt = $db->prepare("
        SELECT 
            table_name,
            table_rows,
            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
        FROM information_schema.tables 
        WHERE table_schema = ?
        ORDER BY (data_length + index_length) DESC
    ");
    $stmt->execute([DB_NAME]);
    $tables = $stmt->fetchAll();
    
    // Database statistics
    $stmt = $db->query("
        SELECT 
            (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) as total_users,
            (SELECT COUNT(*) FROM programs WHERE deleted_at IS NULL) as total_programs,
            (SELECT COUNT(*) FROM applications WHERE deleted_at IS NULL) as total_applications,
            (SELECT COUNT(*) FROM events WHERE deleted_at IS NULL) as total_events,
            (SELECT COUNT(*) FROM contacts WHERE deleted_at IS NULL) as total_contacts,
            (SELECT COUNT(*) FROM testimonials WHERE deleted_at IS NULL) as total_testimonials,
            (SELECT COUNT(*) FROM admin_logs) as total_logs
    ");
    $dbStats = $stmt->fetch();
    
    // Recent database activity
    $stmt = $db->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count
        FROM admin_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ");
    $recentActivity = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("System monitor database error: " . $e->getMessage());
    $dbSize = 0;
    $tables = [];
    $dbStats = [];
    $recentActivity = [];
}

// Get file system information
$uploadsDir = UPLOAD_PATH;
$uploadsSize = 0;
$fileCount = 0;

if (is_dir($uploadsDir)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadsDir));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $uploadsSize += $file->getSize();
            $fileCount++;
        }
    }
}

// Get error log information
$errorLogPath = LOGS_PATH . '/error.log';
$errorLogSize = 0;
$errorLogLines = 0;

if (file_exists($errorLogPath)) {
    $errorLogSize = filesize($errorLogPath);
    $errorLogLines = count(file($errorLogPath));
}

// Performance metrics
$startTime = microtime(true);
$memoryUsage = memory_get_usage(true);
$peakMemoryUsage = memory_get_peak_usage(true);

// Test database connection speed
$dbConnectionTime = 0;
$dbConnectionStart = microtime(true);
try {
    $db = (new Database())->connect();
    $stmt = $db->query("SELECT 1");
    $stmt->fetch();
    $dbConnectionTime = (microtime(true) - $dbConnectionStart) * 1000; // Convert to milliseconds
} catch (PDOException $e) {
    $dbConnectionTime = -1;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <!-- System Status Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Database Size
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($dbSize, 2) ?> MB
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-database fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Files Storage
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= Utilities::formatFileSize($uploadsSize) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-folder fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Memory Usage
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= Utilities::formatFileSize($memoryUsage) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-memory fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            DB Connection
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $dbConnectionTime > 0 ? number_format($dbConnectionTime, 2) . ' ms' : 'Error' ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- System Information -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>PHP Version:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['php_version']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Server Software:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['server_software']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Server Name:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['server_name']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Memory Limit:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['memory_limit']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Max Execution Time:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['max_execution_time']) ?> seconds</td>
                        </tr>
                        <tr>
                            <td><strong>Upload Max Size:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['upload_max_filesize']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Post Max Size:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['post_max_size']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Timezone:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['timezone']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Current Time:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['current_time']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Database Statistics -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Database Statistics</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Total Users:</strong></td>
                            <td><?= number_format($dbStats['total_users'] ?? 0) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Programs:</strong></td>
                            <td><?= number_format($dbStats['total_programs'] ?? 0) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Applications:</strong></td>
                            <td><?= number_format($dbStats['total_applications'] ?? 0) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Events:</strong></td>
                            <td><?= number_format($dbStats['total_events'] ?? 0) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Contacts:</strong></td>
                            <td><?= number_format($dbStats['total_contacts'] ?? 0) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Testimonials:</strong></td>
                            <td><?= number_format($dbStats['total_testimonials'] ?? 0) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Activity Logs:</strong></td>
                            <td><?= number_format($dbStats['total_logs'] ?? 0) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Database Size:</strong></td>
                            <td><?= number_format($dbSize, 2) ?> MB</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Performance Metrics -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Performance Metrics</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Page Load Time:</strong></td>
                            <td><?= number_format((microtime(true) - $startTime) * 1000, 2) ?> ms</td>
                        </tr>
                        <tr>
                            <td><strong>Memory Usage:</strong></td>
                            <td><?= Utilities::formatFileSize($memoryUsage) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Peak Memory Usage:</strong></td>
                            <td><?= Utilities::formatFileSize($peakMemoryUsage) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Database Connection Time:</strong></td>
                            <td><?= $dbConnectionTime > 0 ? number_format($dbConnectionTime, 2) . ' ms' : 'Error' ?></td>
                        </tr>
                        <tr>
                            <td><strong>Free Disk Space:</strong></td>
                            <td><?= Utilities::formatFileSize($systemInfo['disk_free_space']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Disk Space:</strong></td>
                            <td><?= Utilities::formatFileSize($systemInfo['disk_total_space']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Uploads Directory Size:</strong></td>
                            <td><?= Utilities::formatFileSize($uploadsSize) ?> (<?= number_format($fileCount) ?> files)</td>
                        </tr>
                        <tr>
                            <td><strong>Error Log Size:</strong></td>
                            <td><?= Utilities::formatFileSize($errorLogSize) ?> (<?= number_format($errorLogLines) ?> lines)</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Activity (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivity)): ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <p>No recent activity</p>
                    </div>
                <?php else: ?>
                    <canvas id="activityChart" style="height: 200px;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Database Tables -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Database Tables</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Table Name</th>
                                <th>Rows</th>
                                <th>Size (MB)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tables as $table): ?>
                                <tr>
                                    <td><?= htmlspecialchars($table['table_name']) ?></td>
                                    <td><?= number_format($table['table_rows']) ?></td>
                                    <td><?= number_format($table['size_mb'], 2) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="analyzeTable('<?= htmlspecialchars($table['table_name']) ?>')">
                                            <i class="fas fa-search"></i> Analyze
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Health Check -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">System Health Check</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h3 text-<?= $dbConnectionTime > 0 && $dbConnectionTime < 100 ? 'success' : 'warning' ?>">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="font-weight-bold">Database</div>
                            <div class="text-muted">
                                <?= $dbConnectionTime > 0 ? 'Connected' : 'Error' ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h3 text-<?= is_writable(UPLOAD_PATH) ? 'success' : 'danger' ?>">
                                <i class="fas fa-folder"></i>
                            </div>
                            <div class="font-weight-bold">Uploads Directory</div>
                            <div class="text-muted">
                                <?= is_writable(UPLOAD_PATH) ? 'Writable' : 'Not Writable' ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h3 text-<?= is_writable(LOGS_PATH) ? 'success' : 'danger' ?>">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="font-weight-bold">Logs Directory</div>
                            <div class="text-muted">
                                <?= is_writable(LOGS_PATH) ? 'Writable' : 'Not Writable' ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h3 text-<?= $memoryUsage < (1024 * 1024 * 100) ? 'success' : 'warning' ?>">
                                <i class="fas fa-memory"></i>
                            </div>
                            <div class="font-weight-bold">Memory</div>
                            <div class="text-muted">
                                <?= $memoryUsage < (1024 * 1024 * 100) ? 'Optimal' : 'High Usage' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Recent activity chart
<?php if (!empty($recentActivity)): ?>
const ctx = document.getElementById('activityChart').getContext('2d');
const activityChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($recentActivity, 'date')) ?>,
        datasets: [{
            label: 'Activities',
            data: <?= json_encode(array_column($recentActivity, 'count')) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
<?php endif; ?>

function analyzeTable(tableName) {
    alert('Table analysis feature would be implemented here for: ' + tableName);
}

// Auto-refresh every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>