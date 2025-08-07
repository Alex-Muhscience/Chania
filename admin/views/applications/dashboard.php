<!-- Dashboard Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Applications Dashboard</h1>
        <p class="text-muted mb-0">Comprehensive overview and analytics</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>../../public/applications.php" class="btn btn-outline-primary">
            <i class="fas fa-list"></i> View All Applications
        </a>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download"></i> Export
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/applications_export.php?format=csv">CSV Export</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/applications_export.php?format=pdf">PDF Export</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Applications
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['total_applications'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Approved Applications
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['status_approved'] ?? 0) ?>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Applications This Month
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['applications_this_month'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Assigned to Cohorts
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['applications_with_cohorts'] ?? 0) ?>
                        </div>
                        <div class="text-xs">
                            <?php 
                            $total = $stats['total_applications'] ?? 1;
                            $assigned = $stats['applications_with_cohorts'] ?? 0;
                            $percentage = round(($assigned / $total) * 100, 1);
                            ?>
                            <span class="mr-2"><?= $percentage ?>% of total</span>
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

<div class="row">
    <!-- Applications Over Time Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Applications Over Time</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="applicationsOverTimeChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Distribution Pie Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Status Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="statusDistributionChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <?php foreach ($chartData['status_distribution'] ?? [] as $status): ?>
                    <span class="mr-2">
                        <i class="fas fa-circle text-<?= 
                            $status['status'] === 'approved' ? 'success' : 
                            ($status['status'] === 'rejected' ? 'danger' : 'warning') 
                        ?>"></i> <?= ucfirst($status['status']) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Program Popularity Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Program Popularity</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar">
                    <canvas id="programPopularityChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Mode Preferences -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Delivery Mode Preferences</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="deliveryModeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Applications -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Applications</h6>
                <a href="<?= BASE_URL ?>../../public/applications.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-list fa-sm text-white-50"></i> View All
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Program</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentApplications)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No recent applications</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentApplications as $app): ?>
                                <tr>
                                    <td><?= htmlspecialchars($app['full_name']) ?></td>
                                    <td><?= htmlspecialchars($app['email']) ?></td>
                                    <td>
                                        <small><?= htmlspecialchars($app['program_title'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= 
                                            $app['status'] === 'approved' ? 'success' : 
                                            ($app['status'] === 'rejected' ? 'danger' : 'warning') 
                                        ?> badge-pill">
                                            <?= ucfirst($app['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= date('M j, Y', strtotime($app['submitted_at'])) ?></small>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/public/application_view.php?id=<?= $app['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Cohort Overview -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Upcoming Cohorts</h6>
            </div>
            <div class="card-body">
                <?php if (empty($cohortData)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <p>No upcoming cohorts</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($cohortData as $cohort): ?>
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($cohort['title']) ?></h6>
                                <p class="text-muted small mb-1"><?= htmlspecialchars($cohort['program_title']) ?></p>
                                <p class="text-muted small mb-1">
                                    <?= date('M j', strtotime($cohort['start_date'])) ?> - 
                                    <?= date('M j, Y', strtotime($cohort['end_date'])) ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-<?= $cohort['current_participants'] >= $cohort['max_participants'] ? 'danger' : 'success' ?>">
                                    <?= $cohort['current_participants'] ?>/<?= $cohort['max_participants'] ?>
                                </span>
                            </div>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <?php 
                            $percentage = ($cohort['max_participants'] > 0) ? 
                                ($cohort['current_participants'] / $cohort['max_participants']) * 100 : 0;
                            $progressClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                            ?>
                            <div class="progress-bar <?= $progressClass ?>" role="progressbar" 
                                 style="width: <?= $percentage ?>%" 
                                 aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <?php if ($cohort['available_spots'] > 0): ?>
                            <small class="text-success"><?= $cohort['available_spots'] ?> spots available</small>
                        <?php else: ?>
                            <small class="text-danger">Fully booked</small>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Popular Programs -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Popular Programs</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($stats['popular_programs'] ?? [] as $program): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 border-left-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title"><?= htmlspecialchars($program['title']) ?></h6>
                                        <p class="card-text text-muted">
                                            <?= number_format($program['application_count']) ?> applications
                                        </p>
                                    </div>
                                    <span class="badge badge-info badge-pill">
                                        <?= $program['application_count'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Chart Data from PHP
const chartData = <?= json_encode($chartData) ?>;

// Applications Over Time Chart
if (document.getElementById('applicationsOverTimeChart')) {
    const ctx = document.getElementById('applicationsOverTimeChart').getContext('2d');
    const timeData = chartData.applications_over_time || [];
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: timeData.map(item => item.month),
            datasets: [{
                label: 'Applications',
                data: timeData.map(item => item.count),
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

// Status Distribution Chart
if (document.getElementById('statusDistributionChart')) {
    const ctx = document.getElementById('statusDistributionChart').getContext('2d');
    const statusData = chartData.status_distribution || [];
    
    const statusColors = {
        'pending': '#f6c23e',
        'approved': '#1cc88a',
        'rejected': '#e74a3b',
        'under_review': '#36b9cc'
    };
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
            datasets: [{
                data: statusData.map(item => item.count),
                backgroundColor: statusData.map(item => statusColors[item.status] || '#858796'),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Program Popularity Chart
if (document.getElementById('programPopularityChart')) {
    const ctx = document.getElementById('programPopularityChart').getContext('2d');
    const programData = chartData.program_popularity || [];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: programData.map(item => item.program),
            datasets: [{
                label: 'Applications',
                data: programData.map(item => item.count),
                backgroundColor: '#4e73df',
                borderColor: '#4e73df',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

// Delivery Mode Preferences Chart
if (document.getElementById('deliveryModeChart')) {
    const ctx = document.getElementById('deliveryModeChart').getContext('2d');
    const deliveryData = chartData.delivery_mode_preferences || [];
    
    const modeColors = {
        'online': '#1cc88a',
        'physical': '#36b9cc',
        'either': '#f6c23e'
    };
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: deliveryData.map(item => item.preferred_delivery_mode.charAt(0).toUpperCase() + item.preferred_delivery_mode.slice(1)),
            datasets: [{
                data: deliveryData.map(item => item.count),
                backgroundColor: deliveryData.map(item => modeColors[item.preferred_delivery_mode] || '#858796'),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
</script>

<style>
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
.text-gray-800 {
    color: #5a5c69 !important;
}
.text-gray-300 {
    color: #dddfeb !important;
}
</style>
