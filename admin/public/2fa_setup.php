<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/User.php';
require_once __DIR__ . '/../../shared/Core/TOTP.php';
require_once __DIR__ . '/../../shared/Core/SecurityLogger.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);
$logger = new SecurityLogger($db);
$userId = $_SESSION['user_id'];

// Handle form submissions BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enable_2fa'])) {
        // Generate new secret but don't enable yet
        $secret = TOTP::generateSecret();
        $userModel->setTwoFactorSecret($userId, $secret);
        $logger->logEvent(
            $userId,
            $_SESSION['username'],
            '2fa_secret_generated',
            'medium',
            '2FA secret generated (pending verification)'
        );
        $_SESSION['success'] = '2FA secret key generated. Please verify to enable.';
    } elseif (isset($_POST['disable_2fa'])) {
        // Disable 2FA
        $userModel->disableTwoFactor($userId);
        $logger->logEvent(
            $userId,
            $_SESSION['username'],
            '2fa_disabled',
            'high',
            'User disabled 2FA'
        );
        $_SESSION['success'] = '2FA has been disabled.';
    } elseif (isset($_POST['verify_2fa'])) {
        // Verify and enable 2FA
        $code = $_POST['code'];
        if ($userModel->verifyTwoFactorSetup($userId, $code)) {
            // Enable 2FA after successful verification
            $userModel->confirmTwoFactorSetup($userId);
            $logger->logEvent(
                $userId,
                $_SESSION['username'],
                '2fa_enabled',
                'high',
                'User successfully verified and enabled 2FA'
            );
            $_SESSION['success'] = '2FA has been successfully enabled and verified!';
        } else {
            $logger->logEvent(
                $userId,
                $_SESSION['username'],
                'suspicious_activity',
                'medium',
                'Failed 2FA verification during setup'
            );
            $_SESSION['error'] = 'Invalid 2FA code. Please try again.';
        }
    }
    header('Location: 2fa_setup.php');
    exit();
}

// Get user data before including header
$user = $userModel->getById($userId);
$is2faEnabled = $user['two_factor_enabled'];
$secret = $user['two_factor_secret'];

// Now include header after all redirects are handled
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Two-Factor Authentication (2FA)</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">2FA Setup</li>
    </ol>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <i class="fas fa-shield-alt"></i> 2FA Status
                </div>
                <div class="card-body">
                    <?php if ($is2faEnabled): ?>
                        <p class="text-success"><i class="fas fa-check-circle"></i> Two-Factor Authentication is currently enabled on your account.</p>
                        <p>To disable 2FA, please click the button below. This will remove the extra security layer from your account.</p>
                        <form method="POST">
                            <button type="submit" name="disable_2fa" class="btn btn-danger">Disable 2FA</button>
                        </form>
                    <?php else: ?>
                        <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Two-Factor Authentication is currently disabled.</p>
                        <p>Enable 2FA to add an extra layer of security to your account. You will need an authenticator app like Google Authenticator or Authy.</p>
                        
                        <?php if (!$secret): ?>
                            <p>Click the button below to generate your unique 2FA secret key and QR code.</p>
                            <form method="POST">
                                <button type="submit" name="enable_2fa" class="btn btn-primary">Enable 2FA</button>
                            </form>
                        <?php else: ?>
                            <div class="text-center">
                                <p><strong>Scan this QR code with your authenticator app:</strong></p>
                                <img src="<?= TOTP::getQRCodeUrl($_SESSION['username'], $secret) ?>" alt="2FA QR Code" class="img-fluid mb-3">
                                <p>Or manually enter this secret key: <code><?= htmlspecialchars($secret) ?></code></p>
                            </div>
                            
                            <hr>
                            
                            <p>After scanning, enter the 6-digit code from your app to verify and complete the setup.</p>
                            <form method="POST" class="form-inline">
                                <div class="form-group mb-2">
                                    <label for="code" class="sr-only">6-Digit Code</label>
                                    <input type="text" class="form-control" id="code" name="code" placeholder="Enter 6-digit code" required maxlength="6">
                                </div>
                                <button type="submit" name="verify_2fa" class="btn btn-success mb-2">Verify & Enable</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> What is 2FA?
                </div>
                <div class="card-body">
                    <p>Two-Factor Authentication (2FA) adds an extra layer of security by requiring a second verification step when you log in. Even if someone gets your password, they won't be able to access your account without your 2FA code.</p>
                    <h6>Recommended Apps:</h6>
                    <ul>
                        <li><a href="#">Google Authenticator</a></li>
                        <li><a href="#">Authy</a></li>
                        <li><a href="#">Microsoft Authenticator</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
