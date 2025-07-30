<?php
require_once __DIR__ . '/shared/Core/Database.php';
require_once __DIR__ . '/admin/includes/config.php';

try {
    $db = (new Database())->connect();
    echo "Database connected successfully\n";

    // Test each statistics query individually
    $statQueries = [
        'total_users' => "SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL",
        'admin_users' => "SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL AND role = 'admin'",
        'new_users_month' => "SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
        'total_programs' => "SELECT COUNT(*) as count FROM programs WHERE deleted_at IS NULL",
        'active_programs' => "SELECT COUNT(*) as count FROM programs WHERE deleted_at IS NULL AND is_active = 1",
        'total_applications' => "SELECT COUNT(*) as count FROM applications WHERE deleted_at IS NULL",
        'pending_applications' => "SELECT COUNT(*) as count FROM applications WHERE deleted_at IS NULL AND status = 'pending'",
        'approved_applications' => "SELECT COUNT(*) as count FROM applications WHERE deleted_at IS NULL AND status = 'approved'",
        'new_applications_month' => "SELECT COUNT(*) as count FROM applications WHERE deleted_at IS NULL AND submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
        'total_events' => "SELECT COUNT(*) as count FROM events WHERE deleted_at IS NULL",
        'upcoming_events' => "SELECT COUNT(*) as count FROM events WHERE deleted_at IS NULL AND event_date >= CURDATE()",
        'total_registrations' => "SELECT COUNT(*) as count FROM event_registrations",
        'confirmed_registrations' => "SELECT COUNT(*) as count FROM event_registrations WHERE status = 'confirmed'",
        'total_contacts' => "SELECT COUNT(*) as count FROM contacts WHERE deleted_at IS NULL",
        'unread_contacts' => "SELECT COUNT(*) as count FROM contacts WHERE deleted_at IS NULL AND is_read = 0",
        'new_contacts_month' => "SELECT COUNT(*) as count FROM contacts WHERE deleted_at IS NULL AND submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
        'total_testimonials' => "SELECT COUNT(*) as count FROM testimonials WHERE deleted_at IS NULL",
        'featured_testimonials' => "SELECT COUNT(*) as count FROM testimonials WHERE deleted_at IS NULL AND is_featured = 1",
        'approved_testimonials' => "SELECT COUNT(*) as count FROM testimonials WHERE deleted_at IS NULL AND is_approved = 1",
        'new_testimonials_month' => "SELECT COUNT(*) as count FROM testimonials WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
        'total_partners' => "SELECT COUNT(*) as count FROM partners WHERE deleted_at IS NULL",
        'active_partners' => "SELECT COUNT(*) as count FROM partners WHERE deleted_at IS NULL AND is_active = 1",
        'new_partners_month' => "SELECT COUNT(*) as count FROM partners WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
        'total_team_members' => "SELECT COUNT(*) as count FROM team_members WHERE deleted_at IS NULL",
        'active_team_members' => "SELECT COUNT(*) as count FROM team_members WHERE deleted_at IS NULL AND is_active = 1",
        'new_team_members_month' => "SELECT COUNT(*) as count FROM team_members WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
        'newsletter_subscribers' => "SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'subscribed'",
        'new_subscribers_month' => "SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'subscribed' AND subscribed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
        'total_files' => "SELECT COUNT(*) as count FROM file_uploads WHERE deleted_at IS NULL",
        'total_file_size_mb' => "SELECT ROUND(SUM(file_size) / 1024 / 1024, 2) as count FROM file_uploads WHERE deleted_at IS NULL",
        'activities_today' => "SELECT COUNT(*) as count FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
        'activities_week' => "SELECT COUNT(*) as count FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    ];
    
    foreach ($statQueries as $statName => $query) {
        try {
            echo "Executing query for $statName: $query\n";
            $stmt = $db->query($query);
            $result = $stmt->fetch();
            $count = $result['count'] ?? 0;
            echo "Successfully executed $statName: $count\n";
        } catch (Exception $e) {
            echo "ERROR in $statName query: " . $e->getMessage() . "\n";
            echo "Failed query: $query\n";
        }
    }

    // Test other queries
    echo "\n=== Testing Recent Activities Query ===\n";
    try {
        $stmt = $db->query("
            SELECT l.*, u.full_name as user_name, u.profile_image as avatar_path
            FROM admin_logs l
            JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT 10
        ");
        $recentActivities = $stmt->fetchAll();
        echo "Recent activities query successful. Found " . count($recentActivities) . " activities.\n";
    } catch (Exception $e) {
        echo "ERROR in recent activities query: " . $e->getMessage() . "\n";
    }

    echo "\n=== Testing Recent Applications Query ===\n";
    try {
        $stmt = $db->query("
            SELECT a.*, p.title as program_title
            FROM applications a
            JOIN programs p ON a.program_id = p.id
            WHERE a.deleted_at IS NULL
            ORDER BY a.submitted_at DESC
            LIMIT 5
        ");
        $recentApplications = $stmt->fetchAll();
        echo "Recent applications query successful. Found " . count($recentApplications) . " applications.\n";
    } catch (Exception $e) {
        echo "ERROR in recent applications query: " . $e->getMessage() . "\n";
    }

    echo "\n=== Testing Recent Contacts Query ===\n";
    try {
        $stmt = $db->query("
            SELECT *
            FROM contacts
            WHERE deleted_at IS NULL
            ORDER BY submitted_at DESC
            LIMIT 5
        ");
        $recentContacts = $stmt->fetchAll();
        echo "Recent contacts query successful. Found " . count($recentContacts) . " contacts.\n";
    } catch (Exception $e) {
        echo "ERROR in recent contacts query: " . $e->getMessage() . "\n";
    }

    echo "\n=== Testing Upcoming Events Query ===\n";
    try {
        $stmt = $db->query("
            SELECT e.*, COUNT(er.id) as registration_count
            FROM events e
            LEFT JOIN event_registrations er ON e.id = er.event_id
            WHERE e.event_date >= CURDATE() AND e.is_active = 1 AND e.deleted_at IS NULL
            GROUP BY e.id, event_date
            ORDER BY e.event_date ASC
            LIMIT 5
        ");
        $upcomingEvents = $stmt->fetchAll();
        echo "Upcoming events query successful. Found " . count($upcomingEvents) . " events.\n";
    } catch (Exception $e) {
        echo "ERROR in upcoming events query: " . $e->getMessage() . "\n";
    }

    echo "\n=== Testing Other Complex Queries ===\n";
    $complexQueries = [
        'application_trends' => "
            SELECT 
                DATE(submitted_at) as date,
                COUNT(*) as count
            FROM applications
            WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND deleted_at IS NULL
            GROUP BY DATE(submitted_at)
            ORDER BY date ASC
        ",
        'application_status_distribution' => "
            SELECT 
                status,
                COUNT(*) as count
            FROM applications
            WHERE deleted_at IS NULL
            GROUP BY status
        ",
        'monthly_user_registrations' => "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            AND deleted_at IS NULL
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        "
    ];

    foreach ($complexQueries as $queryName => $query) {
        try {
            echo "Testing $queryName...\n";
            $stmt = $db->query($query);
            $result = $stmt->fetchAll();
            echo "$queryName successful. Found " . count($result) . " results.\n";
        } catch (Exception $e) {
            echo "ERROR in $queryName query: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== Testing Additional Dashboard Queries ===\n";
    $additionalQueries = [
        'user_activity_insights' => "
            SELECT 
                COUNT(DISTINCT DATE(created_at)) as active_days,
                AVG(daily_count) as avg_daily_activity,
                MAX(daily_count) as peak_daily_activity
            FROM (
                SELECT DATE(created_at) as date, COUNT(*) as daily_count
                FROM admin_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
            ) as daily_stats
        ",
        'storage_usage_breakdown' => "
            SELECT 
                COALESCE(SUM(CASE WHEN file_type LIKE 'image%' THEN file_size END), 0) / 1024 / 1024 as images_mb,
                COALESCE(SUM(CASE WHEN file_type LIKE 'application%' THEN file_size END), 0) / 1024 / 1024 as documents_mb,
                COALESCE(SUM(CASE WHEN file_type NOT LIKE 'image%' AND file_type NOT LIKE 'application%' THEN file_size END), 0) / 1024 / 1024 as other_mb,
                COUNT(CASE WHEN file_type LIKE 'image%' THEN 1 END) as image_count,
                COUNT(CASE WHEN file_type LIKE 'application%' THEN 1 END) as document_count,
                COUNT(CASE WHEN file_type NOT LIKE 'image%' AND file_type NOT LIKE 'application%' THEN 1 END) as other_count
            FROM file_uploads 
            WHERE deleted_at IS NULL
        ",
        'performance_metrics' => "
            SELECT 
                COUNT(*) as total_queries_today,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as queries_last_hour
            FROM admin_logs 
            WHERE DATE(created_at) = CURDATE()
        ",
        'newsletter_engagement_stats' => "
            SELECT 
                COUNT(CASE WHEN status = 'subscribed' THEN 1 END) as subscribed_count,
                COUNT(CASE WHEN status = 'unsubscribed' THEN 1 END) as unsubscribed_count,
                COUNT(CASE WHEN status = 'subscribed' AND subscribed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_this_week,
                ROUND(
                    (COUNT(CASE WHEN status = 'subscribed' THEN 1 END) * 100.0) / 
                    NULLIF(COUNT(*), 0), 1
                ) as subscription_rate
            FROM newsletter_subscribers
        "
    ];

    foreach ($additionalQueries as $queryName => $query) {
        try {
            echo "Testing $queryName...\n";
            $stmt = $db->query($query);
            $result = $stmt->fetch();
            echo "$queryName successful.\n";
        } catch (Exception $e) {
            echo "ERROR in $queryName query: " . $e->getMessage() . "\n";
            echo "Failed query: " . trim($query) . "\n";
        }
    }

} catch (Exception $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
}
?>
