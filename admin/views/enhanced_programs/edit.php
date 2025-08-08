<?php
/**
 * Enhanced Programs Edit View - Clean Implementation
 * Safely handles all form data and displays with proper error handling
 */

// Safe value retrieval function
function getSafeValue($key, $program = [], $default = '') {
    // Check POST data first (for form resubmissions after errors)
    if (isset($_POST[$key])) {
        $value = $_POST[$key];
        return is_string($value) ? trim($value) : $value;
    }
    
    // Check program data
    if (is_array($program) && isset($program[$key])) {
        $value = $program[$key];
        return is_string($value) ? trim($value) : $value;
    }
    
    return $default;
}

// Ensure program array is properly set
$program = $program ?? [];

// Include header
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Enhanced Program</h1>
    <div class="d-none d-sm-inline-block">
        <a href="<?= BASE_URL ?>/admin/public/programs.php" class="btn btn-sm btn-secondary shadow-sm">
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
        <div class="card shadow">
            <div class="card-header py-3">
                <h5 class="m-0 font-weight-bold text-primary">
                    Edit Program: <?= htmlspecialchars($program['title'] ?? 'New Program') ?>
                </h5>
            </div>
            <div class="card-body">

                <form method="POST" enctype="multipart/form-data" id="programForm">
                    <!-- Basic Program Information -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Program Title *</label>
                                <input type="text" class="form-control" id="title" name="title"
                                       value="<?= htmlspecialchars(getSafeValue('title', $program)) ?>" 
                                       required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration *</label>
                                <input type="text" class="form-control" id="duration" name="duration"
                                       value="<?= htmlspecialchars(getSafeValue('duration', $program)) ?>"
                                       placeholder="e.g., 3 days, 2 weeks" required maxlength="100">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Short Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required maxlength="500"><?= htmlspecialchars(getSafeValue('description', $program)) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-control" id="category" name="category">
                                    <?php 
                                    $categories = ['General', 'Technology', 'Business', 'Leadership', 'Skills'];
                                    $selected_category = getSafeValue('category', $program, 'General');
                                    foreach ($categories as $category): ?>
                                        <option value="<?= $category ?>" <?= $selected_category === $category ? 'selected' : '' ?>><?= $category ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="difficulty_level" class="form-label">Difficulty Level</label>
                                <select class="form-control" id="difficulty_level" name="difficulty_level">
                                    <?php 
                                    $levels = ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];
                                    $selected_level = getSafeValue('difficulty_level', $program, 'beginner');
                                    foreach ($levels as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $selected_level === $value ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="fee" class="form-label">Base Fee ($)</label>
                                <input type="number" class="form-control" id="fee" name="fee"
                                       value="<?= htmlspecialchars(getSafeValue('fee', $program, '0')) ?>" min="0" step="0.01">
                                <div class="form-text">This is the base fee. Individual schedules can have different fees.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="max_participants" class="form-label">Max Participants</label>
                                <input type="number" class="form-control" id="max_participants" name="max_participants"
                                       value="<?= htmlspecialchars(getSafeValue('max_participants', $program)) ?>" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                       value="<?= htmlspecialchars(getSafeValue('start_date', $program)) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="<?= htmlspecialchars(getSafeValue('end_date', $program)) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Program Information -->
                    <hr>
                    <h6>Detailed Program Information</h6>
                    
                    <div class="mb-3">
                        <label for="introduction" class="form-label">Program Introduction</label>
                        <textarea class="form-control" id="introduction" name="introduction" rows="4"
                                  placeholder="Detailed introduction to the program, what it covers, and its benefits"><?= htmlspecialchars(getSafeValue('introduction', $program)) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="objectives" class="form-label">Learning Objectives</label>
                        <textarea class="form-control" id="objectives" name="objectives" rows="4"
                                  placeholder="List the key learning objectives participants will achieve"><?= htmlspecialchars(getSafeValue('objectives', $program)) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="target_audience" class="form-label">Target Audience</label>
                                <textarea class="form-control" id="target_audience" name="target_audience" rows="3"
                                          placeholder="Who is this program designed for?"><?= htmlspecialchars(getSafeValue('target_audience', $program)) ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prerequisites" class="form-label">Prerequisites</label>
                                <textarea class="form-control" id="prerequisites" name="prerequisites" rows="3"
                                          placeholder="Any prerequisites or requirements for participants"><?= htmlspecialchars(getSafeValue('prerequisites', $program)) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="course_content" class="form-label">Course Content (Modules)</label>
                        <textarea class="form-control" id="course_content" name="course_content" rows="5"
                                  placeholder="Outline the course modules and content structure"><?= htmlspecialchars(getSafeValue('course_content', $program)) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="general_notes" class="form-label">General Notes</label>
                        <textarea class="form-control" id="general_notes" name="general_notes" rows="3"
                                  placeholder="Additional notes, requirements, or important information"><?= htmlspecialchars(getSafeValue('general_notes', $program)) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="certification_details" class="form-label">Certification Details</label>
                        <textarea class="form-control" id="certification_details" name="certification_details" rows="3"
                                  placeholder="Information about certificates, credentials, or completion recognition"><?= htmlspecialchars(getSafeValue('certification_details', $program)) ?></textarea>
                    </div>

                    <!-- Media and Additional Information -->
                    <hr>
                    <h6>Media & Additional Information</h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image" class="form-label">Main Program Image</label>
                                <?php if (!empty($program['image_path'])): ?>
                                    <div class="current-image mb-2">
                                        <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($program['image_path']) ?>" 
                                             class="img-thumbnail" style="max-width: 200px; max-height: 150px;" 
                                             alt="Current program image">
                                        <div class="form-check mt-1">
                                            <input type="checkbox" class="form-check-input" id="remove_image" name="remove_image" value="1">
                                            <label class="form-check-label text-danger" for="remove_image">
                                                Remove current image
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="image" name="image" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <div class="form-text">Upload new main program image (JPEG, PNG, GIF, WebP. Max: 10MB)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="video_url" class="form-label">Video URL</label>
                                <input type="url" class="form-control" id="video_url" name="video_url"
                                       value="<?= htmlspecialchars(getSafeValue('video_url', $program)) ?>"
                                       placeholder="https://youtube.com/watch?v=... or https://vimeo.com/...">
                                <div class="form-text">Optional. Link to program intro/overview video</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gallery Images Management -->
                    <div class="mb-3">
                        <label class="form-label">Gallery Images</label>
                        
                        <!-- Current Gallery Images -->
                        <?php 
                        $gallery_images = !empty($program['gallery_images']) ? json_decode($program['gallery_images'], true) : [];
                        if (!empty($gallery_images) && is_array($gallery_images)): 
                        ?>
                            <div class="current-gallery mb-3">
                                <h6>Current Gallery Images</h6>
                                <div class="row" id="current-gallery">
                                    <?php foreach ($gallery_images as $index => $image): ?>
                                        <div class="col-md-3 mb-3" data-image="<?= htmlspecialchars($image) ?>">
                                            <div class="card">
                                                <img src="<?= BASE_URL ?>/uploads/programs/gallery/<?= htmlspecialchars($image) ?>" 
                                                     class="card-img-top" style="height: 150px; object-fit: cover;" 
                                                     alt="Gallery image">
                                                <div class="card-body p-2">
                                                    <button type="button" class="btn btn-danger btn-sm w-100" 
                                                            onclick="removeGalleryImage('<?= htmlspecialchars($image) ?>', this)">
                                                        <i class="fas fa-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Add New Gallery Images -->
                        <div class="mb-3">
                            <label for="gallery_images" class="form-label">Add New Gallery Images</label>
                            <input type="file" class="form-control" id="gallery_images" name="gallery_images[]" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" multiple>
                            <div class="form-text">Upload additional images for the program gallery (JPEG, PNG, GIF, WebP. Max: 10MB each)</div>
                            <div id="gallery-preview" class="mt-3"></div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="instructor_name" class="form-label">Instructor Name</label>
                                <input type="text" class="form-control" id="instructor_name" name="instructor_name"
                                       value="<?= htmlspecialchars(getSafeValue('instructor_name', $program)) ?>"
                                       placeholder="Name of the primary instructor">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Default Location</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       value="<?= htmlspecialchars(getSafeValue('location', $program)) ?>"
                                       placeholder="Default location for in-person sessions">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control" id="tags" name="tags"
                                       value="<?= htmlspecialchars(getSafeValue('tags', $program)) ?>"
                                       placeholder="programming, web development, php, mysql">
                                <div class="form-text">Comma-separated tags for better searchability</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <?php $isFeatured = getSafeValue('is_featured', $program, 0); ?>
                                    <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1"
                                           <?= $isFeatured ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_featured">
                                        <strong>Featured Program</strong>
                                    </label>
                                    <div class="form-text">Featured programs appear prominently on the website</div>
                                </div>
                                <div class="form-check">
                                    <?php $isOnline = getSafeValue('is_online', $program, 0); ?>
                                    <input type="checkbox" class="form-check-input" id="is_online" name="is_online" value="1"
                                           <?= $isOnline ? 'checked' : '' ?>>
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
                               value="<?= htmlspecialchars(getSafeValue('meta_title', $program)) ?>"
                               placeholder="SEO title for search engines (optional)">
                        <div class="form-text">If left blank, program title will be used</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" rows="2"
                                  placeholder="Brief description for search engines (optional)"><?= htmlspecialchars(getSafeValue('meta_description', $program)) ?></textarea>
                        <div class="form-text">If left blank, short description will be used</div>
                    </div>

                    <!-- Hidden input to track removed gallery images -->
                    <input type="hidden" id="removed_gallery_images" name="removed_gallery_images" value="">

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/admin/public/programs.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Programs
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Program
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

// Track removed gallery images
let removedImages = [];

function removeGalleryImage(imageName, button) {
    if (confirm('Are you sure you want to remove this image?')) {
        // Add to removed images list
        removedImages.push(imageName);
        document.getElementById('removed_gallery_images').value = JSON.stringify(removedImages);
        
        // Remove the image card from DOM
        button.closest('.col-md-3').remove();
        
        // Send AJAX request to delete the image immediately
        fetch('<?= BASE_URL ?>/admin/public/programs.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_gallery_image&id=<?= $program['id'] ?>&image=${encodeURIComponent(imageName)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Image deleted successfully');
            } else {
                console.error('Error deleting image:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}
</script>

<?php
// Include footer
require_once __DIR__ . '/../../includes/footer.php';
?>
