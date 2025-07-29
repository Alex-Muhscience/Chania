<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$pageTitle = "Applications Management";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'Applications Management']
];

// Handle application actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $applicationId = $_POST['application_id'] ?? '';

    try {
        $db = (new Database())->connect();

        switch ($action) {
            case 'update_status':
                if ($applicationId) {
                    $status = $_POST['status'] ?? '';
                    $stmt = $db->prepare("UPDATE applications SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $applicationId]);
                    $_SESSION['success'] = "Application status updated successfully.";
                }
                break;

            case 'delete':
                if ($applicationId) {
                    $stmt = $db->prepare("DELETE FROM applications WHERE id = ?");
                    $stmt->execute([$applicationId]);
                    $_SESSION['success'] = "Application deleted successfully.";
                }
                break;
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;

    } catch (PDOException $e) {
        error_log("Application management error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while processing your request.";
    }
}

// Get applications
$applications = [];
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $db = (new Database())->connect();

    $whereClause = '';
    $params = [];

    $conditions = [];

    if ($search) {
        $conditions[] = "(CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($status) {
        $conditions[] = "status = ?";
        $params[] = $status;
    }

    if (!empty($conditions)) {
        $whereClause = "WHERE " . implode(" AND ", $conditions);
    }

    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(*) FROM applications $whereClause");
    $countStmt->execute($params);
    $totalApplications = $countStmt->fetchColumn();

    // Get applications
    $stmt = $db->prepare("SELECT a.*, p.title as program_title, CONCAT(a.first_name, ' ', a.last_name) as full_name FROM applications a LEFT JOIN programs p ON a.program_id = p.id $whereClause ORDER BY a.submitted_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    $applications = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Applications fetch error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading applications.";
}

$totalPages = ceil($totalApplications / $limit);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Applications Management</h1>
    <a href="<?= BASE_URL ?>/admin/public/application_export.php" class="btn btn-outline-success">
        <i class="fas fa-download"></i> Export
    </a>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search applications...">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="<?= BASE_URL ?>/admin/public/applications.php" class="btn btn-outline-secondary">
                    <i class="fas fa-refresh"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Applications Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($applications)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <p class="text-muted">No applications found</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped no-datatables">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $application): ?>
                            <tr>
                                <td><?= $application['id'] ?></td>
                                <td><?= htmlspecialchars($application['full_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($application['email']) ?></td>
                                <td><?= htmlspecialchars($application['program_title'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= $application['status'] === 'approved' ? 'success' : ($application['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($application['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($application['submitted_at'])) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?= BASE_URL ?>/admin/public/application_view.php?id=<?= $application['id'] ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-check text-success"></i> Approve
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-times text-danger"></i> Reject
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                                                        <input type="hidden" name="status" value="pending">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-clock text-warning"></i> Pending
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>

                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this application?')">
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
                <nav aria-label="Applications pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>