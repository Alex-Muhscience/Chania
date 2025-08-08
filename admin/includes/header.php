<?php
// Fix include path to ensure config.php is included correctly regardless of execution context
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

// Configure session settings before starting session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Digital Empowerment Network - Admin Panel">
    <meta name="author" content="Digital Empowerment Network">

    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Euroafrique Admin</title>

    <!-- Custom fonts for this template -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template -->
   <!-- <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.min.css"
        integrity="sha512-cQqPLpOc8aZx7aK0eX7QnWbF8x0l8y4cX1b3r2T+8R3g6p5y2l5l5l5l5l5l5l5l5l5l5l5l5l5l5l5"
        crossorigin="anonymous" referrerpolicy="no-referrer" /> -->

    <link href="<?= BASE_URL ?>/admin/public/assets/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


    <!-- Custom Admin Styles -->
    <style>
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        }

        .sidebar .nav-item .nav-link {
            color: #ecf0f1;
            transition: all 0.3s ease;
            position: relative; /* Enable absolute positioning for badges */
        }

        .sidebar .nav-item .nav-link:hover {
            color: #3498db;
            background-color: rgba(52, 152, 219, 0.1);
        }

        .sidebar .nav-item .nav-link.active {
            background-color: #3498db;
            color: white;
        }

        .sidebar-heading {
            color: #bdc3c7;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .topbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e3e6f0;
        }

        .navbar-nav .nav-item .nav-link {
            color: #5a5c69;
        }

        .navbar-nav .nav-item .nav-link:hover {
            color: #3498db;
        }

        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .border-left-primary {
            border-left: 0.25rem solid #3498db !important;
        }

        .border-left-success {
            border-left: 0.25rem solid #27ae60 !important;
        }

        .border-left-info {
            border-left: 0.25rem solid #17a2b8 !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid #f39c12 !important;
        }

        .border-left-danger {
            border-left: 0.25rem solid #e74c3c !important;
        }

        .border-left-dark {
            border-left: 0.25rem solid #34495e !important;
        }

        .border-left-secondary {
            border-left: 0.25rem solid #95a5a6 !important;
        }

        .notification-badge {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            border: 2px solid #fff;
            z-index: 10;
            animation: pulse-badge 2s infinite;
        }
        
        @keyframes pulse-badge {
            0% {
                transform: translateY(-50%) scale(1);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }
            50% {
                transform: translateY(-50%) scale(1.1);
                box-shadow: 0 4px 8px rgba(231, 76, 60, 0.4);
            }
            100% {
                transform: translateY(-50%) scale(1);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }
        }

        .activity-feed .small {
            line-height: 1.2;
        }

        .calendar-icon {
            font-size: 10px;
        }

        .avatar-circle {
            font-size: 12px;
            font-weight: 600;
        }

        .sidebar-brand {
            background: rgba(0, 0, 0, 0.1);
        }

        .sidebar-brand:hover {
            background: rgba(0, 0, 0, 0.2);
        }

        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .collapse-item {
            color: #2c3e50 !important; /* Dark color for better visibility on white background */
            font-size: 0.85rem;
            padding: .5rem 1.5rem;
            text-decoration: none;
        }

        .collapse-item:hover {
            color: #3498db !important; /* Blue color on hover */
            background-color: #f8f9fc;
            text-decoration: none;
        }

        .collapse-item.active {
            color: #ffffff !important; /* White text on active */
            font-weight: 700;
            background-color: #3498db; /* Blue background for active */
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background-color: #1a1a1a;
            color: #e0e0e0;
        }

        body.dark-mode .topbar {
            background-color: #2d2d2d;
            border-bottom: 1px solid #404040;
        }

        body.dark-mode .card {
            background-color: #2d2d2d;
            color: #e0e0e0;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(0, 0, 0, 0.15);
        }

        body.dark-mode .card-header {
            background-color: #404040;
            border-bottom: 1px solid #555;
            color: #e0e0e0;
        }

        body.dark-mode .table {
            background-color: #2d2d2d;
            color: #e0e0e0;
        }

        body.dark-mode .table th,
        body.dark-mode .table td {
            border-color: #404040;
        }

        body.dark-mode .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.05);
        }

        body.dark-mode .form-control {
            background-color: #404040;
            border-color: #555;
            color: #e0e0e0;
        }

        body.dark-mode .form-control:focus {
            background-color: #404040;
            border-color: #3498db;
            color: #e0e0e0;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        body.dark-mode .btn-outline-primary {
            color: #3498db;
            border-color: #3498db;
        }

        body.dark-mode .btn-outline-primary:hover {
            background-color: #3498db;
            border-color: #3498db;
            color: white;
        }

        body.dark-mode .alert {
            border: none;
        }

        body.dark-mode .alert-success {
            background-color: #27ae60;
            color: white;
        }

        body.dark-mode .alert-danger {
            background-color: #e74c3c;
            color: white;
        }

        body.dark-mode .alert-warning {
            background-color: #f39c12;
            color: white;
        }

        body.dark-mode .alert-info {
            background-color: #17a2b8;
            color: white;
        }

        body.dark-mode .dropdown-menu {
            background-color: #2d2d2d;
            border-color: #404040;
        }

        body.dark-mode .dropdown-item {
            color: #e0e0e0;
        }

        body.dark-mode .dropdown-item:hover {
            background-color: #404040;
            color: #e0e0e0;
        }

        body.dark-mode .breadcrumb {
            background-color: #404040;
        }

        body.dark-mode .breadcrumb-item + .breadcrumb-item::before {
            color: #e0e0e0;
        }

        body.dark-mode .text-gray-800 {
            color: #e0e0e0 !important;
        }

        body.dark-mode .text-gray-600 {
            color: #b0b0b0 !important;
        }

        body.dark-mode .text-gray-500 {
            color: #888 !important;
        }

        /* Scroll to top button */
        .scroll-to-top {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            display: none;
            width: 2.75rem;
            height: 2.75rem;
            text-align: center;
            color: white;
            background: rgba(90, 92, 105, 0.5);
            line-height: 46px;
            z-index: 1000;
        }

        .scroll-to-top:focus,
        .scroll-to-top:hover {
            color: white;
        }

        .scroll-to-top:hover {
            background: #5a5c69;
        }

        .scroll-to-top.rounded {
            border-radius: 100%;
        }

        /* Badge styles */
        .badge-counter {
            color: #fff;
            background-color: #e74c3c;
            border-radius: 1rem;
            padding: .25rem .5rem;
            font-size: .75rem;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
        }

        /* Icon circle for notifications */
        .icon-circle {
            height: 2.5rem;
            width: 2.5rem;
            border-radius: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Mobile Responsive Tables */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .table {
                min-width: 600px;
            }
            
            .table th,
            .table td {
                padding: 0.5rem;
                font-size: 0.875rem;
            }
            
            .btn-group-vertical {
                flex-direction: column;
            }
            
            .btn-group-vertical .btn {
                margin-bottom: 0.25rem;
            }
        }

        @media (max-width: 576px) {
            .table th,
            .table td {
                padding: 0.25rem;
                font-size: 0.8rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php require_once __DIR__ . '/sidebar.php'; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search -->
                    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search" method="GET" action="<?= BASE_URL ?>/admin/public/search.php">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control bg-light border-0 small" placeholder="Search users, applications, events..." aria-label="Search" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end p-3 shadow animated--grow-in">
                                <form class="form-inline me-auto w-100 navbar-search" method="GET" action="<?= BASE_URL ?>/admin/public/search.php">
                                    <div class="input-group">
                                        <input type="text" name="q" class="form-control bg-light border-0 small" placeholder="Search users, applications, events..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <!-- Nav Item - Quick Actions -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="quickActionsDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-plus fa-fw"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" style="right: 0; left: auto; margin-right: 10px;">
                                <h6 class="dropdown-header">Quick Actions:</h6>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/programs.php?action=add">
                                    <i class="fas fa-graduation-cap fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Add Program
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/events.php?action=add">
                                    <i class="fas fa-calendar-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Add Event
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/users.php?action=add">
                                    <i class="fas fa-user-plus fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Add User
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/partners.php?action=add">
                                    <i class="fas fa-handshake fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Add Partner
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/team_members.php?action=add">
                                    <i class="fas fa-users-cog fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Add Team Member
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/system_monitor.php">
                                    <i class="fas fa-chart-line fa-sm fa-fw mr-2 text-gray-400"></i>
                                    System Monitor
                                </a>
                            </div>
                        </li>

                        <!-- Nav Item - Dark Mode Toggle -->
                        <li class="nav-item no-arrow mx-1">
                            <button class="btn btn-link nav-link" id="darkModeToggle" title="Toggle Dark Mode">
                                <i class="fas fa-moon fa-fw" id="darkModeIcon"></i>
                            </button>
                        </li>

                        <!-- Nav Item - Notifications -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell fa-fw"></i>
                                <?php
                                try {
                                    $db = (new Database())->connect();
                                    $stmt = $db->query("
                                        SELECT 
                                            (SELECT COUNT(*) FROM applications WHERE status = 'pending' AND deleted_at IS NULL) +
                                            (SELECT COUNT(*) FROM contacts WHERE is_read = 0) +
                                            (SELECT COUNT(*) FROM event_registrations WHERE status = 'registered') +
                                            (SELECT COUNT(*) FROM newsletter_subscribers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status = 'subscribed') as total_notifications
                                    ");
                                    $totalNotifications = $stmt->fetchColumn();
                                    if ($totalNotifications > 0): ?>
                                        <span class="badge badge-danger badge-counter"><?= $totalNotifications > 9 ? '9+' : $totalNotifications ?></span>
                                    <?php endif;
                                } catch (Exception $e) {
                                    // Silently handle error
                                }
                                ?>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in" style="right: 0; left: auto; margin-right: 10px;">
                                <h6 class="dropdown-header">
                                    Notifications
                                </h6>
                                <?php
                                try {
                                    $db = (new Database())->connect();

                                    // Get recent notifications
                                    $stmt = $db->query("
                                        SELECT 'application' as type, id, CONCAT(first_name, ' ', last_name) as title, submitted_at as created_at
                                        FROM applications 
                                        WHERE status = 'pending' AND deleted_at IS NULL
                                        ORDER BY submitted_at DESC
                                        LIMIT 2
                                    ");
                                    $notifications = $stmt->fetchAll();

                                    $stmt = $db->query("
                                        SELECT 'contact' as type, id, subject as title, submitted_at as created_at
                                        FROM contacts 
                                        WHERE is_read = 0
                                        ORDER BY submitted_at DESC
                                        LIMIT 2
                                    ");
                                    $contactNotifications = $stmt->fetchAll();

                                    // Get recent event registrations
                                    $stmt = $db->query("
                                        SELECT 'event' as type, er.id, CONCAT(er.first_name, ' ', er.last_name, ' - ', e.title) as title, er.registered_at as created_at
                                        FROM event_registrations er
                                        LEFT JOIN events e ON er.event_id = e.id
                                        WHERE er.status = 'registered'
                                        ORDER BY er.registered_at DESC
                                        LIMIT 2
                                    ");
                                    $eventNotifications = $stmt->fetchAll();

                                    // Get recent newsletter subscriptions (last 24 hours)
                                    $stmt = $db->query("
                                        SELECT 'newsletter' as type, id, email as title, created_at
                                        FROM newsletter_subscribers 
                                        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status = 'subscribed'
                                        ORDER BY created_at DESC
                                        LIMIT 2
                                    ");
                                    $newsletterNotifications = $stmt->fetchAll();

                                    $notifications = array_merge($notifications, $contactNotifications, $eventNotifications, $newsletterNotifications);
                                    
                                    // Sort by created_at desc and limit to 5 most recent
                                    usort($notifications, function($a, $b) {
                                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                                    });
                                    $notifications = array_slice($notifications, 0, 5);

                                    if (empty($notifications)): ?>
                                        <div class="dropdown-item text-center small text-gray-500">No new notifications</div>
                                    <?php else:
                                        foreach ($notifications as $notification): ?>
                                            <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>/admin/public/<?= $notification['type'] === 'application' ? 'applications' : ($notification['type'] === 'contact' ? 'contacts' : ($notification['type'] === 'event' ? 'event_registrations' : 'newsletter')) ?>.php">
                                                <div class="mr-3">
                                                    <div class="icon-circle bg-<?= $notification['type'] === 'application' ? 'primary' : ($notification['type'] === 'contact' ? 'success' : ($notification['type'] === 'event' ? 'warning' : 'secondary')) ?>">
                                                        <i class="fas fa-<?= $notification['type'] === 'application' ? 'file-alt' : ($notification['type'] === 'contact' ? 'envelope' : ($notification['type'] === 'event' ? 'calendar-check' : 'envelope-open')) ?> text-white"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="small text-gray-500"><?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?></div>
                                                    <span class="font-weight-bold"><?= $notification['type'] === 'newsletter' ? 'New subscriber: ' : ($notification['type'] === 'event' ? 'Event registration: ' : '') ?><?= Utilities::truncate($notification['title'], 30) ?></span>
                                                </div>
                                            </a>
                                        <?php endforeach;
                                    endif;
                                } catch (Exception $e) {
                                    echo '<div class="dropdown-item text-center small text-gray-500">Error loading notifications</div>';
                                }
                                ?>
                                <a class="dropdown-item text-center small text-gray-500" href="<?= BASE_URL ?>/admin/public/admin_logs.php">Show All Activity</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                                    <br><small class="text-muted"><?= ucfirst($_SESSION['role'] ?? 'User') ?></small>
                                </span>
                                <?php if (!empty($_SESSION['avatar_path'])): ?>
                                    <img class="img-profile rounded-circle" src="<?= BASE_URL ?>/<?= htmlspecialchars($_SESSION['avatar_path']) ?>" style="width: 40px; height: 40px; object-fit: cover;" alt="avatar">
                                <?php else: ?>
                                    <div class="img-profile rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 16px;">
                                        <?= strtoupper(substr($_SESSION['username'] ?? 'User', 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" style="right: 0; left: auto; margin-right: 10px;">
                                <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/2fa_setup.php">
                                    <i class="fas fa-shield-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Two-Factor Auth
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/settings.php">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/admin_logs.php">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/logout.php" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Breadcrumb -->
                    <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                                    <?php if ($index === count($breadcrumbs) - 1): ?>
                                        <li class="breadcrumb-item active" aria-current="page">
                                            <?= htmlspecialchars($breadcrumb['title']) ?>
                                        </li>
                                    <?php else: ?>
                                        <li class="breadcrumb-item">
                                            <a href="<?= $breadcrumb['url'] ?>"><?= htmlspecialchars($breadcrumb['title']) ?></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ol>
                        </nav>
                    <?php endif; ?>

                    <!-- Flash Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['warning'])): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['warning']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['warning']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['info'])): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['info']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['info']); ?>
                    <?php endif; ?>
