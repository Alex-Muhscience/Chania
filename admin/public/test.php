<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

$pageTitle = "System Test";

// Test database connection
$dbStatus = false;
$dbError = '';

try {
    $db = (new Database())->connect();
    $dbStatus = true;

    // Test a simple query
    $stmt = $db->query("SELECT 1");
    $result = $stmt->fetch();

} catch (PDOException $e) {
    $dbError = $e->getMessage();
}

// Test session
$sessionStatus = session_status() === PHP_SESSION_ACTIVE;

// Test file permissions
$logDir = __DIR__ . '/../../logs';
$logDirWritable = is_dir($logDir) && is_writable($logDir);

// Test configuration
$configStatus = defined('BASE_URL') && defined('ADMIN_URL');

// Test PHP version
$phpVersion = phpversion();
$phpVersionOK = version_compare($phpVersion, '7.4', '>=');

// Test required extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'session', 'json'];
$extensionStatus = [];

foreach ($requiredExtensions as $ext) {
    $extensionStatus[$ext] = extension_loaded($ext);
}

// Test email configuration (basic)
$emailTest = function_exists('mail');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Status Test</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Database Test -->
                        <div class="col-md-6 mb-4">
                            <h6><i class="fas fa-database"></i> Database Connection</h6>
                            <div class="alert alert-<?= $dbStatus ? 'success' : 'danger' ?>">
                                <?php if ($dbStatus): ?>
                                    <i class="fas fa-check"></i> Database connection successful
                                <?php else: ?>
                                    <i class="fas fa-times"></i> Database connection failed
                                    <br><small><?= htmlspecialchars($dbError) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Session Test -->
                        <div class="col-md-6 mb-4">
                            <h6><i class="fas fa-user-shield"></i> Session Management</h6>
                            <div class="alert alert-<?= $sessionStatus ? 'success' : 'danger' ?>">
                                <?php if ($sessionStatus): ?>
                                    <i class="fas fa-check"></i> Sessions working properly
                                <?php else: ?>
                                    <i class="fas fa-times"></i> Session issues detected
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- PHP Version -->
                        <div class="col-md-6 mb-4">
                            <h6><i class="fab fa-php"></i> PHP Version</h6>
                            <div class="alert alert-<?= $phpVersionOK ? 'success' : 'warning' ?>">
                                <i class="fas fa-<?= $phpVersionOK ? 'check' : 'exclamation-triangle' ?>"></i>
                                PHP <?= $phpVersion ?>
                                <?php if (!$phpVersionOK): ?>
                                    <br><small>PHP 7.4+ recommended</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Configuration -->
                        <div class="col-md-6 mb-4">
                            <h6><i class="fas fa-cogs"></i> Configuration</h6>
                            <div class="alert alert-<?= $configStatus ? 'success' : 'danger' ?>">
                                <?php if ($configStatus): ?>
                                    <i class="fas fa-check"></i> Configuration loaded
                                    <br><small>Base URL: <?= BASE_URL ?></small>
                                <?php else: ?>
                                    <i class="fas fa-times"></i> Configuration issues
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- File Permissions -->
                        <div class="col-md-6 mb-4">
                            <h6><i class="fas fa-folder"></i> File Permissions</h6>
                            <div class="alert alert-<?= $logDirWritable ? 'success' : 'warning' ?>">
                                <?php if ($logDirWritable): ?>
                                    <i class="fas fa-check"></i> Log directory writable
                                <?php else: ?>
                                    <i class="fas fa-exclamation-triangle"></i> Log directory not writable
                                    <br><small>Path: <?= $logDir ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-4">
                            <h6><i class="fas fa-envelope"></i> Email Function</h6>
                            <div class="alert alert-<?= $emailTest ? 'success' : 'warning' ?>">
                                <?php if ($emailTest): ?>
                                    <i class="fas fa-check"></i> Email function available
                                <?php else: ?>
                                    <i class="fas fa-exclamation-triangle"></i> Email function not available
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Extensions -->
                    <div class="row">
                        <div class="col-12">
                            <h6><i class="fas fa-puzzle-piece"></i> Required Extensions</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Extension</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($extensionStatus as $ext => $loaded): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($ext) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $loaded ? 'success' : 'danger' ?>">
                                                        <?= $loaded ? 'Loaded' : 'Missing' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6><i class="fas fa-info-circle"></i> System Information</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td><strong>Server Software:</strong></td>
                                            <td><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Document Root:</strong></td>
                                            <td><?= htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Script Path:</strong></td>
                                            <td><?= htmlspecialchars(__FILE__) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Memory Limit:</strong></td>
                                            <td><?= ini_get('memory_limit') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Max Execution Time:</strong></td>
                                            <td><?= ini_get('max_execution_time') ?> seconds</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Upload Max Size:</strong></td>
                                            <td><?= ini_get('upload_max_filesize') ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="<?= BASE_URL ?>/admin/" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>