<?php
require_once 'shared/Core/Database.php';

try {
    $db = (new Database())->connect();
    
    // Check if admin_logs table exists
    $stmt = $db->query("SHOW TABLES LIKE 'admin_logs'");
    $tableExists = $stmt->rowCount() > 0;
    
    echo "Admin_logs table exists: " . ($tableExists ? 'YES' : 'NO') . "\n\n";
    
    if ($tableExists) {
        // Show table structure
        echo "Admin_logs table structure:\n";
        $stmt = $db->query('DESCRIBE admin_logs');
        $columns = $stmt->fetchAll();
        foreach ($columns as $column) {
            echo $column['Field'] . ' - ' . $column['Type'] . ' - ' . $column['Null'] . ' - ' . $column['Key'] . "\n";
        }
        
        // Check if we can select from the table
        echo "\nSample query test:\n";
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM admin_logs LIMIT 1");
            $result = $stmt->fetch();
            echo "Total rows: " . $result['count'] . "\n";
        } catch (Exception $e) {
            echo "Error querying admin_logs: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
