<?php
$pageTitle = 'Edit Team Member';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Require authentication
Utilities::requireLogin();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: team_members.php?error=Invalid team member ID');
    exit;
}

$db = (new Database())->connect();
$stmt = $db->prepare("SELECT * FROM team_members WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$id]);
$team_member = $stmt->fetch();

if (!$team_member) {
    header('Location: team_members.php?error=Team member not found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $position = $_POST['position'] ?? null;
    $bio = $_POST['bio'] ?? null;
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $linkedin_url = $_POST['linkedin_url'] ?? null;
    $twitter_url = $_POST['twitter_url'] ?? null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle file upload
    $image_path = $team_member['image_path']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/team/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'team_' . time() . '.' . $file_extension;
        $image_path = 'uploads/team/' . $filename;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
            header('Location: team_member_edit.php?id=' . $id . '&error=Failed to upload image');
            exit;
        }
    }
    
    try {
        $sql = "UPDATE team_members SET name = ?, position = ?, bio = ?, email = ?, phone = ?, linkedin_url = ?, twitter_url = ?, image_path = ?, is_active = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$name, $position, $bio, $email, $phone, $linkedin_url, $twitter_url, $image_path, $is_active, $id]);
        header('Location: team_members.php?updated=1');
        exit;
    } catch (Exception $e) {
        header('Location: team_member_edit.php?id=' . $id . '&error=' . urlencode($e->getMessage()));
        exit;
    }
}

$message = '';
if (isset($_GET['error'])) {
    $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($_GET['error']) . '</div>';
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <?= $message ?>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Team Member</h1>
        <a href="team_members.php" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Team Members
        </a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($team_member['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" class="form-control" id="position" name="position" value="<?= htmlspecialchars($team_member['position'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($team_member['bio'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($team_member['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($team_member['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="linkedin_url">LinkedIn URL</label>
                    <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" value="<?= htmlspecialchars($team_member['linkedin_url'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="twitter_url">Twitter URL</label>
                    <input type="url" class="form-control" id="twitter_url" name="twitter_url" value="<?= htmlspecialchars($team_member['twitter_url'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="image">Image</label>
                    <?php if ($team_member['image_path']): ?>
                        <div class="mb-2">
                            <img src="<?= BASE_URL ?>/<?= $team_member['image_path'] ?>" alt="Current Image" class="img-thumbnail" style="max-width: 100px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">Upload a new image to replace the current one.</small>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active_team" name="is_active" value="1" <?= $team_member['is_active'] ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="is_active_team">Active</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Team Member</button>
                <a href="team_members.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
