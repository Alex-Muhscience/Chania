<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$pageTitle = "My Profile";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'My Profile']
];

$errors = [];
$success = false;

// Get current user data
try {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "User not found.";
        Utilities::redirect('/admin/logout.php');
    }

} catch (PDOException $e) {
    error_log("Profile fetch error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading profile.";
    Utilities::redirect('/admin/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        // Validation
        if (empty($fullName)) {
            $errors[] = "Full name is required.";
        }

        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!Utilities::isValidEmail($email)) {
            $errors[] = "Please enter a valid email address.";
        }

        // Check if email is taken by another user
        if (empty($errors)) {
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $_SESSION['user_id']]);

                if ($stmt->fetchColumn() > 0) {
                    $errors[] = "Email is already taken by another user.";
                }
            } catch (PDOException $e) {
                error_log("Email check error: " . $e->getMessage());
                $errors[] = "Database error occurred.";
            }
        }

        // Update profile if no errors
        if (empty($errors)) {
            try {
                // Check if full_name column exists, otherwise use available columns
                $updateFields = "email = ?, updated_at = NOW()";
                $updateParams = [$email];
                
                // Add phone and bio if they exist in the table
                $stmt = $db->query("DESCRIBE users");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (in_array('full_name', $columns)) {
                    $updateFields = "full_name = ?, " . $updateFields;
                    array_unshift($updateParams, $fullName);
                }
                if (in_array('phone', $columns)) {
                    $updateFields = "phone = ?, " . $updateFields;
                    array_unshift($updateParams, $phone);
                }
                if (in_array('bio', $columns)) {
                    $updateFields = "bio = ?, " . $updateFields;
                    array_unshift($updateParams, $bio);
                }
                
                $updateParams[] = $_SESSION['user_id'];
                
                $stmt = $db->prepare("UPDATE users SET {$updateFields} WHERE id = ?");
                $stmt->execute($updateParams);

                $_SESSION['success'] = "Profile updated successfully.";
                $success = true;

                // Update user data
                if (in_array('full_name', $columns)) {
                    $user['full_name'] = $fullName;
                }
                $user['email'] = $email;
                if (in_array('phone', $columns)) {
                    $user['phone'] = $phone;
                }
                if (in_array('bio', $columns)) {
                    $user['bio'] = $bio;
                }

            } catch (PDOException $e) {
                error_log("Profile update error: " . $e->getMessage());
                $errors[] = "Failed to update profile. Please try again.";
            }
        }
    }

    elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($currentPassword)) {
            $errors[] = "Current password is required.";
        }

        if (empty($newPassword)) {
            $errors[] = "New password is required.";
        } elseif (strlen($newPassword) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = "New passwords do not match.";
        }

        // Verify current password
        if (empty($errors)) {
            if (!password_verify($currentPassword, $user['password_hash'])) {
                $errors[] = "Current password is incorrect.";
            }
        }

        // Update password if no errors
        if (empty($errors)) {
            try {
                $stmt = $db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $_SESSION['user_id']]);

                $_SESSION['success'] = "Password changed successfully.";
                $success = true;

            } catch (PDOException $e) {
                error_log("Password update error: " . $e->getMessage());
                $errors[] = "Failed to change password. Please try again.";
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-user-circle fa-5x text-muted"></i>
                </div>
                <h5><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h5>
                <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>">
                    <?= ucfirst($user['role']) ?>
                </span>

                <hr>

                <div class="text-start">
                    <small class="text-muted">
                        <i class="fas fa-calendar"></i> Joined: <?= date('M j, Y', strtotime($user['created_at'])) ?>
                    </small>
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> Last Login: <?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                Changes saved successfully!
            </div>
        <?php endif; ?>

        <!-- Profile Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                        <div class="form-text">Username cannot be changed</div>
                    </div>

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name"
                               value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password *</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password *</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text">Password must be at least 6 characters long</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>