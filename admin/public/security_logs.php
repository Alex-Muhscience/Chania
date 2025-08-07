<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/SecurityLogger.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

session_start();

// Require login and admin role
Utilities::requireLogin();
Utilities::requireRole('admin');

$pageTitle = "Security Audit Logs";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/public/index.php'],
    ['title' => 'Security Logs']
];

try {
    $db = (new Database())->connect();
    $logger = new SecurityLogger($db);
    
    // Log this security log access
    $logger->log('data_access', 'medium', 'Security audit logs accessed', $_SESSION['user_id'] ?? null, [
        'affected_resource' => 'security_audit_logs',
        'access_type' => 'view'
    ]);
    
    // Implement pagination
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 25; // Reduced for better performance
    $offset = ($page - 1) * $limit;
    
    // Get filters
    $eventType = $_GET['event_type'] ?? '';
    $severity = $_GET['severity'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    $searchTerm = $_GET['search'] ?? '';
    
    // Get logs with filters and pagination
    $logs = $logger->getLogs($limit, $offset, $eventType, $severity, $dateFrom, $dateTo, $searchTerm);
    $totalLogs = $logger->getLogsCount($eventType, $severity, $dateFrom, $dateTo, $searchTerm);
    $totalPages = ceil($totalLogs / $limit);
    
    // Get unique event types and severities for filters
    $eventTypes = $logger->getUniqueEventTypes();
    $severities = ['low', 'medium', 'high', 'critical'];
    
    // Get security statistics
    $securityStats = [
        'total_logs' => $totalLogs,
        'critical_events' => $logger->getLogsCount('', 'critical'),
        'high_events' => $logger->getLogsCount('', 'high'),
        'failed_logins' => $logger->getLogsCount('login_failed'),
        'recent_events' => $logger->getLogsCount('', '', date('Y-m-d', strtotime('-24 hours')))
    ];
    
} catch (Exception $e) {
    error_log('Security logs error: ' . $e->getMessage());
    $_SESSION['flash_message'] = 'Error loading security logs: ' . $e->getMessage();
    $_SESSION['flash_type'] = 'error';
    $logs = [];
    $totalLogs = 0;
    $totalPages = 0;
    $eventTypes = [];
    $securityStats = [];
}

// Include header after authentication checks
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Security Audit Logs</h1>
    <div class="d-none d-sm-inline-block">
        <a href="<?= BASE_URL ?>/admin/public/index.php" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Security Statistics Row -->
<?php if (!empty($securityStats)): ?>
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Events</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($securityStats['total_logs'] ?? 0) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Critical Events</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($securityStats['critical_events'] ?? 0) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">High Priority</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($securityStats['high_events'] ?? 0) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Recent (24h)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($securityStats['recent_events'] ?? 0) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filter Section -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Security Logs</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row">
            <div class="col-md-2">
                <label for="event_type" class="form-label small font-weight-bold">Event Type</label>
                <select name="event_type" id="event_type" class="form-control form-control-sm">
                    <option value="">All Types</option>
                    <?php foreach ($eventTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= $eventType === $type ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', htmlspecialchars($type))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="severity" class="form-label small font-weight-bold">Severity</label>
                <select name="severity" id="severity" class="form-control form-control-sm">
                    <option value="">All Severities</option>
                    <?php foreach ($severities as $sev): ?>
                        <option value="<?= $sev ?>" <?= $severity === $sev ? 'selected' : '' ?>>
                            <?= ucfirst($sev) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label small font-weight-bold">From Date</label>
                <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label small font-weight-bold">To Date</label>
                <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label small font-weight-bold">Search</label>
                <input type="text" name="search" id="search" class="form-control form-control-sm" placeholder="Search description..." value="<?= htmlspecialchars($searchTerm) ?>">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm mr-2">Filter</button>
                <a href="security_logs.php" class="btn btn-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Security Logs Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-shield-alt mr-2"></i>Security Events
            <?php if ($totalLogs > 0): ?>
                <span class="badge badge-info ml-2"><?= number_format($totalLogs) ?> total</span>
            <?php endif; ?>
        </h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                <a class="dropdown-item" href="#" onclick="exportLogs()"><i class="fas fa-download mr-2"></i>Export CSV</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="#" onclick="clearOldLogs()"><i class="fas fa-trash mr-2"></i>Clear Old Logs</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <div class="text-center py-4">
                <i class="fas fa-shield-alt fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-gray-600">No security logs found</h5>
                <p class="text-muted">No security events match the current filters.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="securityLogsTable">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 60px;">ID</th>
                            <th style="width: 150px;">Timestamp</th>
                            <th style="width: 120px;">User</th>
                            <th style="width: 120px;">Event Type</th>
                            <th class="text-center" style="width: 80px;">Severity</th>
                            <th>Description</th>
                            <th style="width: 120px;">IP Address</th>
                            <th class="text-center" style="width: 80px;">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr class="<?= getSeverityRowClass($log['severity'] ?? 'low') ?>">
                                <td class="text-center font-weight-bold"><?= htmlspecialchars($log['id']) ?></td>
                                <td>
                                    <small class="text-muted d-block"><?= date('M j, Y', strtotime($log['created_at'])) ?></small>
                                    <small class="font-weight-bold"><?= date('g:i:s A', strtotime($log['created_at'])) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($log['username'])): ?>
                                        <div class="font-weight-bold"><?= htmlspecialchars($log['username']) ?></div>
                                        <small class="text-muted">ID: <?= htmlspecialchars($log['user_id'] ?? 'N/A') ?></small>
                                    <?php else: ?>
                                        <span class="text-muted font-italic">System</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= getEventTypeBadge($log['event_type']) ?> px-2 py-1">
                                        <?= ucfirst(str_replace('_', ' ', htmlspecialchars($log['event_type']))) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= getSeverityBadge($log['severity'] ?? 'low') ?> px-2 py-1">
                                        <i class="fas fa-<?= getSeverityIcon($log['severity'] ?? 'low') ?> mr-1"></i>
                                        <?= ucfirst($log['severity'] ?? 'low') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($log['description']) ?></td>
                                <td>
                                    <code class="small"><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></code>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($log['details'])): ?>
                                        <button class="btn btn-outline-info btn-sm" onclick="showLogDetails(<?= htmlspecialchars($log['id']) ?>, <?= htmlspecialchars(json_encode($log['details'])) ?>)">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <p class="small text-muted mb-0">
                            Showing <?= ($page - 1) * $limit + 1 ?> to <?= min($page * $limit, $totalLogs) ?> of <?= number_format($totalLogs) ?> entries
                        </p>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="Security logs pagination">
                            <ul class="pagination justify-content-end mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &raquo;</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Security Log Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
