
<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require admin role
Utilities::requireRole('admin');

$pageTitle = "Add User";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'Users', 'url' => BASE_URL . '/admin/public/users.php'],
    ['title' => 'Add User']
];

$errors = [];
$formData = [
    'username' => '',
    'email' => '',
    'full_name' => '',
    'role' => 'user',
    'is_active' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'role' => $_POST['role'] ?? 'user',
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    // Validation
    if (empty($formData['username'])) {
        $errors[] = "Username is required.";
    } elseif (strlen($formData['username']) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    }

    if (empty($formData['email'])) {
        $errors[] = "Email is required.";
    } elseif (!Utilities::isValidEmail($formData['email'])) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($formData['full_name'])) {
        $errors[] = "Full name is required.";
    }

    if (empty($formData['password'])) {
        $errors[] = "Password is required.";
    } elseif (strlen($formData['password']) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($formData['password'] !== $formData['confirm_password']) {
        $errors[] = "Passwords do not match.";
    }

    if (!in_array($formData['role'], ['user', 'admin'])) {
        $errors[] = "Invalid role selected.";
    }

    // Check for duplicate username/email
    if (empty($errors)) {
        try {
            $db = (new Database())->connect();

            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$formData['username'], $formData['email']]);

            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Username or email already exists.";
            }
        } catch (PDOException $e) {
            error_log("User check error: " . $e->getMessage());
            $errors[] = "Database error occurred.";
        }
    }

    // Insert user if no errors
    if (empty($errors)) {
        try {
            $db = (new Database())->connect();

            $stmt = $db->prepare("INSERT INTO users (username, email, full_name, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $formData['username'],
                $formData['email'],
                $formData['full_name'],
                password_hash($formData['password'], PASSWORD_DEFAULT),
                $formData['role'],
                $formData['is_active']
            ]);

            $_SESSION['success'] = "User created successfully.";
            Utilities::redirect('/admin/public/users.php');

        } catch (PDOException $e) {
            error_log("User creation error: " . $e->getMessage());
            $errors[] = "Failed to create user. Please try again.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add New User</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username"
                                       value="<?= htmlspecialchars($formData['username']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= htmlspecialchars($formData['email']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name"
                               value="<?= htmlspecialchars($formData['full_name']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="user" <?= $formData['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= $formData['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                           <?= $formData['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/admin/public/users.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Users
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>