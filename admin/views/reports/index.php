<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
        <div>
            <a href="report_builder.php" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Custom Report Builder
            </a>
            <a href="data_export.php" class="btn btn-success btn-sm shadow-sm">
                <i class="fas fa-download fa-sm text-white-50"></i> Data Export Tools
            </a>
        </div>
    </div>

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

    <!-- Quick Stats Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_users']) ?></div>
                            <div class="text-xs text-success">+<?= $stats['new_users_30d'] ?> this month</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Applications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_applications']) ?></div>
                            <div class="text-xs text-success">+<?= $stats['new_applications_30d'] ?> this month</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Programs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['active_programs']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Upcoming Events</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['upcoming_events']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Predefined Reports -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Predefined Reports</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($predefinedReports as $report): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-circle bg-primary text-white mr-3">
                                        <i class="fas <?= $report['icon'] ?>"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-0"><?= htmlspecialchars($report['name']) ?></h6>
                                    </div>
                                </div>
                                <p class="card-text text-muted small"><?= htmlspecialchars($report['description']) ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-<?= $report['type'] === 'users' ? 'primary' : ($report['type'] === 'applications' ? 'success' : 'info') ?>">
                                        <?= ucfirst($report['type']) ?>
                                    </span>
                                    <a href="<?= $report['url'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-chart-bar"></i> View Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Custom Reports -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Custom Reports</h6>
            <a href="report_builder.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create Report
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($customReports)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Custom Reports Found</h5>
                    <p class="text-muted">Create your first custom report using the report builder.</p>
                    <a href="report_builder.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create First Report
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0" id="customReportsTable">
                        <thead>
                            <tr>
                                <th>Report Name</th>
                                <th>Type</th>
                                <th>Visibility</th>
                                <th>Created By</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customReports as $report): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($report['name']) ?></strong>
                                        <?php if ($report['description']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($report['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= ucfirst($report['type']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $report['is_public'] ? 'success' : 'secondary' ?>">
                                            <?= $report['is_public'] ? 'Public' : 'Private' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($report['created_by_name'] ?? 'Unknown') ?></td>
                                    <td><?= date('M j, Y', strtotime($report['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="report_view.php?id=<?= $report['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Report">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="report_builder.php?id=<?= $report['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit Report">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../actions/delete_report.php?id=<?= $report['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete Report" onclick="return confirm('Are you sure you want to delete this report?')">
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

<style>
.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>

<script>
$(document).ready(function() {
    $('#customReportsTable').DataTable({
        "pageLength": 25,
        "order": [[ 4, "desc" ]], // Sort by created date descending
        "columnDefs": [
            { "orderable": false, "targets": [5] } // Actions column
        ]
    });
});
</script>
