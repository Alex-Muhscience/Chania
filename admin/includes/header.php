<?php
// Fix include path to ensure config.php is included correctly regardless of execution context
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

if (session_status() === PHP_SESSION_NONE) {
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

    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>DEN Admin</title>

    <!-- Custom fonts for this template -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template -->
   <!--<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.min.css"
      integrity="sha512-cQqPLpOc8aZx7aK0eX7QnWbF8x0l8y4cX1b3r2T+8R3g6p5y2l5l5l5l5l5l5l5l5l5l5l5l5l5l5l5l5l5l5l5l5l5l5"
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
            top: -5px;
            right: -5px;
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
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
            color: #ecf0f1 !important;
            font-size: 0.85rem;
        }

        .collapse-item:hover {
            color: #3498db !important;
            background-color: rgba(52, 152, 219, 0.1);
        }

        .collapse-item.active {
            color: #3498db !important;
            background-color: rgba(52, 152, 219, 0.2);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }
        }
    </style>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= BASE_URL ?>/admin/">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-digital-tachograph"></i>
                </div>
                <div class="sidebar-brand-text mx-3">DEN Admin</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                User Management
            </div>

            <!-- Nav Item - Users -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/users.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Users</span>
                    <?php
                    try {
                        $db = (new Database())->connect();
                        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND is_active = 1");
                        $newUsers = $stmt->fetchColumn();
                        if ($newUsers > 0): ?>
                            <span class="notification-badge"><?= $newUsers ?></span>
                        <?php endif;
                    } catch (Exception $e) {
                        // Silently handle error
                    }
                    ?>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Programs & Applications
            </div>

            <!-- Nav Item - Programs -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'programs.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/programs.php">
                    <i class="fas fa-fw fa-graduation-cap"></i>
                    <span>Programs</span>
                </a>
            </li>

            <!-- Nav Item - Applications -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'applications.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/applications.php">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Applications</span>
                    <?php
                    try {
                        $db = (new Database())->connect();
                        $stmt = $db->query("SELECT COUNT(*) FROM applications WHERE status = 'pending'");
                        $pendingApps = $stmt->fetchColumn();
                        if ($pendingApps > 0): ?>
                            <span class="notification-badge"><?= $pendingApps ?></span>
                        <?php endif;
                    } catch (Exception $e) {
                        // Silently handle error
                    }
                    ?>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Events & Communication
            </div>

            <!-- Nav Item - Events -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/events.php">
                    <i class="fas fa-fw fa-calendar-alt"></i>
                    <span>Events</span>
                </a>
            </li>

            <!-- Nav Item - Event Registrations -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'event_registrations.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/event_registrations.php">
                    <i class="fas fa-fw fa-user-plus"></i>
                    <span>Event Registrations</span>
                </a>
            </li>

            <!-- Nav Item - Contacts -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'contacts.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/contacts.php">
                    <i class="fas fa-fw fa-envelope"></i>
                    <span>Contacts</span>
                    <?php
                    try {
                        $db = (new Database())->connect();
                        $stmt = $db->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0");
                        $unreadContacts = $stmt->fetchColumn();
                        if ($unreadContacts > 0): ?>
                            <span class="notification-badge"><?= $unreadContacts ?></span>
                        <?php endif;
                    } catch (Exception $e) {
                        // Silently handle error
                    }
                    ?>
                </a>
            </li>

            <!-- Nav Item - Newsletter -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'newsletter.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/newsletter.php">
                    <i class="fas fa-fw fa-envelope-open"></i>
                    <span>Newsletter</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Content Management
            </div>

            <!-- Nav Item - Testimonials -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'testimonials.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/testimonials.php">
                    <i class="fas fa-fw fa-quote-left"></i>
                    <span>Testimonials</span>
                </a>
            </li>

            <!-- Nav Item - Partners -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'partners.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/partners.php">
                    <i class="fas fa-fw fa-handshake"></i>
                    <span>Partners</span>
                </a>
            </li>

            <!-- Nav Item - Team Members -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'team_members.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/team_members.php">
                    <i class="fas fa-fw fa-users-cog"></i>
                    <span>Team Members</span>
                </a>
            </li>

            <!-- Nav Item - Pages -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'pages.php' || basename($_SERVER['PHP_SELF']) === 'page_edit.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/pages.php">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Pages</span>
                </a>
            </li>

            <!-- Nav Item - Media Library -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'media.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/media.php">
                    <i class="fas fa-fw fa-photo-video"></i>
                    <span>Media Library</span>
                </a>
            </li>

            <!-- Nav Item - Files -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'files.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/files.php">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>File Manager</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                System
            </div>

            <!-- Nav Item - Settings -->
            <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/public/settings.php">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>

            <!-- Nav Item - System Monitor (Admin Only) -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'system_monitor.php' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/public/system_monitor.php">
                        <i class="fas fa-fw fa-chart-line"></i>
                        <span>System Monitor</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Nav Item - Activity Logs (Admin Only) -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'admin_logs.php' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/public/admin_logs.php">
                        <i class="fas fa-fw fa-clipboard-list"></i>
                        <span>Activity Logs</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
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

                        <!-- Nav Item - Notifications -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell fa-fw"></i>
                                <?php
                                try {
                                    $db = (new Database())->connect();
                                    $stmt = $db->query("
                                        SELECT 
                                            (SELECT COUNT(*) FROM applications WHERE status = 'pending') +
                                            (SELECT COUNT(*) FROM contacts WHERE is_read = 0) as total_notifications
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
                                        WHERE status = 'pending'
                                        ORDER BY submitted_at DESC
                                        LIMIT 3
                                    ");
                                    $notifications = $stmt->fetchAll();

                                    $stmt = $db->query("
                                        SELECT 'contact' as type, id, subject as title, submitted_at as created_at
                                        FROM contacts 
                                        WHERE is_read = 0
                                        ORDER BY submitted_at DESC
                                        LIMIT 3
                                    ");
                                    $contactNotifications = $stmt->fetchAll();

                                    $notifications = array_merge($notifications, $contactNotifications);

                                    if (empty($notifications)): ?>
                                        <div class="dropdown-item text-center small text-gray-500">No new notifications</div>
                                    <?php else:
                                        foreach ($notifications as $notification): ?>
                                            <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>/admin/public/<?= $notification['type'] === 'application' ? 'applications' : 'contacts' ?>.php">
                                                <div class="mr-3">
                                                    <div class="icon-circle bg-<?= $notification['type'] === 'application' ? 'primary' : 'success' ?>">
                                                        <i class="fas fa-<?= $notification['type'] === 'application' ? 'file-alt' : 'envelope' ?> text-white"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="small text-gray-500"><?= date('M j, Y', strtotime($notification['created_at'])) ?></div>
                                                    <span class="font-weight-bold"><?= Utilities::truncate($notification['title'], 30) ?></span>
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
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['warning'])): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['warning']) ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['warning']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['info'])): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['info']) ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['info']); ?>
                    <?php endif; ?>