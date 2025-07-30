<?php
require_once __DIR__ . '/shared/Core/Database.php';

try {
    $db = (new Database())->connect();
    
    // Make user_id nullable in admin_logs
    $db->exec("ALTER TABLE admin_logs MODIFY COLUMN user_id int(10) unsigned NULL");
    echo "✓ Updated admin_logs.user_id to allow NULL values\n";
    
    // Test inserting with NULL user_id
    $stmt = $db->prepare("
        INSERT INTO admin_logs (
            user_id, action, entity_type, entity_id, details, 
            ip_address, user_agent, created_at, source
        ) VALUES (NULL, ?, ?, ?, ?, ?, ?, NOW(), 'client')
    ");
    
    $stmt->execute([
        'TEST_CLIENT_ACTION',
        'test',
        999999,
        'Test notification from client',
        '127.0.0.1',
        'Test Agent'
    ]);
    
    echo "✓ Successfully inserted test admin log with NULL user_id\n";
    
    // Clean up test data
    $db->exec("DELETE FROM admin_logs WHERE entity_id = 999999 AND source = 'client'");
    echo "✓ Test data cleaned up\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
