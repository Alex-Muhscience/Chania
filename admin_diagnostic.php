<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Comprehensive Admin Diagnostic</h1>";

// Test 1: Basic includes
echo "<h2>1. Core Files Test</h2>";
$coreFiles = [
    'shared/Core/Database.php',
    'shared/Core/Utilities.php', 
    'admin/includes/config.php',
    'admin/includes/simple_session.php',
    'admin/includes/auth.php',
    'admin/includes/header.php',
    'admin/includes/footer.php',
    'admin/public/login.php',
    'admin/public/index.php',
    'admin/public/logout.php'
];

foreach ($coreFiles as $file) {
    $path = __DIR__ . '/' . $file;
    echo "$file: " . (file_exists($path) ? '✓ EXISTS' : '✗ MISSING') . "<br>";
}

// Test 2: Database tables
echo "<h2>2. Database Tables Test</h2>";
try {
    require_once __DIR__ . '/shared/Core/Database.php';
    require_once __DIR__ . '/shared/Core/Utilities.php'; 
    require_once __DIR__ . '/admin/includes/config.php';
    require_once __DIR__ . '/admin/includes/simple_session.php';
    
    $db = (new Database())->connect();
    echo "✓ Database connected<br>";
    
    $requiredTables = [
        'users', 'admin_logs', 'sessions', 'login_attempts',
        'programs', 'applications', 'events', 'contacts',
        'testimonials', 'partners', 'team_members',
        'newsletter_subscribers', 'file_uploads'
    ];
    
    foreach ($requiredTables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "✓ $table: $count records<br>";
        } catch (Exception $e) {
            echo "✗ $table: " . $e->getMessage() . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Login simulation
echo "<h2>3. Login Simulation Test</h2>";
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'admin'; 

echo "Session status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "isLoggedIn(): " . (Utilities::isLoggedIn() ? 'TRUE' : 'FALSE') . "<br>";

// Test 4: URL tests
echo "<h2>4. URL Access Tests</h2>";
$testUrls = [
    'admin/public/login.php',
    'admin/public/index.php', 
    'admin/public/test_dashboard.php',
    'admin/public/logout.php'
];

foreach ($testUrls as $url) {
    $fullUrl = "http://localhost/chania/$url";
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true,
            'header' => "Cookie: " . session_name() . "=" . session_id()
        ]
    ]);
    
    $response = @file_get_contents($fullUrl, false, $context);
    $responseCode = isset($http_response_header[0]) ? $http_response_header[0] : 'No response';
    
    echo "$url: ";
    if ($response !== false) {
        echo "✓ ACCESSIBLE ($responseCode)<br>";
    } else {
        echo "✗ ERROR ($responseCode)<br>";
    }
}

// Test 5: Permission tests
echo "<h2>5. File Permissions Test</h2>";
$checkPaths = [
    __DIR__ . '/uploads',
    __DIR__ . '/logs',
    __DIR__ . '/admin/public/assets',
];

foreach ($checkPaths as $path) {
    if (is_dir($path)) {
        echo basename($path) . ": ";
        echo is_readable($path) ? '✓ Readable ' : '✗ Not readable ';
        echo is_writable($path) ? '✓ Writable' : '✗ Not writable';
        echo "<br>";
    } else {
        echo basename($path) . ": ✗ Directory not found<br>";
    }
}
?>

<h2>6. Quick Test Links</h2>
<p><a href="admin/public/login.php" target="_blank">Login Page</a></p>
<p><a href="admin/public/test_dashboard.php" target="_blank">Test Dashboard</a></p>
<p><a href="admin/public/index.php" target="_blank">Full Dashboard</a></p>
<p><a href="test_admin_routes.php" target="_blank">Route Test</a></p>
