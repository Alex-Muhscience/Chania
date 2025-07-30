<?php
require_once 'shared/Core/Database.php';
require_once 'shared/Core/Utilities.php';
require_once 'admin/includes/config.php';

echo "<h2>Testing System Monitor Database Queries</h2>\n";

try {
    $db = (new Database())->connect();
    
    // Test the main database statistics query from system_monitor.php
    echo "<h3>Database Statistics Query Test:</h3>\n";
    $stmt = $db->query("
        SELECT 
            (SELECT COUNT(*) FROM users WHERE is_active = 1) as total_users,
            (SELECT COUNT(*) FROM programs WHERE deleted_at IS NULL) as total_programs,
            (SELECT COUNT(*) FROM applications) as total_applications,
            (SELECT COUNT(*) FROM events WHERE deleted_at IS NULL) as total_events,
            (SELECT COUNT(*) FROM contacts) as total_contacts,
            (SELECT COUNT(*) FROM testimonials WHERE deleted_at IS NULL) as total_testimonials,
            (SELECT COUNT(*) FROM admin_logs) as total_logs
    ");
    $dbStats = $stmt->fetch();
    
    echo "<pre>";
    print_r($dbStats);
    echo "</pre>";
    
    // Test database size query
    echo "<h3>Database Size Query Test:</h3>\n";
    $stmt = $db->prepare("
        SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
        FROM information_schema.tables 
        WHERE table_schema = ?
    ");
    $stmt->execute([DB_NAME]);
    $dbSize = $stmt->fetchColumn();
    echo "Database size: " . $dbSize . " MB<br>\n";
    
    // Test recent activity query
    echo "<h3>Recent Activity Query Test:</h3>\n";
    $stmt = $db->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count
        FROM admin_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ");
    $recentActivity = $stmt->fetchAll();
    
    echo "<pre>";
    print_r($recentActivity);
    echo "</pre>";
    
    echo "<h3>All queries executed successfully!</h3>\n";
    
} catch (PDOException $e) {
    echo "<h3>Database Error:</h3>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
    echo "Code: " . $e->getCode() . "<br>\n";
}
?>
