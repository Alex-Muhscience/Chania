<?php
require_once '../includes/header.php';
require_once '../../shared/Core/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $website_url = $_POST['website_url'] ?? null;
    $description = $_POST['description'] ?? null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle file upload
    $logo_path = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/partners/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $filename = 'partner_' . time() . '.' . $file_extension;
        $logo_path = 'uploads/partners/' . $filename;
        
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $filename)) {
            die('Failed to upload logo');
        }
    }
    
    try {
        $db = (new Database())->connect();
        $sql = "INSERT INTO partners (name, website_url, description, logo_path, is_active) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$name, $website_url, $description, $logo_path, $is_active]);
        header('Location: partners.php');
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}
?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Add Partner</h1>
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Partner Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="website_url">Website URL</label>
                    <input type="url" class="form-control" id="website_url" name="website_url">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="logo">Logo</label>
                    <input type="file" class="form-control-file" id="logo" name="logo" accept="image/*">
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                        <label class="custom-control-label" for="is_active">Active</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Partner</button>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>

