<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-plus"></i> Add New Team Member</h5>
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

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="position">Position/Title *</label>
                                <input type="text" class="form-control" id="position" name="position"
                                       value="<?= htmlspecialchars($_POST['position'] ?? '') ?>" required
                                       placeholder="e.g., Executive Director, Program Manager">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       placeholder="member@organization.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                       placeholder="+254 700 123 456">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bio">Biography</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4"
                                  placeholder="Enter a brief biography about this team member..."><?= htmlspecialchars($_POST['bio'] ?? '') ?></textarea>
                        <small class="form-text text-muted">Brief description of the team member's background and expertise.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="image">Profile Photo</label>
                                <input type="file" class="form-control-file" id="image" name="image" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <small class="form-text text-muted">Upload profile photo (JPEG, PNG, GIF, WebP. Max: 5MB)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="isActive">Status</label>
                                <div class="mt-2">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1"
                                               <?= !isset($_POST['isActive']) || isset($_POST['isActive']) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="isActive">
                                            <strong>Active Member</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Only active members are displayed on the website.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="socialLinks">Social Media Links</label>
                        <textarea class="form-control" id="socialLinks" name="socialLinks" rows="3"
                                  placeholder='{"linkedin": "https://linkedin.com/in/member", "twitter": "https://twitter.com/member"}'><?= htmlspecialchars($_POST['socialLinks'] ?? '') ?></textarea>
                        <small class="form-text text-muted">
                            Enter social media links in JSON format. Supported platforms: linkedin, twitter, facebook, instagram, github.
                            <br><strong>Example:</strong> {"linkedin": "https://linkedin.com/in/john", "twitter": "https://twitter.com/john"}
                        </small>
                    </div>

                    <div class="form-group">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Social Links Preview</h6>
                                <div id="socialLinksPreview">
                                    <small class="text-muted">Enter social links above to see preview</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="form-group d-flex justify-content-between">
                        <a href="team_members.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Team Members
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Team Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea
    const bioTextarea = document.getElementById('bio');
    bioTextarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
    
    // Preview image upload
    const imageInput = document.getElementById('image');
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File is too large. Maximum size is 5MB.');
                this.value = '';
                return;
            }
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Invalid file type. Please select a JPEG, PNG, GIF, or WebP image.');
                this.value = '';
                return;
            }
        }
    });
    
    // Social links validation and preview
    const socialLinksTextarea = document.getElementById('socialLinks');
    const socialLinksPreview = document.getElementById('socialLinksPreview');
    
    socialLinksTextarea.addEventListener('input', function() {
        updateSocialLinksPreview();
    });
    
    function updateSocialLinksPreview() {
        const value = socialLinksTextarea.value.trim();
        if (!value) {
            socialLinksPreview.innerHTML = '<small class="text-muted">Enter social links above to see preview</small>';
            return;
        }
        
        try {
            const links = JSON.parse(value);
            let previewHtml = '';
            
            for (const [platform, url] of Object.entries(links)) {
                if (isValidUrl(url)) {
                    previewHtml += `<a href="${url}" target="_blank" class="btn btn-outline-primary btn-sm mr-2 mb-2">
                        <i class="fab fa-${platform}"></i> ${platform}
                    </a>`;
                } else {
                    previewHtml += `<span class="btn btn-outline-danger btn-sm mr-2 mb-2">
                        <i class="fas fa-exclamation-triangle"></i> ${platform} (Invalid URL)
                    </span>`;
                }
            }
            
            socialLinksPreview.innerHTML = previewHtml || '<small class="text-muted">No valid links found</small>';
            
        } catch (e) {
            socialLinksPreview.innerHTML = '<small class="text-danger">Invalid JSON format</small>';
        }
    }
    
    function isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }
    
    // Validate form on submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const socialLinksValue = socialLinksTextarea.value.trim();
        
        if (socialLinksValue) {
            try {
                const links = JSON.parse(socialLinksValue);
                for (const [platform, url] of Object.entries(links)) {
                    if (!isValidUrl(url)) {
                        e.preventDefault();
                        alert(`Invalid URL for ${platform}: ${url}`);
                        return false;
                    }
                }
            } catch (error) {
                e.preventDefault();
                alert('Social links must be in valid JSON format.');
                return false;
            }
        }
    });
    
    // Initialize preview
    updateSocialLinksPreview();
});
</script>
