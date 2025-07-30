<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumbs -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="index.php">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">File Management</li>
        </ol>

        <div class="row">
            <div class="col-12">
                <h1>File Management</h1>
                <p>Manage uploaded files and documents.</p>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fas fa-upload"></i> Upload File
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Files</h5>
                        <h3 class="text-primary"><?= $pagination['totalItems'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Images</h5>
                        <h3 class="text-success"><?= $fileTypeCounts['image'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Documents</h5>
                        <h3 class="text-info"><?= $fileTypeCounts['document'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Other</h5>
                        <h3 class="text-warning"><?= $fileTypeCounts['other'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-filter"></i> Filters
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Search files...">
                    </div>
                    <div class="col-md-3">
                        <label for="file_type" class="form-label">File Type</label>
                        <select class="form-select" id="file_type" name="file_type">
                            <option value="">All Types</option>
                            <option value="image" <?= $filters['file_type'] === 'image' ? 'selected' : '' ?>>Images</option>
                            <option value="document" <?= $filters['file_type'] === 'document' ? 'selected' : '' ?>>Documents</option>
                            <option value="other" <?= $filters['file_type'] === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="entity_type" class="form-label">Entity Type</label>
                        <select class="form-select" id="entity_type" name="entity_type">
                            <option value="">All Entities</option>
                            <option value="user" <?= $filters['entity_type'] === 'user' ? 'selected' : '' ?>>Users</option>
                            <option value="program" <?= $filters['entity_type'] === 'program' ? 'selected' : '' ?>>Programs</option>
                            <option value="application" <?= $filters['entity_type'] === 'application' ? 'selected' : '' ?>>Applications</option>
                            <option value="general" <?= $filters['entity_type'] === 'general' ? 'selected' : '' ?>>General</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="files.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Files Table -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fa fa-table"></i> Files
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="filesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Entity</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($files)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No files found. <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload one now</button>.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($files as $file): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (in_array(strtolower(pathinfo($file['original_name'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                                    <img src="<?= BASE_URL ?>/<?= str_replace(__DIR__ . '/../../', '', $file['file_path']) ?>" 
                                                         class="me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="Preview">
                                                <?php else: ?>
                                                    <i class="fas fa-file me-2" style="font-size: 24px;"></i>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?= htmlspecialchars($file['original_name']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($file['stored_name']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $file['file_type'] === 'image' ? 'success' : ($file['file_type'] === 'document' ? 'info' : 'secondary') ?>">
                                                <?= ucfirst($file['file_type']) ?>
                                            </span>
                                        </td>
                                        <td><?= number_format($file['file_size'] / 1024, 2) ?> KB</td>
                                        <td>
                                            <?php if ($file['entity_type']): ?>
                                                <span class="badge bg-light text-dark"><?= ucfirst($file['entity_type']) ?></span>
                                                <?php if ($file['entity_id']): ?>
                                                    <small class="text-muted">#<?= $file['entity_id'] ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $file['uploader_name'] ?? 'Unknown' ?>
                                        </td>
                                        <td><?= date('M j, Y, g:i A', strtotime($file['created_at'])) ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/<?= str_replace(__DIR__ . '/../../', '', $file['file_path']) ?>" 
                                               class="btn btn-info btn-sm" target="_blank" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="files.php?action=delete&id=<?= $file['id'] ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Are you sure you want to delete this file?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['totalPages'] > 1): ?>
                    <nav aria-label="Files pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['totalPages'], $pagination['page'] + 2); $i++): ?>
                                <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="files.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Select File</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                        <div class="form-text">Maximum file size: 10MB</div>
                    </div>
                    <div class="mb-3">
                        <label for="entity_type_upload" class="form-label">Entity Type (Optional)</label>
                        <select class="form-select" id="entity_type_upload" name="entity_type">
                            <option value="">General</option>
                            <option value="user">User</option>
                            <option value="program">Program</option>
                            <option value="application">Application</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="entity_id_upload" class="form-label">Entity ID (Optional)</label>
                        <input type="number" class="form-control" id="entity_id_upload" name="entity_id" placeholder="Enter entity ID">
                        <div class="form-text">Leave blank for general files</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="action" value="upload" class="btn btn-primary">Upload File</button>
                </div>
            </form>
        </div>
    </div>
</div>
