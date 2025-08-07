<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create New Page</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Page Title *</label>
                                <input type="text" class="form-control" id="title" name="title"
                                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="slug" class="form-label">URL Slug *</label>
                                <input type="text" class="form-control" id="slug" name="slug"
                                       value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>" required
                                       placeholder="page-url-slug">
                                <div class="form-text">URL-friendly version of the title</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Page Content *</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="template" class="form-label">Template</label>
                                <select class="form-select" id="template" name="template">
                                    <option value="default" <?= ($_POST['template'] ?? 'default') === 'default' ? 'selected' : '' ?>>Default</option>
                                    <?php if (!empty($templates)): ?>
                                        <?php foreach ($templates as $template): ?>
                                            <option value="<?= htmlspecialchars($template) ?>" 
                                                    <?= ($_POST['template'] ?? '') === $template ? 'selected' : '' ?>>
                                                <?= ucfirst(str_replace(['_', '-'], ' ', $template)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1"
                                           <?= isset($_POST['is_published']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_published">
                                        Publish Page
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6>SEO Meta Information</h6>

                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" class="form-control" id="meta_title" name="meta_title"
                               value="<?= htmlspecialchars($_POST['meta_title'] ?? '') ?>"
                               placeholder="SEO title for search engines">
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" rows="3"
                                  placeholder="Brief description for search engines (recommended: 150-160 characters)"><?= htmlspecialchars($_POST['meta_description'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/admin/pages.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Pages
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Page
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    
    titleInput.addEventListener('input', function() {
        if (!slugInput.dataset.manuallyChanged) {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            slugInput.value = slug;
        }
    });
    
    slugInput.addEventListener('input', function() {
        this.dataset.manuallyChanged = 'true';
    });
});
</script>
