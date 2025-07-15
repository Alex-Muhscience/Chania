<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require admin role
Utilities::requireRole('admin');

$pageTitle = "System Settings";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'System Settings']
];

$errors = [];
$success = false;

// Get current settings
try {
    $db = (new Database())->connect();

    $stmt = $db->query("SELECT * FROM system_settings ORDER BY setting_key");
    $allSettings = $stmt->fetchAll();

    // Group settings by category
    $settings = [];
    foreach ($allSettings as $setting) {
        $category = 'general';
        if (strpos($setting['setting_key'], 'email_') === 0) {
            $category = 'email';
        } elseif (strpos($setting['setting_key'], 'social_') === 0 || in_array($setting['setting_key'], ['facebook_url', 'twitter_url', 'linkedin_url', 'instagram_url'])) {
            $category = 'social';
        } elseif (in_array($setting['setting_key'], ['max_file_size', 'allowed_file_types'])) {
            $category = 'files';
        } elseif (in_array($setting['setting_key'], ['maintenance_mode', 'google_analytics_id'])) {
            $category = 'system';
        }

        $settings[$category][] = $setting;
    }

} catch (PDOException $e) {
    error_log("Settings fetch error: " . $e->getMessage());
    $settings = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedSettings = $_POST['settings'] ?? [];

    try {
        $db->beginTransaction();

        foreach ($updatedSettings as $key => $value) {
            // Validate and sanitize based on setting type
            $stmt = $db->prepare("SELECT setting_type FROM system_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $setting = $stmt->fetch();

            if ($setting) {
                // Type-specific validation
                switch ($setting['setting_type']) {
                    case 'boolean':
                        $value = isset($value) && $value ? 'true' : 'false';
                        break;
                    case 'number':
                        $value = is_numeric($value) ? $value : '0';
                        break;
                    case 'json':
                        if (is_array($value)) {
                            $value = json_encode($value);
                        }
                        // Validate JSON
                        json_decode($value);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $errors[] = "Invalid JSON format for {$key}";
                            continue;
                        }
                        break;
                    default:
                        $value = trim($value);
                }

                // Update setting
                $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$value, $key]);
            }
        }

        if (empty($errors)) {
            $db->commit();
            $success = true;

            // Log activity
            Utilities::logActivity($_SESSION['user_id'], 'UPDATE_SETTINGS', 'system_settings', null, $_SERVER['REMOTE_ADDR']);
        } else {
            $db->rollback();
        }

    } catch (PDOException $e) {
        $db->rollback();
        error_log("Settings update error: " . $e->getMessage());
        $errors[] = "Failed to update settings. Please try again.";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Settings Categories</h6>
                </div>
                <div class="card-body p-0">
                    <div class="nav nav-pills flex-column" id="v-pills-tab" role="tablist">
                        <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general" role="tab">
                            <i class="fas fa-cog"></i> General
                        </a>
                        <a class="nav-link" id="email-tab" data-toggle="pill" href="#email" role="tab">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                        <a class="nav-link" id="social-tab" data-toggle="pill" href="#social" role="tab">
                            <i class="fas fa-share-alt"></i> Social Media
                        </a>
                        <a class="nav-link" id="files-tab" data-toggle="pill" href="#files" role="tab">
                            <i class="fas fa-file"></i> File Management
                        </a>
                        <a class="nav-link" id="system-tab" data-toggle="pill" href="#system" role="tab">
                            <i class="fas fa-server"></i> System
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    Settings updated successfully!
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="tab-content" id="v-pills-tabContent">
                    <!-- General Settings -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
                            </div>
                            <div class="card-body">
                                <?php if (isset($settings['general'])): ?>
                                    <?php foreach ($settings['general'] as $setting): ?>
                                        <div class="form-group">
                                            <label for="<?= $setting['setting_key'] ?>">
                                                <?= ucwords(str_replace('_', ' ', $setting['setting_key'])) ?>
                                            </label>
                                            <?php if ($setting['setting_type'] === 'text'): ?>
                                                <textarea class="form-control" id="<?= $setting['setting_key'] ?>"
                                                          name="settings[<?= $setting['setting_key'] ?>]" rows="3"><?= htmlspecialchars($setting['setting_value']) ?></textarea>
                                            <?php elseif ($setting['setting_type'] === 'boolean'): ?>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                           id="<?= $setting['setting_key'] ?>"
                                                           name="settings[<?= $setting['setting_key'] ?>]"
                                                           value="1" <?= $setting['setting_value'] === 'true' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="<?= $setting['setting_key'] ?>">
                                                        Enable
                                                    </label>
                                                </div>
                                            <?php else: ?>
                                                <input type="text" class="form-control"
                                                       id="<?= $setting['setting_key'] ?>"
                                                       name="settings[<?= $setting['setting_key'] ?>]"
                                                       value="<?= htmlspecialchars($setting['setting_value']) ?>">
                                            <?php endif; ?>
                                            <?php if ($setting['description']): ?>
                                                <small class="form-text text-muted"><?= htmlspecialchars($setting['description']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Email Settings -->
                    <div class="tab-pane fade" id="email" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Email Settings</h6>
                            </div>
                            <div class="card-body">
                                <?php if (isset($settings['email'])): ?>
                                    <?php foreach ($settings['email'] as $setting): ?>
                                        <div class="form-group">
                                            <label for="<?= $setting['setting_key'] ?>">
                                                <?= ucwords(str_replace('_', ' ', $setting['setting_key'])) ?>
                                            </label>
                                            <?php if ($setting['setting_type'] === 'boolean'): ?>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                           id="<?= $setting['setting_key'] ?>"
                                                           name="settings[<?= $setting['setting_key'] ?>]"
                                                           value="1" <?= $setting['setting_value'] === 'true' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="<?= $setting['setting_key'] ?>">
                                                        Enable
                                                    </label>
                                                </div>
                                            <?php else: ?>
                                                <input type="text" class="form-control"
                                                       id="<?= $setting['setting_key'] ?>"
                                                       name="settings[<?= $setting['setting_key'] ?>]"
                                                       value="<?= htmlspecialchars($setting['setting_value']) ?>">
                                            <?php endif; ?>
                                            <?php if ($setting['description']): ?>
                                                <small class="form-text text-muted"><?= htmlspecialchars($setting['description']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Settings -->
                    <div class="tab-pane fade" id="social" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Social Media Settings</h6>
                            </div>
                            <div class="card-body">
                                <?php if (isset($settings['social'])): ?>
                                    <?php foreach ($settings['social'] as $setting): ?>
                                        <div class="form-group">
                                            <label for="<?= $setting['setting_key'] ?>">
                                                <?= ucwords(str_replace('_', ' ', $setting['setting_key'])) ?>
                                            </label>
                                            <input type="url" class="form-control"
                                                   id="<?= $setting['setting_key'] ?>"
                                                   name="settings[<?= $setting['setting_key'] ?>]"
                                                   value="<?= htmlspecialchars($setting['setting_value']) ?>">
                                            <?php if ($setting['description']): ?>
                                                <small class="form-text text-muted"><?= htmlspecialchars($setting['description']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- File Management Settings -->
                    <div class="tab-pane fade" id="files" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">File Management Settings</h6>
                            </div>
                            <div class="card-body">
                                <?php if (isset($settings['files'])): ?>
                                    <?php foreach ($settings['files'] as $setting): ?>
                                        <div class="form-group">
                                            <label for="<?= $setting['setting_key'] ?>">
                                                <?= ucwords(str_replace('_', ' ', $setting['setting_key'])) ?>
                                            </label>
                                            <?php if ($setting['setting_type'] === 'number'): ?>
                                                <input type="number" class="form-control"
                                                       id="<?= $setting['setting_key'] ?>"
                                                       name="settings[<?= $setting['setting_key'] ?>]"
                                                       value="<?= htmlspecialchars($setting['setting_value']) ?>">
                                            <?php elseif ($setting['setting_type'] === 'json'): ?>
                                                <textarea class="form-control" id="<?= $setting['setting_key'] ?>"
                                                          name="settings[<?= $setting['setting_key'] ?>]"
                                                          rows="3"><?= htmlspecialchars($setting['setting_value']) ?></textarea>
                                            <?php else: ?>
                                                <input type="text" class="form-control"
                                                       id="<?= $setting['setting_key'] ?>"
                                                       name="settings[<?= $setting['setting_key'] ?>]"
                                                       value="<?= htmlspecialchars($setting['setting_value']) ?>">
                                            <?php endif; ?>
                                            <?php if ($setting['description']): ?>
                                                <small class="form-text text-muted"><?= htmlspecialchars($setting['description']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- System Settings -->
                    <div class="tab-pane fade" id="system" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">System Settings</h6>
                            </div>
                            <div class="card-body">
                                <?php if (isset($settings['system'])): ?>
                                    <?php foreach ($settings['system'] as $setting): ?>
                                        <div class="form-group">
                                            <label for="<?= $setting['setting_key'] ?>">
                                                <?= ucwords(str_replace('_', ' ', $setting['setting_key'])) ?>
                                            </label>
                                            <?php if ($setting['setting_type'] === 'boolean'): ?>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                           id="<?= $setting['setting_key'] ?>"
                                                           name="settings[<?= $setting['setting_key'] ?>]"
                                                           value="1" <?= $setting['setting_value'] === 'true' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="<?= $setting['setting_key'] ?>">
                                                        Enable
                                                    </label>
                                                </div>
                                            <?php else: ?>
                                                <input type="text" class="form-control"
                                                       id="<?= $setting['setting_key'] ?>"
                                                       name="settings[<?= $setting['setting_key'] ?>]"
                                                       value="<?= htmlspecialchars($setting['setting_value']) ?>">
                                            <?php endif; ?>
                                            <?php if ($setting['description']): ?>
                                                <small class="form-text text-muted"><?= htmlspecialchars($setting['description']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                        <a href="<?= BASE_URL ?>/admin/public/test.php" class="btn btn-secondary">
                            <i class="fas fa-vial"></i> Test System
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>