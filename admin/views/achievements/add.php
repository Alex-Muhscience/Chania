<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus mr-2"></i>Add Achievement
        </h1>
        <a href="achievements.php" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Back to Achievements
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Achievement Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-trophy mr-2"></i>Achievement Details
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="title" class="font-weight-bold">Achievement Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" required 
                                           placeholder="e.g., Students Trained" 
                                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                                    <small class="form-text text-muted">The main title for this achievement</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="category" class="font-weight-bold">Category</label>
                                    <select class="form-control" id="category" name="category">
                                        <option value="general" <?= ($_POST['category'] ?? '') === 'general' ? 'selected' : '' ?>>General</option>
                                        <option value="impact" <?= ($_POST['category'] ?? '') === 'impact' ? 'selected' : '' ?>>Impact</option>
                                        <option value="programs" <?= ($_POST['category'] ?? '') === 'programs' ? 'selected' : '' ?>>Programs</option>
                                        <option value="students" <?= ($_POST['category'] ?? '') === 'students' ? 'selected' : '' ?>>Students</option>
                                        <option value="partnerships" <?= ($_POST['category'] ?? '') === 'partnerships' ? 'selected' : '' ?>>Partnerships</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="font-weight-bold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Brief description of this achievement..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            <small class="form-text text-muted">Optional description providing more context</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="stat_value" class="font-weight-bold">Statistical Value *</label>
                                    <input type="text" class="form-control" id="stat_value" name="stat_value" required 
                                           placeholder="e.g., 2,500" 
                                           value="<?= htmlspecialchars($_POST['stat_value'] ?? '') ?>">
                                    <small class="form-text text-muted">The number/value to display</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="stat_unit" class="font-weight-bold">Unit</label>
                                    <input type="text" class="form-control" id="stat_unit" name="stat_unit" 
                                           placeholder="e.g., +, K+, %" 
                                           value="<?= htmlspecialchars($_POST['stat_unit'] ?? '') ?>">
                                    <small class="form-text text-muted">Optional unit suffix</small>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="icon" class="font-weight-bold">Icon Class</label>
                                    <input type="text" class="form-control" id="icon" name="icon" 
                                           placeholder="e.g., fas fa-users" 
                                           value="<?= htmlspecialchars($_POST['icon'] ?? 'fas fa-trophy') ?>">
                                    <small class="form-text text-muted">Font Awesome icon class</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="display_order" class="font-weight-bold">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" 
                                           placeholder="Leave blank for auto-assignment" 
                                           value="<?= htmlspecialchars($_POST['display_order'] ?? '') ?>">
                                    <small class="form-text text-muted">Lower numbers appear first</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Options</label>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                               <?= isset($_POST['is_active']) ? 'checked' : 'checked' ?>>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" 
                                               <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="is_featured">Featured</label>
                                    </div>
                                    <small class="form-text text-muted">Featured achievements are highlighted</small>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Save Achievement
                            </button>
                            <a href="achievements.php" class="btn btn-secondary ml-2">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Preview Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-eye mr-2"></i>Preview
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="achievement-preview">
                        <div class="icon-preview mb-3">
                            <i id="preview-icon" class="fas fa-trophy fa-3x text-primary"></i>
                        </div>
                        <div class="stat-preview mb-2">
                            <h3 class="text-primary mb-0">
                                <span id="preview-value">2,500</span><span id="preview-unit">+</span>
                            </h3>
                        </div>
                        <div class="title-preview">
                            <h6 id="preview-title" class="text-gray-800">Students Trained</h6>
                        </div>
                        <div class="description-preview">
                            <p id="preview-description" class="text-muted small">Brief description of this achievement...</p>
                        </div>
                        <div class="badges-preview mt-3">
                            <span id="preview-category" class="badge badge-secondary">General</span>
                            <span id="preview-featured" class="badge badge-warning ml-1" style="display: none;">Featured</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle mr-2"></i>Tips & Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p><strong>Statistical Value:</strong> Use clear, impactful numbers. Add commas for thousands (e.g., 2,500).</p>
                        <p><strong>Units:</strong> Common units include +, K+, %, M+ for thousands/millions.</p>
                        <p><strong>Icons:</strong> Use Font Awesome classes. Popular choices:
                            <br><code>fas fa-users</code> - For students/people
                            <br><code>fas fa-graduation-cap</code> - For education
                            <br><code>fas fa-award</code> - For achievements
                            <br><code>fas fa-chart-line</code> - For growth/progress
                        </p>
                        <p><strong>Categories:</strong> Help organize achievements by type for better management.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live preview functionality
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');
    const statValueInput = document.getElementById('stat_value');
    const statUnitInput = document.getElementById('stat_unit');
    const iconInput = document.getElementById('icon');
    const categoryInput = document.getElementById('category');
    const featuredInput = document.getElementById('is_featured');

    const previewTitle = document.getElementById('preview-title');
    const previewDescription = document.getElementById('preview-description');
    const previewValue = document.getElementById('preview-value');
    const previewUnit = document.getElementById('preview-unit');
    const previewIcon = document.getElementById('preview-icon');
    const previewCategory = document.getElementById('preview-category');
    const previewFeatured = document.getElementById('preview-featured');

    function updatePreview() {
        previewTitle.textContent = titleInput.value || 'Achievement Title';
        previewDescription.textContent = descriptionInput.value || 'Brief description of this achievement...';
        previewValue.textContent = statValueInput.value || '2,500';
        previewUnit.textContent = statUnitInput.value || '+';
        
        // Update icon
        previewIcon.className = (iconInput.value || 'fas fa-trophy') + ' fa-3x text-primary';
        
        // Update category
        const categoryText = categoryInput.options[categoryInput.selectedIndex].text;
        previewCategory.textContent = categoryText;
        previewCategory.className = 'badge badge-' + (
            categoryInput.value === 'impact' ? 'success' :
            categoryInput.value === 'programs' ? 'primary' :
            categoryInput.value === 'students' ? 'info' : 'secondary'
        );
        
        // Update featured badge
        previewFeatured.style.display = featuredInput.checked ? 'inline-block' : 'none';
    }

    // Attach event listeners
    titleInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    statValueInput.addEventListener('input', updatePreview);
    statUnitInput.addEventListener('input', updatePreview);
    iconInput.addEventListener('input', updatePreview);
    categoryInput.addEventListener('change', updatePreview);
    featuredInput.addEventListener('change', updatePreview);

    // Initial preview update
    updatePreview();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
