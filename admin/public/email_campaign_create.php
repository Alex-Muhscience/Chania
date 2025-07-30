<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/User.php';
require_once __DIR__ . '/../../shared/Core/EmailTemplate.php';

require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);
$templateModel = new EmailTemplate($db);

// Check permission
if (!$userModel->hasPermission($_SESSION['user_id'], 'campaigns') && !$userModel->hasPermission($_SESSION['user_id'], '*')) {
    $_SESSION['error'] = "You don't have permission to access this resource.";
    header('Location: index.php');
    exit();
}

$pageTitle = 'Create Email Campaign';
$campaignId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$campaign = ['name' => '', 'subject' => '', 'content' => '', 'template_id' => null, 'recipient_type' => 'all_users'];

if ($campaignId) {
    $pageTitle = 'Edit Email Campaign';
    $stmt = $db->prepare("SELECT * FROM email_campaigns WHERE id = ?");
    $stmt->execute([$campaignId]);
    $campaign = $stmt->fetch();
}

if ($_POST) {
    $name = $_POST['name'];
    $subject = $_POST['subject'];
    $content = $_POST['content'];
    $templateId = !empty($_POST['template_id']) ? (int)$_POST['template_id'] : null;
    $recipientType = $_POST['recipient_type'];

    if ($campaignId) {
        $stmt = $db->prepare("
            UPDATE email_campaigns 
            SET name = ?, subject = ?, content = ?, template_id = ?, recipient_type = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $subject, $content, $templateId, $recipientType, $campaignId]);
    } else {
        $stmt = $db->prepare("
            INSERT INTO email_campaigns (name, subject, content, template_id, recipient_type, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $subject, $content, $templateId, $recipientType, $_SESSION['user_id']]);
        $campaignId = $db->lastInsertId();
    }

    header('Location: email_campaigns.php');
    exit();
}

$templates = $templateModel->getAll();

?>

<div class="container-fluid px-4">
    <h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

    <form method="POST">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="form-group">
                    <label for="name">Campaign Name</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= htmlspecialchars($campaign['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" 
                           value="<?= htmlspecialchars($campaign['subject']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="template_id">Email Template (Optional)</label>
                    <select class="form-control" id="template_id" name="template_id">
                        <option value="">-- Select a Template --</option>
                        <?php foreach ($templates as $template): ?>
                            <option value="<?= $template['id'] ?>" <?= $campaign['template_id'] == $template['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($template['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea class="form-control" id="content" name="content" rows="10"><?= htmlspecialchars($campaign['content']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="recipient_type">Recipient Type</label>
                    <select class="form-control" id="recipient_type" name="recipient_type">
                        <option value="all_users" <?= $campaign['recipient_type'] === 'all_users' ? 'selected' : '' ?>>All Users</option>
                        <option value="newsletter_subscribers" <?= $campaign['recipient_type'] === 'newsletter_subscribers' ? 'selected' : '' ?>>Newsletter Subscribers</option>
                        <option value="applicants" <?= $campaign['recipient_type'] === 'applicants' ? 'selected' : '' ?>>Applicants</option>
                        <option value="active_users" <?= $campaign['recipient_type'] === 'active_users' ? 'selected' : '' ?>>Active Users</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save Campaign</button>
                <a href="email_campaigns.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
