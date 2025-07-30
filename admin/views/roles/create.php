<div class="container-fluid px-4">
    <h1 class="mt-4"><?= htmlspecialchars($pageTitle) ?></h1>
    <ol class="breadcrumb mb-4">
        <?php foreach ($breadcrumbs as $breadcrumb): ?>
            <?php if (isset($breadcrumb['url'])): ?>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($breadcrumb['url']) ?>"><?= htmlspecialchars($breadcrumb['title']) ?></a></li>
            <?php else: ?>
                <li class="breadcrumb-item active"><?= htmlspecialchars($breadcrumb['title']) ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-tag me-1"></i>
            Add New Role
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="is_default" class="form-label">Default Role</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1"
                                       <?= isset($_POST['is_default']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_default">
                                    Set as default role for new users
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Permissions</label>
                    <div class="row">
                        <?php foreach ($availablePermissions as $permission => $label): ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" 
                                           id="perm_<?= $permission ?>" 
                                           name="permissions[]" 
                                           value="<?= $permission ?>"
                                           <?= in_array($permission, $_POST['permissions'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="perm_<?= $permission ?>">
                                        <?= htmlspecialchars($label) ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="roles.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Roles
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle "All Permissions" checkbox
document.getElementById('perm_*').addEventListener('change', function() {
    const allPermsChecked = this.checked;
    const otherCheckboxes = document.querySelectorAll('input[name="permissions[]"]:not(#perm_\\*)');
    
    otherCheckboxes.forEach(checkbox => {
        checkbox.disabled = allPermsChecked;
        if (allPermsChecked) {
            checkbox.checked = false;
        }
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const allPermsCheckbox = document.getElementById('perm_*');
    if (allPermsCheckbox.checked) {
        allPermsCheckbox.dispatchEvent(new Event('change'));
    }
});
</script>
