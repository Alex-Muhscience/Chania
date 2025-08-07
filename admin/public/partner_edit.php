<?php
$pageTitle = 'Edit Partner';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Require authentication
Utilities::requireLogin();

if (!function_exists('_can_manage_entity')) {
    function _can_manage_entity($entity) {
        return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'editor']);
    }
}
$can_manage = _can_manage_entity('partner');

if (!$can_manage) {
    header('Location: ../dashboard.php?error=Access denied');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: partners.php?error=Invalid partner ID');
    exit;
}

$db = (new Database())->connect();
$stmt = $db->prepare("SELECT * FROM partners WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$id]);
$partner = $stmt->fetch();

if (!$partner) {
    header('Location: partners.php?error=Partner not found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = !empty($_POST['slug']) ? trim($_POST['slug']) : Utilities::generateSlug($name);
    $description = trim($_POST['description']) ?? null;
    $website_url = !empty($_POST['website_url']) ? trim($_POST['website_url']) : null;
    $contact_email = !empty($_POST['contact_email']) ? trim($_POST['contact_email']) : null;
    $contact_phone = !empty($_POST['contact_phone']) ? trim($_POST['contact_phone']) : null;
    $contact_person = !empty($_POST['contact_person']) ? trim($_POST['contact_person']) : null;
    $partnership_type = $_POST['partnership_type'] ?? 'other';
    $partnership_level = $_POST['partnership_level'] ?? 'standard';
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $display_order = !empty($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
    
    $logo_path = $partner['logo_path']; // Keep existing logo by default
    $logo_url = !empty($_POST['logo_url']) ? trim($_POST['logo_url']) : null;
    
    try {
        // Handle file upload or URL
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            // File upload
            $upload_dir = '../../client/assets/images/partners/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Validate file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
            $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file_extension, $allowed_types)) {
                throw new Exception('Invalid file type. Allowed: JPG, JPEG, PNG, GIF, SVG, WEBP');
            }
            
            // Validate file size (5MB max)
            if ($_FILES['logo']['size'] > 5 * 1024 * 1024) {
                throw new Exception('File size too large. Maximum 5MB allowed.');
            }
            
            $safe_name = preg_replace('/[^a-zA-Z0-9-_]/', '', str_replace(' ', '-', strtolower($name)));
            $filename = $safe_name . '-logo-' . time() . '.' . $file_extension;
            $logo_path = $filename;
            
            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $filename)) {
                throw new Exception('Failed to upload logo file.');
            }
        } elseif ($logo_url) {
            // Use provided URL
            $logo_path = $logo_url;
        }
        
        // Check for duplicate slug (excluding current partner)
        $stmt = $db->prepare("SELECT id FROM partners WHERE slug = ? AND id != ? AND deleted_at IS NULL");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            throw new Exception('A partner with this slug already exists.');
        }
        
        $sql = "UPDATE partners SET name = ?, slug = ?, description = ?, logo_path = ?, website_url = ?, 
                contact_email = ?, contact_phone = ?, contact_person = ?, partnership_type = ?, 
                partnership_level = ?, start_date = ?, end_date = ?, is_featured = ?, is_active = ?, 
                display_order = ?, updated_at = NOW() WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $name, $slug, $description, $logo_path, $website_url, $contact_email, $contact_phone,
            $contact_person, $partnership_type, $partnership_level, $start_date, $end_date, 
            $is_featured, $is_active, $display_order, $id
        ]);
        
        header('Location: partners.php?updated=1');
        exit;
    } catch (Exception $e) {
        $error_message = 'Error: ' . $e->getMessage();
    }
}

