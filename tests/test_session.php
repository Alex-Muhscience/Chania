<?php
// Test session functionality
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Testing session functionality...\n\n";

try {
    // Include required files
    require_once __DIR__ . '/shared/Core/Database.php';
    require_once __DIR__ . '/shared/Core/Utilities.php';
    require_once __DIR__ . '/admin/includes/config.php';
    require_once __DIR__ . '/admin/includes/session.php';
    
    echo "✓ All files included successfully\n";
    
    // Test database connection
    $db = (new Database())->connect();
    echo "✓ Database connection successful\n";
    
    // Check if session is working
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "✓ Session is active\n";
        echo "Session ID: " . session_id() . "\n";
    } else {
        echo "✗ Session is not active\n";
    }
    
    // Test session write to database
    $_SESSION['test'] = 'session_test_value';
    echo "✓ Session test value set\n";
    
    // Check if sessions table has data
    $stmt = $db->query("SELECT COUNT(*) as count FROM sessions");
    $sessionCount = $stmt->fetch();
    echo "Sessions in database: {$sessionCount['count']}\n";
    
    // Test login simulation
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['role'] = 'admin';
    
    echo "✓ Login simulation set\n";
    
    // Test isLoggedIn
    if (Utilities::isLoggedIn()) {
        echo "✓ isLoggedIn() returns true\n";
    } else {
        echo "✗ isLoggedIn() returns false\n";
    }
    
    // Show current session data
    echo "\nCurrent session data:\n";
    foreach ($_SESSION as $key => $value) {
        echo "- $key: $value\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
