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

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-question-circle me-1"></i>FAQs</span>
            <a href="faq_edit.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add FAQ</a>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search questions or answers..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $filters['category'] === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="1" <?= $filters['status'] === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $filters['status'] === 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <!-- FAQs Table -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Question</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($faqs)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No FAQs found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($faqs as $faq): ?>
                                <tr>
                                    <td><?= $faq['id'] ?></td>
                                    <td><?= htmlspecialchars($faq['question']) ?></td>
                                    <td><?= htmlspecialchars($faq['category']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $faq['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $faq['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= $faq['display_order'] ?></td>
                                    <td><?= htmlspecialchars($faq['created_by_username']) ?></td>
                                    <td><?= date('M j, Y', strtotime($faq['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="faq_edit.php?id=<?= $faq['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $faq['id'] ?>">
                                                <button type="submit" name="action" value="<?= $faq['is_active'] ? 'deactivate' : 'activate' ?>" class="btn btn-sm btn-outline-warning" title="<?= $faq['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                    <i class="fas fa-toggle-<?= $faq['is_active'] ? 'on' : 'off' ?>"></i>
                                                </button>
                                            </form>
                                            <a href="../actions/delete_faq.php?id=<?= $faq['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this FAQ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
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
                                <a class="page-link" href="?page=<?= $i ?>&category=<?= urlencode($filters['category']) ?>&status=<?= $filters['status'] ?>&search=<?= urlencode($filters['search']) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
