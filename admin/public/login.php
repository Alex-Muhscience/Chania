<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/User.php';
require_once __DIR__ . '/../../shared/Core/TOTP.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../../shared/Core/SecurityLogger.php';
require_once __DIR__ . '/../../shared/Core/AdminLogger.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

if (Utilities::isLoggedIn()) {
    Utilities::redirect('/admin/public/index.php');
    exit;
}

$error = '';
$username = '';
$show2faForm = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->connect();
    $userModel = new User($db);
    $logger = new SecurityLogger($db);
    
    // Handle 2FA verification
    if (isset($_POST['verify_2fa'])) {
        $userId = $_SESSION['2fa_user_id'];
        $code = $_POST['code'];
        
        $secret = $userModel->getTwoFactorSecret($userId);
        if ($secret && TOTP::verifyTOTP($secret, $code)) {
            // 2FA successful, complete login
            $user = $userModel->getById($userId);
            
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            $_SESSION['role_slug'] = $user['role_slug'] ?? 'user';
            $_SESSION['permissions'] = $user['permissions'] ? json_decode($user['permissions'], true) : [];
            $_SESSION['last_login'] = time();
            unset($_SESSION['2fa_user_id']);
            
            $userModel->updateLastLogin($user['id']);
            
            // Log to both security and admin logs
$logger->log(
                'login_2fa_success',
                'high',
                'User successfully verified 2FA',
                $user['id']
            );
            AdminLogger::log('admin_login_2fa', 'user', $user['id'], 'User logged in with 2FA verification', $user['id']);
            
            // Only redirect to stored URL if it's an admin page, otherwise go to dashboard
            $redirectUrl = '/admin/public/index.php';
            if (isset($_SESSION['redirect_url'])) {
                $storedUrl = $_SESSION['redirect_url'];
                // Only use stored URL if it's an admin page
                if (strpos($storedUrl, '/admin/') !== false || strpos($storedUrl, '/chania/admin/') !== false) {
                    $redirectUrl = $storedUrl;
                }
                unset($_SESSION['redirect_url']);
            }
            Utilities::redirect($redirectUrl);
        } else {
$logger->log(
                'login_2fa_failed',
                'high',
                'User failed 2FA verification',
                $_SESSION['2fa_user_id']
            );
            $error = 'Invalid 2FA code. Please try again.';
            $show2faForm = true;
        }
    } else {
        // Handle initial login
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        try {
            $user = $userModel->authenticate($username, $password);
            
            if ($user) {
                if ($userModel->isTwoFactorEnabled($user['id'])) {
                    // 2FA is enabled, show 2FA form
                    $_SESSION['2fa_user_id'] = $user['id'];
$logger->log(
                        'login_success',
                        'medium',
                        'User successfully logged in, pending 2FA',
                        $user['id']
                    );
                    $show2faForm = true;
                } else {
                    // 2FA is not enabled, complete login
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'] ?? 'user';
                    $_SESSION['role_slug'] = $user['role_slug'] ?? 'user';
                    $_SESSION['permissions'] = $user['permissions'] ? json_decode($user['permissions'], true) : [];
                    $_SESSION['last_login'] = time();

                    $userModel->updateLastLogin($user['id']);

                    // Log to both security and admin logs
                    $logger->log(
                        'login_success',
                        'medium',
                        'User successfully logged in without 2FA',
                        $user['id']
                    );
                    AdminLogger::log('admin_login', 'user', $user['id'], 'User logged in without 2FA', $user['id']);

                    // Only redirect to stored URL if it's an admin page, otherwise go to dashboard
                    $redirectUrl = '/admin/public/index.php';
                    if (isset($_SESSION['redirect_url'])) {
                        $storedUrl = $_SESSION['redirect_url'];
                        // Only use stored URL if it's an admin page
                        if (strpos($storedUrl, '/admin/') !== false || strpos($storedUrl, '/chania/admin/') !== false) {
                            $redirectUrl = $storedUrl;
                        }
                        unset($_SESSION['redirect_url']);
                    }
                    Utilities::redirect($redirectUrl);
                }
            } else {
$logger->log(
                    'login_failed',
                    'high',
                    'Invalid username or password'
                );
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
$logger->log(
                'login_failed',
                'critical',
                'Login error: ' . $e->getMessage()
            );
            error_log("Login Error: " . $e->getMessage());
            $error = $e->getMessage();
        }
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

                    <?php if ($show2faForm): ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="code" class="form-label">Enter 2FA Code</label>
                                <input type="text" class="form-control" id="code" name="code" required autofocus maxlength="6">
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="verify_2fa" class="btn btn-primary">Verify</button>
                            </div>
                        </form>
                    <?php else: ?>
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
                    <?php endif; ?>

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
