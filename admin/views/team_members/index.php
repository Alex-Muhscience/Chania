<div class="row">
    <div class="col-md-12">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Members</h6>
                                <h2><?= number_format($stats['total'] ?? 0) ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Active Members</h6>
                                <h2><?= number_format($stats['active'] ?? 0) ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Inactive Members</h6>
                                <h2><?= number_format($stats['inactive'] ?? 0) ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-times fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">With Photos</h6>
                                <h2><?= number_format($stats['with_photos'] ?? 0) ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-camera fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users"></i> Team Members Management
                    </h5>
                    <a href="team_members.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Member
                    </a>
                </div>
            </div>
            <div class="card-body">
                
                <!-- Filters -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="search">Search Team Members</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($filters['search']) ?>" 
                                       placeholder="Search by name, position, email...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="1" <?= $filters['status'] === 1 ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= $filters['status'] === 0 ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="team_members.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <?php if (!empty($team_members)): ?>
                    <!-- Bulk Actions -->
                    <form method="POST" id="bulk-form">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <select name="bulk_action" class="form-control mr-2" style="width: auto;">
                                    <option value="">Bulk Actions</option>
                                    <option value="delete">Delete Selected</option>
                                </select>
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirmBulkDelete()">Apply</button>
                            </div>
                            <small class="text-muted">
                                Showing <?= count($team_members) ?> of <?= number_format($pagination['totalMembers']) ?> members
                            </small>
                        </div>

                        <!-- Team Members Grid -->
                        <div class="row">
                            <?php foreach ($team_members as $member): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body">
                                            <div class="form-check mb-3">
                                                <input type="checkbox" class="form-check-input member-checkbox" 
                                                       id="member_<?= $member['id'] ?>" 
                                                       name="selected_members[]" 
                                                       value="<?= $member['id'] ?>">
                                                <label class="form-check-label" for="member_<?= $member['id'] ?>"></label>
                                            </div>

                                            <div class="text-center mb-3">
                                                <?php if (!empty($member['image_path'])): ?>
                                                    <img src="<?= htmlspecialchars('uploads/' . $member['image_path']) ?>" 
                                                         alt="<?= htmlspecialchars($member['name']) ?>" 
                                                         class="rounded-circle img-thumbnail" 
                                                         style="width: 80px; height: 80px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                                         style="width: 80px; height: 80px;">
                                                        <i class="fas fa-user fa-2x text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="text-center">
                                                <h6 class="card-title mb-1"><?= htmlspecialchars($member['name']) ?></h6>
                                                <p class="text-muted mb-2"><?= htmlspecialchars($member['position']) ?></p>
                                                
                                                <?php if (!empty($member['email'])): ?>
                                                    <p class="small text-info mb-2">
                                                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($member['email']) ?>
                                                    </p>
                                                <?php endif; ?>

                                                <?php if (!empty($member['phone'])): ?>
                                                    <p class="small text-info mb-2">
                                                        <i class="fas fa-phone"></i> <?= htmlspecialchars($member['phone']) ?>
                                                    </p>
                                                <?php endif; ?>

                                                <?php if (!empty($member['bio'])): ?>
                                                    <p class="card-text small mb-3">
                                                        <?= htmlspecialchars(substr($member['bio'], 0, 100)) ?>
                                                        <?= strlen($member['bio']) > 100 ? '...' : '' ?>
                                                    </p>
                                                <?php endif; ?>

                                                <!-- Social Links -->
                                                <?php 
                                                $socialLinks = [];
                                                if (!empty($member['social_links'])) {
                                                    $socialLinks = json_decode($member['social_links'], true) ?: [];
                                                }
                                                if (!empty($socialLinks)): 
                                                ?>
                                                    <div class="mb-3">
                                                        <?php foreach ($socialLinks as $platform => $url): ?>
                                                            <a href="<?= htmlspecialchars($url) ?>" target="_blank" 
                                                               class="btn btn-outline-primary btn-sm mr-1" 
                                                               title="<?= ucfirst($platform) ?>">
                                                                <i class="fab fa-<?= htmlspecialchars($platform) ?>"></i>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="mb-3">
                                                    <span class="badge badge-<?= $member['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst($member['status']) ?>
                                                    </span>
                                                </div>

                                                <small class="text-muted">
                                                    Added: <?= date('M j, Y', strtotime($member['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="card-footer">
                                            <div class="btn-group btn-group-sm w-100" role="group">
                                                <a href="team_members.php?action=edit&id=<?= $member['id'] ?>" 
                                                   class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="team_members.php?action=toggle_status&id=<?= $member['id'] ?>" 
                                                   class="btn btn-outline-<?= $member['status'] === 'active' ? 'warning' : 'success' ?>" 
                                                   title="<?= $member['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                                    <i class="fas fa-<?= $member['status'] === 'active' ? 'eye-slash' : 'eye' ?>"></i>
                                                    <?= $member['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                                </a>
                                                <a href="team_members.php?action=delete&id=<?= $member['id'] ?>" 
                                                   class="btn btn-outline-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this team member?')" 
                                                   title="Delete">
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
                    <?php if ($pagination['totalPages'] > 1): ?>
                        <nav aria-label="Team members pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="team_members.php?page=<?= $pagination['page'] - 1 ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">
                                            &laquo; Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $start = max(1, $pagination['page'] - 2);
                                $end = min($pagination['totalPages'], $pagination['page'] + 2);
                                ?>

                                <?php if ($start > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="team_members.php?page=1<?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">1</a>
                                    </li>
                                    <?php if ($start > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="team_members.php?page=<?= $i ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($end < $pagination['totalPages']): ?>
                                    <?php if ($end < $pagination['totalPages'] - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="team_members.php?page=<?= $pagination['totalPages'] ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">
                                            <?= $pagination['totalPages'] ?>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="team_members.php?page=<?= $pagination['page'] + 1 ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">
                                            Next &raquo;
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- No Team Members Found -->
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-users fa-3x text-muted"></i>
                        </div>
                        <h4 class="text-muted">No team members found</h4>
                        <p class="text-muted mb-4">
                            <?php if (!empty($filters['search']) || $filters['status'] !== ''): ?>
                                Try adjusting your search criteria or <a href="team_members.php">clear all filters</a>.
                            <?php else: ?>
                                Start by adding your first team member to showcase your team.
                            <?php endif; ?>
                        </p>
                        <a href="team_members.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Team Member
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const individualCheckboxes = document.querySelectorAll('input[name="selected_members[]"]');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            individualCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Update select all state when individual checkboxes change
    individualCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('input[name="selected_members[]"]:checked').length;
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = checkedCount === individualCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < individualCheckboxes.length;
            }
        });
    });
});

function confirmBulkDelete() {
    const selectedMembers = document.querySelectorAll('input[name="selected_members[]"]:checked');
    if (selectedMembers.length === 0) {
        alert('Please select team members to delete.');
        return false;
    }
    
    const action = document.querySelector('select[name="bulk_action"]').value;
    if (action === 'delete') {
        return confirm(`Are you sure you want to delete ${selectedMembers.length} selected team member(s)? This action cannot be undone.`);
    }
    return true;
}
</script>
