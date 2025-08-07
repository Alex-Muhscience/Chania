<?php
// Extract current values for form fields
$title = $blog['title'] ?? '';
$category = $blog['category'] ?? '';
$excerpt = $blog['excerpt'] ?? '';
$content = $blog['content'] ?? '';
$featured_image = $blog['featured_image'] ?? '';
$video_url = $blog['video_url'] ?? '';
$video_embed_code = $blog['video_embed_code'] ?? '';
$author_name = $blog['author_name'] ?? $currentUser;
$is_active = isset($blog) ? (bool)$blog['is_active'] : true;
$sort_order = $blog['sort_order'] ?? 0;

// Handle stats data
$statsData = [];
if (isset($blog['stats_data']) && $blog['stats_data']) {
    $statsData = json_decode($blog['stats_data'], true) ?? [];
}

// Handle tags
$tags = [];
if (isset($blog['tags']) && $blog['tags']) {
    $tags = json_decode($blog['tags'], true) ?? [];
}
$tagsString = implode(', ', $tags);
?>

<form method="POST" action="<?= htmlspecialchars($formAction) ?>" novalidate>
    <div class="row">
        <div class="col-lg-8">
            <!-- Main Content Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>Story Content
                </div>
                <div class="card-body">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($title) ?>" required maxlength="255">
                        <div class="form-text">A compelling title for your impact story</div>
                    </div>

                    <!-- Excerpt -->
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Excerpt <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" 
                                  required maxlength="500"><?= htmlspecialchars($excerpt) ?></textarea>
                        <div class="form-text">Brief summary of the impact story (max 500 characters)</div>
                    </div>

                    <!-- Content -->
                    <div class="mb-3">
                        <label for="content" class="form-label">Story Content <span class="text-danger">*</span></label>
                        <div class="editor-toolbar mb-2">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('bold')" title="Bold">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('italic')" title="Italic">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('underline')" title="Underline">
                                    <i class="fas fa-underline"></i>
                                </button>
                            </div>
                            <div class="btn-group ms-2" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('insertUnorderedList')" title="Bullet List">
                                    <i class="fas fa-list-ul"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('insertOrderedList')" title="Numbered List">
                                    <i class="fas fa-list-ol"></i>
                                </button>
                            </div>
                            <div class="btn-group ms-2" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertLink()" title="Insert Link">
                                    <i class="fas fa-link"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertHeading()" title="Insert Heading">
                                    <i class="fas fa-heading"></i>
                                </button>
                            </div>
                        </div>
                        <div id="content-editor" contenteditable="true" class="form-control" style="min-height: 400px; max-height: 600px; overflow-y: auto;"><?= $content ?></div>
                        <textarea id="content" name="content" style="display: none;" required><?= htmlspecialchars($content) ?></textarea>
                        <div class="form-text">Full story content with detailed impact information. Use the toolbar above for basic formatting.</div>
                    </div>
                </div>
            </div>

            <!-- Media Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-image me-1"></i>Media Content
                </div>
                <div class="card-body">
                    <!-- Featured Image -->
                    <div class="mb-3">
                        <label for="featured_image" class="form-label">Featured Image URL</label>
                        <input type="url" class="form-control" id="featured_image" name="featured_image" 
                               value="<?= htmlspecialchars($featured_image) ?>" maxlength="500">
                        <div class="form-text">URL to the main image for this story</div>
                        <?php if ($featured_image): ?>
                            <div class="mt-2">
                                <img src="<?= htmlspecialchars($featured_image) ?>" alt="Preview" 
                                     class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Video URL -->
                    <div class="mb-3">
                        <label for="video_url" class="form-label">Video URL</label>
                        <input type="url" class="form-control" id="video_url" name="video_url" 
                               value="<?= htmlspecialchars($video_url) ?>" maxlength="500">
                        <div class="form-text">YouTube, Vimeo, or direct video URL</div>
                    </div>

                    <!-- Video Embed Code -->
                    <div class="mb-3">
                        <label for="video_embed_code" class="form-label">Video Embed Code</label>
                        <textarea class="form-control" id="video_embed_code" name="video_embed_code" rows="3" 
                                  maxlength="1000"><?= htmlspecialchars($video_embed_code) ?></textarea>
                        <div class="form-text">Custom embed code for videos (optional)</div>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>Impact Statistics
                </div>
                <div class="card-body">
                    <div class="form-text mb-3">Add key statistics to highlight the impact of this story</div>
                    
                    <div id="stats-container">
                        <?php if (empty($statsData)): ?>
                            <div class="row stats-row mb-3">
                                <div class="col-md-5">
                                    <input type="text" name="stats_labels[]" class="form-control" 
                                           placeholder="Label (e.g., People Trained)" maxlength="100">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" name="stats_values[]" class="form-control" 
                                           placeholder="Value (e.g., 250)" maxlength="50">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger remove-stat">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($statsData as $label => $value): ?>
                                <div class="row stats-row mb-3">
                                    <div class="col-md-5">
                                        <input type="text" name="stats_labels[]" class="form-control" 
                                               value="<?= htmlspecialchars($label) ?>" 
                                               placeholder="Label (e.g., People Trained)" maxlength="100">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" name="stats_values[]" class="form-control" 
                                               value="<?= htmlspecialchars($value) ?>" 
                                               placeholder="Value (e.g., 250)" maxlength="50">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger remove-stat">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" id="add-stat" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-1"></i>Add Statistic
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Publishing Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-cog me-1"></i>Publishing Options
                </div>
                <div class="card-body">
                    <!-- Status -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   value="1" <?= $is_active ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">
                                Publish Story
                            </label>
                        </div>
                        <div class="form-text">Check to make this story visible on the website</div>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key) ?>" 
                                        <?= $category === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sort Order -->
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="sort_order" name="sort_order" 
                               value="<?= htmlspecialchars($sort_order) ?>" min="0" max="9999">
                        <div class="form-text">Lower numbers appear first</div>
                    </div>

                    <!-- Author Name -->
                    <div class="mb-3">
                        <label for="author_name" class="form-label">Author Name</label>
                        <input type="text" class="form-control" id="author_name" name="author_name" 
                               value="<?= htmlspecialchars($author_name) ?>" maxlength="100">
                        <div class="form-text">Display name for the story author</div>
                    </div>
                </div>
            </div>

            <!-- Tags Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tags me-1"></i>Tags
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" class="form-control" id="tags" name="tags" 
                               value="<?= htmlspecialchars($tagsString) ?>" 
                               placeholder="success, agriculture, kenya">
                        <div class="form-text">Comma-separated tags for this story</div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            <?= $blog ? 'Update Story' : 'Create Story' ?>
                        </button>
                        <a href="impact_blogs.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- JavaScript for dynamic stats -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statsContainer = document.getElementById('stats-container');
    const addStatButton = document.getElementById('add-stat');

    // Add new stat row
    addStatButton.addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.className = 'row stats-row mb-3';
        newRow.innerHTML = `
            <div class="col-md-5">
                <input type="text" name="stats_labels[]" class="form-control" 
                       placeholder="Label (e.g., People Trained)" maxlength="100">
            </div>
            <div class="col-md-5">
                <input type="text" name="stats_values[]" class="form-control" 
                       placeholder="Value (e.g., 250)" maxlength="50">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger remove-stat">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        `;
        statsContainer.appendChild(newRow);
    });

    // Remove stat row
    statsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-stat') || e.target.closest('.remove-stat')) {
            const row = e.target.closest('.stats-row');
            if (statsContainer.children.length > 1) {
                row.remove();
            } else {
                // Clear inputs instead of removing the last row
                row.querySelectorAll('input').forEach(input => input.value = '');
            }
        }
    });

    // Initialize simple content editor
    const contentEditor = document.getElementById('content-editor');
    const contentTextarea = document.getElementById('content');
    
    // Sync content editor with hidden textarea
    contentEditor.addEventListener('input', function() {
        contentTextarea.value = contentEditor.innerHTML;
    });
    
    // Sync on paste
    contentEditor.addEventListener('paste', function(e) {
        setTimeout(() => {
            contentTextarea.value = contentEditor.innerHTML;
        }, 10);
    });
    
    // Initial sync
    if (contentEditor.innerHTML.trim()) {
        contentTextarea.value = contentEditor.innerHTML;
    }

    // Image preview
    const featuredImageInput = document.getElementById('featured_image');
    featuredImageInput.addEventListener('change', function() {
        const url = this.value;
        const existingPreview = this.parentNode.querySelector('.img-thumbnail');
        
        if (existingPreview) {
            existingPreview.remove();
        }
        
        if (url) {
            const preview = document.createElement('div');
            preview.className = 'mt-2';
            preview.innerHTML = `<img src="${url}" alt="Preview" class="img-thumbnail" style="max-width: 300px; max-height: 200px;" onerror="this.style.display='none'">`;
            this.parentNode.appendChild(preview);
        }
    });
});

