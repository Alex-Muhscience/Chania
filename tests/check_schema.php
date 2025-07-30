<?php
require_once 'shared/Core/Database.php';

try {
    $db = new Database();
    $pdo = $db->connect();
    
    echo "Admin Logs Table Structure:\n";
    echo str_repeat("=", 50) . "\n";
    
    $result = $pdo->query("DESCRIBE admin_logs");
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-15s | %-15s | %-5s | %-5s | %-10s | %s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key'], 
            $row['Default'] ?? 'NULL',
            $row['Extra']
        );
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
