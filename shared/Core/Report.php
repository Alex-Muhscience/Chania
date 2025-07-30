<?php
require_once __DIR__ . '/../../shared/Core/Database.php';

class Report {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get all predefined reports
    public function getPredefinedReports() {
        return [
            [
                'name' => 'User Registration Report',
                'description' => 'Track user registrations over time with demographic breakdowns.',
                'type' => 'users',
                'icon' => 'fa-users',
                'url' => 'report_users.php'
            ],
            [
                'name' => 'Application Analytics',
                'description' => 'Analyze program applications, acceptance rates, and trends.',
                'type' => 'applications',
                'icon' => 'fa-file-alt',
                'url' => 'report_applications.php'
            ],
            [
                'name' => 'Program Performance',
                'description' => 'Evaluate program effectiveness, completion rates, and feedback.',
                'type' => 'programs',
                'icon' => 'fa-graduation-cap',
                'url' => 'report_programs.php'
            ],
            [
                'name' => 'Event Engagement',
                'description' => 'Monitor event attendance, registrations, and participant feedback.',
                'type' => 'events',
                'icon' => 'fa-calendar-alt',
                'url' => 'report_events.php'
            ],
            [
                'name' => 'Email Campaign Performance',
                'description' => 'Track email open rates, click-through rates, and conversions.',
                'type' => 'email',
                'icon' => 'fa-envelope',
                'url' => 'report_email.php'
            ],
            [
                'name' => 'SMS Campaign Analytics',
                'description' => 'Monitor SMS delivery rates, response rates, and engagement.',
                'type' => 'sms',
                'icon' => 'fa-sms',
                'url' => 'report_sms.php'
            ]
        ];
    }
    
    // Get all custom reports for a user
    public function getCustomReports($userId) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.username as created_by_name
            FROM reports r
            LEFT JOIN users u ON r.created_by = u.id
            WHERE r.is_public = 1 OR r.created_by = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // Get quick stats for the dashboard
    public function getQuickStats() {
        $stats = [];
        // Users stats
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetchColumn();
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['new_users_30d'] = $stmt->fetchColumn();
        // Applications stats
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM applications");
        $stats['total_applications'] = $stmt->fetchColumn();
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM applications WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['new_applications_30d'] = $stmt->fetchColumn();
        // Programs stats
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM programs WHERE is_active = 1");
        $stats['active_programs'] = $stmt->fetchColumn();
        // Events stats
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM events WHERE event_date >= CURDATE()");
        $stats['upcoming_events'] = $stmt->fetchColumn();
        return $stats;
    }
}
?>
