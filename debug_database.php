<?php
/**
 * Debug file to check actual database structure and data
 */

require_once __DIR__ . '/shared/Core/Database.php';
require_once __DIR__ . '/shared/Core/Utilities.php';

try {
    $db = (new Database())->connect();
    
    echo "<h1>Database Structure and Data Debug</h1>\n";
    
    // Show all tables
    echo "<h2>1. Available Tables</h2>\n";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    echo "<ul>\n";
    foreach ($tables as $table) {
        $tableName = $table[array_keys($table)[0]];
        echo "<li>$tableName</li>\n";
    }
    echo "</ul>\n";
    
    // Check users table structure and data
    echo "<h2>2. Users Table</h2>\n";
    try {
        $stmt = $db->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        echo "<h3>Structure:</h3>\n";
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>\n";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>\n";
        }
        echo "</table>\n";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL");
        $userCount = $stmt->fetch();
        echo "<p>Total users (not deleted): {$userCount['count']}</p>\n";
        
        if ($userCount['count'] > 0) {
            $stmt = $db->query("SELECT * FROM users WHERE deleted_at IS NULL LIMIT 3");
            $users = $stmt->fetchAll();
            echo "<h3>Sample Data:</h3>\n";
            echo "<pre>" . print_r($users, true) . "</pre>\n";
        }
    } catch (Exception $e) {
        echo "<p>Error with users table: " . $e->getMessage() . "</p>\n";
    }
    
    // Check applications table
    echo "<h2>3. Applications Table</h2>\n";
    try {
        $stmt = $db->query("DESCRIBE applications");
        $columns = $stmt->fetchAll();
        echo "<h3>Structure:</h3>\n";
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>\n";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>\n";
        }
        echo "</table>\n";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM applications WHERE deleted_at IS NULL");
        $appCount = $stmt->fetch();
        echo "<p>Total applications (not deleted): {$appCount['count']}</p>\n";
        
        if ($appCount['count'] > 0) {
            $stmt = $db->query("SELECT * FROM applications WHERE deleted_at IS NULL LIMIT 3");
            $apps = $stmt->fetchAll();
            echo "<h3>Sample Data:</h3>\n";
            echo "<pre>" . print_r($apps, true) . "</pre>\n";
        }
    } catch (Exception $e) {
        echo "<p>Error with applications table: " . $e->getMessage() . "</p>\n";
    }
    
    // Check programs table
    echo "<h2>4. Programs Table</h2>\n";
    try {
        $stmt = $db->query("DESCRIBE programs");
        $columns = $stmt->fetchAll();
        echo "<h3>Structure:</h3>\n";
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Key</th></tr>\n";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Key']}</td></tr>\n";
        }
        echo "</table>\n";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM programs WHERE deleted_at IS NULL");
        $programCount = $stmt->fetch();
        echo "<p>Total programs (not deleted): {$programCount['count']}</p>\n";
        
        if ($programCount['count'] > 0) {
            $stmt = $db->query("SELECT * FROM programs WHERE deleted_at IS NULL LIMIT 3");
            $programs = $stmt->fetchAll();
            echo "<h3>Sample Data:</h3>\n";
            echo "<pre>" . print_r($programs, true) . "</pre>\n";
        }
    } catch (Exception $e) {
        echo "<p>Error with programs table: " . $e->getMessage() . "</p>\n";
    }
    
    // Check contacts table
    echo "<h2>5. Contacts Table</h2>\n";
    try {
        $stmt = $db->query("DESCRIBE contacts");
        $columns = $stmt->fetchAll();
        echo "<h3>Structure:</h3>\n";
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Key</th></tr>\n";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Key']}</td></tr>\n";
        }
        echo "</table>\n";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM contacts WHERE deleted_at IS NULL");
        $contactCount = $stmt->fetch();
        echo "<p>Total contacts (not deleted): {$contactCount['count']}</p>\n";
        
        if ($contactCount['count'] > 0) {
            $stmt = $db->query("SELECT * FROM contacts WHERE deleted_at IS NULL LIMIT 3");
            $contacts = $stmt->fetchAll();
            echo "<h3>Sample Data:</h3>\n";
            echo "<pre>" . print_r($contacts, true) . "</pre>\n";
        }
    } catch (Exception $e) {
        echo "<p>Error with contacts table: " . $e->getMessage() . "</p>\n";
    }
    
    // Check admin_logs table
    echo "<h2>6. Admin Logs Table</h2>\n";
    try {
        $stmt = $db->query("DESCRIBE admin_logs");
        $columns = $stmt->fetchAll();
        echo "<h3>Structure:</h3>\n";
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Key</th></tr>\n";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Key']}</td></tr>\n";
        }
        echo "</table>\n";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM admin_logs");
        $logCount = $stmt->fetch();
        echo "<p>Total admin logs: {$logCount['count']}</p>\n";
        
        if ($logCount['count'] > 0) {
            $stmt = $db->query("SELECT * FROM admin_logs LIMIT 3");
            $logs = $stmt->fetchAll();
            echo "<h3>Sample Data:</h3>\n";
            echo "<pre>" . print_r($logs, true) . "</pre>\n";
        }
    } catch (Exception $e) {
        echo "<p>Error with admin_logs table: " . $e->getMessage() . "</p>\n";
    }
    
    // Check events table
    echo "<h2>7. Events Table</h2>\n";
    try {
        $stmt = $db->query("DESCRIBE events");
        $columns = $stmt->fetchAll();
        echo "<h3>Structure:</h3>\n";
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Key</th></tr>\n";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Key']}</td></tr>\n";
        }
        echo "</table>\n";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM events WHERE deleted_at IS NULL");
        $eventCount = $stmt->fetch();
        echo "<p>Total events (not deleted): {$eventCount['count']}</p>\n";
        
        if ($eventCount['count'] > 0) {
            $stmt = $db->query("SELECT * FROM events WHERE deleted_at IS NULL LIMIT 3");
            $events = $stmt->fetchAll();
            echo "<h3>Sample Data:</h3>\n";
            echo "<pre>" . print_r($events, true) . "</pre>\n";
        }
    } catch (Exception $e) {
        echo "<p>Error with events table: " . $e->getMessage() . "</p>\n";
    }
    
    // Test the exact dashboard queries
    echo "<h2>8. Dashboard Query Tests</h2>\n";
    
    // Test statistics query
    echo "<h3>Statistics Query Test:</h3>\n";
    try {
        $stmt = $db->query("
            SELECT 
                (SELECT COUNT(*) FROM users WHERE is_active = 1) as total_users,
                (SELECT COUNT(*) FROM applications) as total_applications,
                (SELECT COUNT(*) FROM programs WHERE deleted_at IS NULL) as total_programs,
                (SELECT COUNT(*) FROM contacts) as total_contacts,
                (SELECT COUNT(*) FROM events WHERE deleted_at IS NULL) as total_events
        ");
        $stats = $stmt->fetch();
        echo "<pre>" . print_r($stats, true) . "</pre>\n";
    } catch (Exception $e) {
        echo "<p>Error with statistics query: " . $e->getMessage() . "</p>\n";
    }
    
    // Test recent applications query
    echo "<h3>Recent Applications Query Test:</h3>\n";
    try {
        $stmt = $db->query("
            SELECT a.*, p.title as program_title, u.username as user_name
            FROM applications a
            JOIN programs p ON a.program_id = p.id
            LEFT JOIN users u ON a.user_id = u.id AND u.is_active = 1
            ORDER BY a.submitted_at DESC
            LIMIT 3
        ");
        $recentApps = $stmt->fetchAll();
        echo "<p>Found " . count($recentApps) . " recent applications</p>\n";
        if (!empty($recentApps)) {
            echo "<pre>" . print_r($recentApps, true) . "</pre>\n";
        }
    } catch (Exception $e) {
        echo "<p>Error with recent applications query: " . $e->getMessage() . "</p>\n";
    }
    
} catch (Exception $e) {
    echo "<h1>Connection Error</h1>\n";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
?>
