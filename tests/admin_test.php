<?php
/**
 * Admin Panel Testing and Routing Script
 * Tests all admin panel components and ensures proper routing
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Chania Admin Panel - Testing & Routing</h1>";
echo "<hr>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    require_once __DIR__ . '/shared/Core/Database.php';
    $db = (new Database())->connect();
    echo "<span style='color: green;'>✓ Database connection successful</span><br>";
    
    // Test database tables
    $tables = ['users', 'programs', 'applications', 'events', 'contacts', 'admin_logs'];
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "<span style='color: green;'>✓ Table '$table' exists - {$result['count']} records</span><br>";
        } catch (Exception $e) {
            echo "<span style='color: red;'>✗ Table '$table' error: {$e->getMessage()}</span><br>";
        }
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Database connection failed: {$e->getMessage()}</span><br>";
}

echo "<hr>";

// Test 2: Core Classes
echo "<h2>2. Core Classes Test</h2>";
try {
    require_once __DIR__ . '/shared/Core/Utilities.php';
    echo "<span style='color: green;'>✓ Utilities class loaded</span><br>";
    
    // Test Utilities methods
    $methods = ['isLoggedIn', 'sanitizeInput', 'formatDate', 'generateToken', 'formatFileSize'];
    foreach ($methods as $method) {
        if (method_exists('Utilities', $method)) {
            echo "<span style='color: green;'>✓ Method '$method' exists</span><br>";
        } else {
            echo "<span style='color: red;'>✗ Method '$method' missing</span><br>";
        }
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Core classes error: {$e->getMessage()}</span><br>";
}

echo "<hr>";

// Test 3: Admin Configuration
echo "<h2>3. Admin Configuration Test</h2>";
try {
    require_once __DIR__ . '/admin/includes/config.php';
    echo "<span style='color: green;'>✓ Admin config loaded</span><br>";
    
    $constants = ['BASE_URL', 'ADMIN_URL', 'DB_HOST', 'DB_NAME', 'UPLOAD_PATH'];
    foreach ($constants as $constant) {
        if (defined($constant)) {
            echo "<span style='color: green;'>✓ Constant '$constant' defined: " . constant($constant) . "</span><br>";
        } else {
            echo "<span style='color: red;'>✗ Constant '$constant' not defined</span><br>";
        }
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Admin config error: {$e->getMessage()}</span><br>";
}

echo "<hr>";

// Test 4: Admin Panel Files
echo "<h2>4. Admin Panel Files Test</h2>";
$adminFiles = [
    '/admin/includes/header.php',
    '/admin/includes/footer.php',
    '/admin/includes/auth.php',
    '/admin/public/index.php',
    '/admin/public/login.php',
    '/admin/public/logout.php',
    '/admin/public/users.php',
    '/admin/public/applications.php',
    '/admin/public/events.php',
    '/admin/public/contacts.php',
    '/admin/public/programs.php'
];

foreach ($adminFiles as $file) {
    $fullPath = __DIR__ . $file;
    if (file_exists($fullPath)) {
        echo "<span style='color: green;'>✓ File exists: $file</span><br>";
    } else {
        echo "<span style='color: red;'>✗ File missing: $file</span><br>";
    }
}

echo "<hr>";

// Test 5: Asset Files
echo "<h2>5. Asset Files Test</h2>";
$assetFiles = [
    '/admin/public/assets/css/admin.css',
    '/admin/public/assets/css/sb-admin-2.min.css',
    '/admin/public/assets/js/admin.js',
    '/admin/public/assets/js/main.js'
];

foreach ($assetFiles as $file) {
    $fullPath = __DIR__ . $file;
    if (file_exists($fullPath)) {
        echo "<span style='color: green;'>✓ Asset exists: $file</span><br>";
    } else {
        echo "<span style='color: red;'>✗ Asset missing: $file</span><br>";
    }
}

echo "<hr>";

// Test 6: Directory Permissions
echo "<h2>6. Directory Permissions Test</h2>";
$directories = [
    '/uploads',
    '/logs',
    '/admin/public',
    '/admin/includes'
];

foreach ($directories as $dir) {
    $fullPath = __DIR__ . $dir;
    if (is_dir($fullPath)) {
        if (is_writable($fullPath)) {
            echo "<span style='color: green;'>✓ Directory writable: $dir</span><br>";
        } else {
            echo "<span style='color: orange;'>⚠ Directory not writable: $dir</span><br>";
        }
    } else {
        echo "<span style='color: red;'>✗ Directory missing: $dir</span><br>";
    }
}

echo "<hr>";

// Test 7: URL Routing Test
echo "<h2>7. URL Routing Test</h2>";
$baseUrl = 'http://localhost/chania';
$adminRoutes = [
    '/admin/public/login.php' => 'Login Page',
    '/admin/public/index.php' => 'Dashboard',
    '/admin/public/users.php' => 'Users Management',
    '/admin/public/applications.php' => 'Applications',
    '/admin/public/events.php' => 'Events',
    '/admin/public/contacts.php' => 'Contacts',
    '/admin/public/programs.php' => 'Programs'
];

foreach ($adminRoutes as $route => $description) {
    $url = $baseUrl . $route;
    echo "<a href='$url' target='_blank' style='color: blue;'>🔗 $description</a> - $url<br>";
}

echo "<hr>";

// Test 8: Session Management
echo "<h2>8. Session Management Test</h2>";
session_start();
echo "<span style='color: green;'>✓ Session started successfully</span><br>";
echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . session_status() . "<br>";

if (isset($_SESSION['user_id'])) {
    echo "<span style='color: green;'>✓ User logged in - ID: {$_SESSION['user_id']}</span><br>";
} else {
    echo "<span style='color: orange;'>⚠ No user logged in</span><br>";
}

echo "<hr>";

// Test 9: Missing Components Detection
echo "<h2>9. Missing Components Detection</h2>";

// Check for missing admin files that should exist
$missingFiles = [];
$criticalFiles = [
    '/admin/public/profile.php',
    '/admin/public/settings.php',
    '/admin/public/system_monitor.php',
    '/admin/public/admin_logs.php'
];

foreach ($criticalFiles as $file) {
    if (!file_exists(__DIR__ . $file)) {
        $missingFiles[] = $file;
    }
}

if (empty($missingFiles)) {
    echo "<span style='color: green;'>✓ All critical admin files present</span><br>";
} else {
    echo "<span style='color: red;'>✗ Missing critical files:</span><br>";
    foreach ($missingFiles as $file) {
        echo "<span style='color: red;'>  - $file</span><br>";
    }
}

echo "<hr>";

// Test 10: Recommendations
echo "<h2>10. Recommendations</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff;'>";
echo "<h4>Next Steps:</h4>";
echo "<ol>";
echo "<li>Fix any missing files identified above</li>";
echo "<li>Test login functionality with a valid admin user</li>";
echo "<li>Verify all admin routes are accessible</li>";
echo "<li>Check database schema for all required tables</li>";
echo "<li>Test file upload functionality</li>";
echo "<li>Verify session management and security</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
