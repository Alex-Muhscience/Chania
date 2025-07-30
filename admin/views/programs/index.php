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
                <input type="text" class="form-control" name="search" 
                       value="<?= htmlspecialchars($search) ?>"
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
            <?php if ($pagination['totalPages'] > 1): ?>
                <nav aria-label="Programs pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                            <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
