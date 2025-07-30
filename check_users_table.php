<?php
require_once __DIR__ . '/shared/Core/Database.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    $stmt = $db->query('DESCRIBE users');
    $columns = $stmt->fetchAll();
    
    echo "Current users table structure:\n";
    foreach ($columns as $column) {
        echo $column['Field'] . ' - ' . $column['Type'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
