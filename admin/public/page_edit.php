<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../../shared/Core/Page.php';

$pageManager = new Page($db);
$templates = $pageManager->getTemplates();

$pageData = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $isEdit = true;
    $pageData = $pageManager->getById($_GET['id']);
    if (!$pageData) {
        $_SESSION['error_message'] = 'Page not found.';
        header('Location: ' . BASE_URL . '/admin/public/pages.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'title' => $_POST['title'],
            'slug' => $_POST['slug'],
            'content' => $_POST['content'],
            'meta_title' => $_POST['meta_title'],
            'meta_description' => $_POST['meta_description'],
            'template' => $_POST['template'],
            'is_published' => isset($_POST['is_published']) ? 1 : 0
        ];

        if ($isEdit) {
            $pageManager->update($_GET['id'], $data);
            $_SESSION['success_message'] = 'Page updated successfully!';
        } else {
            $pageManager->create($data);
            $_SESSION['success_message'] = 'Page created successfully!';
        }
        
        header('Location: ' . BASE_URL . '/admin/public/pages.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error saving page: ' . $e->getMessage();
    }
}

?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumbs-->
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="index.php">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="pages.php">Pages</a>
            </li>
            <li class="breadcrumb-item active"><?= $isEdit ? 'Edit Page' : 'Create Page' ?></li>
        </ol>

        <form method="POST">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><?= $isEdit ? 'Edit Page' : 'Create New Page' ?></h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="title">Page Title *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?= htmlspecialchars($pageData['title'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="slug">URL Slug</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><?= BASE_URL ?>/</span>
                                    </div>
                                    <input type="text" class="form-control" id="slug" name="slug" 
                                           value="<?= htmlspecialchars($pageData['slug'] ?? '') ?>" placeholder="auto-generated">
                                </div>
                                <small class="form-text text-muted">Leave blank to auto-generate from title</small>
                            </div>

                            <div class="form-group">
                                <label for="content">Page Content</label>
                                <textarea class="form-control" id="content" name="content" rows="20"><?= htmlspecialchars($pageData['content'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>SEO Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="meta_title">Meta Title</label>
                                <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                       value="<?= htmlspecialchars($pageData['meta_title'] ?? '') ?>" maxlength="255">
                                <small class="form-text text-muted">Leave blank to use page title</small>
                            </div>

                            <div class="form-group">
                                <label for="meta_description">Meta Description</label>
                                <textarea class="form-control" id="meta_description" name="meta_description" 
                                          rows="3" maxlength="160"><?= htmlspecialchars($pageData['meta_description'] ?? '') ?></textarea>
                                <small class="form-text text-muted">Recommended: 150-160 characters</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Publish Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Publish Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_published" name="is_published" 
                                           <?= ($pageData['is_published'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="is_published">Publish this page</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="template">Page Template</label>
                                <select class="form-control" id="template" name="template">
                                    <?php foreach ($templates as $key => $name): ?>
                                        <option value="<?= $key ?>" <?= ($pageData['template'] ?? 'default') === $key ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php if ($isEdit): ?>
                                <div class="form-group">
                                    <small class="text-muted">
                                        Created: <?= date('M j, Y, g:i A', strtotime($pageData['created_at'])) ?><br>
                                        Updated: <?= date('M j, Y, g:i A', strtotime($pageData['updated_at'])) ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> <?= $isEdit ? 'Update Page' : 'Create Page' ?>
                            </button>
                            
                            <?php if ($isEdit): ?>
                                <a href="<?= BASE_URL ?>/page.php?slug=<?= $pageData['slug'] ?>" 
                                   class="btn btn-info btn-block" target="_blank">
                                    <i class="fas fa-eye"></i> Preview Page
                                </a>
                            <?php endif; ?>
                            
                            <a href="<?= BASE_URL ?>/admin/public/pages.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Back to Pages
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Include TinyMCE Rich Text Editor -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize TinyMCE
    tinymce.init({
        selector: '#content',
        height: 400,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic forecolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help | code',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
        branding: false,
        promotion: false
    });

    // Auto-generate slug from title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    
    titleInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.getAttribute('data-auto') !== 'false') {
            const slug = this.value
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
        }
    });
    
    slugInput.addEventListener('input', function() {
        this.setAttribute('data-auto', 'false');
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
