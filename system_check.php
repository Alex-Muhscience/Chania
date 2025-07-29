<?php
// System Check Script for Chania Admin Panel
echo "=== CHANIA ADMIN PANEL SYSTEM CHECK ===\n\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current Directory: " . __DIR__ . "\n\n";

// Test database connection
echo "--- DATABASE CONNECTION TEST ---\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=chania_admin', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection: SUCCESS\n";
    
    // Check if admin user exists
    $stmt = $pdo->prepare('SELECT username, role FROM users WHERE username = ? AND role = ?');
    $stmt->execute(['admin', 'admin']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✓ Admin user exists: YES (username: {$user['username']}, role: {$user['role']})\n";
    } else {
        echo "✗ Admin user exists: NO\n";
    }
    
    // Check key tables exist
    echo "\n--- TABLE EXISTENCE CHECK ---\n";
    $tables = ['users', 'admin_logs', 'sessions', 'applications', 'events', 'newsletter_subscribers', 'contact_messages'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            // Get row count
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $countStmt->fetch()['count'];
            echo "✓ Table $table: EXISTS ($count rows)\n";
        } else {
            echo "✗ Table $table: MISSING\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

// Test session functionality
echo "\n--- SESSION TEST ---\n";
session_start();
$_SESSION['test'] = 'working';
if (isset($_SESSION['test']) && $_SESSION['test'] === 'working') {
    echo "✓ Session handling: WORKING\n";
    echo "Session ID: " . session_id() . "\n";
    echo "Session save path: " . session_save_path() . "\n";
} else {
    echo "✗ Session handling: FAILED\n";
}

// Test file permissions
echo "\n--- FILE PERMISSIONS TEST ---\n";
$important_files = [
    'admin/public/login.php',
    'admin/public/index.php',
    'admin/includes/auth.php',
    'admin/includes/simple_session.php'
];

foreach ($important_files as $file) {
    if (file_exists($file)) {
        echo "✓ File $file: EXISTS (" . (is_readable($file) ? 'readable' : 'not readable') . ")\n";
    } else {
        echo "✗ File $file: MISSING\n";
    }
}

session_destroy();

echo "\n=== SYSTEM CHECK COMPLETE ===\n";
?>