// Helper functions for styling
function getSeverityBadge($severity) {
    switch ($severity) {
        case 'critical': return 'danger';
        case 'high': return 'warning';
        case 'medium': return 'info';
        case 'low': return 'success';
        default: return 'secondary';
    }
}

function getSeverityIcon($severity) {
    switch ($severity) {
        case 'critical': return 'exclamation-triangle';
        case 'high': return 'exclamation-circle';
        case 'medium': return 'info-circle';
        case 'low': return 'check-circle';
        default: return 'circle';
    }
}

function getSeverityRowClass($severity) {
    switch ($severity) {
        case 'critical': return 'table-danger';
        case 'high': return 'table-warning';
        default: return '';
    }
}

function getEventTypeBadge($eventType) {
    switch ($eventType) {
        case 'login_failed': return 'danger';
        case 'login_attempt': return 'success';
        case 'permission_check': return 'info';
        case 'data_access': return 'primary';
        case 'system_startup': return 'secondary';
        default: return 'dark';
    }
}
?>

<script>
function showLogDetails(logId, details) {
    let content = '<h6>Log ID: ' + logId + '</h6><hr>';
    
    if (details && typeof details === 'object') {
        content += '<div class="row">';
        for (let key in details) {
            if (details.hasOwnProperty(key)) {
                content += '<div class="col-md-6 mb-3">';
                content += '<strong>' + key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + ':</strong><br>';
                content += '<code>' + (details[key] || 'N/A') + '</code>';
                content += '</div>';
            }
        }
        content += '</div>';
    } else {
        content += '<p class="text-muted">No additional details available.</p>';
    }
    
    document.getElementById('logDetailsContent').innerHTML = content;
    $('#logDetailsModal').modal('show');
}

function exportLogs() {
    // Add export functionality here
    alert('Export functionality will be implemented soon.');
}

function clearOldLogs() {
    if (confirm('Are you sure you want to clear old logs? This action cannot be undone.')) {
        // Add clear logs functionality here
        alert('Clear logs functionality will be implemented soon.');
    }
}

// Auto-refresh every 30 seconds
setInterval(function() {
    if (document.getElementById('auto-refresh-checkbox') && document.getElementById('auto-refresh-checkbox').checked) {
        location.reload();
    }
}, 30000);
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
