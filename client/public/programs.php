<?php
$pageTitle = "Our Programs - Skills for Africa";
$pageDescription = "Browse our digital skills training programs for African youth";
$activePage = "programs";

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

// Get category filter if set
$categoryFilter = isset($_GET['category']) ? Database::sanitize($_GET['category']) : null;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT * FROM programs";
$countSql = "SELECT COUNT(*) FROM programs";
$params = [];
$countParams = [];

if ($categoryFilter) {
    $sql .= " WHERE category = ?";
    $countSql .= " WHERE category = ?";
    $params[] = $categoryFilter;
    $countParams[] = $categoryFilter;
}

$sql .= " ORDER BY title LIMIT ? OFFSET ?";

// Get programs
$stmt = $db->prepare($sql);

// Bind parameters with proper types
$paramIndex = 1;
if ($categoryFilter) {
    $stmt->bindValue($paramIndex++, $categoryFilter, PDO::PARAM_STR);
}
$stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
$stmt->bindValue($paramIndex, $offset, PDO::PARAM_INT);

$stmt->execute();
$programs = $stmt->fetchAll();

// Get total count
$countStmt = $db->prepare($countSql);
if ($categoryFilter) {
    $countStmt->bindValue(1, $categoryFilter, PDO::PARAM_STR);
}
$countStmt->execute();
$totalPrograms = $countStmt->fetchColumn();
$totalPages = ceil($totalPrograms / $limit);

// Get categories for filter
$categories = getProgramCategories($db);
?>

<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-6">
                <h1 class="fw-bold mb-3">Our Programs</h1>
                <p class="lead">Browse our comprehensive digital skills training programs designed for African youth.</p>
            </div>
            <div class="col-lg-6">
                <form method="get" class="row g-2">
                    <div class="col-md-8">
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <?php 
                                // Handle both array and string formats for backwards compatibility
                                $catValue = is_array($cat) ? ($cat['name'] ?? $cat['id']) : $cat;
                                $catDisplay = is_array($cat) ? ($cat['name'] ?? $catValue) : $cat;
                            ?>
                            <option value="<?php echo htmlspecialchars($catValue); ?>" <?php echo $categoryFilter === $catValue ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($catDisplay); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/programs.php" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($programs)): ?>
        <div class="text-center py-5">
            <div class="py-5">
                <i class="fas fa-graduation-cap fa-4x text-muted mb-4"></i>
                <h3 class="h4">No programs found</h3>
                <p class="text-muted">We couldn't find any programs matching your criteria.</p>
                <a href="<?php echo BASE_URL; ?>/programs.php" class="btn btn-primary">View All Programs</a>
            </div>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($programs as $program): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-img-top overflow-hidden" style="height: 180px;">
                        <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($program['image_path']); ?>"
                             alt="<?php echo htmlspecialchars($program['title']); ?>"
                             class="img-fluid w-100 h-100 object-fit-cover">
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($program['title']); ?></h5>
                            <?php if ($program['is_featured']): ?>
                            <span class="badge bg-primary">Featured</span>
                            <?php endif; ?>
                        </div>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($program['short_description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($program['category']); ?></span>
                            <span class="text-muted small"><?php echo htmlspecialchars($program['duration']); ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <div class="d-grid">
                            <a href="<?php echo BASE_URL; ?>/program.php?id=<?php echo $program['id']; ?>" class="btn btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-5">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/programs.php?<?php echo http_build_query(['category' => $categoryFilter, 'page' => $page - 1]); ?>">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/programs.php?<?php echo http_build_query(['category' => $categoryFilter, 'page' => $i]); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>

                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/programs.php?<?php echo http_build_query(['category' => $categoryFilter, 'page' => $page + 1]); ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>