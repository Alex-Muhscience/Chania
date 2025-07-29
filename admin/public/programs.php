<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$pageTitle = "Programs Management";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/public/'],
    ['title' => 'Programs Management']
];

// Handle program actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $programId = $_POST['program_id'] ?? '';

    try {
        $db = (new Database())->connect();

        switch ($action) {
            case 'delete':
                if ($programId) {
                    $stmt = $db->prepare("DELETE FROM programs WHERE id = ?");
                    $stmt->execute([$programId]);
                    $_SESSION['success'] = "Program deleted successfully.";
                }
                break;

            case 'toggle_status':
                if ($programId) {
                    $stmt = $db->prepare("UPDATE programs SET is_active = NOT is_active WHERE id = ?");
                    $stmt->execute([$programId]);
                    $_SESSION['success'] = "Program status updated successfully.";
                }
                break;
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;

    } catch (PDOException $e) {
        error_log("Program management error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while processing your request.";
    }
}

// Get programs
$programs = [];
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
    $countStmt = $db->prepare("SELECT COUNT(*) FROM programs $whereClause");
    $countStmt->execute($params);
    $totalPrograms = $countStmt->fetchColumn();

    // Get programs
    $stmt = $db->prepare("SELECT * FROM programs $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    $programs = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Programs fetch error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading programs.";
}

$totalPages = ceil($totalPrograms / $limit);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Programs Management</h1>
    <div>
        <a href="<?= BASE_URL ?>/admin/public/program_export.php" class="btn btn-outline-success">
            <i class="fas fa-download"></i> Export
        </a>
        <a href="<?= BASE_URL ?>/admin/public/program_add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Program
        </a>
    </div>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search programs...">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="<?= BASE_URL ?>/admin/public/programs.php" class="btn btn-outline-secondary">
                    <i class="fas fa-refresh"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Programs Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($programs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                <p class="text-muted">No programs found</p>
                <a href="<?= BASE_URL ?>/admin/public/program_add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add First Program
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Duration</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs as $program): ?>
                            <tr>
                                <td><?= $program['id'] ?></td>
                                <td><?= htmlspecialchars($program['title']) ?></td>
                                <td><?= htmlspecialchars($program['duration'] ?? 'N/A') ?></td>
                                <td><?= $program['fee'] ? '$' . number_format($program['fee'], 2) : 'Free' ?></td>
                                <td>
                                    <span class="badge bg-<?= $program['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $program['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($program['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?= BASE_URL ?>/admin/public/program_edit.php?id=<?= $program['id'] ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning"
                                                    onclick="return confirm('Toggle program status?')">
                                                <i class="fas fa-toggle-<?= $program['is_active'] ? 'off' : 'on' ?>"></i>
                                            </button>
                                        </form>

                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this program?')">
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
                <nav aria-label="Programs pagination">
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