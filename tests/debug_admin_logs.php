<?php
require_once __DIR__ . '/shared/Core/Database.php';

try {
    $db = (new Database())->connect();
    echo "<h2>Admin Logs Diagnostics</h2>\n";
    
    // 1. Check if admin_logs table exists and count total records
    echo "<h3>1. Admin Logs Table Analysis</h3>\n";
    $stmt = $db->query("SELECT COUNT(*) FROM admin_logs");
    $totalLogs = $stmt->fetchColumn();
    echo "Total admin_logs records: $totalLogs<br>\n";
    
    if ($totalLogs == 0) {
        echo "<strong>No records found in admin_logs table!</strong><br>\n";
        
        // Check table structure
        echo "<h4>Admin Logs Table Structure:</h4>\n";
        $stmt = $db->query("DESCRIBE admin_logs");
        $columns = $stmt->fetchAll();
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})<br>\n";
        }
    } else {
        // Show sample data
        echo "<h4>Sample Admin Logs (first 5 records):</h4>\n";
        $stmt = $db->query("SELECT * FROM admin_logs ORDER BY created_at DESC LIMIT 5");
        $logs = $stmt->fetchAll();
        foreach ($logs as $log) {
            echo "ID: {$log['id']}, User ID: {$log['user_id']}, Action: {$log['action']}, Created: {$log['created_at']}<br>\n";
        }
    }
    
    // 2. Check users table and active users
    echo "<h3>2. Users Table Analysis</h3>\n";
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE is_active = 1");
    $activeUsers = $stmt->fetchColumn();
    echo "Active users count: $activeUsers<br>\n";
    
    if ($activeUsers > 0) {
        echo "<h4>Active Users:</h4>\n";
        $stmt = $db->query("SELECT id, username FROM users WHERE is_active = 1 LIMIT 10");
        $users = $stmt->fetchAll();
        foreach ($users as $user) {
            echo "- ID: {$user['id']}, Username: {$user['username']}<br>\n";
        }
    }
    
    // 3. Test the JOIN query used in admin_logs.php
    echo "<h3>3. Testing JOIN Query</h3>\n";
    $stmt = $db->query("
        SELECT COUNT(*) 
        FROM admin_logs l
        JOIN users u ON l.user_id = u.id
        WHERE u.is_active = 1
    ");
    $joinedCount = $stmt->fetchColumn();
    echo "Records with valid active user joins: $joinedCount<br>\n";
    
    // 4. Check for orphaned logs (logs with no matching users)
    echo "<h3>4. Orphaned Logs Check</h3>\n";
    $stmt = $db->query("
        SELECT COUNT(*) 
        FROM admin_logs l
        LEFT JOIN users u ON l.user_id = u.id
        WHERE u.id IS NULL
    ");
    $orphanedLogs = $stmt->fetchColumn();
    echo "Orphaned logs (no matching user): $orphanedLogs<br>\n";
    
    // 5. Check for logs with inactive users
    echo "<h3>5. Logs with Inactive Users</h3>\n";
    $stmt = $db->query("
        SELECT COUNT(*) 
        FROM admin_logs l
        JOIN users u ON l.user_id = u.id
        WHERE u.is_active = 0
    ");
    $inactiveUserLogs = $stmt->fetchColumn();
    echo "Logs with inactive users: $inactiveUserLogs<br>\n";
    
    // 6. Test the exact statistics query from admin_logs.php
    echo "<h3>6. Testing Statistics Query (Last 30 Days)</h3>\n";
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_logs,
            COUNT(DISTINCT user_id) as active_users,
            COUNT(DISTINCT DATE(created_at)) as active_days
        FROM admin_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stats = $stmt->fetch();
    echo "Last 30 days - Total logs: {$stats['total_logs']}, Active users: {$stats['active_users']}, Active days: {$stats['active_days']}<br>\n";
    
    // 7. Test the main query with JOIN for last 30 days
    echo "<h3>7. Testing Main Query with JOIN (Last 30 Days)</h3>\n";
    $stmt = $db->query("
        SELECT COUNT(*) 
        FROM admin_logs l
        JOIN users u ON l.user_id = u.id
        WHERE l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $recentJoinedCount = $stmt->fetchColumn();
    echo "Recent logs with valid user joins (last 30 days): $recentJoinedCount<br>\n";
    
    // 8. Show recent admin_logs with user info
    if ($totalLogs > 0) {
        echo "<h3>8. Recent Logs with User Info</h3>\n";
        $stmt = $db->query("
            SELECT l.id, l.action, l.created_at, u.username, u.is_active
            FROM admin_logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT 10
        ");
        $recentLogs = $stmt->fetchAll();
        foreach ($recentLogs as $log) {
            $username = $log['username'] ?? 'USER NOT FOUND';
            $active = $log['is_active'] ?? 'N/A';
            echo "- Log ID: {$log['id']}, Action: {$log['action']}, User: $username (Active: $active), Date: {$log['created_at']}<br>\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>\n";
    echo "Error code: " . $e->getCode() . "<br>\n";
}
?>
