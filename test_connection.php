<?php
// Test database connection script
echo "Testing database connection...\n\n";

// Database configuration
$host = 'localhost';
$db_name = 'chania_db';
$username = 'root';
$password = '';

try {
    echo "Attempting to connect to MySQL server...\n";
    
    // First test basic MySQL connection
    $basic_conn = new PDO("mysql:host=$host", $username, $password);
    $basic_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Successfully connected to MySQL server\n";
    
    // Check if database exists
    echo "Checking if database '$db_name' exists...\n";
    $stmt = $basic_conn->query("SHOW DATABASES LIKE '$db_name'");
    $db_exists = $stmt->fetch();
    
    if ($db_exists) {
        echo "✓ Database '$db_name' exists\n";
        
        // Test connection to specific database
        echo "Testing connection to database '$db_name'...\n";
        $db_conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        $db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✓ Successfully connected to database '$db_name'\n";
        
        // Test if newsletter_subscribers table exists
        echo "Checking if 'newsletter_subscribers' table exists...\n";
        $stmt = $db_conn->query("SHOW TABLES LIKE 'newsletter_subscribers'");
        $table_exists = $stmt->fetch();
        
        if ($table_exists) {
            echo "✓ Table 'newsletter_subscribers' exists\n";
            
            // Test a simple query
            echo "Testing simple query on newsletter_subscribers...\n";
            $stmt = $db_conn->query("SELECT COUNT(*) as count FROM newsletter_subscribers");
            $result = $stmt->fetch();
            echo "✓ Query successful. Found {$result['count']} subscribers\n";
        } else {
            echo "✗ Table 'newsletter_subscribers' does not exist\n";
        }
        
    } else {
        echo "✗ Database '$db_name' does not exist\n";
        echo "Available databases:\n";
        $stmt = $basic_conn->query("SHOW DATABASES");
        while ($row = $stmt->fetch()) {
            echo "  - " . $row['Database'] . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    
    // Additional debugging info
    echo "\nDebugging information:\n";
    echo "Host: $host\n";
    echo "Database: $db_name\n";
    echo "Username: $username\n";
    echo "Password: " . (empty($password) ? "(empty)" : "(set)") . "\n";
    
} catch (Exception $e) {
    echo "✗ General Error: " . $e->getMessage() . "\n";
}

echo "\nConnection test completed.\n";
?>
