<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/User.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    Utilities::redirect('/admin/public/login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

// Check permission
if (!$userModel->hasPermission($_SESSION['user_id'], 'sms') && !$userModel->hasPermission($_SESSION['user_id'], '*')) {
    $_SESSION['error'] = "You don't have permission to access this resource.";
    Utilities::redirect('/admin/public/index.php');
    exit();
}

require_once __DIR__ . '/../includes/header.php';

// Handle actions
$message = '';
$error = '';

if ($_POST) {
    if (isset($_POST['activate']) && isset($_POST['id'])) {
        $stmt = $db->prepare("UPDATE sms_templates SET is_active = 1 WHERE id = ?");
        if ($stmt->execute([$_POST['id']])) {
            $message = "SMS template activated successfully.";
        } else {
            $error = "Failed to activate SMS template.";
        }
    } elseif (isset($_POST['deactivate']) && isset($_POST['id'])) {
        $stmt = $db->prepare("UPDATE sms_templates SET is_active = 0 WHERE id = ?");
        if ($stmt->execute([$_POST['id']])) {
            $message = "SMS template deactivated successfully.";
        } else {
            $error = "Failed to deactivate SMS template.";
        }
    }
}

// Get all SMS templates
$stmt = $db->prepare("
    SELECT st.*, u.username as created_by_name
    FROM sms_templates st
    LEFT JOIN users u ON st.created_by = u.id
    ORDER BY st.created_at DESC
");
$stmt->execute();
$templates = $stmt->fetchAll();

?>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">SMS Templates</h1>
        <a href="sms_template_create.php" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Create Template
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- SMS Templates Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">SMS Templates</h6>
        </div>
        <div class="card-body">
            <?php if (empty($templates)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-sms fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No SMS Templates Found</h5>
                    <p class="text-muted">Create your first SMS template to start sending text messages.</p>
                    <a href="sms_template_create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create First Template
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Content Preview</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($templates as $template): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($template['name']) ?></strong></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;">
                                            <?= htmlspecialchars($template['content']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= ucfirst($template['template_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($template['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($template['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="sms_template_edit.php?id=<?= $template['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($template['is_active']): ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $template['id'] ?>">
                                                    <button type="submit" name="deactivate" class="btn btn-sm btn-outline-warning" 
                                                            title="Deactivate">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $template['id'] ?>">
                                                    <button type="submit" name="activate" class="btn btn-sm btn-outline-success" 
                                                            title="Activate">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
