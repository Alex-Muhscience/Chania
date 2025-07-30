<?php
require_once 'includes/config.php';

// Change to the admin directory
chdir(__DIR__);

try {
    // Read the SQL file
    $sql = file_get_contents('sql/create_security_audit_logs_table.sql');
    
    if ($sql === false) {
        die('Failed to read SQL file');
    }
    
    // Execute the SQL
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    $result = $pdo->exec($sql);
    
    if ($result !== false) {
        echo "Security audit logs table created successfully!\n";
    } else {
        echo "Error creating security audit logs table\n";
        print_r($pdo->errorInfo());
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
