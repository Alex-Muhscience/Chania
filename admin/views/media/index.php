<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumbs -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="index.php">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Media Library</li>
        </ol>

        <div class="row">
            <div class="col-12">
                <h1>Media Library</h1>
                <p>Manage your uploaded files.</p>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-4">
                        <label for="search">Search Media</label>
                        <input type="text" class="form-control" id="search" name="search" placeholder="Search by filename..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="filter">Filter by Type</label>
                        <select class="form-control" id="filter" name="filter">
                            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Files</option>
                            <option value="images" <?= $filter === 'images' ? 'selected' : '' ?>>Images</option>
                            <option value="documents" <?= $filter === 'documents' ? 'selected' : '' ?>>Documents</option>
                            <option value="videos" <?= $filter === 'videos' ? 'selected' : '' ?>>Videos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sort">Sort by</label>
                        <select class="form-control" id="sort" name="sort">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A-Z</option>
                            <option value="size" <?= $sort === 'size' ? 'selected' : '' ?>>Largest First</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fa fa-table"></i> Media Files
                    <?php if ($totalItems > 0): ?>
                        <span class="badge badge-info ml-2"><?= number_format($totalItems) ?> files</span>
                    <?php endif; ?>
                </div>
                <div>
                    <button class="btn btn-danger btn-sm" id="bulkDeleteBtn" style="display: none;" onclick="bulkDelete()">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                    <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#uploadModal">
                        <i class="fas fa-upload"></i> Upload New Media
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($mediaItems)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-photo-video fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted"><?= !empty($search) ? 'No media files match your search' : 'No media files found' ?></h5>
                        <p class="text-muted">
                            <?= !empty($search) ? 'Try adjusting your search terms or filters.' : 'Upload your first media file to get started.' ?>
                        </p>
                        <?php if (empty($search)): ?>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#uploadModal">
                                <i class="fas fa-upload"></i> Upload Media
                            </button>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/admin/public/media.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear Search
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <form id="bulk-action-form" method="POST">
                        <input type="hidden" name="action" value="bulk_delete">
                        <table class="table table-bordered table-hover" id="mediaTable" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 30px;">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th style="width: 100px;">Thumbnail</th>
                                    <th>File Details</th>
                                    <th style="width: 120px;">File Size</th>
                                    <th style="width: 170px;">Uploaded On</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mediaItems as $item): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_items[]" value="<?= $item['id'] ?>">
                                        </td>
                                        <td>
                                            <?php if (strpos($item['mime_type'], 'image/') === 0): ?>
                                                <a href="<?= BASE_URL ?><?= $item['file_path'] ?>" target="_blank" title="View Image">
                                                    <img src="<?= BASE_URL ?><?= $item['file_path'] ?>" alt="<?= htmlspecialchars($item['original_name']) ?>" width="80" height="60" style="object-fit: cover; border-radius: 4px;">
                                                </a>
                                            <?php else: ?>
                                                <div class="text-center" style="width: 80px; height: 60px; line-height: 60px; background: #f8f9fa; border-radius: 4px;">
                                                    <i class="fas fa-file-alt fa-2x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($item['original_name']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($item['file_name']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-light"><?= $media->formatFileSize($item['file_size']) ?></span>
                                        </td>
                                        <td><?= date('M j, Y, g:i A', strtotime($item['created_at'])) ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?><?= $item['file_path'] ?>" class="btn btn-info btn-sm" target="_blank" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/actions/delete_media.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this media file?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </form>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Media pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload New Media</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?= BASE_URL ?>/admin/actions/upload_media.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="mediaFile">Select file</label>
                        <input type="file" class="form-control-file" id="mediaFile" name="mediaFile">
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced Media Library JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('input[name="selected_items[]"]');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    // Select All functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkDeleteButton();
        });
    }
    
    // Individual checkbox functionality
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            toggleBulkDeleteButton();
        });
    });
    
    function updateSelectAllState() {
        if (!selectAllCheckbox) return;
        
        const checkedBoxes = document.querySelectorAll('input[name="selected_items[]"]:checked');
        const totalBoxes = itemCheckboxes.length;
        
        if (checkedBoxes.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedBoxes.length === totalBoxes) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
            selectAllCheckbox.checked = false;
        }
    }
    
    function toggleBulkDeleteButton() {
        if (!bulkDeleteBtn) return;
        
        const checkedBoxes = document.querySelectorAll('input[name="selected_items[]"]:checked');
        
        if (checkedBoxes.length > 0) {
            bulkDeleteBtn.style.display = 'inline-block';
            bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> Delete Selected (${checkedBoxes.length})`;
        } else {
            bulkDeleteBtn.style.display = 'none';
        }
    }
    
    // Auto-submit search form when filter/sort changes
    const filterSelect = document.getElementById('filter');
    const sortSelect = document.getElementById('sort');
    
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    // Enhanced upload modal
    const uploadModal = document.getElementById('uploadModal');
    const fileInput = document.getElementById('mediaFile');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const fileSize = file.size;
                const maxSize = 10 * 1024 * 1024; // 10MB
                
                if (fileSize > maxSize) {
                    alert('File size exceeds 10MB limit. Please choose a smaller file.');
                    this.value = '';
                    return;
                }
                
                // Show file info
                const fileName = file.name;
                const fileType = file.type;
                const fileSizeFormatted = formatFileSize(fileSize);
                
                console.log(`Selected file: ${fileName} (${fileType}, ${fileSizeFormatted})`);
            }
        });
    }
    
    function formatFileSize(bytes) {
        if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        } else {
            return bytes + ' bytes';
        }
    }
    
    // Image hover effects
    const thumbnails = document.querySelectorAll('img[width="80"]');
    thumbnails.forEach(img => {
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});

// Bulk delete function
function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('input[name="selected_items[]"]:checked');
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one item to delete.');
        return;
    }
    
    const confirmMessage = `Are you sure you want to delete ${checkedBoxes.length} selected media file(s)? This action cannot be undone.`;
    
    if (confirm(confirmMessage)) {
        document.getElementById('bulk-action-form').submit();
    }
}

// Enhanced search with debounce
let searchTimeout;
const searchInput = document.getElementById('search');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            if (this.value.length >= 3 || this.value.length === 0) {
                // Auto-submit after 500ms of no typing
                this.form.submit();
            }
        }, 500);
    });
    
    // Clear search functionality
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            this.form.submit();
        }
    });
}
</script>
