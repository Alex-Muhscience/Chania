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
            (SELECT COUNT(*) FROM users WHERE is_active = 1) as total_users,
            (SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1) as admin_users,
            (SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND is_active = 1) as new_users_month,
            (SELECT COUNT(*) FROM programs WHERE deleted_at IS NULL) as total_programs,
            (SELECT COUNT(*) FROM programs WHERE deleted_at IS NULL AND is_active = 1) as active_programs,
            (SELECT COUNT(*) FROM applications WHERE deleted_at IS NULL) as total_applications,
            (SELECT COUNT(*) FROM applications WHERE status = 'pending' AND deleted_at IS NULL) as pending_applications,
            (SELECT COUNT(*) FROM applications WHERE status = 'approved' AND deleted_at IS NULL) as approved_applications,
            (SELECT COUNT(*) FROM applications WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND deleted_at IS NULL) as new_applications_month,
            (SELECT COUNT(*) FROM events WHERE deleted_at IS NULL) as total_events,
            (SELECT COUNT(*) FROM events WHERE deleted_at IS NULL AND event_date >= NOW()) as upcoming_events,
            (SELECT COUNT(*) FROM event_registrations) as total_registrations,
            (SELECT COUNT(*) FROM event_registrations WHERE status = 'confirmed') as confirmed_registrations,
            (SELECT COUNT(*) FROM contacts WHERE deleted_at IS NULL) as total_contacts,
            (SELECT COUNT(*) FROM contacts WHERE is_read = 0 AND deleted_at IS NULL) as unread_contacts,
            (SELECT COUNT(*) FROM contacts WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND deleted_at IS NULL) as new_contacts_month,
            (SELECT COUNT(*) FROM blog_posts WHERE status = 'published') as total_testimonials,
            (SELECT COUNT(*) FROM blog_posts WHERE is_featured = 1 AND status = 'published') as featured_testimonials,
            (SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'subscribed') as newsletter_subscribers,
            (SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'subscribed' AND subscribed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_subscribers_month,
            (SELECT COUNT(*) FROM file_uploads WHERE deleted_at IS NULL) as total_files,
            (SELECT ROUND(COALESCE(SUM(file_size), 0) / 1024 / 1024, 2) FROM file_uploads WHERE deleted_at IS NULL) as total_file_size_mb,
            (SELECT COUNT(*) FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as activities_today,
            (SELECT COUNT(*) FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as activities_week
    ");
    $stats = $stmt->fetch();

    // Get recent activities
    $stmt = $db->query("
        SELECT l.*, u.username as user_name
        FROM admin_logs l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
        LIMIT 10
    ");
    $recentActivities = $stmt->fetchAll();

    // Get recent applications
    $stmt = $db->query("
        SELECT a.*, p.title as program_title
        FROM applications a
        JOIN programs p ON a.program_id = p.id
        WHERE a.deleted_at IS NULL AND p.deleted_at IS NULL
        ORDER BY a.submitted_at DESC
        LIMIT 5
    ");
    $recentApplications = $stmt->fetchAll();

    // Get recent contacts
    $stmt = $db->query("
        SELECT *
        FROM contacts
        WHERE deleted_at IS NULL
        ORDER BY submitted_at DESC
        LIMIT 5
    ");
    $recentContacts = $stmt->fetchAll();
    
    // Get recent newsletter subscriptions (last 7 days)
    $stmt = $db->query("
        SELECT *, subscribed_at as created_at, 'website_footer' as source
        FROM newsletter_subscribers 
        WHERE subscribed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
        ORDER BY subscribed_at DESC 
        LIMIT 10
    ");
    $recentNewsletterSubscriptions = $stmt->fetchAll();

    // Get upcoming events
    $stmt = $db->query("
        SELECT e.*, COUNT(er.id) as registration_count
        FROM events e
        LEFT JOIN event_registrations er ON e.id = er.event_id
        WHERE e.event_date >= CURDATE() AND e.is_active = 1 AND e.deleted_at IS NULL
        GROUP BY e.id, e.title, e.event_date, e.location, e.is_active, e.deleted_at
        ORDER BY e.event_date ASC
        LIMIT 5
    ");
    $upcomingEvents = $stmt->fetchAll();

    // Get application trends (last 30 days)
    $stmt = $db->query("
        SELECT 
            DATE(submitted_at) as date,
            COUNT(*) as count
        FROM applications
        WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND deleted_at IS NULL
        GROUP BY DATE(submitted_at)
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
        AND is_active = 1
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $userRegistrationTrends = $stmt->fetchAll();

    // Get event attendance trends
    $stmt = $db->query("
        SELECT 
            DATE_FORMAT(e.event_date, '%Y-%m') as month,
            COUNT(DISTINCT er.id) as registrations,
            COUNT(DISTINCT e.id) as events
        FROM events e
        LEFT JOIN event_registrations er ON e.id = er.event_id
        WHERE e.event_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(e.event_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $eventAttendanceTrends = $stmt->fetchAll();

    // Get comprehensive program enrollment statistics with better handling for large datasets
    $stmt = $db->query("
        SELECT 
            p.id as program_id,
            p.title as program_name,
            p.category,
            p.difficulty_level,
            p.max_participants,
            p.fee,
            COUNT(DISTINCT a.id) as total_applications,
            COUNT(DISTINCT CASE WHEN a.status = 'approved' THEN a.id END) as approved,
            COUNT(DISTINCT CASE WHEN a.status = 'pending' THEN a.id END) as pending,
            COUNT(DISTINCT CASE WHEN a.status = 'rejected' THEN a.id END) as rejected,
            COUNT(DISTINCT CASE WHEN a.status = 'completed' THEN a.id END) as completed,
            0 as avg_rating,
            0 as feedback_count,
            p.created_at as program_created,
            p.start_date,
            p.end_date,
            CASE 
                WHEN p.max_participants > 0 AND COUNT(DISTINCT CASE WHEN a.status = 'approved' THEN a.id END) >= p.max_participants THEN 'Full'
                WHEN COUNT(DISTINCT CASE WHEN a.status = 'approved' THEN a.id END) = 0 THEN 'Empty'
                ELSE 'Available'
            END as enrollment_status,
            ROUND(
                CASE 
                    WHEN p.max_participants > 0 THEN 
                        (COUNT(DISTINCT CASE WHEN a.status = 'approved' THEN a.id END) * 100.0) / p.max_participants
                    ELSE 0
                END, 1
            ) as capacity_percentage
        FROM programs p
        LEFT JOIN applications a ON p.id = a.program_id AND a.deleted_at IS NULL
        WHERE p.deleted_at IS NULL AND p.is_active = 1
        GROUP BY p.id, p.title, p.category, p.difficulty_level, p.created_at, p.max_participants, p.fee, p.start_date, p.end_date
        HAVING total_applications > 0 OR p.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        ORDER BY total_applications DESC, approved DESC
        LIMIT 25
    ");
    $programEnrollmentData = $stmt->fetchAll();
    
    // Get program enrollment trends over time
    $stmt = $db->query("
        SELECT 
            DATE_FORMAT(a.submitted_at, '%Y-%m') as month,
            p.title as program_name,
            COUNT(a.id) as applications
        FROM applications a
        JOIN programs p ON a.program_id = p.id
        WHERE a.submitted_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        AND a.deleted_at IS NULL AND p.deleted_at IS NULL
        GROUP BY DATE_FORMAT(a.submitted_at, '%Y-%m'), p.id, p.title
        ORDER BY month DESC, applications DESC
    ");
    $programEnrollmentTrends = $stmt->fetchAll();
    
    // Get category-wise enrollment statistics
    $stmt = $db->query("
        SELECT 
            COALESCE(p.category, 'Uncategorized') as category,
            COUNT(DISTINCT p.id) as program_count,
            COUNT(DISTINCT a.id) as total_applications,
            COUNT(DISTINCT CASE WHEN a.status = 'approved' THEN a.id END) as approved_applications,
            ROUND(AVG(CASE WHEN a.status = 'approved' THEN 1.0 ELSE 0.0 END) * 100, 1) as approval_rate
        FROM programs p
        LEFT JOIN applications a ON p.id = a.program_id AND a.deleted_at IS NULL
        WHERE p.deleted_at IS NULL AND p.is_active = 1
        GROUP BY p.category
        ORDER BY total_applications DESC
    ");
    $categoryStats = $stmt->fetchAll();

    // Get system health metrics
    $memoryUsage = memory_get_usage(true);
    $peakMemoryUsage = memory_get_peak_usage(true);

    // Test database connection speed
    $dbConnectionStart = microtime(true);
    $db->query("SELECT 1");
    $dbConnectionTime = (microtime(true) - $dbConnectionStart) * 1000;

} catch (PDOException $e) {
    error_log("Dashboard data fetch error: " . $e->getMessage());
    // For debugging - remove this in production
    if (isset($_GET['debug'])) {
        echo "<div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div class='alert alert-info'>Stack trace: <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></div>";
    }
    $stats = [
        'total_users' => 0,
        'admin_users' => 0,
        'new_users_month' => 0,
        'total_programs' => 0,
        'active_programs' => 0,
        'total_applications' => 0,
        'pending_applications' => 0,
        'approved_applications' => 0,
        'new_applications_month' => 0,
        'total_events' => 0,
        'upcoming_events' => 0,
        'total_registrations' => 0,
        'confirmed_registrations' => 0,
        'total_contacts' => 0,
        'unread_contacts' => 0,
        'new_contacts_month' => 0,
        'total_testimonials' => 0,
        'featured_testimonials' => 0,
        'newsletter_subscribers' => 0,
        'new_subscribers_month' => 0,
        'total_files' => 0,
        'total_file_size_mb' => 0,
        'activities_today' => 0,
        'activities_week' => 0
    ];
    $recentActivities = [];
    $recentApplications = [];
    $recentContacts = [];
    $upcomingEvents = [];
    $applicationTrends = [];
    $applicationStatusData = [];
    $userRegistrationTrends = [];
    $eventAttendanceTrends = [];
    $programEnrollmentData = [];
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
        <a href="<?= BASE_URL ?>/admin/public/system_monitor.php" class="btn btn-sm btn-primary shadow-sm mr-2">
            <i class="fas fa-chart-line fa-sm text-white-50"></i> System Monitor
        </a>
        <a href="<?= BASE_URL ?>/admin/public/settings.php" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-cog fa-sm text-white-50"></i> Settings
        </a>
    </div>
</div>

<!-- Quick Actions Row -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Quick Actions</div>
                        <div class="row">
                            <div class="col-md-2">
                                <a href="users.php?action=add" class="btn btn-outline-primary btn-sm btn-block">
                                    <i class="fas fa-user-plus"></i> Add User
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="programs.php?action=add" class="btn btn-outline-success btn-sm btn-block">
                                    <i class="fas fa-graduation-cap"></i> Add Program
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="events.php?action=add" class="btn btn-outline-warning btn-sm btn-block">
                                    <i class="fas fa-calendar-alt"></i> Add Event
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="partner_add.php" class="btn btn-outline-info btn-sm btn-block">
                                    <i class="fas fa-handshake"></i> Add Partner
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="team_member_add.php" class="btn btn-outline-secondary btn-sm btn-block">
                                    <i class="fas fa-users-cog"></i> Add Team Member
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="files.php" class="btn btn-outline-dark btn-sm btn-block">
                                    <i class="fas fa-folder"></i> File Manager
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                <div class="progress mt-2">
                    <?php 
                    $total_apps = $stats['total_applications'] ?? 0;
                    $pending_apps = $stats['pending_applications'] ?? 0;
                    $progress_width = $total_apps > 0 ? ($pending_apps / $total_apps) * 100 : 0;
                    ?>
                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $progress_width ?>%" aria-valuenow="<?= $pending_apps ?>" aria-valuemin="0" aria-valuemax="<?= $total_apps ?>"></div>
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
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/applications.php">View All Applications</a>
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/application_export.php">Export Data</a>
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

<!-- Content Row - Additional Analytics Charts -->
<div class="row">
    <!-- User Registration Trends Chart -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">User Registrations (Last 12 Months)</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="userDropdownMenuLink" data-toggle="dropdown">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/users.php">View All Users</a>
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/user_export.php">Export Data</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="userRegistrationsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Attendance Trends Chart -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Event Attendance (Last 12 Months)</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="eventDropdownMenuLink" data-toggle="dropdown">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/events.php">View All Events</a>
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/event_export.php">Export Data</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="eventAttendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row - Program Enrollment Chart -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Program Enrollment Statistics</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="programDropdownMenuLink" data-toggle="dropdown">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                        <a class="dropdown-item" href="<?= BASE_URL ?>programs.php">View All Programs</a>
                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/data_export.php">Export Data</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-bar">
                    <canvas id="programEnrollmentChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row - Program Details Table -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Program Performance Details</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($programEnrollmentData)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Program</th>
                                    <th>Category</th>
                                    <th>Level</th>
                                    <th class="text-center">Total Applications</th>
                                    <th class="text-center">Approved</th>
                                    <th class="text-center">Pending</th>
                                    <th class="text-center">Completed</th>
                                    <th class="text-center">Capacity Status</th>
                                    <th class="text-center">Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($programEnrollmentData as $program): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-3">
                                                    <div class="icon-circle bg-primary">
                                                        <i class="fas fa-graduation-cap text-white"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($program['program_name']) ?></strong>
                                                    <br><small class="text-muted">Created: <?= date('M j, Y', strtotime($program['program_created'])) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= htmlspecialchars($program['category'] ?: 'General') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= 
                                                $program['difficulty_level'] === 'beginner' ? 'success' :
                                                ($program['difficulty_level'] === 'intermediate' ? 'warning' : 'danger')
                                            ?>">
                                                <?= ucfirst($program['difficulty_level'] ?: 'N/A') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <strong><?= number_format($program['total_applications']) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-success font-weight-bold"><?= number_format($program['approved']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-warning font-weight-bold"><?= number_format($program['pending']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-info font-weight-bold"><?= number_format($program['completed']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($program['max_participants'] > 0): ?>
                                                <div class="progress" style="width: 80px; margin: 0 auto;">
                                                    <div class="progress-bar bg-<?= 
                                                        $program['capacity_percentage'] >= 90 ? 'danger' :
                                                        ($program['capacity_percentage'] >= 70 ? 'warning' : 'info')
                                                    ?>" 
                                                         role="progressbar" 
                                                         style="width: <?= min(100, $program['capacity_percentage']) ?>%" 
                                                         title="<?= $program['capacity_percentage'] ?>% full">
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    <?= $program['approved'] ?>/<?= $program['max_participants'] ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">No limit</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($program['avg_rating'] > 0): ?>
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="text-warning mr-1">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?= $i <= round($program['avg_rating']) ? '' : '-half-alt' ?><?= $i > $program['avg_rating'] ? ' text-muted' : '' ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <small>(<?= $program['feedback_count'] ?>)</small>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No rating</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-graduation-cap fa-3x text-gray-300 mb-3"></i>
                        <h5 class="text-gray-600">No program data available</h5>
                        <p class="text-muted">No programs found or no applications submitted yet.</p>
                    </div>
                <?php endif; ?>
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
                <a href="<?= BASE_URL ?>/admin/public/applications.php" class="btn btn-primary btn-sm">
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
                                            <strong><?= $application['first_name'] . ' ' . $application['last_name'] ?></strong>
                                            <br><small class="text-muted"><?= $application['email'] ?></small>
                                        </td>
                                        <td>
                                            <small><?= $application['program_title'] ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?=
                                                $application['status'] === 'approved' ? 'success' :
                                                ($application['status'] === 'pending' ? 'warning' : 'danger')
                                            ?>">
                                            <?= $application['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= $application['submitted_at'] ?></small>
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
                <a href="<?= BASE_URL ?>/admin/public/contacts.php" class="btn btn-primary btn-sm">
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
                                            <strong><?= $contact['name'] ?></strong>
                                            <br><small class="text-muted"><?= $contact['email'] ?></small>
                                        </td>
                                        <td>
                                            <small><?= $contact['subject'] ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $contact['is_read'] ? 'success' : 'warning' ?>">
                                                <?= $contact['is_read'] ? 'Read' : 'Unread' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= $contact['submitted_at'] ?></small>
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
    
    <!-- Recent Newsletter Subscriptions -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Newsletter Subscriptions</h6>
                <a href="<?= BASE_URL ?>/admin/public/newsletter.php" class="btn btn-primary btn-sm">
                    View All <i class="fas fa-arrow-right fa-sm"></i>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentNewsletterSubscriptions)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-envelope-open fa-3x mb-2"></i>
                        <p>No recent newsletter subscriptions</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Source</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentNewsletterSubscriptions as $subscription): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($subscription['email']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?= htmlspecialchars($subscription['source']) ?></span>
                                        </td>
                                        <td>
                                            <small><?= date('M j, Y g:i A', strtotime($subscription['created_at'])) ?></small>
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
                <a href="<?= BASE_URL ?>/admin/public/events.php" class="btn btn-primary btn-sm">
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
                                <div style="font-size: 12px; line-height: 1;"><?= substr($event['event_date'], 5, 2) ?></div>
                                <div style="font-size: 16px; font-weight: bold; line-height: 1;"><?= substr($event['event_date'], 8, 2) ?></div>
                            </div>
                            <div class="flex-grow-1">
                                            <h6 class="mb-0"><?= $event['title'] ?></h6>
                                <small class="text-muted">
                                    <?= $event['event_date'] ?>
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
                <a href="<?= BASE_URL ?>/admin/public/admin_logs.php" class="btn btn-primary btn-sm">
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
                                    <?= strtoupper(substr($activity['user_name'], 0, 1)) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small">
                                        <strong><?= $activity['user_name'] ?></strong>
                                        <span class="text-muted"><?= $activity['action'] ?></span>
                                        <?php if ($activity['entity_type']): ?>
                                            <span class="badge badge-secondary"><?= $activity['entity_type'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= $activity['created_at'] ?>
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

<style>
.icon-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    font-size: 14px;
}

.progress {
    height: 8px;
}

.chart-bar {
    position: relative;
    height: 400px;
}

.chart-area {
    position: relative;
    height: 300px;
}

.chart-pie {
    position: relative;
    height: 250px;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-responsive {
    border-radius: 0.35rem;
}

.badge {
    font-size: 0.75em;
}

.activity-feed .avatar-circle {
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .chart-bar,
    .chart-area,
    .chart-pie {
        height: 250px;
    }
    
    .table-responsive {
        font-size: 0.85rem;
    }
    
    .icon-circle {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
}
</style>

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

// User registration trends chart (BAR CHART)
const userRegistrationsCtx = document.getElementById('userRegistrationsChart').getContext('2d');
const userRegistrationsChart = new Chart(userRegistrationsCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($userRegistrationTrends, 'month')) ?>,
        datasets: [{
            label: 'New Users',
            data: <?= json_encode(array_column($userRegistrationTrends, 'count')) ?>,
            backgroundColor: [
                'rgba(78, 115, 223, 0.8)',
                'rgba(28, 200, 138, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(231, 74, 59, 0.8)',
                'rgba(54, 185, 204, 0.8)',
                'rgba(133, 135, 150, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(199, 199, 199, 0.8)',
                'rgba(83, 102, 255, 0.8)'
            ],
            borderColor: [
                'rgb(78, 115, 223)',
                'rgb(28, 200, 138)',
                'rgb(255, 193, 7)',
                'rgb(231, 74, 59)',
                'rgb(54, 185, 204)',
                'rgb(133, 135, 150)',
                'rgb(255, 99, 132)',
                'rgb(75, 192, 192)',
                'rgb(153, 102, 255)',
                'rgb(255, 159, 64)',
                'rgb(199, 199, 199)',
                'rgb(83, 102, 255)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            title: {
                display: true,
                text: 'Monthly User Registrations'
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

// Event attendance trends chart
const eventAttendanceCtx = document.getElementById('eventAttendanceChart').getContext('2d');
const eventAttendanceChart = new Chart(eventAttendanceCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($eventAttendanceTrends, 'month')) ?>,
        datasets: [
            {
                label: 'Registrations',
                data: <?= json_encode(array_column($eventAttendanceTrends, 'registrations')) ?>,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.3,
                fill: true,
                yAxisID: 'y'
            },
            {
                label: 'Events',
                data: <?= json_encode(array_column($eventAttendanceTrends, 'events')) ?>,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.3,
                fill: true,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                },
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});

// Enhanced Program enrollment statistics chart with better handling for large datasets
const programEnrollmentCtx = document.getElementById('programEnrollmentChart').getContext('2d');

// Prepare data for the enhanced chart
const programLabels = <?= json_encode(array_column($programEnrollmentData, 'program_name')) ?>;
const programData = {
    total_applications: <?= json_encode(array_column($programEnrollmentData, 'total_applications')) ?>,
    approved: <?= json_encode(array_column($programEnrollmentData, 'approved')) ?>,
    pending: <?= json_encode(array_column($programEnrollmentData, 'pending')) ?>,
    rejected: <?= json_encode(array_column($programEnrollmentData, 'rejected')) ?>,
    completed: <?= json_encode(array_column($programEnrollmentData, 'completed')) ?>
};

// Truncate long program names for better display
const truncatedLabels = programLabels.map(label => {
    return label.length > 25 ? label.substring(0, 25) + '...' : label;
});

const programEnrollmentChart = new Chart(programEnrollmentCtx, {
    type: 'bar',
    data: {
        labels: truncatedLabels,
        datasets: [
            {
                label: 'Total Applications',
                data: programData.total_applications,
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: 'rgb(78, 115, 223)',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false
            },
            {
                label: 'Approved',
                data: programData.approved,
                backgroundColor: 'rgba(28, 200, 138, 0.8)',
                borderColor: 'rgb(28, 200, 138)',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false
            },
            {
                label: 'Pending',
                data: programData.pending,
                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                borderColor: 'rgb(255, 193, 7)',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false
            },
            {
                label: 'Completed',
                data: programData.completed,
                backgroundColor: 'rgba(54, 185, 204, 0.8)',
                borderColor: 'rgb(54, 185, 204)',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false
            }
        ]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                align: 'start',
                labels: {
                    usePointStyle: true,
                    padding: 20
                }
            },
            tooltip: {
                callbacks: {
                    title: function(context) {
                        const index = context[0].dataIndex;
                        return programLabels[index]; // Show full program name in tooltip
                    },
                    afterLabel: function(context) {
                        const index = context.dataIndex;
                        const total = programData.total_applications[index];
                        if (total > 0) {
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return `${percentage}% of total applications`;
                        }
                        return '';
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                stacked: false,
                ticks: {
                    stepSize: 1
                },
                title: {
                    display: true,
                    text: 'Number of Applications'
                }
            },
            y: {
                stacked: false,
                ticks: {
                    maxTicksLimit: 20, // Limit number of programs shown
                    callback: function(value, index, values) {
                        const label = this.getLabelForValue(value);
                        return label.length > 20 ? label.substring(0, 20) + '...' : label;
                    }
                },
                title: {
                    display: true,
                    text: 'Programs'
                }
            }
        },
        layout: {
            padding: {
                left: 10,
                right: 10,
                top: 10,
                bottom: 10
            }
        }
    }
});

// Add click handler for program chart
programEnrollmentChart.canvas.onclick = function(evt) {
    const points = programEnrollmentChart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
    if (points.length) {
        const firstPoint = points[0];
        const programName = programLabels[firstPoint.index];
        // You can add navigation to program details here
        console.log('Clicked on program:', programName);
    }
};

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