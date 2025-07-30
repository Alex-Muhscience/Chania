<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Applications Management</h1>
    <a href="<?= BASE_URL ?>\admin\public\application_export.php" class="btn btn-outline-success">
        <i class="fas fa-download"></i> Export
    </a>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <input type="text" class="form-control" name="search" 
                       value="<?= htmlspecialchars($search) ?>"
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
                <a href="<?= BASE_URL ?>\admin\public\applications.php" class="btn btn-outline-secondary">
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
                                        <a href="<?= BASE_URL ?>\admin\public\application_view.php?id=<?= $application['id'] ?>"
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
            <?php if ($pagination['totalPages'] > 1): ?>
                <nav aria-label="Applications pagination">
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

