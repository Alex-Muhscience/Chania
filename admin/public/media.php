<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../../shared/Core/Media.php';

$media = new Media($db);
$mediaItems = $media->getAll();

?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumbs-->
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

        <div class="card mb-3">
            <div class="card-header">
                <i class="fa fa-table"></i> Media Files
                <a href="#" class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#uploadModal">Upload New Media</a>
            </div>
            <div class="card-body">
                <?php if (empty($mediaItems)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-photo-video fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No media files found</h5>
                        <p class="text-muted">Upload your first media file to get started.</p>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#uploadModal">
                            <i class="fas fa-upload"></i> Upload Media
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Thumbnail</th>
                                    <th>Original Name</th>
                                    <th>File Type</th>
                                    <th>Uploaded On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mediaItems as $item) : ?>
                                    <tr>
                                        <td>
                                            <?php if (strpos($item['mime_type'], 'image/') === 0): ?>
                                                <img src="<?= BASE_URL ?><?= $item['file_path'] ?>" alt="<?= $item['original_name'] ?>" width="80" height="60" style="object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <div class="text-center" style="width: 80px; height: 60px; line-height: 60px; background: #f8f9fa; border-radius: 4px;">
                                                    <i class="fas fa-file fa-2x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['original_name']) ?></td>
                                        <td><span class="badge badge-secondary"><?= $item['file_type'] ?></span></td>
                                        <td><?= date('M j, Y g:i A', strtotime($item['created_at'])) ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?><?= $item['file_path'] ?>" class="btn btn-info btn-sm" target="_blank" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/actions/delete_media.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this media?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal-->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload New Media</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

