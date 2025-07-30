<?php
$pageTitle = 'Team Members';
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
$can_manage = _can_manage_entity('team_member');

$db = (new Database())->connect();
$stmt = $db->query("SELECT * FROM team_members WHERE deleted_at IS NULL ORDER BY name ASC");
$team_members = $stmt->fetchAll();

$message = '';
if (isset($_GET['deleted'])) {
    $message = '<div class="alert alert-success">Team member deleted successfully!</div>';
} elseif (isset($_GET['error'])) {
    $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($_GET['error']) . '</div>';
} elseif (isset($_GET['added'])) {
    $message = '<div class="alert alert-success">Team member added successfully!</div>';
} elseif (isset($_GET['updated'])) {
    $message = '<div class="alert alert-success">Team member updated successfully!</div>';
}

require_once __DIR__ . '/../includes/header.php';
?>
<!-- Main Content -->
<div class="container-fluid">
    <?= $message ?>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Team Members</h1>
        <?php if ($can_manage): ?>
            <a href="team_member_add.php" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Add New Team Member
            </a>
        <?php endif; ?>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Team Members List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <?php if ($can_manage): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($team_members)): ?>
                            <tr>
                                <td colspan="<?= $can_manage ? 6 : 5 ?>" class="text-center">No team members found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($team_members as $member): ?>
                                <tr>
                                    <td>
                                        <?php if ($member['image_path']): ?>
                                            <img src="../../<?= htmlspecialchars($member['image_path']) ?>" alt="<?= htmlspecialchars($member['name']) ?>" class="img-thumbnail" style="max-width: 50px;">
                                        <?php else: ?>
                                            No Image
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($member['name']) ?></td>
                                    <td><?= htmlspecialchars($member['position']) ?></td>
                                    <td>
                                        <?= $member['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($member['created_at'])) ?></td>
                                    <?php if ($can_manage): ?>
                                        <td>
                                            <a href="team_member_edit.php?id=<?= $member['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="team_member_delete.php?id=<?= $member['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this team member?')">
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

