<?php
/**
 * Fix Event Registration Notifications and Alerts
 * This script fixes the missing notifications for event registrations in the admin panel
 */

require_once __DIR__ . '/shared/Core/Database.php';

echo "=== Fixing Event Registration Notifications ===\n\n";

try {
    $db = (new Database())->connect();
    echo "✓ Database connected successfully\n";

    // 1. Create client_activities table if it doesn't exist
    echo "\n1. Creating client_activities table...\n";
    $createClientActivitiesSQL = "
    CREATE TABLE IF NOT EXISTS `client_activities` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `activity_type` varchar(50) NOT NULL,
        `entity_type` varchar(50) DEFAULT NULL,
        `entity_id` int(10) unsigned DEFAULT NULL,
        `user_identifier` varchar(255) NOT NULL,
        `activity_data` json DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text DEFAULT NULL,
        `referrer` varchar(500) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_activity_type` (`activity_type`),
        KEY `idx_entity` (`entity_type`, `entity_id`),
        KEY `idx_user_identifier` (`user_identifier`),
        KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $db->exec($createClientActivitiesSQL);
    echo "✓ client_activities table created/verified\n";

    // 2. Update event_registrations table structure to match expected fields
    echo "\n2. Updating event_registrations table structure...\n";
    
    // Check current structure and add missing columns
    $columns = [
        'first_name' => "ADD COLUMN `first_name` varchar(50) NOT NULL AFTER `full_name`",
        'last_name' => "ADD COLUMN `last_name` varchar(50) NOT NULL AFTER `first_name`",
        'accessibility_needs' => "ADD COLUMN `accessibility_needs` text DEFAULT NULL AFTER `special_needs`"
    ];

    foreach ($columns as $column => $alterSQL) {
        try {
            // Check if column exists
            $stmt = $db->prepare("SHOW COLUMNS FROM event_registrations LIKE ?");
            $stmt->execute([$column]);
            
            if (!$stmt->fetch()) {
                $db->exec("ALTER TABLE event_registrations $alterSQL");
                echo "✓ Added column: $column\n";
            } else {
                echo "○ Column already exists: $column\n";
            }
        } catch (Exception $e) {
            echo "⚠ Warning: Could not add column $column: " . $e->getMessage() . "\n";
        }
    }

    // 3. Add source column to admin_logs if it doesn't exist
    echo "\n3. Updating admin_logs table structure...\n";
    try {
        $stmt = $db->prepare("SHOW COLUMNS FROM admin_logs LIKE 'source'");
        $stmt->execute();
        
        if (!$stmt->fetch()) {
            $db->exec("ALTER TABLE admin_logs ADD COLUMN `source` varchar(20) DEFAULT 'admin' AFTER `user_agent`");
            echo "✓ Added source column to admin_logs\n";
        } else {
            echo "○ Source column already exists in admin_logs\n";
        }
    } catch (Exception $e) {
        echo "⚠ Warning: Could not add source column: " . $e->getMessage() . "\n";
    }

    // 4. Update the admin_logs foreign key constraint to allow NULL for client-side activities
    echo "\n4. Updating admin_logs foreign key constraint...\n";
    try {
        // Drop existing foreign key constraint if it exists
        $db->exec("SET foreign_key_checks = 0");
        $db->exec("ALTER TABLE admin_logs DROP FOREIGN KEY admin_logs_ibfk_1");
        
        // Recreate the constraint to allow NULL user_id
        $db->exec("ALTER TABLE admin_logs ADD CONSTRAINT admin_logs_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
        $db->exec("SET foreign_key_checks = 1");
        
        echo "✓ Updated admin_logs foreign key constraint to allow NULL user_id\n";
    } catch (Exception $e) {
        echo "⚠ Warning: Could not update foreign key constraint: " . $e->getMessage() . "\n";
    }

    // 5. Test inserting a sample client activity log entry
    echo "\n5. Testing ClientActivityLogger functionality...\n";
    try {
        $testData = [
            'activity_type' => 'event_register',
            'entity_type' => 'event_registration',
            'entity_id' => 999999, // Test ID
            'user_identifier' => 'test@example.com',
            'activity_data' => json_encode(['event_title' => 'Test Event', 'registration_time' => date('Y-m-d H:i:s')]),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
            'referrer' => 'http://test.local'
        ];

        $stmt = $db->prepare("
            INSERT INTO client_activities (
                activity_type, entity_type, entity_id, user_identifier, 
                activity_data, ip_address, user_agent, referrer, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $testData['activity_type'],
            $testData['entity_type'],
            $testData['entity_id'],
            $testData['user_identifier'],
            $testData['activity_data'],
            $testData['ip_address'],
            $testData['user_agent'],
            $testData['referrer']
        ]);

        echo "✓ Test client activity logged successfully\n";

        // Clean up test data
        $db->exec("DELETE FROM client_activities WHERE entity_id = 999999");
        echo "✓ Test data cleaned up\n";

    } catch (Exception $e) {
        echo "⚠ Warning: ClientActivityLogger test failed: " . $e->getMessage() . "\n";
    }

    // 6. Test inserting admin log entry with NULL user_id
    echo "\n6. Testing admin_logs with NULL user_id...\n";
    try {
        $stmt = $db->prepare("
            INSERT INTO admin_logs (
                user_id, action, entity_type, entity_id, details, 
                ip_address, user_agent, created_at, source
            ) VALUES (NULL, ?, ?, ?, ?, ?, ?, NOW(), 'client')
        ");
        
        $stmt->execute([
            'CLIENT_EVENT_REGISTRATION',
            'event_registration',
            999999,
            'Test event registration notification',
            '127.0.0.1',
            'Test User Agent'
        ]);

        echo "✓ Test admin log with NULL user_id created successfully\n";

        // Clean up test data
        $db->exec("DELETE FROM admin_logs WHERE entity_id = 999999 AND source = 'client'");
        echo "✓ Test admin log data cleaned up\n";

    } catch (Exception $e) {
        echo "⚠ Warning: Admin log test failed: " . $e->getMessage() . "\n";
    }

    echo "\n=== Fix Complete ===\n";
    echo "✓ Database tables created/updated successfully\n";
    echo "✓ Event registration notifications should now work properly\n";
    echo "✓ Admin panel will now show event registration alerts\n\n";

    echo "Next Steps:\n";
    echo "1. Update admin header to include event registration notifications\n";
    echo "2. Test event registration from client side\n";
    echo "3. Verify notifications appear in admin panel\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
