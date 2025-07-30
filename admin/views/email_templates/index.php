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

    <?php if (isset($_SESSION['success'])): ?>
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

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-envelope me-1"></i>Email Templates</span>
            <div>
                <a href="email_template_edit.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Template
                </a>
                <form method="post" class="d-inline">
                    <button type="submit" name="create_defaults" class="btn btn-secondary btn-sm" 
                            onclick="return confirm('This will create default email templates. Continue?')">
                        <i class="fas fa-magic"></i> Create Defaults
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($templates)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Email Templates Found</h5>
                    <p class="text-muted">Get started by creating your first email template or adding default templates.</p>
                    <a href="email_template_edit.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Template
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped" id="templatesTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>Variables</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($templates as $template): ?>
                                <?php
                                $variables = !empty($template['variables']) ? json_decode($template['variables'], true) : [];
                                $variables = $variables ?: [];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($template['name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($template['subject']) ?></td>
                                    <td>
                                        <?php if (!empty($variables)): ?>
                                            <div class="small">
                                                <?php foreach ($variables as $var): ?>
                                                    <span class="badge bg-secondary me-1">{{<?= htmlspecialchars($var) ?>}}</span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($template['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($template['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="email_template_edit.php?id=<?= $template['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="previewTemplate(<?= $template['id'] ?>)" title="Preview">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($template['is_active']): ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $template['id'] ?>">
                                                    <button type="submit" name="deactivate" class="btn btn-sm btn-outline-warning" 
                                                            title="Deactivate">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $template['id'] ?>">
                                                    <button type="submit" name="activate" class="btn btn-sm btn-outline-success" 
                                                            title="Activate">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <a href="../actions/delete_email_template.php?id=<?= $template['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this template?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function previewTemplate(templateId) {
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    const previewContent = document.getElementById('previewContent');
    
    // Show loading spinner
    previewContent.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Load preview content
    fetch(`email_template_preview.php?id=${templateId}`)
        .then(response => response.text())
        .then(html => {
            previewContent.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading preview:', error);
            previewContent.innerHTML = '<div class="alert alert-danger">Error loading template preview.</div>';
        });
}

$(document).ready(function() {
    $('#templatesTable').DataTable({
        "pageLength": 25,
        "order": [[ 4, "desc" ]], // Sort by created date descending
        "columnDefs": [
            { "orderable": false, "targets": [5] } // Actions column
        ]
    });
});
</script>
