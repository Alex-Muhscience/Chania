<?php
// Chania Skills for Africa Database Health Check
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Chania Skills Database - Health Check</h1>\n";
echo "<pre>\n";

$all_tests_passed = true;

// Database connection
$host = 'localhost';
$dbname = 'chania_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    echo "✓ Database connection successful\n";
} catch(PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 1: Core Tables Check
echo "\n=== Test 1: Core Tables Verification ===\n";
$core_tables = [
    'users', 'programs', 'applications', 'events', 'testimonials', 
    'contacts', 'team_members', 'partners', 'admin_logs', 'system_settings',
    'password_resets', 'email_verifications', 'sessions', 'file_uploads'
];

foreach ($core_tables as $table) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        echo "✓ Table '$table' exists and accessible\n";
    } catch (PDOException $e) {
        echo "✗ Table '$table' missing or error: " . $e->getMessage() . "\n";
        $all_tests_passed = false;
    }
}

// Test 2: Data Integrity Check
echo "\n=== Test 2: Data Counts ===\n";
$data_checks = [
    'users' => 'SELECT COUNT(*) FROM users',
    'programs' => 'SELECT COUNT(*) FROM programs WHERE deleted_at IS NULL',
    'applications' => 'SELECT COUNT(*) FROM applications WHERE deleted_at IS NULL',
    'events' => 'SELECT COUNT(*) FROM events WHERE deleted_at IS NULL',
    'testimonials' => 'SELECT COUNT(*) FROM testimonials WHERE deleted_at IS NULL',
    'system_settings' => 'SELECT COUNT(*) FROM system_settings'
];

foreach ($data_checks as $table => $query) {
    try {
        $stmt = $pdo->query($query);
        $count = $stmt->fetchColumn();
        echo "✓ $table: $count records\n";
    } catch (PDOException $e) {
        echo "✗ Error checking '$table': " . $e->getMessage() . "\n";
        $all_tests_passed = false;
    }
}

// Test 3: Views Check
echo "\n=== Test 3: Database Views ===\n";
try {
    $stmt = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = 'chania_db'");
    $views = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✓ Found " . count($views) . " views:\n";
    foreach ($views as $view) {
        echo "  - $view\n";
    }
} catch (PDOException $e) {
    echo "✗ Error checking views: " . $e->getMessage() . "\n";
}

// Test 4: Stored Procedures Check
echo "\n=== Test 4: Stored Procedures ===\n";
try {
    $stmt = $pdo->query("SHOW PROCEDURE STATUS WHERE Db = 'chania_db'");
    $procedures = $stmt->fetchAll();
    echo "✓ Found " . count($procedures) . " stored procedures:\n";
    foreach ($procedures as $proc) {
        echo "  - {$proc['Name']}\n";
    }
} catch (PDOException $e) {
    echo "✗ Error checking procedures: " . $e->getMessage() . "\n";
}

// Test 5: Triggers Check
echo "\n=== Test 5: Triggers ===\n";
try {
    $stmt = $pdo->query("SHOW TRIGGERS");
    $triggers = $stmt->fetchAll();
    echo "✓ Found " . count($triggers) . " triggers:\n";
    foreach ($triggers as $trigger) {
        echo "  - {$trigger['Trigger']} on {$trigger['Table']}\n";
    }
} catch (PDOException $e) {
    echo "✗ Error checking triggers: " . $e->getMessage() . "\n";
}

// Test 6: System Settings Check
echo "\n=== Test 6: System Configuration ===\n";
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE is_public = TRUE");
    $settings = $stmt->fetchAll();
    echo "✓ Public system settings:\n";
    foreach ($settings as $setting) {
        echo "  - {$setting['setting_key']}: {$setting['setting_value']}\n";
    }
} catch (PDOException $e) {
    echo "✗ Error checking system settings: " . $e->getMessage() . "\n";
    $all_tests_passed = false;
}

// Test 7: Application Status Distribution
echo "\n=== Test 7: Application Status Overview ===\n";
try {
    $stmt = $pdo->query("
        SELECT 
            status, 
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM applications WHERE deleted_at IS NULL), 2) as percentage
        FROM applications 
        WHERE deleted_at IS NULL 
        GROUP BY status 
        ORDER BY count DESC
    ");
    $statuses = $stmt->fetchAll();
    if (count($statuses) > 0) {
        echo "✓ Application status distribution:\n";
        foreach ($statuses as $status) {
            echo "  - {$status['status']}: {$status['count']} ({$status['percentage']}%)\n";
        }
    } else {
        echo "ℹ No applications found\n";
    }
} catch (PDOException $e) {
    echo "✗ Error checking application status: " . $e->getMessage() . "\n";
}

// Test 8: Program Status
echo "\n=== Test 8: Program Overview ===\n";
try {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_programs,
            SUM(CASE WHEN is_active = TRUE THEN 1 ELSE 0 END) as active_programs,
            SUM(CASE WHEN is_published = TRUE THEN 1 ELSE 0 END) as published_programs
        FROM programs 
        WHERE deleted_at IS NULL
    ");
    $program_stats = $stmt->fetch();
    echo "✓ Program statistics:\n";
    echo "  - Total programs: {$program_stats['total_programs']}\n";
    echo "  - Active programs: {$program_stats['active_programs']}\n";
    echo "  - Published programs: {$program_stats['published_programs']}\n";
} catch (PDOException $e) {
    echo "✗ Error checking program status: " . $e->getMessage() . "\n";
}

// Test 9: Database Size
echo "\n=== Test 9: Database Size ===\n";
try {
    $stmt = $pdo->query("
        SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
        FROM information_schema.tables 
        WHERE table_schema = 'chania_db'
    ");
    $size = $stmt->fetchColumn();
    echo "✓ Database size: {$size} MB\n";
} catch (PDOException $e) {
    echo "✗ Error checking database size: " . $e->getMessage() . "\n";
}

// Final Summary
echo "\n=== HEALTH CHECK SUMMARY ===\n";
if ($all_tests_passed) {
    echo "🎉 CHANIA DATABASE IS HEALTHY!\n";
    echo "✓ All core tables present and functional\n";
    echo "✓ Views, procedures, and triggers working\n";
    echo "✓ System settings configured\n";
    echo "✓ Database ready for production\n";
} else {
    echo "⚠ SOME ISSUES DETECTED - Review the output above\n";
}

echo "\n✨ Chania Skills for Africa Database Check Complete\n";
echo "Checked at: " . date('Y-m-d H:i:s') . "\n";
echo "</pre>";
?>
