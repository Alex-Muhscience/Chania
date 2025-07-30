<?php
$pageTitle = 'Media Library';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../../shared/Core/Settings.php';

// Require authentication
Utilities::requireRole('admin');

// Get all media files
$db = (new Database())->connect();
$stmt = $db->query("SELECT m.*, u.username as uploader_name FROM media_library m JOIN users u ON m.uploaded_by = u.id ORDER BY m.created_at DESC");
$media_files = $stmt->fetchAll();

// Handle messages
$message = '';
if (isset($_GET['deleted'])) {
    $message = '<div class="alert alert-success">File deleted successfully!</div>';
} elseif (isset($_GET['error'])) {
    $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($_GET['error']) . '</div>';
} elseif (isset($_GET['uploaded'])) {
    $message = '<div class="alert alert-success">File uploaded successfully!</div>';
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <?= $message ?>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Media Library</h1>
        <a href="#" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#uploadModal">
            <i class="fas fa-upload fa-sm text-white-50"></i> Upload New File
        </a>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <?php if (empty($media_files)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-folder-open fa-3x mb-2"></i>
                        <p>No media files found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($media_files as $file): ?>
                        <div class="col-md-2 mb-4">
                            <div class="card h-100">
                                <div class="card-img-top text-center pt-3">
                                    <?php if (strpos($file['mime_type'], 'image') === 0): ?>
                                        <img src="../../<?= htmlspecialchars($file['file_path']) ?>" alt="<?= htmlspecialchars($file['alt_text']) ?>" class="img-fluid" style="max-height: 100px;">
                                    <?php else: ?>
                                        <i class="fas fa-file-alt fa-3x text-gray-500"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body text-center">
                                    <p class="card-text small"><?= htmlspecialchars($file['original_name']) ?></p>
                                </div>
                                <div class="card-footer text-center">
                                    <button class="btn btn-secondary btn-sm view-btn" data-id="<?= $file['id'] ?>"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="<?= $file['id'] ?>"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload New File</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data" action="media_upload.php" method="POST">
                    <div class="form-group">
                        <label for="file">Choose File</label>
                        <input type="file" class="form-control-file" id="file" name="file" required>
                    </div>
                    <div class="form-group">
                        <label for="alt_text">Alt Text</label>
                        <input type="text" class="form-control" id="alt_text" name="alt_text">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="folder">Folder</label>
                        <input type="text" class="form-control" id="folder" name="folder" value="general">
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Details</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- File details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
