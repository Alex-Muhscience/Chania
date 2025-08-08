<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= BASE_URL ?>/admin/public/">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Euroafrique Admin</div>
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
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'users.php' || basename($_SERVER['PHP_SELF']) === 'user_edit.php' ? 'active' : '' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/public/users.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Users</span>
        </a>
    </li>

    <!-- Nav Item - Programs Dropdown -->
    <li class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['programs.php', 'program_add.php', 'program_edit.php', 'program_categories.php', 'schedules.php', 'schedule_add.php', 'schedule_edit.php', 'program_export.php']) ? 'active' : '' ?>">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePrograms" aria-expanded="<?= in_array(basename($_SERVER['PHP_SELF']), ['programs.php', 'program_add.php', 'program_edit.php', 'program_categories.php', 'schedules.php', 'schedule_add.php', 'schedule_edit.php', 'program_export.php']) ? 'true' : 'false' ?>">
            <i class="fas fa-fw fa-graduation-cap"></i>
            <span>Programs</span>
        </a>
        <div id="collapsePrograms" class="collapse <?= in_array(basename($_SERVER['PHP_SELF']), ['programs.php', 'program_add.php', 'program_edit.php', 'program_categories.php', 'schedules.php', 'schedule_add.php', 'schedule_edit.php', 'program_export.php']) ? 'show' : '' ?>" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Program Management:</h6>
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'programs.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/programs.php">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>All Programs
                </a>
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'programs.php' && ($_GET['action'] ?? '') === 'add' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/programs.php?action=add">
                    <i class="fas fa-plus fa-sm fa-fw mr-2 text-gray-400"></i>Add New Program
                </a>
                <div class="collapse-divider"></div>
                <h6 class="collapse-header">Schedule Management:</h6>
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'schedules.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/schedules.php">
                    <i class="fas fa-calendar fa-sm fa-fw mr-2 text-gray-400"></i>All Schedules
                </a>
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'schedule_add.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/schedule_add.php">
                    <i class="fas fa-calendar-plus fa-sm fa-fw mr-2 text-gray-400"></i>Add New Schedule
                </a>
                <div class="collapse-divider"></div>
                <h6 class="collapse-header">Other:</h6>
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'program_categories.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/program_categories.php">
                    <i class="fas fa-tags fa-sm fa-fw mr-2 text-gray-400"></i>Categories
                </a>
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'program_export.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/program_export.php">
                    <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i>Export Programs
                </a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Applications -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'applications.php' || basename($_SERVER['PHP_SELF']) === 'application_view.php' ? 'active' : '' ?>">
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
            <?php
            try {
                $db = (new Database())->connect();
                $stmt = $db->query("SELECT COUNT(*) FROM event_registrations WHERE status = 'registered'");
                $newRegistrations = $stmt->fetchColumn();
                if ($newRegistrations > 0): ?>
                    <span class="notification-badge"><?= $newRegistrations ?></span>
                <?php endif;
            } catch (Exception $e) {
                // Silently handle error
            }
            ?>
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

    <!-- Nav Item - Email Templates -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'email_templates.php' || basename($_SERVER['PHP_SELF']) === 'email_template_edit.php' ? 'active' : '' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/public/email_templates.php">
            <i class="fas fa-fw fa-envelope-square"></i>
            <span>Email Templates</span>
        </a>
    </li>

    <!-- Nav Item - Email Campaigns -->
    <li class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['email_campaigns.php', 'email_campaign_create.php']) ? 'active' : '' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/public/email_campaigns.php">
            <i class="fas fa-fw fa-paper-plane"></i>
            <span>Email Campaigns</span>
        </a>
    </li>

    <!-- Nav Item - SMS Templates -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'sms_templates.php' ? 'active' : '' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/public/sms_templates.php">
            <i class="fas fa-fw fa-sms"></i>
            <span>SMS Templates</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Reports & Analytics
    </div>

    <!-- Nav Item - Reports -->
    <li class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['reports.php', 'report_builder.php', 'report_view.php', 'data_export.php']) ? 'active' : '' ?>">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseReports">
            <i class="fas fa-fw fa-chart-bar"></i>
            <span>Reports</span>
        </a>
        <div id="collapseReports" class="collapse <?= in_array(basename($_SERVER['PHP_SELF']), ['reports.php', 'report_builder.php', 'report_view.php', 'data_export.php']) ? 'show' : '' ?>">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/reports.php">All Reports</a>
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'report_builder.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/report_builder.php">Report Builder</a>
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'data_export.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/data_export.php">Data Export</a>
            </div>
        </div>
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

    <!-- Nav Item - Achievements -->
    <li class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['achievements.php', 'achievement_add.php', 'achievement_edit.php']) ? 'active' : '' ?>">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseAchievements" aria-expanded="<?= in_array(basename($_SERVER['PHP_SELF']), ['achievements.php', 'achievement_add.php', 'achievement_edit.php']) ? 'true' : 'false' ?>">
            <i class="fas fa-fw fa-trophy"></i>
            <span>Achievements</span>
        </a>
        <div id="collapseAchievements" class="collapse <?= in_array(basename($_SERVER['PHP_SELF']), ['achievements.php', 'achievement_add.php', 'achievement_edit.php']) ? 'show' : '' ?>" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Achievement Management:</h6>
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'achievements.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/achievements.php">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>All Achievements
                </a>
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'achievement_add.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/achievements.php?action=add">
                    <i class="fas fa-plus fa-sm fa-fw mr-2 text-gray-400"></i>Add New Achievement
                </a>
            </div>
        </div>
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

    <!-- Nav Item - FAQs -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'faqs.php' || basename($_SERVER['PHP_SELF']) === 'faq_edit.php' ? 'active' : '' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/public/faqs.php">
            <i class="fas fa-fw fa-question-circle"></i>
            <span>FAQs</span>
        </a>
    </li>

    <!-- Nav Item - Blog -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'blog.php' || basename($_SERVER['PHP_SELF']) === 'blog_edit.php' ? 'active' : '' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/public/blog.php">
            <i class="fas fa-fw fa-blog"></i>
            <span>Blog</span>
        </a>
    </li>


    <!-- Nav Item - Files -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'files.php' ? 'active' : '' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/public/files.php">
            <i class="fas fa-fw fa-folder"></i>
            <span>File Manager</span>
        </a>
    </li>

    <!-- Nav Item - Impact Blogs -->
    <li class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['impact_blogs.php', 'impact_blog_add.php', 'impact_blog_edit.php']) ? 'active' : '' ?>">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseImpactBlogs" aria-expanded="<?= in_array(basename($_SERVER['PHP_SELF']), ['impact_blogs.php', 'impact_blog_add.php', 'impact_blog_edit.php']) ? 'true' : 'false' ?>">
            <i class="fas fa-fw fa-trophy"></i>
            <span>Impact Stories</span>
        </a>
        <div id="collapseImpactBlogs" class="collapse <?= in_array(basename($_SERVER['PHP_SELF']), ['impact_blogs.php', 'impact_blog_add.php', 'impact_blog_edit.php']) ? 'show' : '' ?>" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Impact Stories:</h6>
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'impact_blogs.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/impact_blogs.php">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>All Stories
                </a>
                <a class="collapse-item <?= basename($_SERVER['PHP_SELF']) === 'impact_blog_add.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/public/impact_blog_add.php">
                    <i class="fas fa-plus fa-sm fa-fw mr-2 text-gray-400"></i>Add New Story
                </a>
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        System
    </div>

    <!-- Nav Item - Roles -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'roles.php' || basename($_SERVER['PHP_SELF']) === 'role_edit.php' ? 'active' : '' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/public/roles.php">
            <i class="fas fa-fw fa-user-tag"></i>
            <span>Roles</span>
        </a>
    </li>

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

    <!-- Nav Item - Security Logs (Admin Only) -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'security_logs.php' ? 'active' : '' ?>">
            <a class="nav-link" href="<?= BASE_URL ?>/admin/public/security_logs.php">
                <i class="fas fa-fw fa-shield-alt"></i>
                <span>Security Logs</span>
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
