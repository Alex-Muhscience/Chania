<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=chania_db', 'root', '');
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Available tables:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    // Check specifically for users table
    if (in_array('users', $tables)) {
        echo "\nUsers table exists. Checking structure:\n";
        $stmt = $pdo->query('DESCRIBE users');
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})\n";
        }
        
        // Check if there are any users
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
        $count = $stmt->fetch();
        echo "\nTotal users: {$count['count']}\n";
    } else {
        echo "\nUsers table does NOT exist!\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
