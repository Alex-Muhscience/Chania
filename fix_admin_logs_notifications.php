<?php
/**
 * Fix Admin Logs and Notifications System
 * 
 * This script:
 * 1. Verifies admin_logs table structure
 * 2. Adds missing columns if needed
 * 3. Creates client activity logging system
 * 4. Enhances notification system
 */

require_once __DIR__ . '/shared/Core/Database.php';
require_once __DIR__ . '/admin/includes/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = (new Database())->connect();
    echo "✓ Database connection established\n";
    
    // Step 1: Check current admin_logs table structure
    echo "\n1. Checking admin_logs table structure...\n";
    
    $stmt = $db->query("DESCRIBE admin_logs");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $existingColumns = [];
    foreach ($columns as $column) {
        $existingColumns[] = $column['Field'];
        echo "   - {$column['Field']} ({$column['Type']})\n";
    }
    
    // Step 2: Check for missing columns and add them
    $requiredColumns = [
        'id' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT UNSIGNED',
        'action' => 'VARCHAR(100) NOT NULL',
        'entity_type' => 'VARCHAR(50)',
        'entity_id' => 'INT UNSIGNED',
        'old_values' => 'JSON',
        'new_values' => 'JSON',
        'details' => 'TEXT',
        'ip_address' => 'VARCHAR(45)',
        'user_agent' => 'TEXT',
        'session_id' => 'VARCHAR(128)',
        'severity' => "ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info'",
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'source' => "ENUM('admin', 'client', 'system') DEFAULT 'admin'"
    ];
    
    $missingColumns = [];
    foreach ($requiredColumns as $columnName => $columnDef) {
        if (!in_array($columnName, $existingColumns)) {
            $missingColumns[$columnName] = $columnDef;
        }
    }
    
    if (!empty($missingColumns)) {
        echo "\n2. Adding missing columns...\n";
        foreach ($missingColumns as $columnName => $columnDef) {
            try {
                if ($columnName === 'id') {
                    // Skip ID column as it should already exist
                    continue;
                }
                
                $alterQuery = "ALTER TABLE admin_logs ADD COLUMN `{$columnName}` {$columnDef}";
                $db->exec($alterQuery);
                echo "   ✓ Added column: {$columnName}\n";
            } catch (Exception $e) {
                echo "   ✗ Failed to add column {$columnName}: " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "   ✓ All required columns exist\n";
    }
    
    // Step 3: Create client activity logging table if it doesn't exist
    echo "\n3. Creating client activity logging table...\n";
    
    $clientActivityTable = "
    CREATE TABLE IF NOT EXISTS client_activities (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        activity_type ENUM('application_submit', 'event_register', 'contact_submit', 'newsletter_subscribe', 'page_view') NOT NULL,
        entity_type VARCHAR(50),
        entity_id INT UNSIGNED,
        user_identifier VARCHAR(100), -- email, IP, or session ID
        activity_data JSON,
        ip_address VARCHAR(45),
        user_agent TEXT,
        referrer VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        INDEX idx_client_activities_type (activity_type),
        INDEX idx_client_activities_entity (entity_type, entity_id),
        INDEX idx_client_activities_user (user_identifier),
        INDEX idx_client_activities_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $db->exec($clientActivityTable);
        echo "   ✓ Client activities table created/verified\n";
    } catch (Exception $e) {
        echo "   ✗ Failed to create client activities table: " . $e->getMessage() . "\n";
    }
    
    // Step 4: Create notification preferences table
    echo "\n4. Creating notification preferences table...\n";
    
    $notificationPrefsTable = "
    CREATE TABLE IF NOT EXISTS notification_preferences (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        notification_type ENUM('new_application', 'new_contact', 'new_registration', 'system_alert') NOT NULL,
        is_enabled BOOLEAN DEFAULT TRUE,
        delivery_method ENUM('in_app', 'email', 'both') DEFAULT 'both',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        UNIQUE KEY unique_user_notification (user_id, notification_type),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $db->exec($notificationPrefsTable);
        echo "   ✓ Notification preferences table created/verified\n";
    } catch (Exception $e) {
        echo "   ✗ Failed to create notification preferences table: " . $e->getMessage() . "\n";
    }
    
    // Step 5: Insert default notification preferences for existing admin users
    echo "\n5. Setting up default notification preferences...\n";
    
    try {
        $stmt = $db->query("SELECT id FROM users WHERE role = 'admin'");
        $adminUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $notificationTypes = ['new_application', 'new_contact', 'new_registration', 'system_alert'];
        
        foreach ($adminUsers as $userId) {
            foreach ($notificationTypes as $type) {
                $stmt = $db->prepare("
                    INSERT IGNORE INTO notification_preferences (user_id, notification_type, is_enabled, delivery_method)
                    VALUES (?, ?, TRUE, 'both')
                ");
                $stmt->execute([$userId, $type]);
            }
        }
        echo "   ✓ Default notification preferences set for " . count($adminUsers) . " admin users\n";
    } catch (Exception $e) {
        echo "   ✗ Failed to set default notification preferences: " . $e->getMessage() . "\n";
    }
    
    // Step 6: Test the admin_logs table
    echo "\n6. Testing admin_logs functionality...\n";
    
    try {
        // Insert a test log entry
        $testLogStmt = $db->prepare("
            INSERT INTO admin_logs (user_id, action, entity_type, entity_id, details, ip_address, created_at, source)
            VALUES (1, 'TEST_NOTIFICATION_SYSTEM', 'system', NULL, 'Testing notification system setup', '127.0.0.1', NOW(), 'system')
        ");
        $testLogStmt->execute();
        
        // Query recent activities
        $stmt = $db->query("
            SELECT COUNT(*) as count 
            FROM admin_logs 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $recentCount = $stmt->fetchColumn();
        
        echo "   ✓ Admin logs test successful. Recent activities in 24h: {$recentCount}\n";
    } catch (Exception $e) {
        echo "   ✗ Admin logs test failed: " . $e->getMessage() . "\n";
    }
    
    // Step 7: Create stored procedures for notifications
    echo "\n7. Creating notification stored procedures...\n";
    
    $procedures = [
        'GetRecentClientActivities' => "
        CREATE PROCEDURE GetRecentClientActivities(IN days_back INT, IN activity_limit INT)
        BEGIN
            SELECT 
                ca.*,
                CASE 
                    WHEN ca.activity_type = 'application_submit' THEN 
                        CONCAT((SELECT CONCAT(first_name, ' ', last_name) FROM applications WHERE id = ca.entity_id LIMIT 1), ' submitted an application')
                    WHEN ca.activity_type = 'event_register' THEN 
                        CONCAT((SELECT CONCAT(first_name, ' ', last_name) FROM event_registrations WHERE id = ca.entity_id LIMIT 1), ' registered for an event')
                    WHEN ca.activity_type = 'contact_submit' THEN 
                        CONCAT((SELECT name FROM contacts WHERE id = ca.entity_id LIMIT 1), ' sent a contact message')
                    WHEN ca.activity_type = 'newsletter_subscribe' THEN 
                        CONCAT((SELECT name FROM newsletter_subscribers WHERE id = ca.entity_id LIMIT 1), ' subscribed to newsletter')
                    ELSE 'Unknown activity'
                END as activity_description
            FROM client_activities ca
            WHERE ca.created_at >= DATE_SUB(NOW(), INTERVAL days_back DAY)
            ORDER BY ca.created_at DESC
            LIMIT activity_limit;
        END",
        
        'GetUnreadNotificationCount' => "
        CREATE PROCEDURE GetUnreadNotificationCount(IN user_id INT)
        BEGIN
            SELECT 
                (SELECT COUNT(*) FROM applications WHERE status = 'pending' AND deleted_at IS NULL) +
                (SELECT COUNT(*) FROM contacts WHERE is_read = 0 AND deleted_at IS NULL) +
                (SELECT COUNT(*) FROM event_registrations WHERE status = 'registered' AND DATE(registered_at) = CURDATE()) as total_count;
        END"
    ];
    
    foreach ($procedures as $name => $sql) {
        try {
            // Drop procedure if exists
            $db->exec("DROP PROCEDURE IF EXISTS {$name}");
            // Create procedure
            $db->exec($sql);
            echo "   ✓ Created procedure: {$name}\n";
        } catch (Exception $e) {
            echo "   ✗ Failed to create procedure {$name}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Admin logs and notifications system setup completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Update client-side forms to log activities\n";
    echo "2. Test notification system in admin panel\n";
    echo "3. Configure notification preferences\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
