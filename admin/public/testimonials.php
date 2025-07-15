
<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$pageTitle = "Testimonials Management";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'Testimonials Management']
];

$errors = [];
$success = false;

// Get filter parameters
$search = $_GET['search'] ?? '';
$programId = $_GET['program_id'] ?? '';
$featured = $_GET['featured'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

try {
    $db = (new Database())->connect();
    
    // Get programs for filter
    $stmt = $db->query("
        SELECT id, title 
        FROM programs 
        WHERE is_active = TRUE AND deleted_at IS NULL 
        ORDER BY title ASC
    ");
    $programs = $stmt->fetchAll();
    
    // Build query conditions
    $conditions = ["t.deleted_at IS NULL"];
    $params = [];
    
    if ($search) {
        $conditions[] = "(t.author_name LIKE ? OR t.author_title LIKE ? OR t.content LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($programId) {
        $conditions[] = "t.program_id = ?";
        $params[] = $programId;
    }
    
    if ($featured !== '') {
        $conditions[] = "t.is_featured = ?";
        $params[] = $featured;
    }
    
    $whereClause = "WHERE " . implode(" AND ", $conditions);
    
    // Get total count
    $stmt = $db->prepare("SELECT COUNT(*) FROM testimonials t $whereClause");
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    $totalPages = ceil($totalItems / $limit);
    
    // Get testimonials
    $stmt = $db->prepare("
        SELECT t.*, p.title as program_title, u.full_name as created_by_name
        FROM testimonials t
        LEFT JOIN programs p ON t.program_id = p.id
        LEFT JOIN users u ON t.created_by = u.id
        $whereClause
        ORDER BY t.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([...$params, $limit, $offset]);
    $testimonials = $stmt->fetchAll();
    
    // Get statistics
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
            AVG(rating) as avg_rating
        FROM testimonials 
        WHERE deleted_at IS NULL
    ");
    $stats = $stmt->fetch();
    
} catch (PDOException $e) {
    error_log("Testimonials fetch error: " . $e->getMessage());
    $testimonials = [];
    $programs = [];
    $totalItems = 0;
    $totalPages = 0;
    $stats = ['total' => 0, 'featured' => 0, 'active' => 0, 'avg_rating' => 0];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $id = $action === 'edit' ? intval($_POST['id']) : null;
        $authorName = trim($_POST['author_name'] ?? '');
        $authorTitle = trim($_POST['author_title'] ?? '');
        $authorCompany = trim($_POST['author_company'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $rating = intval($_POST['rating'] ?? 5);
        $programId = $_POST['program_id'] ?: null;
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $displayOrder = intval($_POST['display_order'] ?? 0);
        
        // Validation
        if (empty($authorName)) {
            $errors[] = "Author name is required.";
        }
        if (empty($authorTitle)) {
            $errors[] = "Author title is required.";
        }
        if (empty($content)) {
            $errors[] = "Content is required.";
        }
        if ($rating < 1 || $rating > 5) {
            $errors[] = "Rating must be between 1 and 5.";
        }
        
        if (empty($errors)) {
            try {
                $db->beginTransaction();
                
                if ($action === 'add') {
                    $stmt = $db->prepare("
                        INSERT INTO testimonials 
                        (author_name, author_title, author_company, content, rating, program_id, 
                         is_featured, is_active, display_order, created_by, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    $stmt->execute([
                        $authorName, $authorTitle, $authorCompany, $content, $rating, 
                        $programId, $isFeatured, $isActive, $displayOrder, $_SESSION['user_id']
                    ]);
                    $testimonialId = $db->lastInsertId();
                } else {
                    $stmt = $db->prepare("
                        UPDATE testimonials 
                        SET author_name = ?, author_title = ?, author_company = ?, content = ?, 
                            rating = ?, program_id = ?, is_featured = ?, is_active = ?, 
                            display_order = ?, updated_at = NOW()
                        WHERE id = ? AND deleted_at IS NULL
                    ");
                    $stmt->execute([
                        $authorName, $authorTitle, $authorCompany, $content, $rating, 
                        $programId, $isFeatured, $isActive, $displayOrder, $id
                    ]);
                    $testimonialId = $id;
                }
                
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = Utilities::uploadFile($_FILES['image'], 'testimonial', $testimonialId);
                    if ($uploadResult['success']) {
                        $stmt = $db->prepare("UPDATE testimonials SET image_path = ? WHERE id = ?");
                        $stmt->execute([$uploadResult['path'], $testimonialId]);
                    }
                }
                
                $db->commit();
                $success = true;
                
                Utilities::logActivity(
                    $_SESSION['user_id'], 
                    $action === 'add' ? 'CREATE_TESTIMONIAL' : 'UPDATE_TESTIMONIAL', 
                    'testimonials', 
                    $testimonialId, 
                    $_SERVER['REMOTE_ADDR']
                );
                
            } catch (PDOException $e) {
                $db->rollback();
                error_log("Testimonial save error: " . $e->getMessage());
                $errors[] = "Failed to save testimonial. Please try again.";
            }
        }
    }
    
    elseif ($action === 'bulk_action') {
        $testimonialIds = $_POST['testimonial_ids'] ?? [];
        $bulkAction = $_POST['bulk_action'] ?? '';
        
        if (!empty($testimonialIds) && $bulkAction) {
            try {
                $placeholders = str_repeat('?,', count($testimonialIds) - 1) . '?';
                
                if ($bulkAction === 'delete') {
                    $stmt = $db->prepare("UPDATE testimonials SET deleted_at = NOW() WHERE id IN ($placeholders)");
                    $stmt->execute($testimonialIds);
                } elseif ($bulkAction === 'feature') {
                    $stmt = $db->prepare("UPDATE testimonials SET is_featured = 1 WHERE id IN ($placeholders)");
                    $stmt->execute($testimonialIds);
                } elseif ($bulkAction === 'unfeature') {
                    $stmt = $db->prepare("UPDATE testimonials SET is_featured = 0 WHERE id IN ($placeholders)");
                    $stmt->execute($testimonialIds);
                } elseif ($bulkAction === 'activate') {
                    $stmt = $db->prepare("UPDATE testimonials SET is_active = 1 WHERE id IN ($placeholders)");
                    $stmt->execute($testimonialIds);
                } elseif ($bulkAction === 'deactivate') {
                    $stmt = $db->prepare("UPDATE testimonials SET is_active = 0 WHERE id IN ($placeholders)");
                    $stmt->execute($testimonialIds);
                }
                
                $success = true;
                Utilities::logActivity($_SESSION['user_id'], 'BULK_UPDATE_TESTIMONIALS', 'testimonials', null, $_SERVER['REMOTE_ADDR']);
                
            } catch (PDOException $e) {
                error_log("Bulk action error: " . $e->getMessage());
                $errors[] = "Failed to perform bulk action.";
            }
        }
    }
    
    if ($success) {
        Utilities::redirect('/admin/testimonials.php');
    }
}

// Handle individual actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $testimonialId = intval($_GET['id']);
    
    try {
        if ($action === 'delete') {
            $stmt = $db->prepare("UPDATE testimonials SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$testimonialId]);
        } elseif ($action === 'toggle_featured') {
            $stmt = $db->prepare("UPDATE testimonials SET is_featured = NOT is_featured WHERE id = ?");
            $stmt->execute([$testimonialId]);
        } elseif ($action === 'toggle_active') {
            $stmt = $db->prepare("UPDATE testimonials SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$testimonialId]);
        }
        
        $_SESSION['success'] = "Testimonial updated successfully.";
        Utilities::logActivity($_SESSION['user_id'], strtoupper($action) . '_TESTIMONIAL', 'testimonials', $testimonialId, $_SERVER['REMOTE_ADDR']);
        
    } catch (PDOException $e) {
        error_log("Testimonial action error: " . $e->getMessage());
        $_SESSION['error'] = "Failed to update testimonial.";
    }
    
    Utilities::redirect('/admin/testimonials.php');
}

// Get testimonial for editing
$editTestimonial = null;
if (isset($_GET['edit']) && $_GET['edit']) {
    $editId = intval($_GET['edit']);
    try {
        $stmt = $db->prepare("SELECT * FROM testimonials WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$editId]);
        $editTestimonial = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Edit testimonial fetch error: " . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <!-- Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 font-weight-bold text-primary"><?= number_format($stats['total']) ?></div>
                        <div class="text-xs text-gray-500">Total</div>
                    </div>
                    <div class="col-6">
                        <div class="h4 font-weight-bold text-success"><?= number_format($stats['featured']) ?></div>
                        <div class="text-xs text-gray-500">Featured</div>
                    </div>
                    <div class="col-6">
                        <div class="h4 font-weight-bold text-info"><?= number_format($stats['active']) ?></div>
                        <div class="text-xs text-gray-500">Active</div>
                    </div>
                    <div class="col-6">
                        <div class="h4 font-weight-bold text-warning"><?= number_format($stats['avg_rating'], 1) ?></div>
                        <div class="text-xs text-gray-500">Avg Rating</div>
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
                <button type="button" class="btn btn-primary btn-sm btn-block" data-toggle="modal" data-target="#testimonialModal">
                    <i class="fas fa-plus"></i> Add Testimonial
                </button>
                <a href="<?= BASE_URL ?>/admin/testimonials.php?featured=1" class="btn btn-success btn-sm btn-block">
                    <i class="fas fa-star"></i> View Featured
                </a>
                <a href="<?= BASE_URL ?>/admin/testimonials_export.php" class="btn btn-info btn-sm btn-block">
                    <i class="fas fa-download"></i> Export Data
                </a>
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
                        <h6 class="m-0 font-weight-bold text-primary">Testimonials</h6>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#testimonialModal">
                            <i class="fas fa-plus"></i> Add Testimonial
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Search and Filter -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search"
                                   placeholder="Search testimonials..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="program_id" class="form-control">
                                <option value="">All Programs</option>
                                <?php foreach ($programs as $program): ?>
                                    <option value="<?= $program['id'] ?>" <?= $programId == $program['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($program['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="featured" class="form-control">
                                <option value="">All Status</option>
                                <option value="1" <?= $featured === '1' ? 'selected' : '' ?>>Featured</option>
                                <option value="0" <?= $featured === '0' ? 'selected' : '' ?>>Not Featured</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="<?= BASE_URL ?>/admin/testimonials.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>

                <?php if (empty($testimonials)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-quote-left fa-5x text-muted mb-3"></i>
                        <h5 class="text-muted">No testimonials found</h5>
                        <p class="text-muted">Add testimonials to build trust with your audience</p>
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
                                        <option value="feature">Mark as Featured</option>
                                        <option value="unfeature">Remove from Featured</option>
                                        <option value="activate">Activate</option>
                                        <option value="deactivate">Deactivate</option>
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

                        <!-- Testimonials Grid -->
                        <div class="row">
                            <?php foreach ($testimonials as $testimonial): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input type="checkbox" name="testimonial_ids[]" value="<?= $testimonial['id'] ?>" class="form-check-input">
                                            </div>

                                            <?php if ($testimonial['image_path']): ?>
                                                <div class="text-center mb-3">
                                                    <img src="<?= BASE_URL ?>/<?= $testimonial['image_path'] ?>"
                                                         class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                                </div>
                                            <?php endif; ?>

                                            <div class="text-center mb-3">
                                                <h6 class="mb-0"><?= htmlspecialchars($testimonial['author_name']) ?></h6>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($testimonial['author_title']) ?>
                                                    <?php if ($testimonial['author_company']): ?>
                                                        at <?= htmlspecialchars($testimonial['author_company']) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>

                                            <div class="mb-3">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                                <?php endfor; ?>
                                            </div>

                                            <p class="card-text">
                                                <small><?= Utilities::truncate($testimonial['content'], 100) ?></small>
                                            </p>

                                            <?php if ($testimonial['program_title']): ?>
                                                <div class="mb-2">
                                                    <span class="badge badge-info"><?= htmlspecialchars($testimonial['program_title']) ?></span>
                                                </div>
                                            <?php endif; ?>

                                            <div class="mb-2">
                                                <?php if ($testimonial['is_featured']): ?>
                                                    <span class="badge badge-success">Featured</span>
                                                <?php endif; ?>
                                                <?php if ($testimonial['is_active']): ?>
                                                    <span class="badge badge-primary">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="card-footer">
                                            <div class="btn-group btn-group-sm d-flex" role="group">
                                                <a href="?edit=<?= $testimonial['id'] ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?action=toggle_featured&id=<?= $testimonial['id'] ?>"
                                                   class="btn btn-outline-<?= $testimonial['is_featured'] ? 'warning' : 'success' ?>">
                                                    <i class="fas fa-star"></i>
                                                </a>
                                                <a href="?action=toggle_active&id=<?= $testimonial['id'] ?>"
                                                   class="btn btn-outline-<?= $testimonial['is_active'] ? 'secondary' : 'info' ?>">
                                                    <i class="fas fa-eye<?= $testimonial['is_active'] ? '-slash' : '' ?>"></i>
                                                </a>
                                                <a href="?action=delete&id=<?= $testimonial['id'] ?>"
                                                   class="btn btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this testimonial?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </form>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Testimonials pagination">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&program_id=<?= urlencode($programId) ?>&featured=<?= urlencode($featured) ?>">
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

<!-- Add/Edit Testimonial Modal -->
<div class="modal fade" id="testimonialModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $editTestimonial ? 'Edit' : 'Add' ?> Testimonial</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="<?= $editTestimonial ? 'edit' : 'add' ?>">
                    <?php if ($editTestimonial): ?>
                        <input type="hidden" name="id" value="<?= $editTestimonial['id'] ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="author_name">Author Name *</label>
                                <input type="text" class="form-control" id="author_name" name="author_name"
                                       value="<?= htmlspecialchars($editTestimonial['author_name'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="author_title">Author Title *</label>
                                <input type="text" class="form-control" id="author_title" name="author_title"
                                       value="<?= htmlspecialchars($editTestimonial['author_title'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="author_company">Author Company</label>
                                <input type="text" class="form-control" id="author_company" name="author_company"
                                       value="<?= htmlspecialchars($editTestimonial['author_company'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rating">Rating *</label>
                                <select class="form-control" id="rating" name="rating" required>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($editTestimonial['rating'] ?? 5) == $i ? 'selected' : '' ?>>
                                            <?= $i ?> Star<?= $i > 1 ? 's' : '' ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="content">Content *</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required><?= htmlspecialchars($editTestimonial['content'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="program_id">Related Program</label>
                                <select class="form-control" id="program_id" name="program_id">
                                    <option value="">Select Program</option>
                                    <?php foreach ($programs as $program): ?>
                                        <option value="<?= $program['id'] ?>"
                                                <?= ($editTestimonial['program_id'] ?? '') == $program['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($program['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="display_order">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order"
                                       value="<?= $editTestimonial['display_order'] ?? 0 ?>" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">Author Photo</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                        <?php if ($editTestimonial && $editTestimonial['image_path']): ?>
                            <small class="form-text text-muted">
                                Current: <a href="<?= BASE_URL ?>/<?= $editTestimonial['image_path'] ?>" target="_blank">View Image</a>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured"
                                   <?= ($editTestimonial['is_featured'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_featured">
                                Featured Testimonial
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                   <?= ($editTestimonial['is_active'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <?= $editTestimonial ? 'Update' : 'Add' ?> Testimonial
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($editTestimonial): ?>
<script>
$(document).ready(function() {
    $('#testimonialModal').modal('show');
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>