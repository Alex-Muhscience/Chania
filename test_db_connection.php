<?php
// Database Connection Test Script
// This script helps diagnose database connection issues

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Test 1: Check if PDO MySQL extension is loaded
echo "<h3>1. Checking PDO MySQL Extension</h3>";
if (extension_loaded('pdo_mysql')) {
    echo "✅ PDO MySQL extension is loaded<br>";
} else {
    echo "❌ PDO MySQL extension is NOT loaded<br>";
    echo "Please enable pdo_mysql extension in your php.ini file<br><br>";
}

// Test 2: Database connection parameters
$host = 'localhost';
$dbname = 'chania_db';
$username = 'root';
$password = '';

echo "<h3>2. Connection Parameters</h3>";
echo "Host: $host<br>";
echo "Database: $dbname<br>";
echo "Username: $username<br>";
echo "Password: " . (empty($password) ? "(empty)" : "(set)") . "<br><br>";

// Test 3: Attempt database connection
echo "<h3>3. Connection Test</h3>";
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✅ Database connection successful!<br>";
    
    // Test 4: Check database and tables
    echo "<h3>4. Database Structure Check</h3>";
    
    // Check if database exists and we can query it
    $stmt = $pdo->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch();
    echo "Connected to database: " . $result['current_db'] . "<br>";
    
    // Check if required tables exist
    $tables_to_check = ['newsletter_subscribers', 'partners', 'team_members'];
    echo "<br>Checking required tables:<br>";
    
    foreach ($tables_to_check as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "✅ Table '$table' exists with {$result['count']} records<br>";
        } catch (PDOException $e) {
            echo "❌ Table '$table' not found or error: " . $e->getMessage() . "<br>";
        }
    }
    
    // Test 5: Test the actual queries used in newsletter.php
    echo "<h3>5. Testing Newsletter Queries</h3>";
    
    try {
        // Test subscriber count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM newsletter_subscribers");
        $totalSubscribers = $stmt->fetch()['total'];
        echo "✅ Total subscribers query: $totalSubscribers subscribers<br>";
        
        // Test active subscribers count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'active'");
        $activeSubscribers = $stmt->fetch()['total'];
        echo "✅ Active subscribers query: $activeSubscribers active<br>";
        
        // Test pending subscribers count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'pending'");
        $pendingSubscribers = $stmt->fetch()['total'];
        echo "✅ Pending subscribers query: $pendingSubscribers pending<br>";
        
        // Test monthly trends (last 6 months)
        $stmt = $pdo->query("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM newsletter_subscribers 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ");
        $trends = $stmt->fetchAll();
        echo "✅ Monthly trends query: " . count($trends) . " months of data<br>";
        
    } catch (PDOException $e) {
        echo "❌ Query error: " . $e->getMessage() . "<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed!<br>";
    echo "<strong>Error details:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Error code:</strong> " . $e->getCode() . "<br><br>";
    
    echo "<h4>Common solutions:</h4>";
    echo "<ul>";
    echo "<li>Make sure XAMPP MySQL service is running</li>";
    echo "<li>Check if the database 'chania_db' exists in phpMyAdmin</li>";
    echo "<li>Verify MySQL username/password (default XAMPP is root with no password)</li>";
    echo "<li>Check if MySQL is running on port 3306 (default)</li>";
    echo "<li>Try restarting XAMPP services</li>";
    echo "</ul>";
}

// Test 6: PHP and Server Info
echo "<h3>6. PHP Environment Info</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Test 7: Check if your Database class works
echo "<h3>7. Testing Your Database Class</h3>";
if (file_exists('includes/Database.php')) {
    try {
        require_once 'includes/Database.php';
        $db = new Database();
        echo "✅ Database class loaded and instantiated successfully<br>";
    } catch (Exception $e) {
        echo "❌ Database class error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Database.php file not found in includes/ directory<br>";
}

echo "<br><hr>";
echo "<p><strong>Test completed!</strong> If you see connection errors above, please fix them before using the newsletter admin page.</p>";
?>
