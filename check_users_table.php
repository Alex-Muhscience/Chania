<?php
// Check users table structure
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=chania_db;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    
    echo "<h2>Users Table Structure</h2>\n";
    
    // Get table structure
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    echo "<h3>Columns:</h3>\n";
    echo "<ul>\n";
    foreach ($columns as $column) {
        echo "<li><strong>{$column['Field']}</strong> - {$column['Type']}</li>\n";
    }
    echo "</ul>\n";
    
    // Check if admin user exists and show columns
    echo "<h3>Admin User Data:</h3>\n";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<ul>\n";
        foreach ($admin as $key => $value) {
            if ($key === 'password_hash') {
                echo "<li><strong>$key:</strong> [HIDDEN]</li>\n";
            } else {
                echo "<li><strong>$key:</strong> " . htmlspecialchars($value) . "</li>\n";
            }
        }
        echo "</ul>\n";
    } else {
        echo "<p>No admin user found</p>\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
