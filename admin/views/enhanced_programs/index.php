<?php
// Include header
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Enhanced Programs Management</h1>
    <div class="d-none d-sm-inline-block">
        <a href="<?= BASE_URL ?>/admin/public/programs.php?action=add" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Program
        </a>
    </div>
</div>

<!-- Flash Messages -->
<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Search and Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Search Programs</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2" 
                   placeholder="Search programs..." value="<?= htmlspecialchars($search ?? '') ?>">
            <button type="submit" class="btn btn-primary mr-2">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if (!empty($search)): ?>
                <a href="enhanced_programs.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Programs Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Programs List</h6>
        <span class="badge badge-secondary">
            <?= ($pagination['totalPrograms'] ?? 0) ?> Total Programs
        </span>
    </div>
    <div class="card-body">
        <?php if (empty($programs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-graduation-cap fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-gray-600">No programs found</h5>
                <p class="text-muted">
                    <?= !empty($search) ? 'No programs match your search criteria.' : 'No programs have been created yet.' ?>
                </p>
                <a href="<?= BASE_URL ?>/admin/public/programs.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Program
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable">
                    <thead class="thead-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Duration</th>
                            <th>Fee</th>
                            <th width="80">Schedules</th>
                            <th width="80">Status</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs as $program): ?>
                            <tr>
                                <td class="font-weight-bold"><?= $program['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($program['image_path'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($program['image_path']) ?>" 
                                                 class="rounded mr-2" width="40" height="40" 
                                                 style="object-fit: cover;" alt="Program">
                                        <?php else: ?>
                                            <div class="bg-primary rounded mr-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-graduation-cap text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="font-weight-bold"><?= htmlspecialchars($program['title']) ?></div>
                                            <div class="text-muted small">
                                                <?= htmlspecialchars(substr($program['description'], 0, 60)) ?><?= strlen($program['description']) > 60 ? '...' : '' ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?= htmlspecialchars($program['category'] ?: 'General') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($program['duration']) ?></td>
                                <td>
                                    <?php if ($program['fee'] > 0): ?>
                                        <span class="font-weight-bold text-success">
                                            KES <?= number_format($program['fee']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Free</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $program['schedule_count'] > 0 ? 'success' : 'warning' ?>">
                                        <?= $program['schedule_count'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                        <button type="submit" class="btn btn-sm <?= $program['is_active'] ? 'btn-success' : 'btn-secondary' ?>">
                                            <?= $program['is_active'] ? 'Active' : 'Inactive' ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="enhanced_programs.php?action=edit&id=<?= $program['id'] ?>" 
                                           class="btn btn-primary btn-sm" title="Edit Program">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="enhanced_programs.php?action=schedules&program_id=<?= $program['id'] ?>" 
                                           class="btn btn-info btn-sm" title="Manage Schedules">
                                            <i class="fas fa-calendar-alt"></i>
                                        </a>
                                        <form method="POST" style="display: inline-block;" 
                                              onsubmit="return confirm('Are you sure you want to delete this program? This action cannot be undone.')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete Program">
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
            <?php if (($pagination['totalPages'] ?? 1) > 1): ?>
                <nav aria-label="Programs pagination">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= ($pagination['totalPages'] ?? 1); $i++): ?>
                            <li class="page-item <?= $i === ($pagination['page'] ?? 1) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
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

<!-- Statistics Card -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Programs</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pagination['totalPrograms'] ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Programs</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= array_reduce($programs ?? [], function($count, $program) { return $count + ($program['is_active'] ? 1 : 0); }, 0) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Schedules</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= array_reduce($programs ?? [], function($count, $program) { return $count + $program['schedule_count']; }, 0) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Featured Programs</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= array_reduce($programs ?? [], function($count, $program) { return $count + ($program['is_featured'] ? 1 : 0); }, 0) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-star fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../../includes/footer.php';
?>
