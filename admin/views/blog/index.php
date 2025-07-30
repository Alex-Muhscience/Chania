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
            <span><i class="fas fa-blog me-1"></i>Blog Posts</span>
            <a href="blog_edit.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Post</a>
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
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $filters['category'] === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="published" <?= $filters['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <!-- Blog Posts Table -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Author</th>
                            <th>Published</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No blog posts found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?= $post['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($post['title']) ?></strong>
                                        <?php if ($post['excerpt']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($post['excerpt'], 0, 60)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($post['category']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $post['status'] === 'published' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($post['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($post['is_featured']): ?>
                                            <span class="badge bg-warning">Featured</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($post['author_username']) ?></td>
                                    <td>
                                        <?= $post['published_at'] ? date('M j, Y', strtotime($post['published_at'])) : '-' ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="blog_edit.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                                <button type="submit" name="action" value="<?= $post['status'] === 'published' ? 'unpublish' : 'publish' ?>" class="btn btn-sm btn-outline-<?= $post['status'] === 'published' ? 'warning' : 'success' ?>" title="<?= $post['status'] === 'published' ? 'Unpublish' : 'Publish' ?>">
                                                    <i class="fas fa-<?= $post['status'] === 'published' ? 'eye-slash' : 'eye' ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                                <button type="submit" name="action" value="<?= $post['is_featured'] ? 'unfeature' : 'feature' ?>" class="btn btn-sm btn-outline-<?= $post['is_featured'] ? 'secondary' : 'warning' ?>" title="<?= $post['is_featured'] ? 'Remove Featured' : 'Set Featured' ?>">
                                                    <i class="fas fa-star<?= $post['is_featured'] ? '' : '-o' ?>"></i>
                                                </button>
                                            </form>
                                            <a href="../actions/delete_blog.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this post?')">
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
                                <a class="page-link" href="?page=<?= $i ?>&category=<?= urlencode($filters['category']) ?>&status=<?= urlencode($filters['status']) ?>&search=<?= urlencode($filters['search']) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
