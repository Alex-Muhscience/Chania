<?php
require_once '../includes/header.php';
require_once '../../shared/Core/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $position = $_POST['position'] ?? null;
    $bio = $_POST['bio'] ?? null;
    $social_links = $_POST['social_links'] ?? null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle file upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/team/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'team_' . time() . '.' . $file_extension;
        $image_path = 'uploads/team/' . $filename;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
            die('Failed to upload image');
        }
    }
    
    try {
        $db = (new Database())->connect();
        $sql = "INSERT INTO team_members (full_name, position, bio, social_links, image_path, is_active) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$full_name, $position, $bio, $social_links, $image_path, $is_active]);
        header('Location: team_members.php');
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}
?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Add Team Member</h1>
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" class="form-control" id="position" name="position">
                </div>
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea class="form-control" id="bio" name="bio" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="social_links">Social Links (JSON format)</label>
                    <textarea class="form-control" id="social_links" name="social_links" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active_team" name="is_active" value="1" checked>
                        <label class="custom-control-label" for="is_active_team">Active</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Team Member</button>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
