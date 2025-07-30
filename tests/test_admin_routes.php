<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Routes & Session Test</h1>";

// Test 1: Basic file includes
echo "<h2>1. File Includes Test</h2>";
try {
    require_once __DIR__ . '/shared/Core/Database.php';
    echo "✓ Database.php loaded<br>";
} catch (Exception $e) {
    echo "✗ Database.php failed: " . $e->getMessage() . "<br>";
}

try {
    require_once __DIR__ . '/shared/Core/Utilities.php';
    echo "✓ Utilities.php loaded<br>";
} catch (Exception $e) {
    echo "✗ Utilities.php failed: " . $e->getMessage() . "<br>";
}

try {
    require_once __DIR__ . '/admin/includes/config.php';
    echo "✓ config.php loaded<br>";
} catch (Exception $e) {
    echo "✗ config.php failed: " . $e->getMessage() . "<br>";
}

try {
    require_once __DIR__ . '/admin/includes/simple_session.php';
    echo "✓ simple_session.php loaded<br>";
} catch (Exception $e) {
    echo "✗ simple_session.php failed: " . $e->getMessage() . "<br>";
}

// Test 2: Database connection
echo "<h2>2. Database Connection Test</h2>";
try {
    $db = (new Database())->connect();
    echo "✓ Database connected<br>";
    
    // Test user table
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch();
    echo "✓ Users table accessible, found " . $count['count'] . " users<br>";
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Session status
echo "<h2>3. Session Test</h2>";
echo "Session status: " . session_status() . " (1=disabled, 2=active, 3=none)<br>";
echo "Session ID: " . session_id() . "<br>";

// Simulate login
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'admin';

echo "✓ Test session variables set<br>";

// Test 4: Authentication check
echo "<h2>4. Authentication Test</h2>";
if (Utilities::isLoggedIn()) {
    echo "✓ isLoggedIn() returns true<br>";
} else {
    echo "✗ isLoggedIn() returns false<br>";
}

// Test 5: Route definitions
echo "<h2>5. Route Definitions</h2>";
echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "<br>";
echo "ADMIN_URL: " . (defined('ADMIN_URL') ? ADMIN_URL : 'NOT DEFINED') . "<br>";

// Test 6: File permissions
echo "<h2>6. File Permissions Test</h2>";
$uploadPath = __DIR__ . '/uploads';
echo "Upload path: $uploadPath<br>";
echo "Upload path exists: " . (is_dir($uploadPath) ? 'YES' : 'NO') . "<br>";
echo "Upload path writable: " . (is_writable($uploadPath) ? 'YES' : 'NO') . "<br>";

$logsPath = __DIR__ . '/logs';
echo "Logs path: $logsPath<br>";
echo "Logs path exists: " . (is_dir($logsPath) ? 'YES' : 'NO') . "<br>";
echo "Logs path writable: " . (is_writable($logsPath) ? 'YES' : 'NO') . "<br>";

// Test 7: Check specific admin files
echo "<h2>7. Admin Files Check</h2>";
$adminFiles = [
    '/admin/public/login.php',
    '/admin/public/index.php',
    '/admin/includes/header.php',
    '/admin/includes/footer.php',
    '/admin/includes/auth.php'
];

foreach ($adminFiles as $file) {
    $fullPath = __DIR__ . $file;
    echo "$file: " . (file_exists($fullPath) ? '✓ EXISTS' : '✗ MISSING') . "<br>";
}

// Test 8: Current session data
echo "<h2>8. Current Session Data</h2>";
if (!empty($_SESSION)) {
    foreach ($_SESSION as $key => $value) {
        echo "$key: " . (is_string($value) ? $value : json_encode($value)) . "<br>";
    }
} else {
    echo "No session data<br>";
}

?>

<h2>9. Quick Navigation Links</h2>
<a href="admin/public/login.php">Login Page</a> | 
<a href="admin/public/index.php">Dashboard</a> | 
<a href="test_connection.php">Connection Test</a>
