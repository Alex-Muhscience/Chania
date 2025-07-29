<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

// Set page title and breadcrumbs
$pageTitle = "Contact Management";
$breadcrumbs = [
    ["title" => "Dashboard", "url" => BASE_URL . "/admin/public/"],
    ["title" => "Contacts"]
];

try {
    $db = (new Database())->connect();
    
    // Handle contact actions
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $contactId = (int)$_GET['id'];
        
        if ($_GET['action'] === 'mark_read') {
            $stmt = $db->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?");
            $stmt->execute([$contactId]);
            $_SESSION['success'] = "Contact marked as read.";
            Utilities::redirect('/admin/public/contacts.php');
        } elseif ($_GET['action'] === 'delete') {
            $stmt = $db->prepare("UPDATE contacts SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$contactId]);
            $_SESSION['success'] = "Contact deleted successfully.";
            Utilities::redirect('/admin/public/contacts.php');
        }
    }
    
    $stmt = $db->query("SELECT * FROM contacts WHERE deleted_at IS NULL ORDER BY submitted_at DESC");
    $contacts = $stmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error loading contacts. Please try again later.";
    error_log("Contacts loading error: " . $e->getMessage());
    $contacts = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Contact Management</h1>
    <div class="d-none d-sm-inline-block">
        <a href="<?= BASE_URL ?>/admin/public/contacts.php" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-sync fa-sm text-white-50"></i> Refresh
        </a>
    </div>
</div>

<!-- Contacts Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-envelope"></i> All Contacts
            <span class="badge badge-secondary ml-2"><?= count($contacts) ?></span>
        </h6>
    </div>
    <div class="card-body">
        <?php if (empty($contacts)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-envelope fa-3x mb-3"></i>
                <p>No contacts found.</p>
                <small class="text-muted">Contacts submitted through the website will appear here.</small>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="contactsTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                            <tr class="<?= !$contact['is_read'] ? 'table-warning' : '' ?>">
                                <td>
                                    <strong><?= htmlspecialchars($contact['full_name'] ?? $contact['name'] ?? 'N/A') ?></strong>
                                </td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                                        <?= htmlspecialchars($contact['email']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($contact['subject']) ?></td>
                                <td>
                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                        <?= htmlspecialchars(Utilities::truncate($contact['message'], 100)) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $contact['is_read'] ? 'success' : 'warning' ?>">
                                        <?= $contact['is_read'] ? 'Read' : 'Unread' ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <?= date('M j, Y', strtotime($contact['submitted_at'])) ?><br>
                                        <span class="text-muted"><?= date('g:i A', strtotime($contact['submitted_at'])) ?></span>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" 
                                                data-toggle="modal" data-target="#contactModal<?= $contact['id'] ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        
                                        <?php if (!$contact['is_read']): ?>
                                            <a href="?action=mark_read&id=<?= $contact['id'] ?>" 
                                               class="btn btn-sm btn-success" 
                                               title="Mark as Read">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="?action=delete&id=<?= $contact['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this contact?')"
                                           title="Delete">
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

<!-- Contact Detail Modals -->
<?php foreach ($contacts as $contact): ?>
<div class="modal fade" id="contactModal<?= $contact['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contact Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Name:</strong><br>
                        <?= htmlspecialchars($contact['full_name'] ?? $contact['name'] ?? 'N/A') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong><br>
                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                            <?= htmlspecialchars($contact['email']) ?>
                        </a>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Phone:</strong><br>
                        <?= htmlspecialchars($contact['phone'] ?? 'Not provided') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Date Submitted:</strong><br>
                        <?= date('F j, Y g:i A', strtotime($contact['submitted_at'])) ?>
                    </div>
                </div>
                <hr>
                <strong>Subject:</strong><br>
                <?= htmlspecialchars($contact['subject']) ?>
                <hr>
                <strong>Message:</strong><br>
                <div class="border p-3 bg-light" style="white-space: pre-wrap;">
                    <?= htmlspecialchars($contact['message']) ?>
                </div>
            </div>
            <div class="modal-footer">
                <a href="mailto:<?= htmlspecialchars($contact['email']) ?>?subject=Re: <?= urlencode($contact['subject']) ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-reply"></i> Reply via Email
                </a>
                <?php if (!$contact['is_read']): ?>
                    <a href="?action=mark_read&id=<?= $contact['id'] ?>" class="btn btn-success">
                        <i class="fas fa-check"></i> Mark as Read
                    </a>
                <?php endif; ?>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
$(document).ready(function() {
    $('#contactsTable').DataTable({
        "order": [[ 5, "desc" ]],
        "pageLength": 25,
        "responsive": true
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
