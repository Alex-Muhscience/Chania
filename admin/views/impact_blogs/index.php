<div class="container-fluid px-4">
    <h1 class="mt-4"><?= htmlspecialchars($pageTitle) ?></h1>
    <ol class="breadcrumb mb-4">
        <?php foreach ($breadcrumbs as $breadcrumb): ?>
            <?php if (isset($breadcrumb['url'])): ?>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($breadcrumb['url']) ?>"><?= htmlspecialchars($breadcrumb['title']) ?></a></li>
            <?php else: ?>
                <li class="breadcrumb-item active"><?= htmlspecialchars($breadcrumb['title']) ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <?php if (isset($stats)): ?>
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fs-4 fw-bold"><?= number_format($stats['total']) ?></div>
                                <div>Total Impact Stories</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-trophy fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fs-4 fw-bold"><?= number_format($stats['active']) ?></div>
                                <div>Active Stories</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-eye fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fs-4 fw-bold"><?= number_format($stats['inactive']) ?></div>
                                <div>Inactive Stories</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-eye-slash fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fs-4 fw-bold"><?= number_format($stats['total_views'] ?? 0) ?></div>
                                <div>Total Views</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-trophy me-1"></i>Impact Stories</span>
            <a href="impact_blog_add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Story</a>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search title or content..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $filters['category'] === $cat ? 'selected' : '' ?>><?= ucwords($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <!-- Impact Blogs Table -->
            <div class="table-responsive">
                <table class="table table-striped" id="impactBlogsTable">
                    <thead>
                        <tr>
                            <th>Sort</th>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Author</th>
                            <th>Published</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($impactBlogs)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No impact stories found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($impactBlogs as $blog): ?>
                                <tr data-id="<?= $blog['id'] ?>">
                                    <td>
                                        <i class="fas fa-grip-vertical text-muted drag-handle" style="cursor: move;" title="Drag to reorder"></i>
                                    </td>
                                    <td><?= $blog['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($blog['featured_image']): ?>
                                                <img src="<?= htmlspecialchars($blog['featured_image']) ?>" 
                                                     alt="<?= htmlspecialchars($blog['title']) ?>" 
                                                     class="rounded me-2" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= htmlspecialchars($blog['title']) ?></strong>
                                                <?php if ($blog['excerpt']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars(substr($blog['excerpt'], 0, 60)) ?>...</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= ucwords($blog['category']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $blog['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $blog['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($blog['view_count'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars($blog['author_name'] ?: 'Unknown') ?></td>
                                    <td>
                                        <?= $blog['published_at'] ? date('M j, Y', strtotime($blog['published_at'])) : '-' ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="impact_blog_edit.php?id=<?= $blog['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                                <button type="submit" name="action" value="<?= $blog['is_active'] ? 'deactivate' : 'activate' ?>" 
                                                        class="btn btn-sm btn-outline-<?= $blog['is_active'] ? 'warning' : 'success' ?>" 
                                                        title="<?= $blog['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                    <i class="fas fa-<?= $blog['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                                <button type="submit" name="action" value="delete" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="Delete" 
                                                        onclick="return confirm('Are you sure you want to delete this impact story?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&category=<?= urlencode($filters['category']) ?>&status=<?= urlencode($filters['status']) ?>&search=<?= urlencode($filters['search']) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Include drag-and-drop JavaScript for reordering -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sortable table
    const tbody = document.querySelector('#impactBlogsTable tbody');
    if (tbody && tbody.children.length > 0) {
        new Sortable(tbody, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function(evt) {
                const rows = Array.from(tbody.children);
                rows.forEach((row, index) => {
                    const id = row.dataset.id;
                    const sortOrder = index + 1;
                    
                    // Send AJAX request to update sort order
                    fetch('impact_blogs.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=update_sort_order&id=${id}&sort_order=${sortOrder}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            console.error('Failed to update sort order');
                            // Optionally reload the page or show error
                        }
                    })
                    .catch(error => {
                        console.error('Error updating sort order:', error);
                    });
                });
            }
        });
    }
});
</script>
