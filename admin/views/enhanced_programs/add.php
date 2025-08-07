<?php
// Include header
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add New Enhanced Program</h1>
    <div class="d-none d-sm-inline-block">
        <a href="<?= BASE_URL ?>../../public/programs.php" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Programs
        </a>
    </div>
</div>

<!-- Flash Messages -->
<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add New Enhanced Program</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($this->errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($this->errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <!-- Basic Program Information -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Program Title *</label>
                                <input type="text" class="form-control" id="title" name="title"
                                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration *</label>
                                <input type="text" class="form-control" id="duration" name="duration"
                                       value="<?= htmlspecialchars($_POST['duration'] ?? '') ?>"
                                       placeholder="e.g., 3 days, 2 weeks" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Short Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-control" id="category" name="category">
                                    <option value="General" <?= ($_POST['category'] ?? '') === 'General' ? 'selected' : '' ?>>General</option>
                                    <option value="Technology" <?= ($_POST['category'] ?? '') === 'Technology' ? 'selected' : '' ?>>Technology</option>
                                    <option value="Business" <?= ($_POST['category'] ?? '') === 'Business' ? 'selected' : '' ?>>Business</option>
                                    <option value="Leadership" <?= ($_POST['category'] ?? '') === 'Leadership' ? 'selected' : '' ?>>Leadership</option>
                                    <option value="Skills" <?= ($_POST['category'] ?? '') === 'Skills' ? 'selected' : '' ?>>Skills</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="difficulty_level" class="form-label">Difficulty Level</label>
                                <select class="form-control" id="difficulty_level" name="difficulty_level">
                                    <option value="beginner" <?= ($_POST['difficulty_level'] ?? '') === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                                    <option value="intermediate" <?= ($_POST['difficulty_level'] ?? '') === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                                    <option value="advanced" <?= ($_POST['difficulty_level'] ?? '') === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="fee" class="form-label">Base Fee ($)</label>
                                <input type="number" class="form-control" id="fee" name="fee"
                                       value="<?= $_POST['fee'] ?? 0 ?>" min="0" step="0.01">
                                <div class="form-text">This is the base fee. Individual schedules can have different fees.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="max_participants" class="form-label">Max Participants</label>
                                <input type="number" class="form-control" id="max_participants" name="max_participants"
                                       value="<?= $_POST['max_participants'] ?? '' ?>" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                       value="<?= $_POST['start_date'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="<?= $_POST['end_date'] ?? '' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Program Information -->
                    <hr>
                    <h6>Detailed Program Information</h6>
                    
                    <div class="mb-3">
                        <label for="introduction" class="form-label">Program Introduction</label>
                        <textarea class="form-control" id="introduction" name="introduction" rows="4"
                                  placeholder="Detailed introduction to the program, what it covers, and its benefits"><?= htmlspecialchars($_POST['introduction'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="objectives" class="form-label">Learning Objectives</label>
                        <textarea class="form-control" id="objectives" name="objectives" rows="4"
                                  placeholder="List the key learning objectives participants will achieve"><?= htmlspecialchars($_POST['objectives'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="target_audience" class="form-label">Target Audience</label>
                                <textarea class="form-control" id="target_audience" name="target_audience" rows="3"
                                          placeholder="Who is this program designed for?"><?= htmlspecialchars($_POST['target_audience'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prerequisites" class="form-label">Prerequisites</label>
                                <textarea class="form-control" id="prerequisites" name="prerequisites" rows="3"
                                          placeholder="Any prerequisites or requirements for participants"><?= htmlspecialchars($_POST['prerequisites'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="course_content" class="form-label">Course Content (Modules)</label>
                        <textarea class="form-control" id="course_content" name="course_content" rows="5"
                                  placeholder="Outline the course modules and content structure"><?= htmlspecialchars($_POST['course_content'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="general_notes" class="form-label">General Notes</label>
                        <textarea class="form-control" id="general_notes" name="general_notes" rows="3"
                                  placeholder="Additional notes, requirements, or important information"><?= htmlspecialchars($_POST['general_notes'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="certification_details" class="form-label">Certification Details</label>
                        <textarea class="form-control" id="certification_details" name="certification_details" rows="3"
                                  placeholder="Information about certificates, credentials, or completion recognition"><?= htmlspecialchars($_POST['certification_details'] ?? '') ?></textarea>
                    </div>

                    <!-- Media and Additional Information -->
                    <hr>
                    <h6>Media & Additional Information</h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image" class="form-label">Main Program Image</label>
                                <input type="file" class="form-control" id="image" name="image" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <div class="form-text">Upload main program image (JPEG, PNG, GIF, WebP. Max: 10MB)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="video_url" class="form-label">Video URL</label>
                                <input type="url" class="form-control" id="video_url" name="video_url"
                                       value="<?= htmlspecialchars($_POST['video_url'] ?? '') ?>"
                                       placeholder="https://youtube.com/watch?v=... or https://vimeo.com/...">
                                <div class="form-text">Optional. Link to program intro/overview video</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="gallery_images" class="form-label">Gallery Images</label>
                        <input type="file" class="form-control" id="gallery_images" name="gallery_images[]" 
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" multiple>
                        <div class="form-text">Upload multiple images for the program gallery (JPEG, PNG, GIF, WebP. Max: 10MB each)</div>
                        <div id="gallery-preview" class="mt-3"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="instructor_name" class="form-label">Instructor Name</label>
                                <input type="text" class="form-control" id="instructor_name" name="instructor_name"
                                       value="<?= htmlspecialchars($_POST['instructor_name'] ?? '') ?>"
                                       placeholder="Name of the primary instructor">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Default Location</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
                                       placeholder="Default location for in-person sessions">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control" id="tags" name="tags"
                                       value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>"
                                       placeholder="programming, web development, php, mysql">
                                <div class="form-text">Comma-separated tags for better searchability</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1"
                                           <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_featured">
                                        <strong>Featured Program</strong>
                                    </label>
                                    <div class="form-text">Featured programs appear prominently on the website</div>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_online" name="is_online" value="1"
                                           <?= isset($_POST['is_online']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_online">
                                        <strong>Online Program</strong>
                                    </label>
                                    <div class="form-text">Check if this is primarily an online program</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- SEO Section -->
                    <hr>
                    <h6>SEO Information</h6>
                    
                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" class="form-control" id="meta_title" name="meta_title"
                               value="<?= htmlspecialchars($_POST['meta_title'] ?? '') ?>"
                               placeholder="SEO title for search engines (optional)">
                        <div class="form-text">If left blank, program title will be used</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" rows="2"
                                  placeholder="Brief description for search engines (optional)"><?= htmlspecialchars($_POST['meta_description'] ?? '') ?></textarea>
                        <div class="form-text">If left blank, short description will be used</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/admin/enhanced_programs.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Programs
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Program
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
    
    // Gallery images preview
    const galleryInput = document.getElementById('gallery_images');
    const galleryPreview = document.getElementById('gallery-preview');
    
    galleryInput.addEventListener('change', function() {
        galleryPreview.innerHTML = '';
        
        if (this.files.length === 0) return;
        
        const previewContainer = document.createElement('div');
        previewContainer.className = 'row';
        
        Array.from(this.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const col = document.createElement('div');
                col.className = 'col-md-3 mb-3';
                
                const card = document.createElement('div');
                card.className = 'card';
                
                const img = document.createElement('img');
                img.className = 'card-img-top';
                img.style.height = '150px';
                img.style.objectFit = 'cover';
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
                
                const cardBody = document.createElement('div');
                cardBody.className = 'card-body p-2';
                cardBody.innerHTML = `<small class="text-muted">${file.name}<br>Size: ${(file.size / 1024 / 1024).toFixed(2)}MB</small>`;
                
                card.appendChild(img);
                card.appendChild(cardBody);
                col.appendChild(card);
                previewContainer.appendChild(col);
            }
        });
        
        galleryPreview.appendChild(previewContainer);
    });
    
    // Main image preview
    const mainImageInput = document.getElementById('image');
    if (mainImageInput) {
        mainImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                let preview = document.getElementById('main-image-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.id = 'main-image-preview';
                    preview.className = 'mt-2';
                    this.parentNode.appendChild(preview);
                }
                
                const img = document.createElement('img');
                img.className = 'img-thumbnail';
                img.style.maxWidth = '200px';
                img.style.maxHeight = '150px';
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
                
                preview.innerHTML = '';
                preview.appendChild(img);
            }
        });
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../../includes/footer.php';
?>