// Simple editor formatting functions
function formatText(command) {
    document.execCommand(command, false, null);
    // Focus back to editor
    document.getElementById('content-editor').focus();
    // Update hidden textarea
    setTimeout(() => {
        const contentEditor = document.getElementById('content-editor');
        const contentTextarea = document.getElementById('content');
        contentTextarea.value = contentEditor.innerHTML;
    }, 10);
}

function insertLink() {
    const url = prompt('Enter the URL:');
    if (url) {
        const text = prompt('Enter the link text:') || url;
        document.execCommand('insertHTML', false, `<a href="${url}" target="_blank">${text}</a>`);
        // Focus back to editor
        document.getElementById('content-editor').focus();
        // Update hidden textarea
        setTimeout(() => {
            const contentEditor = document.getElementById('content-editor');
            const contentTextarea = document.getElementById('content');
            contentTextarea.value = contentEditor.innerHTML;
        }, 10);
    }
}

function insertHeading() {
    const text = prompt('Enter heading text:');
    if (text) {
        document.execCommand('insertHTML', false, `<h3>${text}</h3>`);
        // Focus back to editor
        document.getElementById('content-editor').focus();
        // Update hidden textarea
        setTimeout(() => {
            const contentEditor = document.getElementById('content-editor');
            const contentTextarea = document.getElementById('content');
            contentTextarea.value = contentEditor.innerHTML;
        }, 10);
    }
}
</script>

<style>
.editor-toolbar {
    border: 1px solid #dee2e6;
    border-bottom: none;
    padding: 0.5rem;
    background-color: #f8f9fa;
    border-radius: 0.375rem 0.375rem 0 0;
}

#content-editor {
    border-radius: 0 0 0.375rem 0.375rem;
    border-top: none;
}

#content-editor:focus {
    outline: none;
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

#content-editor p {
    margin-bottom: 1rem;
}

#content-editor h1, #content-editor h2, #content-editor h3 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

#content-editor ul, #content-editor ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

#content-editor a {
    color: #0d6efd;
    text-decoration: underline;
}
</style>
