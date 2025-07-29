<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$pageTitle = "Event Registrations";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'Event Registrations']
];

$errors = [];
$success = false;

// Get filter parameters
$search = $_GET['search'] ?? '';
$eventId = $_GET['event_id'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

try {
    $db = (new Database())->connect();

    // Get events for filter
    $stmt = $db->query("
        SELECT id, title 
        FROM events 
        WHERE deleted_at IS NULL 
        ORDER BY event_date DESC
    ");
    $events = $stmt->fetchAll();

    // Build query conditions
    $conditions = [];
    $params = [];

    if ($search) {
        $conditions[] = "(er.first_name LIKE ? OR er.last_name LIKE ? OR er.email LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($eventId) {
        $conditions[] = "er.event_id = ?";
        $params[] = $eventId;
    }

    if ($status) {
        $conditions[] = "er.status = ?";
        $params[] = $status;
    }

    $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

    // Get total count
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM event_registrations er
        JOIN events e ON er.event_id = e.id
        $whereClause
    ");
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    $totalPages = ceil($totalItems / $limit);

    // Get registrations
    $stmt = $db->prepare("
        SELECT er.*, e.title as event_title, e.event_date
        FROM event_registrations er
        JOIN events e ON er.event_id = e.id
        $whereClause
        ORDER BY er.registration_date DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([...$params, $limit, $offset]);
    $registrations = $stmt->fetchAll();

    // Get status counts
    $stmt = $db->query("
        SELECT status, COUNT(*) as count
        FROM event_registrations
        GROUP BY status
    ");
    $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Get upcoming events with registration counts
    $stmt = $db->query("
        SELECT 
            e.id, e.title, e.event_date, e.max_attendees,
            COUNT(er.id) as registration_count
        FROM events e
        LEFT JOIN event_registrations er ON e.id = er.event_id
        WHERE e.event_date >= CURDATE() 
        AND e.is_active = TRUE 
        AND e.deleted_at IS NULL
        GROUP BY e.id
        ORDER BY e.event_date ASC
        LIMIT 5
    ");
    $upcomingEvents = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Event registrations fetch error: " . $e->getMessage());
    $registrations = [];
    $events = [];
    $totalItems = 0;
    $totalPages = 0;
    $statusCounts = [];
    $upcomingEvents = [];
}

// Handle registration status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $registrationId = intval($_POST['registration_id']);
    $newStatus = $_POST['new_status'];

    if (in_array($newStatus, ['registered', 'confirmed', 'cancelled', 'attended', 'no_show'])) {
        try {
            $stmt = $db->prepare("UPDATE event_registrations SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newStatus, $registrationId]);

            $success = true;
            Utilities::logActivity($_SESSION['user_id'], 'UPDATE_REGISTRATION_STATUS', 'event_registrations', $registrationId, $_SERVER['REMOTE_ADDR']);

        } catch (PDOException $e) {
            error_log("Registration status update error: " . $e->getMessage());
            $errors[] = "Failed to update registration status.";
        }
    }
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_action') {
    $registrationIds = $_POST['registration_ids'] ?? [];
    $bulkAction = $_POST['bulk_action'] ?? '';

    if (!empty($registrationIds) && $bulkAction) {
        try {
            $placeholders = str_repeat('?,', count($registrationIds) - 1) . '?';

            if ($bulkAction === 'delete') {
                $stmt = $db->prepare("DELETE FROM event_registrations WHERE id IN ($placeholders)");
                $stmt->execute($registrationIds);
            } elseif (in_array($bulkAction, ['confirmed', 'cancelled', 'attended'])) {
                $stmt = $db->prepare("UPDATE event_registrations SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)");
                $stmt->execute([$bulkAction, ...$registrationIds]);
            }

            $success = true;
            Utilities::logActivity($_SESSION['user_id'], 'BULK_UPDATE_REGISTRATIONS', 'event_registrations', null, $_SERVER['REMOTE_ADDR']);

        } catch (PDOException $e) {
            error_log("Bulk action error: " . $e->getMessage());
            $errors[] = "Failed to perform bulk action.";
        }
    }
}

if ($success) {
    Utilities::redirect('/admin/event_registrations.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <!-- Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Registration Stats</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-user-plus text-primary"></i> Registered</span>
                        <span class="badge badge-primary badge-pill"><?= $statusCounts['registered'] ?? 0 ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-check text-success"></i> Confirmed</span>
                        <span class="badge badge-success badge-pill"><?= $statusCounts['confirmed'] ?? 0 ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-times text-danger"></i> Cancelled</span>
                        <span class="badge badge-danger badge-pill"><?= $statusCounts['cancelled'] ?? 0 ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-user-check text-info"></i> Attended</span>
                        <span class="badge badge-info badge-pill"><?= $statusCounts['attended'] ?? 0 ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-user-times text-warning"></i> No Show</span>
                        <span class="badge badge-warning badge-pill"><?= $statusCounts['no_show'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Upcoming Events</h6>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingEvents)): ?>
                    <p class="text-muted">No upcoming events</p>
                <?php else: ?>
                    <?php foreach ($upcomingEvents as $event): ?>
                        <div class="mb-3">
                            <h6 class="mb-1"><?= htmlspecialchars($event['title']) ?></h6>
                            <small class="text-muted">
                                <?= date('M j, Y', strtotime($event['event_date'])) ?>
                            </small>
                            <div class="progress progress-sm mt-1">
                                <?php
                                $percentage = $event['max_attendees'] ?
                                    ($event['registration_count'] / $event['max_attendees']) * 100 : 0;
                                ?>
                                <div class="progress-bar" style="width: <?= min(100, $percentage) ?>%"></div>
                            </div>
                            <small class="text-muted">
                                <?= $event['registration_count'] ?><?= $event['max_attendees'] ? '/' . $event['max_attendees'] : '' ?> registrations
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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

        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 font-weight-bold text-primary">Event Registrations</h6>
                    </div>
                    <div class="col-auto">
                        <a href="<?= BASE_URL ?>/admin/event_registration_export.php" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Export
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Search and Filter -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search"
                                   placeholder="Search participants..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="event_id" class="form-control">
                                <option value="">All Events</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>" <?= $eventId == $event['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($event['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="registered" <?= $status === 'registered' ? 'selected' : '' ?>>Registered</option>
                                <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                <option value="attended" <?= $status === 'attended' ? 'selected' : '' ?>>Attended</option>
                                <option value="no_show" <?= $status === 'no_show' ? 'selected' : '' ?>>No Show</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="<?= BASE_URL ?>/admin/event_registrations.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>

                <?php if (empty($registrations)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-user-plus fa-5x text-muted mb-3"></i>
                        <h5 class="text-muted">No registrations found</h5>
                        <p class="text-muted">Event registrations will appear here</p>
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
                                        <option value="confirmed">Mark as Confirmed</option>
                                        <option value="cancelled">Mark as Cancelled</option>
                                        <option value="attended">Mark as Attended</option>
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

                        <!-- Registrations Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>Participant</th>
                                        <th>Event</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registrations as $registration): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="registration_ids[]" value="<?= $registration['id'] ?>">
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) ?></strong>
                                                <?php if ($registration['organization']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($registration['organization']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($registration['event_title']) ?></strong>
                                                <br><small class="text-muted"><?= date('M j, Y', strtotime($registration['event_date'])) ?></small>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($registration['email']) ?><br>
                                                    <?php if ($registration['phone']): ?>
                                                        <i class="fas fa-phone"></i> <?= htmlspecialchars($registration['phone']) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?=
                                                    $registration['status'] === 'confirmed' ? 'success' :
                                                    ($registration['status'] === 'cancelled' ? 'danger' :
                                                    ($registration['status'] === 'attended' ? 'info' :
                                                    ($registration['status'] === 'no_show' ? 'warning' : 'primary')))
                                                ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $registration['status'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('M j, Y', strtotime($registration['registration_date'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary"
                                                            data-toggle="modal" data-target="#statusModal<?= $registration['id'] ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info"
                                                            data-toggle="modal" data-target="#viewModal<?= $registration['id'] ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
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
                        <nav aria-label="Registrations pagination">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&event_id=<?= urlencode($eventId) ?>&status=<?= urlencode($status) ?>">
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

<!-- Status Update Modals -->
<?php foreach ($registrations as $registration): ?>
    <div class="modal fade" id="statusModal<?= $registration['id'] ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="registration_id" value="<?= $registration['id'] ?>">

                        <p><strong>Participant:</strong> <?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) ?></p>
                        <p><strong>Event:</strong> <?= htmlspecialchars($registration['event_title']) ?></p>

                        <div class="form-group">
                            <label for="new_status">New Status</label>
                            <select name="new_status" class="form-control" required>
                                <option value="registered" <?= $registration['status'] === 'registered' ? 'selected' : '' ?>>Registered</option>
                                <option value="confirmed" <?= $registration['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="cancelled" <?= $registration['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                <option value="attended" <?= $registration['status'] === 'attended' ? 'selected' : '' ?>>Attended</option>
                                <option value="no_show" <?= $registration['status'] === 'no_show' ? 'selected' : '' ?>>No Show</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewModal<?= $registration['id'] ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registration Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Participant Information</h6>
                            <p><strong>Name:</strong> <?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($registration['email']) ?></p>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($registration['phone'] ?? 'N/A') ?></p>
                            <p><strong>Organization:</strong> <?= htmlspecialchars($registration['organization'] ?? 'N/A') ?></p>
                            <p><strong>Position:</strong> <?= htmlspecialchars($registration['position'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Event Information</h6>
                            <p><strong>Event:</strong> <?= htmlspecialchars($registration['event_title']) ?></p>
                            <p><strong>Date:</strong> <?= date('M j, Y', strtotime($registration['event_date'])) ?></p>
                            <p><strong>Status:</strong>
                                <span class="badge badge-<?=
                                    $registration['status'] === 'confirmed' ? 'success' :
                                    ($registration['status'] === 'cancelled' ? 'danger' : 'primary')
                                ?>">
                                    <?= ucfirst(str_replace('_', ' ', $registration['status'])) ?>
                                </span>
                            </p>
                            <p><strong>Registered:</strong> <?= date('M j, Y g:i A', strtotime($registration['registration_date'])) ?></p>
                        </div>
                    </div>

                    <?php if ($registration['dietary_requirements']): ?>
                        <h6>Dietary Requirements</h6>
                        <p><?= nl2br(htmlspecialchars($registration['dietary_requirements'])) ?></p>
                    <?php endif; ?>

                    <?php if ($registration['accessibility_needs']): ?>
                        <h6>Accessibility Needs</h6>
                        <p><?= nl2br(htmlspecialchars($registration['accessibility_needs'])) ?></p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
// Select all checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="registration_ids[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>