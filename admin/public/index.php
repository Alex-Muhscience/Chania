<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$pageTitle = "Dashboard";
$breadcrumbs = [
    ['title' => 'Dashboard']
];

try {
    $db = (new Database())->connect();

    // Get comprehensive statistics
    $stmt = $db->query("
        SELECT 
            (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) as total_users,
            (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL AND role = 'admin') as admin_users,
            (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_users_month,
            (SELECT COUNT(*) FROM programs WHERE deleted_at IS NULL) as total_programs,
            (SELECT COUNT(*) FROM programs WHERE deleted_at IS NULL AND is_active = 1) as active_programs,
            (SELECT COUNT(*) FROM applications WHERE deleted_at IS NULL) as total_applications,
            (SELECT COUNT(*) FROM applications WHERE deleted_at IS NULL AND status = 'pending') as pending_applications,
            (SELECT COUNT(*) FROM applications WHERE deleted_at IS NULL AND status = 'approved') as approved_applications,
            (SELECT COUNT(*) FROM applications WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_applications_month,
            (SELECT COUNT(*) FROM events WHERE deleted_at IS NULL) as total_events,
            (SELECT COUNT(*) FROM events WHERE deleted_at IS NULL AND event_date >= CURDATE()) as upcoming_events,
            (SELECT COUNT(*) FROM event_registrations) as total_registrations,
            (SELECT COUNT(*) FROM event_registrations WHERE status = 'confirmed') as confirmed_registrations,
            (SELECT COUNT(*) FROM contacts WHERE deleted_at IS NULL) as total_contacts,
            (SELECT COUNT(*) FROM contacts WHERE deleted_at IS NULL AND is_read = 0) as unread_contacts,
            (SELECT COUNT(*) FROM contacts WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_contacts_month,
            (SELECT COUNT(*) FROM testimonials WHERE deleted_at IS NULL) as total_testimonials,
            (SELECT COUNT(*) FROM testimonials WHERE deleted_at IS NULL AND is_featured = 1) as featured_testimonials,
            (SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'subscribed') as newsletter_subscribers,
            (SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'subscribed' AND subscribed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_subscribers_month,
            (SELECT COUNT(*) FROM file_uploads WHERE deleted_at IS NULL) as total_files,
            (SELECT ROUND(SUM(file_size) / 1024 / 1024, 2) FROM file_uploads WHERE deleted_at IS NULL) as total_file_size_mb,
            (SELECT COUNT(*) FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as activities_today,
            (SELECT COUNT(*) FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as activities_week
    ");
    $stats = $stmt->fetch();

    // Get recent activities
    $stmt = $db->query("
        SELECT l.*, u.full_name as user_name, u.avatar_path
        FROM admin_logs l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
        LIMIT 10
    ");
    $recentActivities = $stmt->fetchAll();

    // Get recent applications
    $stmt = $db->query("
        SELECT a.*, p.title as program_title, u.full_name as user_name
        FROM applications a
        JOIN programs p ON a.program_id = p.id
        LEFT JOIN users u ON a.user_id = u.id
        WHERE a.deleted_at IS NULL
        ORDER BY a.created_at DESC
        LIMIT 5
    ");
    $recentApplications = $stmt->fetchAll();

    // Get recent contacts
    $stmt = $db->query("
        SELECT *
        FROM contacts
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $recentContacts = $stmt->fetchAll();

    // Get upcoming events
    $stmt = $db->query("
        SELECT e.*, COUNT(er.id) as registration_count
        FROM events e
        LEFT JOIN event_registrations er ON e.id = er.event_id
        WHERE e.event_date >= CURDATE() AND e.is_active = 1 AND e.deleted_at IS NULL
        GROUP BY e.id
        ORDER BY e.event_date ASC
        LIMIT 5
    ");
    $upcomingEvents = $stmt->fetchAll();

    // Get application trends (last 30 days)
    $stmt = $db->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count
        FROM applications
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND deleted_at IS NULL
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $applicationTrends = $stmt->fetchAll();

    // Get application status distribution
    $stmt = $db->query("
        SELECT 
            status,
            COUNT(*) as count
        FROM applications
        WHERE deleted_at IS NULL
        GROUP BY status
    ");
    $applicationStatusData = $stmt->fetchAll();

    // Get monthly user registrations
    $stmt = $db->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count
        FROM users
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        AND deleted_at IS NULL
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $userRegistrationTrends = $stmt->fetchAll();

    // Get system health metrics
    $memoryUsage = memory_get_usage(true);
    $peakMemoryUsage = memory_get_peak_usage(true);

    // Test database connection speed
    $dbConnectionStart = microtime(true);
    $db->query("SELECT 1");
    $dbConnectionTime = (microtime(true) - $dbConnectionStart) * 1000;

} catch (PDOException $e) {
    error_log("Dashboard data fetch error: " . $e->getMessage());
    $stats = [];
    $recentActivities = [];
    $recentApplications = [];
    $recentContacts = [];
    $upcomingEvents = [];
    $applicationTrends = [];
    $applicationStatusData = [];
    $userRegistrationTrends = [];
    $memoryUsage = 0;
    $peakMemoryUsage = 0;
    $dbConnectionTime = 0;
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <div class="d-none d-sm-inline-block">
        <a href="<?= BASE_URL ?>/admin/system_monitor.php" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-chart-line fa-sm text-white-50"></i> System Monitor
        </a>
    </div>
</div>

<!-- Content Row - Main Statistics -->
<div class="row">
    <!-- Users Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Users
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_users'] ?? 0) ?></div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-arrow-up text-success"></i> <?= number_format($stats['new_users_month'] ?? 0) ?> this month
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Applications
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_applications'] ?? 0) ?></div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-clock text-warning"></i> <?= number_format($stats['pending_applications'] ?? 0) ?> pending
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Events Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Events
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_events'] ?? 0) ?></div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-calendar text-info"></i> <?= number_format($stats['upcoming_events'] ?? 0) ?> upcoming
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contacts Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total Contacts
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_contacts'] ?? 0) ?></div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-envelope text-danger"></i> <?= number_format($stats['unread_contacts'] ?? 0) ?> unread
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-envelope fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row - Secondary Statistics -->
<div class="row">
    <!-- Programs Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-dark shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                            Programs
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_programs'] ?? 0) ?></div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-check-circle text-success"></i> <?= number_format($stats['active_programs'] ?? 0) ?> active
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Newsletter Subscribers Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                            Newsletter Subscribers
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['newsletter_subscribers'] ?? 0) ?></div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-arrow-up text-success"></i> <?= number_format($stats['new_subscribers_month'] ?? 0) ?> this month
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-envelope-open fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Files Storage Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Files Storage
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_file_size_mb'] ?? 0, 1) ?> MB</div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-file text-info"></i> <?= number_format($stats['total_files'] ?? 0) ?> files
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-folder fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Activity Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            System Activity
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['activities_today'] ?? 0) ?></div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-clock text-warning"></i> <?= number_format($stats['activities_week'] ?? 0) ?> this week
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row - Charts -->
<div class="row">
    <!-- Application Trends Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Application Trends (Last 30 Days)</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/applications.php">View All Applications</a>
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/applications_export.php">Export Data</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="applicationsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Status Pie Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Application Status</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <?php foreach ($applicationStatusData as $status): ?>
                        <span class="mr-2">
                            <i class="fas fa-circle text-<?=
                                $status['status'] === 'approved' ? 'success' :
                                ($status['status'] === 'pending' ? 'warning' : 'danger')
                            ?>"></i>
                            <?= ucfirst($status['status']) ?>: <?= $status['count'] ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row - Tables and Activities -->
<div class="row">
    <!-- Recent Applications -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Applications</h6>
                <a href="<?= BASE_URL ?>/admin/applications.php" class="btn btn-primary btn-sm">
                    View All <i class="fas fa-arrow-right fa-sm"></i>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentApplications)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-file-alt fa-3x mb-2"></i>
                        <p>No recent applications</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Applicant</th>
                                    <th>Program</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentApplications as $application): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($application['first_name'] . ' ' . $application['last_name']) ?></strong>
                                            <?php if ($application['user_name']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($application['user_name']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($application['program_title']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?=
                                                $application['status'] === 'approved' ? 'success' :
                                                ($application['status'] === 'pending' ? 'warning' : 'danger')
                                            ?>">
                                                <?= ucfirst($application['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= date('M j', strtotime($application['created_at'])) ?></small>
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

    <!-- Recent Contacts -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Contacts</h6>
                <a href="<?= BASE_URL ?>/admin/contacts.php" class="btn btn-primary btn-sm">
                    View All <i class="fas fa-arrow-right fa-sm"></i>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentContacts)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-envelope fa-3x mb-2"></i>
                        <p>No recent contacts</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentContacts as $contact): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($contact['full_name']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($contact['email']) ?></small>
                                        </td>
                                        <td>
                                            <small><?= Utilities::truncate($contact['subject'], 30) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $contact['is_read'] ? 'success' : 'warning' ?>">
                                                <?= $contact['is_read'] ? 'Read' : 'Unread' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= date('M j', strtotime($contact['created_at'])) ?></small>
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
</div>

<!-- Content Row - Upcoming Events and Recent Activity -->
<div class="row">
    <!-- Upcoming Events -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Upcoming Events</h6>
                <a href="<?= BASE_URL ?>/admin/events.php" class="btn btn-primary btn-sm">
                    View All <i class="fas fa-arrow-right fa-sm"></i>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingEvents)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-calendar-alt fa-3x mb-2"></i>
                        <p>No upcoming events</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($upcomingEvents as $event): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="calendar-icon bg-primary text-white rounded text-center mr-3" style="width: 50px; height: 50px; line-height: 50px;">
                                <div style="font-size: 12px; line-height: 1;"><?= date('M', strtotime($event['event_date'])) ?></div>
                                <div style="font-size: 16px; font-weight: bold; line-height: 1;"><?= date('j', strtotime($event['event_date'])) ?></div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?= htmlspecialchars($event['title']) ?></h6>
                                <small class="text-muted">
                                    <?= date('M j, Y g:i A', strtotime($event['event_date'])) ?>
                                    <br><?= $event['registration_count'] ?> registrations
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                <a href="<?= BASE_URL ?>/admin/admin_logs.php" class="btn btn-primary btn-sm">
                    View All <i class="fas fa-arrow-right fa-sm"></i>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivities)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-clipboard-list fa-3x mb-2"></i>
                        <p>No recent activity</p>
                    </div>
                <?php else: ?>
                    <div class="activity-feed">
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-circle bg-secondary text-white rounded-circle mr-3" style="width: 35px; height: 35px; line-height: 35px; text-align: center; font-size: 14px;">
                                    <?php if ($activity['avatar_path']): ?>
                                        <img src="<?= BASE_URL ?>/<?= $activity['avatar_path'] ?>"
                                             class="rounded-circle" style="width: 35px; height: 35px; object-fit: cover;">
                                    <?php else: ?>
                                        <?= strtoupper(substr($activity['user_name'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small">
                                        <strong><?= htmlspecialchars($activity['user_name']) ?></strong>
                                        <span class="text-muted"><?= strtolower(str_replace('_', ' ', $activity['action'])) ?></span>
                                        <?php if ($activity['entity_type']): ?>
                                            <span class="badge badge-secondary"><?= $activity['entity_type'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= Utilities::timeAgo($activity['created_at']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- System Health Status -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">System Health Status</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="h4 text-<?= $dbConnectionTime < 100 ? 'success' : 'warning' ?>">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="font-weight-bold">Database</div>
                        <div class="text-muted small">
                            <?= number_format($dbConnectionTime, 2) ?> ms
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="h4 text-<?= is_writable(UPLOAD_PATH) ? 'success' : 'danger' ?>">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="font-weight-bold">Storage</div>
                        <div class="text-muted small">
                            <?= is_writable(UPLOAD_PATH) ? 'Writable' : 'Read-only' ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="h4 text-<?= $memoryUsage < (1024 * 1024 * 100) ? 'success' : 'warning' ?>">
                            <i class="fas fa-memory"></i>
                        </div>
                        <div class="font-weight-bold">Memory</div>
                        <div class="text-muted small">
                            <?= Utilities::formatFileSize($memoryUsage) ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="h4 text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="font-weight-bold">Status</div>
                        <div class="text-muted small">
                            Online
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Application trends chart
const applicationsCtx = document.getElementById('applicationsChart').getContext('2d');
const applicationsChart = new Chart(applicationsCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($applicationTrends, 'date')) ?>,
        datasets: [{
            label: 'Applications',
            data: <?= json_encode(array_column($applicationTrends, 'count')) ?>,
            borderColor: 'rgb(78, 115, 223)',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.3,
            fill: true
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
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Application status pie chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($applicationStatusData, 'status')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($applicationStatusData, 'count')) ?>,
            backgroundColor: [
                'rgb(28, 200, 138)',
                'rgb(255, 193, 7)',
                'rgb(231, 74, 59)',
                'rgb(54, 185, 204)',
                'rgb(133, 135, 150)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>