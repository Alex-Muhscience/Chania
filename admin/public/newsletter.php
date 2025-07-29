<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$pageTitle = "Newsletter Management";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'Newsletter Management']
];

$errors = [];
$success = false;

// Get filter parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

try {
    $db = (new Database())->connect();

    // Build query conditions
    $conditions = [];
    $params = [];

    if ($search) {
        $conditions[] = "(email LIKE ? OR name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($status) {
        $conditions[] = "status = ?";
        $params[] = $status;
    }

    $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

    // Get total count
    $stmt = $db->prepare("SELECT COUNT(*) FROM newsletter_subscribers $whereClause");
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    $totalPages = ceil($totalItems / $limit);

    // Get subscribers
    $stmt = $db->prepare("
        SELECT *
        FROM newsletter_subscribers
        $whereClause
        ORDER BY subscribed_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([...$params, $limit, $offset]);
    $subscribers = $stmt->fetchAll();

    // Get status counts
    $stmt = $db->query("
        SELECT status, COUNT(*) as count
        FROM newsletter_subscribers
        GROUP BY status
    ");
    $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Get subscription trends
    $stmt = $db->query("
        SELECT 
            DATE_FORMAT(subscribed_at, '%Y-%m') as month,
            COUNT(*) as count
        FROM newsletter_subscribers
        WHERE subscribed_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(subscribed_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $subscriptionTrends = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Newsletter fetch error: " . $e->getMessage());
    $subscribers = [];
    $totalItems = 0;
    $totalPages = 0;
    $statusCounts = [];
    $subscriptionTrends = [];
}

// Handle subscriber actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_subscriber') {
        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');

        if (empty($email) || !Utilities::isValidEmail($email)) {
            $errors[] = "Please enter a valid email address.";
        } else {
            try {
                // Check if email already exists
                $stmt = $db->prepare("SELECT COUNT(*) FROM newsletter_subscribers WHERE email = ?");
                $stmt->execute([$email]);

                if ($stmt->fetchColumn() > 0) {
                    $errors[] = "Email address already exists.";
                } else {
                    // Add subscriber
                    $stmt = $db->prepare("
                        INSERT INTO newsletter_subscribers (email, name, status, subscribed_at, ip_address)
                        VALUES (?, ?, 'subscribed', NOW(), ?)
                    ");
                    $stmt->execute([$email, $name, $_SERVER['REMOTE_ADDR']]);

                    $success = true;
                    Utilities::logActivity($_SESSION['user_id'], 'ADD_SUBSCRIBER', 'newsletter_subscribers', $db->lastInsertId(), $_SERVER['REMOTE_ADDR']);
                }
            } catch (PDOException $e) {
                error_log("Add subscriber error: " . $e->getMessage());
                $errors[] = "Failed to add subscriber.";
            }
        }
    }

    elseif ($action === 'bulk_action') {
        $subscriberIds = $_POST['subscriber_ids'] ?? [];
        $bulkAction = $_POST['bulk_action'] ?? '';

        if (!empty($subscriberIds) && $bulkAction) {
            try {
                $placeholders = str_repeat('?,', count($subscriberIds) - 1) . '?';

                if ($bulkAction === 'delete') {
                    $stmt = $db->prepare("DELETE FROM newsletter_subscribers WHERE id IN ($placeholders)");
                    $stmt->execute($subscriberIds);
                    $success = true;
                } elseif (in_array($bulkAction, ['subscribed', 'unsubscribed'])) {
                    $stmt = $db->prepare("UPDATE newsletter_subscribers SET status = ? WHERE id IN ($placeholders)");
                    $stmt->execute([$bulkAction, ...$subscriberIds]);
                    $success = true;
                }

                Utilities::logActivity($_SESSION['user_id'], 'BULK_UPDATE_SUBSCRIBERS', 'newsletter_subscribers', null, $_SERVER['REMOTE_ADDR']);

            } catch (PDOException $e) {
                error_log("Bulk action error: " . $e->getMessage());
                $errors[] = "Failed to perform bulk action.";
            }
        }
    }

    if ($success) {
        Utilities::redirect('/admin/public/newsletter.php');
    }
}

// Handle individual subscriber actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $subscriberId = intval($_GET['id']);

    try {
        if ($action === 'unsubscribe') {
            $stmt = $db->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE id = ?");
            $stmt->execute([$subscriberId]);
        } elseif ($action === 'resubscribe') {
            $stmt = $db->prepare("UPDATE newsletter_subscribers SET status = 'subscribed', unsubscribed_at = NULL WHERE id = ?");
            $stmt->execute([$subscriberId]);
        } elseif ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
            $stmt->execute([$subscriberId]);
        }

        $_SESSION['success'] = "Subscriber updated successfully.";
        Utilities::logActivity($_SESSION['user_id'], strtoupper($action) . '_SUBSCRIBER', 'newsletter_subscribers', $subscriberId, $_SERVER['REMOTE_ADDR']);

    } catch (PDOException $e) {
        error_log("Subscriber action error: " . $e->getMessage());
        $_SESSION['error'] = "Failed to update subscriber.";
    }

    Utilities::redirect('/admin/public/newsletter.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <!-- Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Subscriber Stats</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users text-success"></i> Subscribed</span>
                        <span class="badge badge-success badge-pill"><?= $statusCounts['subscribed'] ?? 0 ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-user-times text-warning"></i> Unsubscribed</span>
                        <span class="badge badge-warning badge-pill"><?= $statusCounts['unsubscribed'] ?? 0 ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-exclamation-triangle text-danger"></i> Bounced</span>
                        <span class="badge badge-danger badge-pill"><?= $statusCounts['bounced'] ?? 0 ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-ban text-dark"></i> Complained</span>
                        <span class="badge badge-dark badge-pill"><?= $statusCounts['complained'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-primary btn-sm btn-block" data-toggle="modal" data-target="#addSubscriberModal">
                    <i class="fas fa-plus"></i> Add Subscriber
                </button>
                <a href="<?= BASE_URL ?>/admin/newsletter_export.php" class="btn btn-success btn-sm btn-block">
                    <i class="fas fa-download"></i> Export List
                </a>
                <button type="button" class="btn btn-info btn-sm btn-block" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-upload"></i> Import List
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Subscription Trends Chart -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Subscription Trends</h6>
            </div>
            <div class="card-body">
                <canvas id="subscriptionChart" style="height: 300px;"></canvas>
            </div>
        </div>

        <!-- Subscribers Table -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 font-weight-bold text-primary">Subscribers</h6>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addSubscriberModal">
                                <i class="fas fa-plus"></i> Add Subscriber
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Search and Filter -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search"
                                   placeholder="Search email or name..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="subscribed" <?= $status === 'subscribed' ? 'selected' : '' ?>>Subscribed</option>
                                <option value="unsubscribed" <?= $status === 'unsubscribed' ? 'selected' : '' ?>>Unsubscribed</option>
                                <option value="bounced" <?= $status === 'bounced' ? 'selected' : '' ?>>Bounced</option>
                                <option value="complained" <?= $status === 'complained' ? 'selected' : '' ?>>Complained</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="<?= BASE_URL ?>/admin/newsletter.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>

                <?php if (empty($subscribers)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-envelope-open fa-5x text-muted mb-3"></i>
                        <h5 class="text-muted">No subscribers found</h5>
                        <p class="text-muted">Start building your newsletter list</p>
                    </div>
                <?php else: ?>
                    <form method="POST" id="bulkForm">
                        <input type="hidden" name="action" value="bulk_action">

                        <!-- Bulk Actions -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select name="bulk_action" class="form-control" required>
                                        <option value="">Select Action</option>
                                        <option value="subscribed">Mark as Subscribed</option>
                                        <option value="unsubscribed">Mark as Unsubscribed</option>
                                        <option value="delete">Delete</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-outline-primary" onclick="return confirm('Are you sure?')">
                                            Apply
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Subscribers Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>Email</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Subscribed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subscribers as $subscriber): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="subscriber_ids[]" value="<?= $subscriber['id'] ?>">
                                            </td>
                                            <td><?= htmlspecialchars($subscriber['email']) ?></td>
                                            <td><?= htmlspecialchars($subscriber['name'] ?? 'N/A') ?></td>
                                            <td>
                                                <span class="badge badge-<?= $subscriber['status'] === 'subscribed' ? 'success' : ($subscriber['status'] === 'unsubscribed' ? 'warning' : 'danger') ?>">
                                                    <?= ucfirst($subscriber['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('M j, Y', strtotime($subscriber['subscribed_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <?php if ($subscriber['status'] === 'subscribed'): ?>
                                                        <a href="?action=unsubscribe&id=<?= $subscriber['id'] ?>"
                                                           class="btn btn-outline-warning" title="Unsubscribe">
                                                            <i class="fas fa-user-times"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="?action=resubscribe&id=<?= $subscriber['id'] ?>"
                                                           class="btn btn-outline-success" title="Resubscribe">
                                                            <i class="fas fa-user-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="?action=delete&id=<?= $subscriber['id'] ?>"
                                                       class="btn btn-outline-danger" title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete this subscriber?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Subscribers pagination">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">
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

<!-- Add Subscriber Modal -->
<div class="modal fade" id="addSubscriberModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Subscriber</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_subscriber">

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Subscriber</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Subscription trends chart
const ctx = document.getElementById('subscriptionChart').getContext('2d');
const subscriptionChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($subscriptionTrends, 'month')) ?>,
        datasets: [{
            label: 'New Subscribers',
            data: <?= json_encode(array_column($subscriptionTrends, 'count')) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Select all checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="subscriber_ids[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>