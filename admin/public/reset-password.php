<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Redirect if already logged in
if (Utilities::isLoggedIn()) {
    Utilities::redirect('/admin/');
}

$token = $_GET['token'] ?? '';
$pageTitle = "Reset Password";
$errors = [];
$success = false;
$validToken = false;

if (empty($token)) {
    $_SESSION['error'] = "Invalid reset link.";
    Utilities::redirect('/admin/forgot-password.php');
}

// Validate token
try {
    $db = (new Database())->connect();

    $stmt = $db->prepare("
        SELECT pr.*, u.username, u.email 
        FROM password_resets pr 
        JOIN users u ON pr.user_id = u.id 
        WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0
    ");
    $stmt->execute([$token]);
    $resetData = $stmt->fetch();

    if ($resetData) {
        $validToken = true;
    } else {
        $_SESSION['error'] = "Invalid or expired reset link.";
        Utilities::redirect('/admin/forgot-password.php');
    }

} catch (PDOException $e) {
    error_log("Token validation error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again.";
    Utilities::redirect('/admin/forgot-password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($newPassword)) {
        $errors[] = "New password is required.";
    } elseif (strlen($newPassword) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Update password
            $stmt = $db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $resetData['user_id']]);

            // Mark token as used
            $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);

            $db->commit();

            $success = true;

        } catch (PDOException $e) {
            $db->rollback();
            error_log("Password reset error: " . $e->getMessage());
            $errors[] = "Failed to reset password. Please try again.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <h3>Reset Password</h3>
                        <p class="text-muted">Enter your new password</p>
                    </div>

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
                            <p class="mb-0">Your password has been reset successfully!</p>
                        </div>
                        <div class="text-center">
                            <a href="<?= BASE_URL ?>/admin/public/login.php" class="btn btn-primary">
                                Login with New Password
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <small class="text-muted">
                                Resetting password for: <strong><?= htmlspecialchars($resetData['email']) ?></strong>
                            </small>
                        </div>

                        <form method="post">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Password must be at least 6 characters long</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    Reset Password
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>/admin/public/login.php">Back to Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>