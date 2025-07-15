<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$pageTitle = "Events Management";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'Events Management']
];

// Handle event actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $eventId = $_POST['event_id'] ?? '';

    try {
        $db = (new Database())->connect();

        switch ($action) {
            case 'delete':
                if ($eventId) {
                    $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
                    $stmt->execute([$eventId]);
                    $_SESSION['success'] = "Event deleted successfully.";
                }
                break;

            case 'toggle_status':
                if ($eventId) {
                    $stmt = $db->prepare("UPDATE events SET is_active = NOT is_active WHERE id = ?");
                    $stmt->execute([$eventId]);
                    $_SESSION['success'] = "Event status updated successfully.";
                }
                break;
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;

    } catch (PDOException $e) {
        error_log("Event management error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while processing your request.";
    }
}

// Get events
$events = [];
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $db = (new Database())->connect();

    $whereClause = '';
    $params = [];

    if ($search) {
        $whereClause = "WHERE title LIKE ? OR description LIKE ?";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm];
    }

    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(*) FROM events $whereClause");
    $countStmt->execute($params);
    $totalEvents = $countStmt->fetchColumn();

    // Get events
    $stmt = $db->prepare("SELECT * FROM events $whereClause ORDER BY event_date DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    $events = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Events fetch error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading events.";
}

$totalPages = ceil($totalEvents / $limit);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Events Management</h1>
    <div>
        <a href="<?= BASE_URL ?>/admin/public/event_export.php" class="btn btn-outline-success">
            <i class="fas fa-download"></i> Export
        </a>
        <a href="<?= BASE_URL ?>/admin/public/event_add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Event
        </a>
    </div>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search events...">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="<?= BASE_URL ?>/admin/public/events.php" class="btn btn-outline-secondary">
                    <i class="fas fa-refresh"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Events Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($events)): ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                <p class="text-muted">No events found</p>
                <a href="<?= BASE_URL ?>/admin/public/event_add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add First Event
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?= $event['id'] ?></td>
                                <td><?= htmlspecialchars($event['title']) ?></td>
                                <td><?= date('M j, Y', strtotime($event['event_date'])) ?></td>
                                <td><?= htmlspecialchars($event['location'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= $event['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $event['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($event['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?= BASE_URL ?>/admin/public/event_edit.php?id=<?= $event['id'] ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning"
                                                    onclick="return confirm('Toggle event status?')">
                                                <i class="fas fa-toggle-<?= $event['is_active'] ? 'off' : 'on' ?>"></i>
                                            </button>
                                        </form>

                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this event?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Events pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>