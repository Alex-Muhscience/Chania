<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/User.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    Utilities::redirect('/admin/public/login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

// Check permission
if (!$userModel->hasPermission($_SESSION['user_id'], 'campaigns') && !$userModel->hasPermission($_SESSION['user_id'], '*')) {
    $_SESSION['error'] = "You don't have permission to access this resource.";
    Utilities::redirect('/admin/public/index.php');
    exit();
}

require_once __DIR__ . '/../includes/header.php';

// Get campaigns
$stmt = $db->prepare("
    SELECT ec.*, et.name as template_name, u.username as created_by_name
    FROM email_campaigns ec
    LEFT JOIN email_templates et ON ec.template_id = et.id
    LEFT JOIN users u ON ec.created_by = u.id
    ORDER BY ec.created_at DESC
");
$stmt->execute();
$campaigns = $stmt->fetchAll();

?>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Email Campaigns</h1>
        <a href="email_campaign_create.php" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Create Campaign
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Campaigns</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($campaigns) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bullhorn fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sent Campaigns</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($campaigns, fn($c) => $c['status'] === 'sent')) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Draft Campaigns</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($campaigns, fn($c) => $c['status'] === 'draft')) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Recipients</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= array_sum(array_column($campaigns, 'total_recipients')) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Email Campaigns</h6>
        </div>
        <div class="card-body">
            <?php if (empty($campaigns)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Email Campaigns Found</h5>
                    <p class="text-muted">Create your first email campaign to start sending newsletters and announcements.</p>
                    <a href="email_campaign_create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create First Campaign
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Campaign Name</th>
                                <th>Subject</th>
                                <th>Recipients</th>
                                <th>Status</th>
                                <th>Sent</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaigns as $campaign): ?>
                                <tr>
                                    <td><?= htmlspecialchars($campaign['name']) ?></td>
                                    <td><?= htmlspecialchars($campaign['subject']) ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= $campaign['recipient_type'] ?></span>
                                        <small class="d-block text-muted"><?= number_format($campaign['total_recipients']) ?> recipients</small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'scheduled' => 'warning',
                                            'sending' => 'info',
                                            'sent' => 'success',
                                            'failed' => 'danger'
                                        ];
                                        ?>
                                        <span class="badge badge-<?= $statusColors[$campaign['status']] ?>">
                                            <?= ucfirst($campaign['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $campaign['sent_count'] ?>/<?= $campaign['total_recipients'] ?>
                                        <?php if ($campaign['failed_count'] > 0): ?>
                                            <small class="text-danger d-block"><?= $campaign['failed_count'] ?> failed</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($campaign['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="email_campaign_view.php?id=<?= $campaign['id'] ?>" 
                                               class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($campaign['status'] === 'draft'): ?>
                                                <a href="email_campaign_edit.php?id=<?= $campaign['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="email_campaign_send.php?id=<?= $campaign['id'] ?>" 
                                                   class="btn btn-sm btn-outline-success" title="Send Now">
                                                    <i class="fas fa-paper-plane"></i>
                                                </a>
                                            <?php endif; ?>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
