<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../../shared/Core/Page.php';

$pageManager = new Page($db);
$pages = $pageManager->getAll();

?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumbs-->
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="index.php">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Pages</li>
        </ol>

        <div class="row">
            <div class="col-12">
                <h1>Pages</h1>
                <p>Manage your website's pages.</p>
                <a href="<?= BASE_URL ?>/admin/public/page_edit.php" class="btn btn-primary mb-3">
                    <i class="fas fa-plus"></i> Create New Page
                </a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <i class="fa fa-table"></i> All Pages
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="pagesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Template</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pages)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No pages found. <a href="<?= BASE_URL ?>/admin/public/page_edit.php">Create one now</a>.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pages as $page): ?>
                                    <tr>
                                        <td>
                                            <strong><a href="<?= BASE_URL ?>/admin/public/page_edit.php?id=<?= $page['id'] ?>"><?= htmlspecialchars($page['title']) ?></a></strong>
                                        </td>
                                        <td>/<?= htmlspecialchars($page['slug']) ?></td>
                                        <td><?= htmlspecialchars($pageManager->getTemplates()[$page['template']] ?? 'Default') ?></td>
                                        <td>
                                            <span class="badge badge-<?= $page['is_published'] ? 'success' : 'secondary' ?>">
                                                <?= $page['is_published'] ? 'Published' : 'Draft' ?>
                                            </span>
                                        </td>
                                        <td><?= date('M j, Y, g:i A', strtotime($page['updated_at'])) ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/public/page_edit.php?id=<?= $page['id'] ?>" class="btn btn-primary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/page.php?slug=<?= $page['slug'] ?>" class="btn btn-info btn-sm" target="_blank" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-danger btn-sm" 
                                               onclick="if(confirm('Are you sure you want to delete this page?')) { document.getElementById('delete-form-<?= $page['id'] ?>').submit(); }" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <form id="delete-form-<?= $page['id'] ?>" action="<?= BASE_URL ?>/admin/actions/delete_page.php" method="POST" style="display: none;">
                                                <input type="hidden" name="id" value="<?= $page['id'] ?>">
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
