<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/EmailTemplate.php';
require_once __DIR__ . '/../../shared/Core/User.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

session_start();

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    Utilities::redirect('/admin/public/login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

// Check for permission
if (!$userModel->hasPermission($_SESSION['user_id'], 'templates') && !$userModel->hasPermission($_SESSION['user_id'], '*')) {
     $_SESSION['error'] = "You don't have permission to access this resource.";
     Utilities::redirect('/admin/public/index.php');
     exit();
}

require_once __DIR__ . '/../includes/header.php';

$emailTemplate = new EmailTemplate($db);

$message = '';
$error = '';
$template = null;
$isEdit = false;

// Check if editing existing template
if (isset($_GET['id'])) {
    $isEdit = true;
    $template = $emailTemplate->getById($_GET['id']);
    if (!$template) {
        $error = "Template not found.";
    }
}

// Handle form submission
if ($_POST) {
    $data = [
        'name' => trim($_POST['name']),
        'subject' => trim($_POST['subject']),
        'body' => $_POST['body'],
        'variables' => array_filter(array_map('trim', explode(',', $_POST['variables']))),
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    if (empty($data['name']) || empty($data['subject']) || empty($data['body'])) {
        $error = "Please fill in all required fields.";
    } else {
        if ($isEdit) {
            if ($emailTemplate->update($_GET['id'], $data)) {
                $message = "Template updated successfully.";
                $template = $emailTemplate->getById($_GET['id']); // Refresh data
            } else {
                $error = "Failed to update template.";
            }
        } else {
            if ($emailTemplate->create($data)) {
                $message = "Template created successfully.";
                // Get the newly created template
                $newTemplate = $emailTemplate->getByName($data['name']);
                if ($newTemplate) {
                    Utilities::redirect('/admin/public/email_template_edit.php?id=' . $newTemplate['id']);
                    exit();
                }
            } else {
                $error = "Failed to create template.";
            }
        }
    }
}

// Initialize form data
$formData = [
    'name' => $template['name'] ?? '',
    'subject' => $template['subject'] ?? '',
    'body' => $template['body'] ?? '',
    'variables' => $template ? implode(', ', json_decode($template['variables'], true) ?: []) : '',
    'is_active' => $template ? $template['is_active'] : 1
];

?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $isEdit ? 'Edit' : 'Add'; ?> Email Template</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="email_templates.php">Email Templates</a></li>
        <li class="breadcrumb-item active"><?php echo $isEdit ? 'Edit' : 'Add'; ?> Template</li>
    </ol>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-envelope me-1"></i>
                    Template Details
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($formData['name']); ?>" required>
                                <div class="form-text">Unique identifier for this template (e.g., 'application_received')</div>
                            </div>
                            <div class="col-md-6">
                                <label for="is_active" class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           <?php echo $formData['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Email Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?php echo htmlspecialchars($formData['subject']); ?>" required>
                            <div class="form-text">You can use variables like {{variable_name}} in the subject</div>
                        </div>

                        <div class="mb-3">
                            <label for="variables" class="form-label">Template Variables</label>
                            <input type="text" class="form-control" id="variables" name="variables" 
                                   value="<?php echo htmlspecialchars($formData['variables']); ?>">
                            <div class="form-text">Comma-separated list of variables (e.g., name, email, date)</div>
                        </div>

                        <div class="mb-3">
                            <label for="body" class="form-label">Email Body <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="body" name="body" rows="15" required><?php echo htmlspecialchars($formData['body']); ?></textarea>
                            <div class="form-text">HTML content for the email body. Use {{variable_name}} for dynamic content.</div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> Template
                            </button>
                            <a href="email_templates.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Templates
                            </a>
                            <?php if ($isEdit): ?>
                                <button type="button" class="btn btn-info" onclick="testTemplate()">
                                    <i class="fas fa-paper-plane"></i> Test Template
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Help & Tips
                </div>
                <div class="card-body">
                    <h6>Using Variables</h6>
                    <p class="small">Variables allow you to insert dynamic content into your emails. Use double curly braces around variable names:</p>
                    <ul class="small">
                        <li><code>{{name}}</code> - Recipient's name</li>
                        <li><code>{{email}}</code> - Recipient's email</li>
                        <li><code>{{date}}</code> - Current date</li>
                    </ul>

                    <h6 class="mt-3">HTML Formatting</h6>
                    <p class="small">You can use HTML tags for formatting:</p>
                    <ul class="small">
                        <li><code>&lt;h2&gt;</code> for headings</li>
                        <li><code>&lt;p&gt;</code> for paragraphs</li>
                        <li><code>&lt;strong&gt;</code> for bold text</li>
                        <li><code>&lt;ul&gt;&lt;li&gt;</code> for lists</li>
                    </ul>

                    <h6 class="mt-3">Common Templates</h6>
                    <p class="small">Consider creating templates for:</p>
                    <ul class="small">
                        <li>Welcome emails</li>
                        <li>Application confirmations</li>
                        <li>Event registrations</li>
                        <li>Password resets</li>
                    </ul>
                </div>
            </div>

            <?php if ($isEdit): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <i class="fas fa-eye me-1"></i>
                    Preview
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="previewTemplate()">
                        <i class="fas fa-eye"></i> Preview Template
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test Email Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="testForm">
                    <div class="mb-3">
                        <label for="test_email" class="form-label">Send test email to:</label>
                        <input type="email" class="form-control" id="test_email" name="test_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sample Variables (JSON format):</label>
                        <textarea class="form-control" id="test_variables" name="test_variables" rows="4">{
  "name": "John Doe",
  "email": "john@example.com",
  "date": "<?php echo date('Y-m-d'); ?>"
}</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendTestEmail()">Send Test</button>
            </div>
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
                <div id="previewContent"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
// Initialize TinyMCE for the email body
tinymce.init({
    selector: '#body',
    height: 400,
    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
    toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | code',
    content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
    setup: function (editor) {
        editor.on('change', function () {
            editor.save();
        });
    }
});

function testTemplate() {
    <?php if ($isEdit): ?>
    $('#testModal').modal('show');
    <?php endif; ?>
}

function sendTestEmail() {
    const email = document.getElementById('test_email').value;
    const variables = document.getElementById('test_variables').value;
    
    if (!email) {
        alert('Please enter an email address');
        return;
    }
    
    // Send via AJAX
    fetch('email_template_test.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            template_id: <?php echo $template['id'] ?? 'null'; ?>,
            email: email,
            variables: variables
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Test email sent successfully!');
            $('#testModal').modal('hide');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error sending test email: ' + error);
    });
}

function previewTemplate() {
    <?php if ($isEdit): ?>
    $('#previewModal').modal('show');
    $('#previewContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    
    fetch('email_template_preview.php?id=<?php echo $template['id']; ?>')
        .then(response => response.text())
        .then(html => {
            $('#previewContent').html(html);
        })
        .catch(error => {
            $('#previewContent').html('<div class="alert alert-danger">Error loading preview.</div>');
        });
    <?php endif; ?>
}
</script>

<?php include '../includes/footer.php'; ?>
