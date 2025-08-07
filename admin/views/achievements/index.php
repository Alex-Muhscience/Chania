<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
    <!-- Success/Error Messages -->
    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Achievement added successfully!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php elseif (isset($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Achievement updated successfully!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Achievement deleted successfully!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php elseif (isset($_GET['status_updated'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Achievement status updated successfully!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-trophy mr-2"></i>Achievements & Statistics
        </h1>
        <a href="achievements.php?action=add" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add Achievement
        </a>
    </div>

    <!-- Info Card -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                About Achievements
                            </div>
                            <div class="text-sm text-gray-700">
                                Manage the statistics and achievements displayed on your website's About page. These numbers help showcase your organization's impact and success.
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-info-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Achievements Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-chart-line mr-2"></i>Statistics & Achievements
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($achievements)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-trophy fa-3x text-gray-300 mb-3"></i>
                    <p class="text-gray-600 mb-3">No achievements have been added yet.</p>
                    <a href="achievements.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Add First Achievement
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="50">Icon</th>
                                <th>Achievement Details</th>
                                <th width="120">Statistics</th>
                                <th width="100">Category</th>
                                <th width="100">Status</th>
                                <th width="80">Order</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($achievements as $achievement): ?>
                                <tr>
                                    <td class="text-center align-middle">
                                        <i class="<?= htmlspecialchars($achievement['icon'] ?? 'fas fa-trophy') ?> fa-2x text-primary"></i>
                                    </td>
                                    <td>
                                        <div class="achievement-info">
                                            <h6 class="mb-1">
                                                <?= htmlspecialchars($achievement['title']) ?>
                                                <?php if ($achievement['is_featured']): ?>
                                                    <span class="badge badge-warning badge-sm ml-1">Featured</span>
                                                <?php endif; ?>
                                            </h6>
                                            <?php if ($achievement['description']): ?>
                                                <p class="text-muted small mb-1">
                                                    <?= htmlspecialchars(substr($achievement['description'], 0, 100)) ?>
                                                    <?= strlen($achievement['description']) > 100 ? '...' : '' ?>
                                                </p>
                                            <?php endif; ?>
                                            <div class="small text-muted">
                                                <i class="fas fa-calendar mr-1"></i>
                                                Created: <?= date('M j, Y', strtotime($achievement['created_at'])) ?>
                                                <?php if ($achievement['updated_at']): ?>
                                                    <br><i class="fas fa-edit mr-1"></i>
                                                    Updated: <?= date('M j, Y', strtotime($achievement['updated_at'])) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="stat-display">
                                            <div class="h4 mb-1 text-primary">
                                                <?= htmlspecialchars($achievement['stat_value']) ?><?= $achievement['stat_unit'] ? htmlspecialchars($achievement['stat_unit']) : '' ?>
                                            </div>
                                            <small class="text-muted">Value</small>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-<?= 
                                            $achievement['category'] == 'impact' ? 'success' : 
                                            ($achievement['category'] == 'programs' ? 'primary' : 
                                            ($achievement['category'] == 'students' ? 'info' : 'secondary')) 
                                        ?>">
                                            <?= ucfirst(htmlspecialchars($achievement['category'] ?? 'general')) ?>
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="mb-1">
                                            <?php if ($achievement['is_active']): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($achievement['is_featured']): ?>
                                            <div>
                                                <span class="badge badge-warning badge-sm">Featured</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-light"><?= $achievement['display_order'] ?></span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group-vertical" role="group">
                                            <a href="achievements.php?action=edit&id=<?= $achievement['id'] ?>" 
                                               class="btn btn-outline-primary btn-sm" 
                                               title="Edit Achievement">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="achievements.php?action=toggle_status&id=<?= $achievement['id'] ?>" 
                                               class="btn btn-outline-<?= $achievement['is_active'] ? 'warning' : 'success' ?> btn-sm" 
                                               title="<?= $achievement['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                <i class="fas fa-<?= $achievement['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                            </a>
                                            <a href="achievements.php?action=toggle_featured&id=<?= $achievement['id'] ?>" 
                                               class="btn btn-outline-<?= $achievement['is_featured'] ? 'warning' : 'secondary' ?> btn-sm" 
                                               title="<?= $achievement['is_featured'] ? 'Unfeature' : 'Feature' ?>">
                                                <i class="fas fa-star"></i>
                                            </a>
                                            <a href="achievements.php?action=delete&id=<?= $achievement['id'] ?>" 
                                               class="btn btn-outline-danger btn-sm" 
                                               title="Delete Achievement"
                                               onclick="return confirm('Are you sure you want to delete this achievement? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats -->
    <?php if (!empty($achievements)): ?>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Achievements
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($achievements) ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-trophy fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Active Achievements
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= count(array_filter($achievements, function($a) { return $a['is_active']; })) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Featured
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= count(array_filter($achievements, function($a) { return $a['is_featured']; })) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-star fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Categories
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= count(array_unique(array_column($achievements, 'category'))) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tags fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
