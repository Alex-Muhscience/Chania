<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require admin role
Utilities::requireRole('admin');

$pageTitle = "Admin Activity Logs";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'Admin Activity Logs']
];

$errors = [];
$success = false;

// Get filter parameters
$search = $_GET['search'] ?? '';
$userId = $_GET['user_id'] ?? '';
$action = $_GET['action'] ?? '';
$entityType = $_GET['entity_type'] ?? '';
$severity = $_GET['severity'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

try {
    $db = (new Database())->connect();

    // Get users for filter
    $stmt = $db->query("
        SELECT id, full_name, username 
        FROM users 
        WHERE deleted_at IS NULL 
        ORDER BY full_name ASC
    ");
    $users = $stmt->fetchAll();

    // Build query conditions
    $conditions = [];
    $params = [];

    if ($search) {
        $conditions[] = "(l.action LIKE ? OR l.entity_type LIKE ? OR u.full_name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($userId) {
        $conditions[] = "l.user_id = ?";
        $params[] = $userId;
    }

    if ($action) {
        $conditions[] = "l.action = ?";
        $params[] = $action;
    }

    if ($entityType) {
        $conditions[] = "l.entity_type = ?";
        $params[] = $entityType;
    }

    if ($severity) {
        $conditions[] = "l.severity = ?";
        $params[] = $severity;
    }

    if ($dateFrom) {
        $conditions[] = "DATE(l.created_at) >= ?";
        $params[] = $dateFrom;
    }

    if ($dateTo) {
        $conditions[] = "DATE(l.created_at) <= ?";
        $params[] = $dateTo;
    }

    $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

    // Get total count
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM admin_logs l
        JOIN users u ON l.user_id = u.id
        $whereClause
    ");
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    $totalPages = ceil($totalItems / $limit);

    // Get logs
    $stmt = $db->prepare("
        SELECT l.*, u.full_name as user_name, u.username
        FROM admin_logs l
        JOIN users u ON l.user_id = u.id
        $whereClause
        ORDER BY l.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([...$params, $limit, $offset]);
    $logs = $stmt->fetchAll();

    // Get statistics
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_logs,
            COUNT(DISTINCT user_id) as active_users,
            COUNT(DISTINCT DATE(created_at)) as active_days
        FROM admin_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stats = $stmt->fetch();

    // Get activity trends
    $stmt = $db->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count
        FROM admin_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $activityTrends = $stmt->fetchAll();

    // Get top actions
    $stmt = $db->query("
        SELECT 
            action,
            COUNT(*) as count
        FROM admin_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY action
        ORDER BY count DESC
        LIMIT 10
    ");
    $topActions = $stmt->fetchAll();

    // Get severity counts
    $stmt = $db->query("
        SELECT 
            severity,
            COUNT(*) as count
        FROM admin_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY severity
    ");
    $severityCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    error_log("Admin logs fetch error: " . $e->getMessage());
    $logs = [];
    $users = [];
    $totalItems = 0;
    $totalPages = 0;
    $stats = ['total_logs' => 0, 'active_users' => 0, 'active_days' => 0];
    $activityTrends = [];
    $topActions = [];
    $severityCounts = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <!-- Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">30-Day Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="h4 font-weight-bold text-primary"><?= number_format($stats['total_logs']) ?></div>
                        <div class="text-xs text-gray-500">Total Activities</div>
                    </div>
                    <div class="col-6">
                        <div class="h5 font-weight-bold text-info"><?= number_format($stats['active_users']) ?></div>
                        <div class="text-xs text-gray-500">Active Users</div>
                    </div>
                    <div class="col-6">
                        <div class="h5 font-weight-bold text-success"><?= number_format($stats['active_days']) ?></div>
                        <div class="text-xs text-gray-500">Active Days</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Severity Distribution -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Severity Distribution</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-info-circle text-info"></i> Info</span>
                        <span class="badge badge-info badge-pill"><?= $severityCounts['info'] ?? 0 ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-exclamation-triangle text-warning"></i> Warning</span>
                        <span class="badge badge-warning badge-pill"><?= $severityCounts['warning'] ?? 0 ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-times-circle text-danger"></i> Error</span>
                        <span class="badge badge-danger badge-pill"><?= $severityCounts['error'] ?? 0 ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-exclamation-circle text-dark"></i> Critical</span>
                        <span class="badge badge-dark badge-pill"><?= $severityCounts['critical'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Top Actions</h6>
            </div>
            <div class="card-body">
                <?php if (empty($topActions)): ?>
                    <p class="text-muted">No activities recorded</p>
                <?php else: ?>
                    <?php foreach ($topActions as $actionStat): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small"><?= htmlspecialchars($actionStat['action']) ?></span>
                            <span class="badge badge-secondary"><?= $actionStat['count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <!-- Activity Trends Chart -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Activity Trends (Last 30 Days)</h6>
            </div>
            <div class="card-body">
                <canvas id="activityChart" style="height: 300px;"></canvas>
            </div>
        </div>

        <!-- Activity Logs -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 font-weight-bold text-primary">Activity Logs</h6>
                    </div>
                    <div class="col-auto">
                        <a href="<?= BASE_URL ?>/admin/logs_export.php" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Export
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Search and Filter -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search"
                                   placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-2">
                            <select name="user_id" class="form-control">
                                <option value="">All Users</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= $userId == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="severity" class="form-control">
                                <option value="">All Severity</option>
                                <option value="info" <?= $severity === 'info' ? 'selected' : '' ?>>Info</option>
                                <option value="warning" <?= $severity === 'warning' ? 'selected' : '' ?>>Warning</option>
                                <option value="error" <?= $severity === 'error' ? 'selected' : '' ?>>Error</option>
                                <option value="critical" <?= $severity === 'critical' ? 'selected' : '' ?>>Critical</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_from"
                                   value="<?= htmlspecialchars($dateFrom) ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_to"
                                   value="<?= htmlspecialchars($dateTo) ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <?php if (empty($logs)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-5x text-muted mb-3"></i>
                        <h5 class="text-muted">No activity logs found</h5>
                        <p class="text-muted">Activity logs will appear here as users perform actions</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>Severity</th>
                                    <th>IP Address</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <small><?= date('M j, Y g:i A', strtotime($log['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($log['user_name']) ?></strong>
                                            <br><small class="text-muted">@<?= htmlspecialchars($log['username']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?= htmlspecialchars($log['action']) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($log['entity_type']): ?>
                                                <span class="badge badge-secondary">
                                                    <?= htmlspecialchars($log['entity_type']) ?>
                                                    <?php if ($log['entity_id']): ?>
                                                        #<?= $log['entity_id'] ?>
                                                    <?php endif; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?=
                                                $log['severity'] === 'critical' ? 'dark' :
                                                ($log['severity'] === 'error' ? 'danger' :
                                                ($log['severity'] === 'warning' ? 'warning' : 'info'))
                                            ?>">
                                                <?= ucfirst($log['severity']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= htmlspecialchars($log['ip_address']) ?></small>
                                        </td>
                                        <td>
                                            <?php if ($log['old_values'] || $log['new_values']): ?>
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                        data-toggle="modal" data-target="#logModal<?= $log['id'] ?>">
                                                    <i class="fas fa-eye"></i>
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
                        <nav aria-label="Logs pagination">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&user_id=<?= urlencode($userId) ?>&severity=<?= urlencode($severity) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modals -->
<?php foreach ($logs as $log): ?>
    <?php if ($log['old_values'] || $log['new_values']): ?>
        <div class="modal fade" id="logModal<?= $log['id'] ?>" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Activity Details</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Activity Information</h6>
                                <p><strong>User:</strong> <?= htmlspecialchars($log['user_name']) ?></p>
                                <p><strong>Action:</strong> <?= htmlspecialchars($log['action']) ?></p>
                                <p><strong>Entity:</strong> <?= htmlspecialchars($log['entity_type'] ?? 'N/A') ?></p>
                                <p><strong>Entity ID:</strong> <?= htmlspecialchars($log['entity_id'] ?? 'N/A') ?></p>
                                <p><strong>Date:</strong> <?= date('M j, Y g:i A', strtotime($log['created_at'])) ?></p>
                                <p><strong>IP Address:</strong> <?= htmlspecialchars($log['ip_address']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Technical Details</h6>
                                <p><strong>Severity:</strong> <?= ucfirst($log['severity']) ?></p>
                                <p><strong>Session ID:</strong> <?= htmlspecialchars($log['session_id'] ?? 'N/A') ?></p>
                                <p><strong>User Agent:</strong> <small><?= htmlspecialchars($log['user_agent'] ?? 'N/A') ?></small></p>
                            </div>
                        </div>

                        <?php if ($log['old_values']): ?>
                            <h6>Previous Values</h6>
                            <pre class="bg-light p-3 rounded"><?= htmlspecialchars(json_encode(json_decode($log['old_values']), JSON_PRETTY_PRINT)) ?></pre>
                        <?php endif; ?>

                        <?php if ($log['new_values']): ?>
                            <h6>New Values</h6>
                            <pre class="bg-light p-3 rounded"><?= htmlspecialchars(json_encode(json_decode($log['new_values']), JSON_PRETTY_PRINT)) ?></pre>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Activity trends chart
const ctx = document.getElementById('activityChart').getContext('2d');
const activityChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($activityTrends, 'date')) ?>,
        datasets: [{
            label: 'Activities',
            data: <?= json_encode(array_column($activityTrends, 'count')) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
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
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>