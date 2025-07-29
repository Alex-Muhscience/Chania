<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require admin role
Utilities::requireRole('admin');

$pageTitle = "Edit User";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'Users', 'url' => BASE_URL . '/admin/public/users.php'],
    ['title' => 'Edit User']
];

$errors = [];
$userId = $_GET['id'] ?? '';

if (empty($userId)) {
    $_SESSION['error'] = "User ID is required.";
    Utilities::redirect('/admin/public/users.php');
}

try {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "User not found.";
        Utilities::redirect('/admin/public/users.php');
    }

} catch (PDOException $e) {
    error_log("User fetch error: " . $e->getMessage());
    $_SESSION['error'] = "Failed to load user.";
    Utilities::redirect('/admin/public/users.php');
}

$formData = [
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role'],
    'is_active' => $user['is_active']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'role' => $_POST['role'] ?? 'user',
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? ''
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


    if (!in_array($formData['role'], ['user', 'admin'])) {
        $errors[] = "Invalid role selected.";
    }

    // Optional password validation
    if (!empty($formData['password'])) {
        if (strlen($formData['password']) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }
        if ($formData['password'] !== $formData['confirm_password']) {
            $errors[] = "Passwords do not match.";
        }
    }

    // Check for duplicate username/email
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$formData['username'], $formData['email'], $userId]);

            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Username or email already exists.";
            }
        } catch (PDOException $e) {
            error_log("User check error: " . $e->getMessage());
            $errors[] = "Database error occurred.";
        }
    }

    // Update user if no errors
    if (empty($errors)) {
        try {
            // Update with or without password
            if (!empty($formData['password'])) {
                $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, role = ?, is_active = ?, password_hash = ? WHERE id = ?");
                $stmt->execute([
                    $formData['username'],
                    $formData['email'],
                    $formData['role'],
                    $formData['is_active'],
                    password_hash($formData['password'], PASSWORD_DEFAULT),
                    $userId
                ]);
            } else {
                $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, role = ?, is_active = ? WHERE id = ?");
                $stmt->execute([
                    $formData['username'],
                    $formData['email'],
                    $formData['role'],
                    $formData['is_active'],
                    $userId
                ]);
            }

            $_SESSION['success'] = "User updated successfully.";
            Utilities::redirect('/admin/public/users.php');

        } catch (PDOException $e) {
            error_log("User update error: " . $e->getMessage());
            $errors[] = "Failed to update user. Please try again.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit User</h5>
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
                        <h6 class="text-muted">Change Password (Optional)</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                                </div>
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
                            <i class="fas fa-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
