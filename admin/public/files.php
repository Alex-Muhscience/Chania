
<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$pageTitle = "File Management";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'File Management']
];

$errors = [];
$success = false;

// Get filter parameters
$search = $_GET['search'] ?? '';
$fileType = $_GET['file_type'] ?? '';
$entityType = $_GET['entity_type'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

try {
    $db = (new Database())->connect();
    
    // Build query conditions
    $conditions = ["deleted_at IS NULL"];
    $params = [];
    
    if ($search) {
        $conditions[] = "(original_name LIKE ? OR stored_name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($fileType) {
        $conditions[] = "file_type = ?";
        $params[] = $fileType;
    }
    
    if ($entityType) {
        $conditions[] = "entity_type = ?";
        $params[] = $entityType;
    }
    
    $whereClause = "WHERE " . implode(" AND ", $conditions);
    
    // Get total count
    $stmt = $db->prepare("SELECT COUNT(*) FROM file_uploads $whereClause");
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    $totalPages = ceil($totalItems / $limit);
    
    // Get files
    $stmt = $db->prepare("
        SELECT f.*, u.full_name as uploaded_by_name
        FROM file_uploads f
        LEFT JOIN users u ON f.uploaded_by = u.id
        $whereClause
        ORDER BY f.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([...$params, $limit, $offset]);
    $files = $stmt->fetchAll();
    
    // Get file type counts
    $stmt = $db->query("
        SELECT file_type, COUNT(*) as count
        FROM file_uploads
        WHERE deleted_at IS NULL
        GROUP BY file_type
    ");
    $fileTypeCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
} catch (PDOException $e) {
    error_log("Files fetch error: " . $e->getMessage());
    $files = [];
    $totalItems = 0;
    $totalPages = 0;
    $fileTypeCounts = [];
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $uploadedFile = $_FILES['file'] ?? null;
    
    if ($uploadedFile && $uploadedFile['error'] === UPLOAD_ERR_OK) {
        $result = Utilities::uploadFile($uploadedFile, $_POST['entity_type'] ?? null, $_POST['entity_id'] ?? null);
        
        if ($result['success']) {
            $success = true;
            $_SESSION['success'] = "File uploaded successfully.";
            Utilities::redirect('/admin/files.php');
        } else {
            $errors[] = $result['error'];
        }
    } else {
        $errors[] = "Please select a file to upload.";
    }
}

// Handle file deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $fileId = intval($_GET['id']);
    
    try {
        $db->beginTransaction();
        
        // Get file info
        $stmt = $db->prepare("SELECT * FROM file_uploads WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch();
        
        if ($file) {
            // Soft delete
            $stmt = $db->prepare("UPDATE file_uploads SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$fileId]);
            
            // Delete physical file
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }
            
            $db->commit();
            $_SESSION['success'] = "File deleted successfully.";
            Utilities::logActivity($_SESSION['user_id'], 'DELETE_FILE', 'file_uploads', $fileId, $_SERVER['REMOTE_ADDR']);
        } else {
            $errors[] = "File not found.";
        }
        
    } catch (PDOException $e) {
        $db->rollback();
        error_log("File deletion error: " . $e->getMessage());
        $errors[] = "Failed to delete file.";
    }
    
    Utilities::redirect('/admin/files.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">File Types</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="<?= BASE_URL ?>/admin/files.php" 
                       class="list-group-item list-group-item-action <?= !$fileType ? 'active' : '' ?>">
                        <i class="fas fa-file"></i> All Files
                        <span class="badge badge-primary badge-pill float-right">
                            <?= array_sum($fileTypeCounts) ?>
                        </span>
                    </a>
                    <?php foreach ($fileTypeCounts as $type => $count): ?>
                        <a href="<?= BASE_URL ?>/admin/files.php?file_type=<?= urlencode($type) ?>" 
                           class="list-group-item list-group-item-action <?= $fileType === $type ? 'active' : '' ?>">
                            <i class="fas fa-<?= $type === 'image' ? 'image' : ($type === 'document' ? 'file-alt' : 'file') ?>"></i> 
                            <?= ucfirst($type) ?>s
                            <span class="badge badge-primary badge-pill float-right"><?= $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Storage Stats</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="mb-2">
                        <small class="text-muted">Total Files</small>
                        <div class="h4 font-weight-bold"><?= number_format($totalItems) ?></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Storage Used</small>
                        <div class="h4 font-weight-bold">
                            <?php
                            $totalSize = 0;
                            foreach ($files as $file) {
                                $totalSize += $file['file_size'];
                            }
                            echo Utilities::formatFileSize($totalSize);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Operation completed successfully!
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 font-weight-bold text-primary">Files</h6>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#uploadModal">
                            <i class="fas fa-upload"></i> Upload File
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Search and Filter -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search files..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="entity_type" class="form-control">
                                <option value="">All Entities</option>
                                <option value="program" <?= $entityType === 'program' ? 'selected' : '' ?>>Programs</option>
                                <option value="event" <?= $entityType === 'event' ? 'selected' : '' ?>>Events</option>
                                <option value="testimonial" <?= $entityType === 'testimonial' ? 'selected' : '' ?>>Testimonials</option>
                                <option value="team_member" <?= $entityType === 'team_member' ? 'selected' : '' ?>>Team Members</option>
                                <option value="partner" <?= $entityType === 'partner' ? 'selected' : '' ?>>Partners</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="<?= BASE_URL ?>/admin/files.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
                
                <!-- Files Grid -->
                <?php if (empty($files)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-5x text-muted mb-3"></i>
                        <h5 class="text-muted">No files found</h5>
                        <p class="text-muted">Upload some files to get started</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($files as $file): ?>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <?php if ($file['file_type'] === 'image'): ?>
                                                <img src="<?= BASE_URL ?>/<?= $file['file_path'] ?>" 
                                                     class="img-fluid rounded" style="max-height: 100px;">
                                            <?php else: ?>
                                                <i class="fas fa-file-<?= $file['file_type'] === 'document' ? 'alt' : 'archive' ?> fa-4x text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                        <h6 class="card-title" title="<?= htmlspecialchars($file['original_name']) ?>">
                                            <?= Utilities::truncate($file['original_name'], 20) ?>
                                        </h6>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <?= Utilities::formatFileSize($file['file_size']) ?><br>
                                                <?= ucfirst($file['file_type']) ?><br>
                                                <?= date('M j, Y', strtotime($file['created_at'])) ?>
                                            </small>
                                        </p>
                                        <?php if ($file['entity_type']): ?>
                                            <span class="badge badge-info"><?= ucfirst($file['entity_type']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer">
                                        <div class="btn-group btn-group-sm d-flex" role="group">
                                            <a href="<?= BASE_URL ?>/<?= $file['file_path'] ?>" 
                                               class="btn btn-outline-primary" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/<?= $file['file_path'] ?>" 
                                               class="btn btn-outline-success" download>
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/files.php?action=delete&id=<?= $file['id'] ?>" 
                                               class="btn btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to delete this file?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Files pagination">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&file_type=<?= urlencode($fileType) ?>&entity_type=<?= urlencode($entityType) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload File</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="upload">
                    
                    <div class="form-group">
                        <label for="file">Select File *</label>
                        <input type="file" class="form-control-file" id="file" name="file" required>
                        <small class="form-text text-muted">
                            Maximum size: <?= Utilities::formatFileSize(MAX_FILE_SIZE) ?><br>
                            Allowed types: <?= implode(', ', array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOCUMENT_TYPES)) ?>
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="entity_type">Entity Type</label>
                        <select name="entity_type" class="form-control" id="entity_type">
                            <option value="">General</option>
                            <option value="program">Program</option>
                            <option value="event">Event</option>
                            <option value="testimonial">Testimonial</option>
                            <option value="team_member">Team Member</option>
                            <option value="partner">Partner</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="entity_id">Entity ID</label>
                        <input type="number" class="form-control" id="entity_id" name="entity_id" 
                               placeholder="Leave empty for general files">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>