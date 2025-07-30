<?php
$pageTitle = 'Edit Partner';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Require authentication
Utilities::requireLogin();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: partners.php?error=Invalid partner ID');
    exit;
}

$db = (new Database())->connect();
$stmt = $db->prepare("SELECT * FROM partners WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$id]);
$partner = $stmt->fetch();

if (!$partner) {
    header('Location: partners.php?error=Partner not found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $website_url = $_POST['website_url'] ?? null;
    $description = $_POST['description'] ?? null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle file upload
    $logo_path = $partner['logo_path']; // Keep existing logo by default
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/partners/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $filename = 'partner_' . time() . '.' . $file_extension;
        $logo_path = 'uploads/partners/' . $filename;
        
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $filename)) {
            header('Location: partner_edit.php?id=' . $id . '&error=Failed to upload logo');
            exit;
        }
    }
    
    try {
        $sql = "UPDATE partners SET name = ?, website_url = ?, description = ?, logo_path = ?, is_active = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$name, $website_url, $description, $logo_path, $is_active, $id]);
        header('Location: partners.php?updated=1');
        exit;
    } catch (Exception $e) {
        header('Location: partner_edit.php?id=' . $id . '&error=' . urlencode($e->getMessage()));
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
        <h1 class="h3 mb-0 text-gray-800">Edit Partner</h1>
        <a href="partners.php" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Partners
        </a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Partner Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($partner['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="website_url">Website URL</label>
                    <input type="url" class="form-control" id="website_url" name="website_url" value="<?= htmlspecialchars($partner['website_url']) ?>">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($partner['description']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="logo">Logo</label>
                    <?php if ($partner['logo_path']): ?>
                        <div class="mb-2">
                            <img src="<?= BASE_URL ?>/<?= $partner['logo_path'] ?>" alt="Current Logo" class="img-thumbnail" style="max-width: 100px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control-file" id="logo" name="logo" accept="image/*">
                    <small class="form-text text-muted">Upload a new logo to replace the current one.</small>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" <?= $partner['is_active'] ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="is_active">Active</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Partner</button>
                <a href="partners.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
