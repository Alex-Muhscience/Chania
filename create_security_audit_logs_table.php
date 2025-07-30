<?php
require_once __DIR__ . '/shared/Core/Database.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    $sql = file_get_contents(__DIR__ . '/admin/sql/create_security_audit_logs_table.sql');
    
    $db->exec($sql);
    echo "Security audit logs table created successfully!";
    
} catch (PDOException $e) {
    echo "Error creating security audit logs table: " . $e->getMessage();
}
?>
