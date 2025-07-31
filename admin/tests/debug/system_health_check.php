<?php
/**
 * Digital Empowerment Network - System Health Check
 * Comprehensive testing script to identify bugs and issues
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';

// Prevent web access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

echo "=== Digital Empowerment Network System Health Check ===\n";
echo "Running comprehensive system diagnostics...\n\n";

// Test results storage
$tests = [];
$errors = [];
$warnings = [];

// 1. Database Connection Test
echo "1. Testing Database Connection...\n";
try {
    $db = (new Database())->connect();
    if ($db) {
        $tests['database_connection'] = 'PASS';
        echo "   ✓ Database connection successful\n";
    } else {
        $tests['database_connection'] = 'FAIL';
        $errors[] = 'Database connection failed';
        echo "   ✗ Database connection failed\n";
    }
} catch (Exception $e) {
    $tests['database_connection'] = 'FAIL';
    $errors[] = 'Database connection error: ' . $e->getMessage();
    echo "   ✗ Database connection error: " . $e->getMessage() . "\n";
}

// 2. Required Tables Test
echo "\n2. Testing Database Schema...\n";
$requiredTables = [
    'users', 'roles', 'permissions', 'role_permissions', 
    'applications', 'programs', 'events', 'contacts',
    'blog_posts', 'faqs', 'pages', 'media_library',
    'email_templates', 'admin_logs', 'security_logs',
    'site_settings', 'file_uploads'
];

$missingTables = [];
foreach ($requiredTables as $table) {
    try {
        $stmt = $db->query("SELECT 1 FROM `$table` LIMIT 1");
        echo "   ✓ Table `$table` exists\n";
    } catch (Exception $e) {
        $missingTables[] = $table;
        echo "   ✗ Table `$table` missing or inaccessible\n";
    }
}

if (empty($missingTables)) {
    $tests['database_schema'] = 'PASS';
} else {
    $tests['database_schema'] = 'FAIL';
    $errors[] = 'Missing tables: ' . implode(', ', $missingTables);
}

// 3. PHP Extensions Test
echo "\n3. Testing Required PHP Extensions...\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl', 'curl'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✓ Extension `$ext` loaded\n";
    } else {
        $missingExtensions[] = $ext;
        echo "   ✗ Extension `$ext` missing\n";
    }
}

if (empty($missingExtensions)) {
    $tests['php_extensions'] = 'PASS';
} else {
    $tests['php_extensions'] = 'FAIL';
    $errors[] = 'Missing PHP extensions: ' . implode(', ', $missingExtensions);
}

// 4. File Permissions Test
echo "\n4. Testing File Permissions...\n";
$writablePaths = [
    __DIR__ . '/../../uploads',
    __DIR__ . '/../../admin/logs',
    __DIR__ . '/../../shared/temp',
    __DIR__ . '/../../assets/media'
];

$permissionIssues = [];
foreach ($writablePaths as $path) {
    if (!file_exists($path)) {
        if (!mkdir($path, 0755, true)) {
            $permissionIssues[] = "Cannot create directory: $path";
            echo "   ✗ Cannot create directory: $path\n";
            continue;
        }
    }
    
    if (is_writable($path)) {
        echo "   ✓ Directory writable: $path\n";
    } else {
        $permissionIssues[] = "Directory not writable: $path";
        echo "   ✗ Directory not writable: $path\n";
    }
}

if (empty($permissionIssues)) {
    $tests['file_permissions'] = 'PASS';
} else {
    $tests['file_permissions'] = 'FAIL';
    $errors = array_merge($errors, $permissionIssues);
}

// 5. Core Classes Test
echo "\n5. Testing Core Classes...\n";
$coreClasses = [
    'User' => __DIR__ . '/../../shared/Core/User.php',
    'Database' => __DIR__ . '/../../shared/Core/Database.php',
    'Utilities' => __DIR__ . '/../../shared/Core/Utilities.php',
    'Program' => __DIR__ . '/../classes/Program.php',
    'Application' => __DIR__ . '/../classes/Application.php',
    'Event' => __DIR__ . '/../classes/Event.php'
];

$classIssues = [];
foreach ($coreClasses as $className => $filePath) {
    if (file_exists($filePath)) {
        try {
            require_once $filePath;
            if (class_exists($className)) {
                echo "   ✓ Class `$className` loaded successfully\n";
            } else {
                $classIssues[] = "Class `$className` not found in file";
                echo "   ✗ Class `$className` not found in file\n";
            }
        } catch (Exception $e) {
            $classIssues[] = "Error loading class `$className`: " . $e->getMessage();
            echo "   ✗ Error loading class `$className`: " . $e->getMessage() . "\n";
        }
    } else {
        $classIssues[] = "File not found: $filePath";
        echo "   ✗ File not found: $filePath\n";
    }
}

if (empty($classIssues)) {
    $tests['core_classes'] = 'PASS';
} else {
    $tests['core_classes'] = 'FAIL';
    $errors = array_merge($errors, $classIssues);
}

// 6. Configuration Test
echo "\n6. Testing Configuration...\n";
$configIssues = [];

// Check required constants
$requiredConstants = ['BASE_URL', 'DB_HOST', 'DB_NAME', 'DB_USER'];
foreach ($requiredConstants as $constant) {
    if (defined($constant)) {
        echo "   ✓ Constant `$constant` defined\n";
    } else {
        $configIssues[] = "Missing constant: $constant";
        echo "   ✗ Missing constant: $constant\n";
    }
}

// Check session configuration
if (session_status() === PHP_SESSION_ACTIVE || headers_sent()) {
    echo "   ✓ Session handling configured\n";
} else {
    $warnings[] = "Session not started - may cause issues in web context";
    echo "   ⚠ Session not started (CLI context - normal)\n";
}

if (empty($configIssues)) {
    $tests['configuration'] = 'PASS';
} else {
    $tests['configuration'] = 'FAIL';
    $errors = array_merge($errors, $configIssues);
}

// 7. Security Test
echo "\n7. Testing Security Configuration...\n";
$securityIssues = [];

// Check if admin user exists
try {
    $stmt = $db->prepare("SELECT COUNT(*) as admin_count FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'admin'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['admin_count'] > 0) {
        echo "   ✓ Admin user(s) found\n";
    } else {
        $securityIssues[] = "No admin users found in system";
        echo "   ✗ No admin users found in system\n";
    }
} catch (Exception $e) {
    $securityIssues[] = "Cannot verify admin users: " . $e->getMessage();
    echo "   ✗ Cannot verify admin users: " . $e->getMessage() . "\n";
}

// Check password hashing
if (function_exists('password_hash') && function_exists('password_verify')) {
    echo "   ✓ Password hashing functions available\n";
} else {
    $securityIssues[] = "Password hashing functions not available";
    echo "   ✗ Password hashing functions not available\n";
}

if (empty($securityIssues)) {
    $tests['security'] = 'PASS';
} else {
    $tests['security'] = 'FAIL';
    $errors = array_merge($errors, $securityIssues);
}

// 8. Performance Test (Basic)
echo "\n8. Running Basic Performance Tests...\n";
$performanceIssues = [];

// Test database query performance
$start = microtime(true);
try {
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    $queryTime = microtime(true) - $start;
    
    if ($queryTime < 1.0) {
        echo "   ✓ Database query performance acceptable ({$queryTime}s)\n";
    } else {
        $performanceIssues[] = "Slow database queries detected ({$queryTime}s)";
        echo "   ⚠ Slow database query detected ({$queryTime}s)\n";
    }
} catch (Exception $e) {
    $performanceIssues[] = "Cannot test database performance: " . $e->getMessage();
    echo "   ✗ Cannot test database performance: " . $e->getMessage() . "\n";
}

// Check memory usage
$memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
if ($memoryUsage < 32) {
    echo "   ✓ Memory usage acceptable ({$memoryUsage}MB)\n";
} else {
    $warnings[] = "High memory usage detected ({$memoryUsage}MB)";
    echo "   ⚠ High memory usage detected ({$memoryUsage}MB)\n";
}

if (empty($performanceIssues)) {
    $tests['performance'] = 'PASS';
} else {
    $tests['performance'] = 'WARNING';
    $warnings = array_merge($warnings, $performanceIssues);
}

// Summary Report
echo "\n" . str_repeat("=", 60) . "\n";
echo "SYSTEM HEALTH CHECK SUMMARY\n";
echo str_repeat("=", 60) . "\n";

$passCount = array_count_values($tests)['PASS'] ?? 0;
$failCount = array_count_values($tests)['FAIL'] ?? 0;
$warnCount = array_count_values($tests)['WARNING'] ?? 0;

echo "Tests Run: " . count($tests) . "\n";
echo "Passed: $passCount\n";
echo "Failed: $failCount\n";
echo "Warnings: $warnCount\n\n";

if (!empty($errors)) {
    echo "CRITICAL ERRORS:\n";
    foreach ($errors as $error) {
        echo "  ✗ $error\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  ⚠ $warning\n";
    }
    echo "\n";
}

// Overall Status
if ($failCount > 0) {
    echo "OVERALL STATUS: FAILED\n";
    echo "Please address the critical errors before deploying to production.\n";
    exit(1);
} elseif ($warnCount > 0) {
    echo "OVERALL STATUS: WARNING\n";
    echo "System functional but consider addressing warnings for optimal performance.\n";
    exit(0);
} else {
    echo "OVERALL STATUS: HEALTHY\n";
    echo "All systems are functioning correctly.\n";
    exit(0);
}
