<?php
// Comprehensive Testing Script for Chania Admin Panel
// This script tests all major functionality

require_once __DIR__ . '/admin/includes/config.php';

echo "<h1>Chania Admin Panel - Comprehensive Testing</h1>\n";
echo "<pre>\n";

$tests = [];
$passed = 0;
$failed = 0;

function logTest($name, $result, $message = '') {
    global $tests, $passed, $failed;
    $status = $result ? 'PASS' : 'FAIL';
    $color = $result ? '✓' : '✗';
    echo "{$color} {$name}: {$status}";
    if ($message) echo " - {$message}";
    echo "\n";
    
    $tests[] = ['name' => $name, 'result' => $result, 'message' => $message];
    $result ? $passed++ : $failed++;
}

echo "=== DATABASE CONNECTION TESTS ===\n";

// Test database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    logTest("Database Connection", true, "Connected successfully to " . DB_NAME);
} catch (Exception $e) {
    logTest("Database Connection", false, $e->getMessage());
    exit("Cannot proceed without database connection\n");
}

echo "\n=== TABLE STRUCTURE TESTS ===\n";

// Test required tables exist
$required_tables = [
    'users', 'sessions', 'contacts', 'admin_logs', 'login_attempts', 
    'remember_tokens', 'services', 'projects', 'newsletters'
];

foreach ($required_tables as $table) {
    try {
        $stmt = $pdo->query("DESCRIBE `{$table}`");
        logTest("Table: {$table}", $stmt->rowCount() > 0);
    } catch (Exception $e) {
        logTest("Table: {$table}", false, "Missing or inaccessible");
    }
}

echo "\n=== ADMIN USER TESTS ===\n";

// Test admin user exists and is active
try {
    $stmt = $pdo->prepare("SELECT id, username, email, is_active, created_at FROM users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        logTest("Admin User Exists", true, "ID: {$admin['id']}, Active: " . ($admin['is_active'] ? 'Yes' : 'No'));
        logTest("Admin User Active", $admin['is_active'] == 1);
    } else {
        logTest("Admin User Exists", false, "Admin user not found");
    }
} catch (Exception $e) {
    logTest("Admin User Query", false, $e->getMessage());
}

echo "\n=== SESSION FUNCTIONALITY TESTS ===\n";

// Test session table structure
try {
    $stmt = $pdo->query("DESCRIBE sessions");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $required_session_cols = ['session_id', 'user_id', 'created_at', 'expires_at', 'ip_address'];
    
    foreach ($required_session_cols as $col) {
        logTest("Session Column: {$col}", in_array($col, $columns));
    }
} catch (Exception $e) {
    logTest("Session Table Structure", false, $e->getMessage());
}

echo "\n=== SECURITY FEATURES TESTS ===\n";

// Test security columns in users table
try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $security_cols = ['is_active', 'failed_login_attempts', 'account_locked_until', 'last_login_at'];
    
    foreach ($security_cols as $col) {
        logTest("Security Column: {$col}", in_array($col, $columns));
    }
} catch (Exception $e) {
    logTest("Security Columns Check", false, $e->getMessage());
}

echo "\n=== FILE STRUCTURE TESTS ===\n";

// Test critical admin files exist
$admin_files = [
    'admin/public/index.php',
    'admin/public/login.php', 
    'admin/public/logout.php',
    'admin/includes/functions.php',
    'admin/includes/session_handler.php'
];

foreach ($admin_files as $file) {
    logTest("File: {$file}", file_exists($file));
}

// Test client files exist
$client_files = [
    'client/public/index.php',
    'client/includes/contact_handler.php'
];

foreach ($client_files as $file) {
    logTest("File: {$file}", file_exists($file));
}

echo "\n=== PERMISSIONS TESTS ===\n";

// Test uploads directory
$uploads_writable = is_writable('uploads');
logTest("Uploads Directory Writable", $uploads_writable);

// Test logs directory  
$logs_writable = is_writable('logs');
logTest("Logs Directory Writable", $logs_writable);

echo "\n=== RECENT DATA TESTS ===\n";

// Test recent contacts
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contacts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recent_contacts = $stmt->fetch()['count'];
    logTest("Recent Contacts Query", true, "Found {$recent_contacts} contacts in last 7 days");
} catch (Exception $e) {
    logTest("Recent Contacts Query", false, $e->getMessage());
}

// Test recent admin logs
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recent_logs = $stmt->fetch()['count'];
    logTest("Recent Admin Logs Query", true, "Found {$recent_logs} log entries in last 7 days");
} catch (Exception $e) {
    logTest("Recent Admin Logs Query", false, $e->getMessage());
}

echo "\n=== SUMMARY ===\n";
echo "Tests Run: " . ($passed + $failed) . "\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "Success Rate: " . round(($passed / ($passed + $failed)) * 100, 1) . "%\n";

if ($failed == 0) {
    echo "\n🎉 ALL TESTS PASSED! System is ready for next phase.\n";
} else {
    echo "\n⚠️  Some tests failed. Review the failures above before proceeding.\n";
}

echo "</pre>\n";
?>
