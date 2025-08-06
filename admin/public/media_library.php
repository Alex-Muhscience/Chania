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
                                        <img src="<?= BASE_URL . htmlspecialchars($file['file_path']) ?>" alt="<?= htmlspecialchars($file['alt_text']) ?>" class="img-fluid" style="max-height: 100px;">
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
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="file">Choose File</label>
                        <input type="file" class="form-control-file" id="file" name="file" required>
                        <small class="form-text text-muted">Max file size: 10MB. Supported formats: Images, Documents, Videos, Audio</small>
                    </div>
                    <div class="form-group">
                        <label for="alt_text">Alt Text</label>
                        <input type="text" class="form-control" id="alt_text" name="alt_text" placeholder="Alternative text for screen readers">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional file description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="folder">Folder</label>
                        <select class="form-control" id="folder" name="folder">
                            <option value="general">General</option>
                            <option value="images">Images</option>
                            <option value="documents">Documents</option>
                            <option value="videos">Videos</option>
                            <option value="audio">Audio</option>
                        </select>
                    </div>
                    <div class="progress" id="uploadProgress" style="display: none; margin-bottom: 15px;">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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

<script>
// Enhanced Media Library JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadBtn = document.getElementById('uploadBtn');
    const fileInput = document.getElementById('file');
    
    // File input validation
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert('File size exceeds 10MB limit. Please choose a smaller file.');
                this.value = '';
                return;
            }
            
            // Auto-set folder based on file type
            const folderSelect = document.getElementById('folder');
            const fileType = file.type;
            
            if (fileType.startsWith('image/')) {
                folderSelect.value = 'images';
            } else if (fileType.startsWith('video/')) {
                folderSelect.value = 'videos';
            } else if (fileType.startsWith('audio/')) {
                folderSelect.value = 'audio';
            } else if (fileType.includes('pdf') || fileType.includes('document') || fileType.includes('word') || fileType.includes('excel') || fileType.includes('powerpoint')) {
                folderSelect.value = 'documents';
            }
        }
    });
    
    // Upload form submission
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Show progress
        uploadProgress.style.display = 'block';
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        
        // Create XMLHttpRequest for progress tracking
        const xhr = new XMLHttpRequest();
        
        // Upload progress
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                const progressBar = uploadProgress.querySelector('.progress-bar');
                progressBar.style.width = percentComplete + '%';
                progressBar.textContent = Math.round(percentComplete) + '%';
            }
        });
        
        // Upload complete
        xhr.addEventListener('load', function() {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload';
            uploadProgress.style.display = 'none';
            
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Show success message
                    showAlert('success', response.message);
                    
                    // Close modal
                    $('#uploadModal').modal('hide');
                    
                    // Reset form
                    uploadForm.reset();
                    
                    // Reload page to show new file
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlert('danger', response.message);
                }
            } catch (error) {
                showAlert('danger', 'Error processing response: ' + error.message);
            }
        });
        
        // Upload error
        xhr.addEventListener('error', function() {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload';
            uploadProgress.style.display = 'none';
            showAlert('danger', 'Upload failed. Please try again.');
        });
        
        // Send request
        xhr.open('POST', 'media_upload.php');
        xhr.send(formData);
    });
    
    // View button functionality
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', function() {
            const mediaId = this.getAttribute('data-id');
            viewMediaFile(mediaId);
        });
    });
    
    // Delete button functionality
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const mediaId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this media file? This action cannot be undone.')) {
                deleteMediaFile(mediaId);
            }
        });
    });
    
    // Function to view media file
    function viewMediaFile(id) {
        fetch(`media_api.php?action=view&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const file = data.file;
                    const modalBody = document.getElementById('viewModalBody');
                    
                    let imagePreview = '';
                    if (file.mime_type.startsWith('image/')) {
                        imagePreview = `<div class="text-center mb-3">
                            <img src="<?= BASE_URL ?>${file.file_path}" class="img-fluid" style="max-height: 300px;" alt="${file.alt_text || file.original_name}">
                        </div>`;
                    }
                    
                    modalBody.innerHTML = `
                        ${imagePreview}
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 30%;">File Name:</th>
                                <td>${file.original_name}</td>
                            </tr>
                            <tr>
                                <th>File Size:</th>
                                <td>${file.formatted_size}</td>
                            </tr>
                            <tr>
                                <th>File Type:</th>
                                <td>${file.mime_type}</td>
                            </tr>
                            <tr>
                                <th>Folder:</th>
                                <td>${file.folder || 'general'}</td>
                            </tr>
                            <tr>
                                <th>Alt Text:</th>
                                <td>${file.alt_text || 'Not set'}</td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td>${file.description || 'No description'}</td>
                            </tr>
                            <tr>
                                <th>Uploaded By:</th>
                                <td>${file.uploader_name}</td>
                            </tr>
                            <tr>
                                <th>Upload Date:</th>
                                <td>${file.upload_date}</td>
                            </tr>
                            <tr>
                                <th>File Path:</th>
                                <td><code>${file.file_path}</code></td>
                            </tr>
                            <tr>
                                <th>Actions:</th>
                                <td>
                                    <a href="../../${file.file_path}" class="btn btn-primary btn-sm" target="_blank">
                                        <i class="fas fa-external-link-alt"></i> Open File
                                    </a>
                                    <button class="btn btn-info btn-sm" onclick="copyToClipboard('../../${file.file_path}')">
                                        <i class="fas fa-copy"></i> Copy URL
                                    </button>
                                </td>
                            </tr>
                        </table>
                    `;
                    
                    $('#viewModal').modal('show');
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                showAlert('danger', 'Error loading file details: ' + error.message);
            });
    }
    
    // Function to delete media file
    function deleteMediaFile(id) {
        fetch(`media_api.php?action=delete&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    // Reload page to reflect changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                showAlert('danger', 'Error deleting file: ' + error.message);
            });
    }
    
    // Function to show alerts
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        
        const container = document.querySelector('.container-fluid');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Function to copy text to clipboard
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(window.location.origin + '/' + text.replace(/^\.\.?\//, '')).then(() => {
            showAlert('success', 'URL copied to clipboard!');
        }).catch(() => {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = window.location.origin + '/' + text.replace(/^\.\.?\//, '');
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showAlert('success', 'URL copied to clipboard!');
        });
    };
    
    // Drag and drop functionality
    const dropZone = document.querySelector('.card-body');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropZone.classList.add('drag-over');
    }
    
    function unhighlight() {
        dropZone.classList.remove('drag-over');
    }
    
    dropZone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            const file = files[0];
            
            // Set the file in the upload form
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
            
            // Trigger change event
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            
            // Open upload modal
            $('#uploadModal').modal('show');
        }
    }
});
</script>

<style>
.drag-over {
    border: 2px dashed #007bff !important;
    background-color: #f8f9fa !important;
}

.progress {
    height: 20px;
}

.progress-bar {
    font-size: 12px;
    line-height: 20px;
    text-align: center;
}

.card-img-top img {
    transition: transform 0.2s ease;
}

.card-img-top img:hover {
    transform: scale(1.05);
}

#viewModal .table th {
    font-weight: 600;
    color: #495057;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
