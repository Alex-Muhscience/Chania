<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Redirect if already logged in
if (Utilities::isLoggedIn()) {
    Utilities::redirect('/admin/');
}

$pageTitle = "Forgot Password";
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!Utilities::isValidEmail($email)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($errors)) {
        try {
            $db = (new Database())->connect();

            // Check if user exists
            $stmt = $db->prepare("SELECT id, username, email FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate reset token
                $token = Utilities::generateToken(32);
                $expires = date('Y-m-d H:i:s', time() + (24 * 60 * 60)); // 24 hours

                // Store reset token
                $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at), created_at = NOW()");
                $stmt->execute([$user['id'], $token, $expires]);

                // Send email (basic implementation)
                $resetLink = BASE_URL . "/admin/reset-password.php?token=" . $token;
                $subject = "Password Reset Request";
                $message = "
                    Hello {$user['username']},
                    
                    You have requested a password reset for your admin account.
                    
                    Please click the following link to reset your password:
                    {$resetLink}
                    
                    This link will expire in 24 hours.
                    
                    If you did not request this reset, please ignore this email.
                    
                    Best regards,
                    Admin Team
                ";

                // In production, use a proper email service
                $headers = "From: admin@example.com\r\n";
                $headers .= "Reply-To: admin@example.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                if (mail($email, $subject, $message, $headers)) {
                    $success = true;
                } else {
                    $errors[] = "Failed to send reset email. Please try again.";
                }
            } else {
                // Don't reveal if email exists or not for security
                $success = true;
            }

        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again later.";
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
                        <h3>Forgot Password</h3>
                        <p class="text-muted">Enter your email address to reset your password</p>
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
                            <p class="mb-0">
                                If an account with that email exists, we've sent password reset instructions to your email address.
                            </p>
                        </div>
                        <div class="text-center">
                            <a href="<?= BASE_URL ?>/admin/login.php" class="btn btn-primary">
                                Back to Login
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= htmlspecialchars($email ?? '') ?>" required autofocus>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    Send Reset Link
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