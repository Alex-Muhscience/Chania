<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=chania_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Admin logs table structure:\n";
    $stmt = $pdo->query('DESCRIBE admin_logs');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo $column['Field'] . ' - ' . $column['Type'] . "\n";
    }
    
    echo "\nSample admin_logs data (first 3 records):\n";
    $stmt = $pdo->query('SELECT * FROM admin_logs ORDER BY id DESC LIMIT 3');
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($logs as $log) {
        echo "ID: " . $log['id'] . ", Action: " . $log['action'] . ", User ID: " . $log['user_id'] . ", Timestamp: " . $log['created_at'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
