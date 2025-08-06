<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/User.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    Utilities::redirect('/admin/public/login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

if (!$userModel->hasPermission($_SESSION['user_id'], 'sms') && !$userModel->hasPermission($_SESSION['user_id'], '*')) {
    $_SESSION['error'] = "You don't have permission to access this resource.";
    Utilities::redirect('/admin/public/index.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $content = $_POST['content'] ?? '';
    $template_type = $_POST['template_type'] ?? 'notification';

    if ($name && $content) {
        $stmt = $db->prepare("INSERT INTO sms_templates (name, content, template_type, created_by) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $content, $template_type, $_SESSION['user_id']])) {
            $_SESSION['message'] = "SMS template created successfully.";
            Utilities::redirect('/admin/public/sms_templates.php');
            exit();
        } else {
            $error = "Failed to create SMS template.";
        }
    } else {
        $error = "Name and content are required.";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Create SMS Template</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <div class="mb-3">
            <label for="name" class="form-label">Template Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label for="template_type" class="form-label">Template Type</label>
            <select class="form-select" id="template_type" name="template_type">
                <option value="notification">Notification</option>
                <option value="reminder">Reminder</option>
                <option value="announcement">Announcement</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create Template</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

