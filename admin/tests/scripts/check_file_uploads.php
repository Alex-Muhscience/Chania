<?php
require_once 'includes/config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Check if file_uploads table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'file_uploads'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "File_uploads table exists.\n\n";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE file_uploads");
        $columns = $stmt->fetchAll();
        
        echo "Table structure:\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']}\n";
        }
    } else {
        echo "File_uploads table does not exist!\n";
        echo "This table needs to be created for file management functionality.\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
