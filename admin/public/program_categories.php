<?php
require_once '../includes/header.php';
require_once '../classes/ProgramCategory.php';
require_once '../../shared/Core/User.php';

$database = new Database();
$db = $database->connect();
$user = new User($db);

// Permission check
if (!$user->hasPermission($_SESSION['user_id'], 'programs') && !$user->hasPermission($_SESSION['user_id'], '*')) {
    die('Access denied. You do not have permission to manage program categories.');
}

$programCategory = new ProgramCategory($db);
$isEdit = false;
$categoryData = [
    'name' => '',
    'description' => '',
];

if (isset($_GET['id'])) {
    $isEdit = true;
    $categoryData = $programCategory->getById($_GET['id']);
    if (!$categoryData) die('Category not found.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = 'Category name is required.';
    }

    if (!$error) {
        if ($isEdit) {
            $ok = $programCategory->update($_GET['id'], $name, $description);
            $_SESSION['success'] = $ok ? 'Category updated.' : 'Failed to update category.';
        } else {
            $ok = $programCategory->create($name, $description);
            $_SESSION['success'] = $ok ? 'Category created.' : 'Failed to create category.';
        }
        header('Location: program_categories.php');
        exit;
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $ok = $programCategory->delete($_GET['id']);
    $_SESSION['success'] = $ok ? 'Category deleted.' : 'Failed to delete category.';
    header('Location: program_categories.php');
    exit;
}

$categories = $programCategory->getAll();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $isEdit ? 'Edit' : 'Add' ?> Program Category</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="programs.php">Programs</a></li>
        <li class="breadcrumb-item active">Program Categories</li>
    </ol>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-plus me-1"></i>
                    <?= $isEdit ? 'Edit' : 'Add' ?> Category
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($categoryData['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($categoryData['description']) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Create' ?> Category</button>
                        <?php if ($isEdit): ?>
                            <a href="program_categories.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i>
                    Program Categories
                </div>
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No categories found. Create your first category to organize programs.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?= $category['id'] ?></td>
                                            <td><?= htmlspecialchars($category['name']) ?></td>
                                            <td><?= htmlspecialchars($category['description']) ?></td>
                                            <td><?= date('M j, Y', strtotime($category['created_at'])) ?></td>
                                            <td>
                                                <a href="program_categories.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="program_categories.php?action=delete&id=<?= $category['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this category?')"
                                                   title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