$message = '';
if (isset($_GET['error'])) {
    $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($_GET['error']) . '</div>';
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit fa-fw"></i> Edit Partner: <?= htmlspecialchars($partner['name']) ?>
        </h1>
        <a href="partners.php" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Partners
        </a>
    </div>
    
    <?= $message ?>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error_message) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <!-- Basic Information -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle"></i> Basic Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Partner Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required 
                                           value="<?= htmlspecialchars($partner['name']) ?>" placeholder="Enter partner organization name">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" class="form-control" id="slug" name="slug" 
                                           value="<?= htmlspecialchars($partner['slug'] ?? '') ?>" placeholder="Auto-generated from name">
                                    <small class="form-text text-muted">Leave blank to auto-generate</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Brief description of the partnership and organization"><?= htmlspecialchars($partner['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="website_url">Website URL</label>
                                    <input type="url" class="form-control" id="website_url" name="website_url" 
                                           value="<?= htmlspecialchars($partner['website_url'] ?? '') ?>" placeholder="https://example.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_person">Contact Person</label>
                                    <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                           value="<?= htmlspecialchars($partner['contact_person'] ?? '') ?>" placeholder="Primary contact name">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_email">Contact Email</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                           value="<?= htmlspecialchars($partner['contact_email'] ?? '') ?>" placeholder="contact@partner.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_phone">Contact Phone</label>
                                    <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                                           value="<?= htmlspecialchars($partner['contact_phone'] ?? '') ?>" placeholder="+1 (555) 123-4567">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Logo Upload -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-image"></i> Logo Management
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($partner['logo_path']): ?>
                            <?php 
                                // Determine current logo URL
                                $current_logo_url = null;
                                if (filter_var($partner['logo_path'], FILTER_VALIDATE_URL)) {
                                    $current_logo_url = $partner['logo_path'];
                                } else {
                                    $current_logo_url = '../../client/assets/images/partners/' . $partner['logo_path'];
                                }
                            ?>
                            <div class="current-logo mb-3">
                                <label class="form-label">Current Logo:</label><br>
                                <img src="<?= htmlspecialchars($current_logo_url) ?>" 
                                     alt="Current Logo" 
                                     class="img-thumbnail" 
                                     style="max-width: 200px; max-height: 100px; object-fit: contain;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div style="display:none;" class="text-muted">Current logo could not be loaded</div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label class="form-label">Logo Options</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="logo_option" id="logo_keep" value="keep" checked>
                                <label class="form-check-label" for="logo_keep">
                                    <strong>Keep Current Logo</strong>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="logo_option" id="logo_upload" value="upload">
                                <label class="form-check-label" for="logo_upload">
                                    <strong>Upload New Logo File</strong>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="logo_option" id="logo_url_option" value="url">
                                <label class="form-check-label" for="logo_url_option">
                                    <strong>Use New Logo URL</strong>
                                </label>
                            </div>
                        </div>
                        
                        <div id="logo_upload_section" style="display: none;">
                            <div class="form-group">
                                <label for="logo">Upload Logo File</label>
                                <input type="file" class="form-control-file" id="logo" name="logo" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml,image/webp">
                                <small class="form-text text-muted">
                                    Supported formats: JPG, PNG, GIF, SVG, WEBP. Maximum size: 5MB.
                                </small>
                            </div>
                        </div>
                        
                        <div id="logo_url_section" style="display: none;">
                            <div class="form-group">
                                <label for="logo_url">Logo URL</label>
                                <input type="url" class="form-control" id="logo_url" name="logo_url" 
                                       placeholder="https://example.com/logo.png">
                                <small class="form-text text-muted">
                                    Direct link to the partner's logo image.
                                </small>
                            </div>
                        </div>
                        
                        <div id="logo_preview" class="mt-3" style="display: none;">
                            <label class="form-label">New Logo Preview:</label><br>
                            <img id="preview_image" src="" alt="Logo Preview" 
                                 style="max-width: 200px; max-height: 100px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Partnership Details -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cogs"></i> Partnership Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="partnership_type">Partnership Type</label>
                            <select class="form-control" id="partnership_type" name="partnership_type">
                                <option value="funding" <?= $partner['partnership_type'] == 'funding' ? 'selected' : '' ?>>Funding Partner</option>
                                <option value="training" <?= $partner['partnership_type'] == 'training' ? 'selected' : '' ?>>Training Partner</option>
                                <option value="employment" <?= $partner['partnership_type'] == 'employment' ? 'selected' : '' ?>>Employment Partner</option>
                                <option value="resource" <?= $partner['partnership_type'] == 'resource' ? 'selected' : '' ?>>Resource Partner</option>
                                <option value="technology" <?= $partner['partnership_type'] == 'technology' ? 'selected' : '' ?>>Technology Partner</option>
                                <option value="other" <?= $partner['partnership_type'] == 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="partnership_level">Partnership Level</label>
                            <select class="form-control" id="partnership_level" name="partnership_level">
                                <option value="strategic" <?= $partner['partnership_level'] == 'strategic' ? 'selected' : '' ?>>Strategic Partner</option>
                                <option value="standard" <?= $partner['partnership_level'] == 'standard' ? 'selected' : '' ?>>Standard Partner</option>
                                <option value="supporter" <?= $partner['partnership_level'] == 'supporter' ? 'selected' : '' ?>>Supporter</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" 
                                   min="0" max="1000" value="<?= htmlspecialchars($partner['display_order'] ?? 0) ?>" placeholder="0">
                            <small class="form-text text-muted">Lower numbers appear first</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="<?= htmlspecialchars($partner['start_date'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="<?= htmlspecialchars($partner['end_date'] ?? '') ?>">
                                    <small class="form-text text-muted">Optional</small>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" 
                                       <?= $partner['is_featured'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="is_featured">
                                    <strong>Featured Partner</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">Featured partners appear prominently on the website</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                       <?= $partner['is_active'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="is_active">
                                    <strong>Active Status</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">Only active partners are displayed on the website</small>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="card shadow mb-4">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Update Partner
                        </button>
                        <a href="partners.php" class="btn btn-secondary btn-lg ml-2">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Auto-generate slug from name (if slug is empty)
document.getElementById('name').addEventListener('input', function() {
    const slugField = document.getElementById('slug');
    if (!slugField.value) {
        const name = this.value;
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        slugField.value = slug;
    }
});

// Logo option handling
document.querySelectorAll('input[name="logo_option"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const uploadSection = document.getElementById('logo_upload_section');
        const urlSection = document.getElementById('logo_url_section');
        const previewSection = document.getElementById('logo_preview');
        
        // Hide all sections first
        uploadSection.style.display = 'none';
        urlSection.style.display = 'none';
        previewSection.style.display = 'none';
        
        if (this.value === 'upload') {
            uploadSection.style.display = 'block';
            document.getElementById('logo_url').value = '';
        } else if (this.value === 'url') {
            urlSection.style.display = 'block';
            document.getElementById('logo').value = '';
        }
        // 'keep' option shows nothing additional
    });
});

// Logo preview for file upload
document.getElementById('logo').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview_image').src = e.target.result;
            document.getElementById('logo_preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('logo_preview').style.display = 'none';
    }
});

// Logo preview for URL
document.getElementById('logo_url').addEventListener('input', function() {
    const url = this.value;
    if (url) {
        document.getElementById('preview_image').src = url;
        document.getElementById('logo_preview').style.display = 'block';
    } else {
        document.getElementById('logo_preview').style.display = 'none';
    }
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
