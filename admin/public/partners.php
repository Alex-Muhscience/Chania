<?php
$pageTitle = 'Partners';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Require authentication
Utilities::requireLogin();

if (!function_exists('_can_manage_entity')) {
    function _can_manage_entity($entity) {
        return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'editor']);
    }
}
$can_manage = _can_manage_entity('partner');

$db = (new Database())->connect();
$stmt = $db->query("
    SELECT p.*, u.full_name as created_by_name 
    FROM partners p 
    LEFT JOIN users u ON p.created_by = u.id 
    WHERE p.deleted_at IS NULL 
    ORDER BY p.is_featured DESC, p.partnership_level ASC, p.display_order ASC, p.name ASC
");
$partners = $stmt->fetchAll();

$message = '';
if (isset($_GET['deleted'])) {
    $message = '<div class="alert alert-success">Partner deleted successfully!</div>';
} elseif (isset($_GET['error'])) {
    $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($_GET['error']) . '</div>';
} elseif (isset($_GET['added'])) {
    $message = '<div class="alert alert-success">Partner added successfully!</div>';
} elseif (isset($_GET['updated'])) {
    $message = '<div class="alert alert-success">Partner updated successfully!</div>';
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <?= $message ?>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Partners</h1>
        <?php if ($can_manage): ?>
            <a href="partner_add.php" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Add New Partner
            </a>
        <?php endif; ?>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Partners List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="80">Logo</th>
                            <th>Partner Details</th>
                            <th width="120">Type & Level</th>
                            <th width="100">Status</th>
                            <th width="100">Display Order</th>
                            <?php if ($can_manage): ?>
                                <th width="120">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($partners)): ?>
                            <tr>
                                <td colspan="<?= $can_manage ? 6 : 5 ?>" class="text-center py-4">
                                    <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No partners found. <a href="partner_add.php">Add the first partner</a>.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($partners as $partner): ?>
                                <?php 
                                    // Determine logo URL
                                    $logo_url = null;
                                    if ($partner['logo_path']) {
                                        if (filter_var($partner['logo_path'], FILTER_VALIDATE_URL)) {
                                            // It's a URL
                                            $logo_url = $partner['logo_path'];
                                        } else {
                                            // It's a file path
                                            $logo_url = '../../client/assets/images/partners/' . $partner['logo_path'];
                                        }
                                    }
                                ?>
                                <tr>
                                    <td class="text-center align-middle">
                                        <?php if ($logo_url): ?>
                                            <img src="<?= htmlspecialchars($logo_url) ?>" 
                                                 alt="<?= htmlspecialchars($partner['name']) ?>" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 60px; max-height: 40px; object-fit: contain;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <div style="display:none;" class="text-muted small">No Image</div>
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 40px; font-size: 11px;">
                                                <span class="text-muted">No Logo</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="partner-info">
                                            <h6 class="mb-1">
                                                <?= htmlspecialchars($partner['name']) ?>
                                                <?php if ($partner['is_featured']): ?>
                                                    <span class="badge badge-warning badge-sm ml-1">Featured</span>
                                                <?php endif; ?>
                                            </h6>
                                            <?php if ($partner['description']): ?>
                                                <p class="text-muted small mb-1"><?= htmlspecialchars(substr($partner['description'], 0, 80)) ?><?= strlen($partner['description']) > 80 ? '...' : '' ?></p>
                                            <?php endif; ?>
                                            <div class="small text-muted">
                                                <?php if ($partner['website_url']): ?>
                                                    <i class="fas fa-globe"></i> <a href="<?= htmlspecialchars($partner['website_url']) ?>" target="_blank" class="text-decoration-none"><?= parse_url($partner['website_url'], PHP_URL_HOST) ?></a><br>
                                                <?php endif; ?>
                                                <?php if ($partner['contact_email']): ?>
                                                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($partner['contact_email']) ?><br>
                                                <?php endif; ?>
                                                <?php if ($partner['contact_person']): ?>
                                                    <i class="fas fa-user"></i> <?= htmlspecialchars($partner['contact_person']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="mb-1">
                                            <span class="badge badge-<?= 
                                                $partner['partnership_type'] == 'funding' ? 'success' : 
                                                ($partner['partnership_type'] == 'technology' ? 'primary' : 
                                                ($partner['partnership_type'] == 'training' ? 'info' : 'secondary')) 
                                            ?> badge-sm">
                                                <?= ucwords($partner['partnership_type']) ?>
                                            </span>
                                        </div>
                                        <div>
                                            <small class="text-muted"><?= ucwords($partner['partnership_level']) ?></small>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?= $partner['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?>
                                        <div class="small text-muted mt-1"><?= date('M j, Y', strtotime($partner['created_at'])) ?></div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-light"><?= $partner['display_order'] ?></span>
                                    </td>
                                    <?php if ($can_manage): ?>
                                        <td class="text-center align-middle">
                                            <div class="btn-group" role="group">
                                                <a href="partner_edit.php?id=<?= $partner['id'] ?>" 
                                                   class="btn btn-outline-primary btn-sm" 
                                                   title="Edit Partner">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="partner_delete.php?id=<?= $partner['id'] ?>" 
                                                   class="btn btn-outline-danger btn-sm" 
                                                   title="Delete Partner"
                                                   onclick="return confirm('Are you sure you want to delete \"<?= htmlspecialchars($partner['name']) ?>\"? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
