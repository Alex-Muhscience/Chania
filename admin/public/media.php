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
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>File Name</th>
                                <th>File Type</th>
                                <th>Uploaded On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mediaItems as $item) : ?>
                                <tr>
                                    <td><img src="<?= $item['file_path'] ?>" alt="<?= $item['file_name'] ?>" width="100"></td>
                                    <td><?= $item['file_name'] ?></td>
                                    <td><?= $item['file_type'] ?></td>
                                    <td><?= $item['uploaded_at'] ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/actions/delete_media.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this media?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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

