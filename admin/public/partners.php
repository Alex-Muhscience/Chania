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
$stmt = $db->query("SELECT * FROM partners WHERE deleted_at IS NULL ORDER BY name ASC");
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
                            <th>Logo</th>
                            <th>Name</th>
                            <th>Website</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <?php if ($can_manage): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($partners)): ?>
                            <tr>
                                <td colspan="<?= $can_manage ? 6 : 5 ?>" class="text-center">No partners found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($partners as $partner): ?>
                                <tr>
                                    <td>
                                        <?php if ($partner['logo_path']): ?>
                                            <img src="../../<?= htmlspecialchars($partner['logo_path']) ?>" alt="<?= htmlspecialchars($partner['name']) ?>" class="img-thumbnail" style="max-width: 50px;">
                                        <?php else: ?>
                                            No Logo
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($partner['name']) ?></td>
                                    <td>
                                        <?php if ($partner['website_url']): ?>
                                            <a href="<?= htmlspecialchars($partner['website_url']) ?>" target="_blank"><?= htmlspecialchars($partner['website_url']) ?></a>
                                        <?php else: ?>
                                            No Website
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $partner['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($partner['created_at'])) ?></td>
                                    <?php if ($can_manage): ?>
                                        <td>
                                            <a href="partner_edit.php?id=<?= $partner['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="partner_delete.php?id=<?= $partner['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this partner?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
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
