<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$applicationId = intval($_GET['id'] ?? 0);
if (!$applicationId) {
    $_SESSION['error'] = "Invalid application ID.";
    Utilities::redirect('/admin/applications.php');
}

$pageTitle = "View Application";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'Applications', 'url' => BASE_URL . '/admin/applications.php'],
    ['title' => 'View Application']
];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $status = $_POST['status'] ?? '';

        try {
            $db = (new Database())->connect();
            $stmt = $db->prepare("UPDATE applications SET status = ? WHERE id = ?");
            $stmt->execute([$status, $applicationId]);

            $_SESSION['success'] = "Application status updated successfully.";
            header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $applicationId);
            exit;

        } catch (PDOException $e) {
            error_log("Application status update error: " . $e->getMessage());
            $_SESSION['error'] = "Failed to update application status.";
        }
    }
}

// Get application data
try {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT a.*, p.title as program_title, CONCAT(a.first_name, ' ', a.last_name) as full_name FROM applications a LEFT JOIN programs p ON a.program_id = p.id WHERE a.id = ?");
    $stmt->execute([$applicationId]);
    $application = $stmt->fetch();

    if (!$application) {
        $_SESSION['error'] = "Application not found.";
        Utilities::redirect('/admin/public/applications.php');
    }

} catch (PDOException $e) {
    error_log("Application fetch error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading application.";
    Utilities::redirect('/admin/public/applications.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Application Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Personal Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Full Name:</strong></td>
                                <td><?= htmlspecialchars($application['full_name'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?= htmlspecialchars($application['email']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td><?= htmlspecialchars($application['phone'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Date of Birth:</strong></td>
                                <td><?= $application['date_of_birth'] ? date('M j, Y', strtotime($application['date_of_birth'])) : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td><?= htmlspecialchars($application['address'] ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Application Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Program:</strong></td>
                                <td><?= htmlspecialchars($application['program_title'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge bg-<?= $application['status'] === 'approved' ? 'success' : ($application['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($application['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Applied Date:</strong></td>
                                <td><?= $application['submitted_at'] ? date('M j, Y g:i A', strtotime($application['submitted_at'])) : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Last Updated:</strong></td>
                                <td><?= $application['updated_at'] ? date('M j, Y g:i A', strtotime($application['updated_at'])) : 'N/A' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if (!empty($application['motivation'])): ?>
                    <div class="mt-4">
                        <h6>Motivation</h6>
                        <div class="bg-light p-3 rounded">
                            <?= nl2br(htmlspecialchars($application['motivation'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($application['education_details'])): ?>
                    <div class="mt-4">
                        <h6>Education Background</h6>
                        <div class="bg-light p-3 rounded">
                            <?= nl2br(htmlspecialchars($application['education_details'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($application['work_experience'])): ?>
                    <div class="mt-4">
                        <h6>Experience</h6>
                        <div class="bg-light p-3 rounded">
                            <?= nl2br(htmlspecialchars($application['work_experience'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <div class="mb-3">
                        <label for="status" class="form-label">Update Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="pending" <?= $application['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $application['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= $application['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </form>

                <hr>

                <div class="d-grid gap-2">
                    <a href="mailto:<?= htmlspecialchars($application['email']) ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-envelope"></i> Send Email
                    </a>

                    <a href="<?= BASE_URL ?>/admin/public/application_export.php?id=<?= $application['id'] ?>" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-download"></i> Export PDF
                    </a>

                    <a href="<?= BASE_URL ?>/admin/public/applications.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Applications
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Application Timeline</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6>Application Submitted</h6>
                            <p class="text-muted small"><?= $application['submitted_at'] ? date('M j, Y g:i A', strtotime($application['submitted_at'])) : 'N/A' ?></p>
                        </div>
                    </div>

                    <?php if ($application['updated_at']): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-<?= $application['status'] === 'approved' ? 'success' : ($application['status'] === 'rejected' ? 'danger' : 'warning') ?>"></div>
                        <div class="timeline-content">
                            <h6>Status: <?= ucfirst($application['status']) ?></h6>
                            <p class="text-muted small"><?= date('M j, Y g:i A', strtotime($application['updated_at'])) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e3e6ea;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -15px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    padding-left: 20px;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-size: 0.9rem;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
