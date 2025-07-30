<?php
require_once '../includes/header.php';
require_once '../classes/Role.php';
require_once '../../shared/Core/User.php';

$database = new Database();
$db = $database->connect();
$user = new User($db);

if (!$user->hasPermission($_SESSION['user_id'], 'roles') && !$user->hasPermission($_SESSION['user_id'], '*')) {
    die('Access denied. You do not have permission to manage roles.');
}

$role = new Role($db);
$isEdit = false;
$roleData = [
    'name' => '',
    'description' => '',
];
$rolePermissions = [];

if (isset($_GET['id'])) {
    $isEdit = true;
    $roleData = $role->getById($_GET['id']);
    if (!$roleData) die('Role not found.');
    $rolePermissions = $role->getPermissions($roleData['id']);
}

$allPermissions = $role->getAllPermissions();
$groupedPermissions = [];
foreach ($allPermissions as $p) {
    $groupedPermissions[$p['category']][] = $p;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $permissions = $_POST['permissions'] ?? [];

    if (empty($name)) {
        $error = 'Role name is required.';
    }

    if (!$error) {
        if ($isEdit) {
            $ok = $role->update($_GET['id'], $name, $description, $permissions);
            $_SESSION['success'] = $ok ? 'Role updated.' : 'Failed to update role.';
        } else {
            $ok = $role->create($name, $description, $permissions);
            $_SESSION['success'] = $ok ? 'Role created.' : 'Failed to create role.';
        }
        header('Location: roles.php');
        exit;
    }
}

?>
<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $isEdit ? 'Edit' : 'Add' ?> Role</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="roles.php">Roles</a></li>
        <li class="breadcrumb-item active"><?= $isEdit ? 'Edit' : 'Add' ?> Role</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-tag me-1"></i>
            <?= $isEdit ? 'Edit' : 'Add' ?> Role
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Role Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($roleData['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($roleData['description']) ?></textarea>
                </div>
                
                <h5>Permissions</h5>
                <?php foreach ($groupedPermissions as $category => $permissions): ?>
                    <fieldset class="mb-3">
                        <legend class="fs-6"><strong><?= htmlspecialchars($category) ?></strong></legend>
                        <div class="row">
                            <?php foreach ($permissions as $p): ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" id="perm_<?= $p['id'] ?>" <?= in_array($p['name'], $rolePermissions) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="perm_<?= $p['id'] ?>">
                                            <?= htmlspecialchars($p['name']) ?>
                                            <small class="text-muted d-block"><?= htmlspecialchars($p['description']) ?></small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                <?php endforeach; ?>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Create' ?> Role</button>
                <a href="roles.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
