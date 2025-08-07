<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require admin role
Utilities::requireRole('admin');

$pageTitle = "Activity Timeline & Audit Logs";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'Activity Timeline']
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
$timeRange = $_GET['time_range'] ?? '30';

// Define severity levels for filtering
$severityLevels = ['Low', 'Medium', 'High', 'Critical'];
$page = max(1, intval($_GET['page'] ?? 1));
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $bulkAction = $_POST['bulk_action'];
    $selectedLogs = $_POST['selected_logs'] ?? [];
    
    if (!empty($selectedLogs) && $bulkAction === 'export') {
        // Export selected logs
        header('Location: ' . BASE_URL . '/admin/public/logs_export.php?ids=' . implode(',', $selectedLogs));
        exit;
    }
}

// Set default date range if not specified
if (!$dateFrom && !$dateTo) {
    $dateTo = date('Y-m-d');
    $dateFrom = date('Y-m-d', strtotime("-{$timeRange} days"));
}

try {
    $db = (new Database())->connect();

    // Get users for filter
    $stmt = $db->query("
        SELECT id, username 
        FROM users 
        WHERE is_active = 1 
        ORDER BY username ASC
    ");
    $users = $stmt->fetchAll();

    // Build query conditions
    $conditions = [];
    $params = [];

    if ($search) {
        $conditions[] = "(l.action LIKE ? OR l.entity_type LIKE ? OR u.username LIKE ?)";
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
        SELECT l.*, u.username as user_name
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

    // Get entity type counts (replacing severity)
    $stmt = $db->query("
        SELECT 
            entity_type,
            COUNT(*) as count
        FROM admin_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY entity_type
    ");
    $entityTypeCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    error_log("Admin logs fetch error: " . $e->getMessage());
    $logs = [];
    $users = [];
    $totalItems = 0;
    $totalPages = 0;
    $stats = ['total_logs' => 0, 'active_users' => 0, 'active_days' => 0];
    $activityTrends = [];
    $topActions = [];
    $entityTypeCounts = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/admin/public/assets/css/timeline.css">

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

        <!-- Entity Type Distribution -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Entity Types</h6>
            </div>
            <div class="card-body">
                <?php if (empty($entityTypeCounts)): ?>
                    <p class="text-muted">No entity types recorded</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($entityTypeCounts as $entityType => $count): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-cube text-secondary"></i> <?= htmlspecialchars($entityType ?: 'General') ?></span>
                                <span class="badge badge-secondary badge-pill"><?= $count ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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

        <!-- Activity Timeline -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clock text-primary mr-2"></i>Activity Timeline
                        </h6>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="timelineView" onclick="switchView('timeline')">
                                <i class="fas fa-stream"></i> Timeline
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="tableView" onclick="switchView('table')">
                                <i class="fas fa-table"></i> Table
                            </button>
                            <button type="button" class="btn btn-info btn-sm" id="refreshBtn" onclick="refreshTimeline()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <a href="<?= BASE_URL ?>/admin/logs_export.php" class="btn btn-success btn-sm">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Bulk Actions -->
                <form method="POST" action="" class="form-inline mb-3">
                    <select name="bulk_action" class="form-control form-control-sm mr-2">
                        <option value="">Bulk actions</option>
                        <option value="export">Export Selected</option>
                        <option value="delete">Delete Selected</option>
                        <option value="review">Mark as Reviewed</option>
                    </select>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Apply</button>
                </form>
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
                                        <?= htmlspecialchars($user['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="entity_type" class="form-control">
                                <option value="">All Entity Types</option>
                                <option value="user" <?= $entityType === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="application" <?= $entityType === 'application' ? 'selected' : '' ?>>Application</option>
                                <option value="program" <?= $entityType === 'program' ? 'selected' : '' ?>>Program</option>
                                <option value="event" <?= $entityType === 'event' ? 'selected' : '' ?>>Event</option>
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

                <!-- Timeline / Table Views -->
                <div id="timeline-view">
                    <ul class="timeline">
                        <?php foreach ($logs as $log): ?>
                            <li class="timeline-item">
                                <div class="timeline-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <h6 class="timeline-title"><?= htmlspecialchars($log['action']) ?></h6>
                                        <small class="timeline-time"><i class="fas fa-clock"></i> <?= date('M j, Y g:i A', strtotime($log['created_at'])) ?></small>
                                    </div>
                                    <div class="timeline-body">
                                        <p><strong>User:</strong> <?= htmlspecialchars($log['user_name']) ?></p>
                                        <p><strong>Entity:</strong> <?= htmlspecialchars($log['entity_type'] ?? 'N/A') ?></p>
                                        <a href="#" class="details-toggle" onclick="toggleDetails(this)">Show Details</a>
                                        <div class="timeline-details">
                                            <?php 
                                            $details = '';
                                            if (!empty($log['old_values']) || !empty($log['new_values'])) {
                                                if (!empty($log['old_values'])) {
                                                    $details .= 'Old Values: ' . $log['old_values'] . "\n";
                                                }
                                                if (!empty($log['new_values'])) {
                                                    $details .= 'New Values: ' . $log['new_values'];
                                                }
                                            } else {
                                                $details = 'No additional details.';
                                            }
                                            ?>
                                            <pre><?= htmlspecialchars($details) ?></pre>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div id="table-view" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>User Agent</th>
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
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?= htmlspecialchars($log['action']) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($log['entity_type']): ?>
                                                <span class="badge badge-secondary">
                                                    <?= htmlspecialchars($log['entity_type']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= htmlspecialchars(substr($log['user_agent'] ?? '', 0, 50)) ?><?= strlen($log['user_agent'] ?? '') > 50 ? '...' : '' ?></small>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= htmlspecialchars($log['ip_address'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($log['old_values']) || !empty($log['new_values'])): ?>
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
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Logs pagination">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&user_id=<?= urlencode($userId) ?>&entity_type=<?= urlencode($entityType) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modals -->
<?php foreach ($logs as $log): ?>
    <?php if (!empty($log['old_values']) || !empty($log['new_values'])): ?>
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
                                <p><strong>Entity ID:</strong> <?= htmlspecialchars(($log['entity_id'] ?? 'N/A') ?: 'N/A') ?></p>
                                <p><strong>Severity:</strong> <?= htmlspecialchars(($log['severity'] ?? 'N/A') ?: 'N/A') ?></p>
                                <p><strong>Date:</strong> <?= date('M j, Y g:i A', strtotime($log['created_at'])) ?></p>
                                <p><strong>IP Address:</strong> <?= htmlspecialchars($log['ip_address'] ?? '') ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Technical Details</h6>
                                <p><strong>User Agent:</strong> <small><?= htmlspecialchars($log['user_agent'] ?? 'N/A') ?></small></p>
                            </div>
                        </div>

                        <?php if (!empty($log['old_values']) || !empty($log['new_values'])): ?>
                            <h6>Change Details</h6>
                            <?php if (!empty($log['old_values'])): ?>
                                <h6>Old Values:</h6>
                                <pre class="bg-light p-3 rounded"><?= htmlspecialchars($log['old_values']) ?></pre>
                            <?php endif; ?>
                            <?php if (!empty($log['new_values'])): ?>
                                <h6>New Values:</h6>
                                <pre class="bg-light p-3 rounded"><?= htmlspecialchars($log['new_values']) ?></pre>
                            <?php endif; ?>
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

function switchView(view) {
    if (view === 'timeline') {
        document.getElementById('timeline-view').style.display = 'block';
        document.getElementById('table-view').style.display = 'none';
    } else {
        document.getElementById('timeline-view').style.display = 'none';
        document.getElementById('table-view').style.display = 'block';
    }
}

function refreshTimeline() {
    location.reload();
}

function toggleDetails(element) {
    const details = element.nextElementSibling;
    if (details.style.display === 'block') {
        details.style.display = 'none';
        element.textContent = 'Show Details';
    } else {
        details.style.display = 'block';
        element.textContent = 'Hide Details';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>