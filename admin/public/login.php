<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

if (Utilities::isLoggedIn()) {
    Utilities::redirect('/admin/');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'user'; // Ensure role is set with default
            $_SESSION['last_login'] = time();

            // Update last login in database
            $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);

            // Log the login (check if admin_logs table exists)
            try {
                $logStmt = $db->prepare("INSERT INTO admin_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
                $logStmt->execute([
                    $user['id'],
                    'login',
                    $_SERVER['REMOTE_ADDR']
                ]);
            } catch (PDOException $e) {
                // Log table might not exist, just log the error
                error_log("Admin log error: " . $e->getMessage());
            }

            // Redirect to intended page or dashboard
            $redirectUrl = $_SESSION['redirect_url'] ?? '/admin/';
            unset($_SESSION['redirect_url']);
            Utilities::redirect($redirectUrl);
        } else {
            $error = 'Invalid username or password.';
        }
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        $error = 'A database error occurred. Please try again later.';
    }
}

$pageTitle = "Admin Login";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <img src="<?= BASE_URL ?>/admin/public/assets/images/logo.png" alt="Admin Login" height="50" onerror="this.style.display='none'">
                        <h3 class="mt-3">Admin Login</h3>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username or Email</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   value="<?= htmlspecialchars($username) ?>" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="<?= BASE_URL ?>/admin/public/forgot-password.php">Forgot password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>