<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Site Settings</h1>
    </div>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <?php foreach ($groupedSettings as $group => $settings): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?= ucfirst($group) ?> Settings</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($settings as $setting): ?>
                        <div class="form-group">
                            <label for="<?= $setting['setting_key'] ?>"><?= htmlspecialchars($setting['setting_label']) ?></label>
                            <?php switch ($setting['setting_type']):
                                case 'textarea': ?>
                                    <textarea class="form-control" id="<?= $setting['setting_key'] ?>" name="<?= $setting['setting_key'] ?>" rows="4"><?= htmlspecialchars($setting['setting_value'] ?? '') ?></textarea>
                                    <?php break;
                                case 'file': ?>
                                    <?php if (!empty($setting['setting_value'])): ?>
                                        <div class="mb-2">
                                            <img src="../../<?= htmlspecialchars($setting['setting_value']) ?>" alt="<?= htmlspecialchars($setting['setting_label']) ?>" style="max-width: 150px; max-height: 150px;" class="img-thumbnail">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control-file" id="<?= $setting['setting_key'] ?>" name="<?= $setting['setting_key'] ?>" accept="image/*">
                                    <?php break;
                                case 'boolean': ?>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="<?= $setting['setting_key'] ?>" name="<?= $setting['setting_key'] ?>" value="1" <?= $setting['setting_value'] ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="<?= $setting['setting_key'] ?>"></label>
                                    </div>
                                    <?php break;
                                case 'number': ?>
                                    <input type="number" class="form-control" id="<?= $setting['setting_key'] ?>" name="<?= $setting['setting_key'] ?>" value="<?= htmlspecialchars($setting['setting_value'] ?? '') ?>">
                                    <?php break;
                                default: ?>
                                    <input type="<?= $setting['setting_type'] ?>" class="form-control" id="<?= $setting['setting_key'] ?>" name="<?= $setting['setting_key'] ?>" value="<?= htmlspecialchars($setting['setting_value'] ?? '') ?>">
                            <?php endswitch; ?>
                            <small class="form-text text-muted"><?= htmlspecialchars($setting['setting_description']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

