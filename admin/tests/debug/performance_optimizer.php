<?php
/**
 * Performance Optimizer
 * Analyzes and optimizes database queries and system performance
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../classes/Cache.php';

// Prevent web access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

echo "=== Digital Empowerment Network Performance Optimizer ===\n";
echo "Analyzing system performance and optimizing queries...\n\n";

$db = (new Database())->connect();
$cache = new Cache();

// Enable query logging for analysis
$db->exec("SET global general_log = 'ON'");
$db->exec("SET global log_output = 'table'");

// 1. Database Schema Analysis
echo "1. Analyzing Database Schema...\n";

// Check for missing indexes
$slowQueries = [
    "SELECT table_name, column_name FROM information_schema.columns 
     WHERE table_schema = DATABASE() AND column_name LIKE '%_id' 
     AND table_name NOT IN (
         SELECT DISTINCT table_name FROM information_schema.statistics 
         WHERE table_schema = DATABASE() AND column_name LIKE '%_id'
     )",
    
    "SELECT table_name, data_length, index_length,
            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'DB_SIZE_MB'
     FROM information_schema.tables 
     WHERE table_schema = DATABASE() 
     ORDER BY (data_length + index_length) DESC"
];

foreach ($slowQueries as $query) {
    $stmt = $db->query($query);
    $results = $stmt->fetchAll();
    
    if (!empty($results)) {
        echo "   Found potential optimization opportunities:\n";
        foreach ($results as $row) {
            echo "   - " . implode(', ', $row) . "\n";
        }
    }
}

// 2. Query Performance Analysis
echo "\n2. Analyzing Query Performance...\n";

// Get slow query log if available
try {
    $stmt = $db->query("SELECT sql_text, start_time, query_time, lock_time, rows_sent, rows_examined 
                        FROM mysql.slow_log 
                        WHERE start_time >= DATE_SUB(NOW(), INTERVAL 1 DAY) 
                        ORDER BY query_time DESC LIMIT 10");
    $slowQueries = $stmt->fetchAll();
    
    if (!empty($slowQueries)) {
        echo "   Recent slow queries found:\n";
        foreach ($slowQueries as $query) {
            echo "   - Query Time: {$query['query_time']}, Rows: {$query['rows_examined']}\n";
            echo "     SQL: " . substr($query['sql_text'], 0, 100) . "...\n";
        }
    } else {
        echo "   ✓ No slow queries detected in the last 24 hours\n";
    }
} catch (Exception $e) {
    echo "   ⚠ Could not access slow query log (this is normal on some configurations)\n";
}

// 3. Test Common Query Performance
echo "\n3. Testing Common Query Performance...\n";

$testQueries = [
    'users_count' => "SELECT COUNT(*) FROM users",
    'users_with_roles' => "SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id LIMIT 100",
    'applications_recent' => "SELECT * FROM applications WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) LIMIT 100",
    'programs_active' => "SELECT * FROM programs WHERE status = 'active' ORDER BY created_at DESC LIMIT 100",
    'events_upcoming' => "SELECT * FROM events WHERE event_date >= NOW() ORDER BY event_date ASC LIMIT 100"
];

$queryTimes = [];
foreach ($testQueries as $name => $query) {
    $start = microtime(true);
    $stmt = $db->query($query);
    $results = $stmt->fetchAll();
    $time = microtime(true) - $start;
    
    $queryTimes[$name] = $time;
    $status = $time < 0.1 ? '✓' : ($time < 0.5 ? '⚠' : '✗');
    echo "   $status $name: " . number_format($time, 4) . "s (" . count($results) . " rows)\n";
}

// 4. Memory Usage Analysis
echo "\n4. Analyzing Memory Usage...\n";
$memoryUsage = memory_get_usage(true) / 1024 / 1024;
$peakMemory = memory_get_peak_usage(true) / 1024 / 1024;

echo "   Current Memory: " . number_format($memoryUsage, 2) . " MB\n";
echo "   Peak Memory: " . number_format($peakMemory, 2) . " MB\n";

if ($memoryUsage > 32) {
    echo "   ⚠ High memory usage detected\n";
} else {
    echo "   ✓ Memory usage is acceptable\n";
}

// 5. Cache Performance Test
echo "\n5. Testing Cache Performance...\n";

// Test cache write performance
$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    $cache->set("test_$i", ['data' => "test data $i", 'timestamp' => time()]);
}
$cacheWriteTime = microtime(true) - $start;

// Test cache read performance
$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    $cache->get("test_$i");
}
$cacheReadTime = microtime(true) - $start;

echo "   Cache Write (100 items): " . number_format($cacheWriteTime, 4) . "s\n";
echo "   Cache Read (100 items): " . number_format($cacheReadTime, 4) . "s\n";

// Clean up test cache
for ($i = 0; $i < 100; $i++) {
    $cache->delete("test_$i");
}

// 6. Generate Optimization Recommendations
echo "\n6. Optimization Recommendations...\n";

$recommendations = [];

// Query performance recommendations
foreach ($queryTimes as $query => $time) {
    if ($time > 0.5) {
        $recommendations[] = "Optimize $query query - taking " . number_format($time, 4) . "s";
    }
}

// Memory recommendations
if ($memoryUsage > 32) {
    $recommendations[] = "Consider increasing PHP memory limit or optimizing memory usage";
}

// Cache recommendations
if ($cacheWriteTime > 0.1 || $cacheReadTime > 0.1) {
    $recommendations[] = "Consider implementing Redis or Memcached for better cache performance";
}

// Index recommendations
echo "   Checking for missing indexes...\n";
$indexQueries = [
    "SHOW INDEX FROM users WHERE Key_name != 'PRIMARY'",
    "SHOW INDEX FROM applications WHERE Key_name != 'PRIMARY'",
    "SHOW INDEX FROM programs WHERE Key_name != 'PRIMARY'",
    "SHOW INDEX FROM events WHERE Key_name != 'PRIMARY'"
];

foreach ($indexQueries as $query) {
    $stmt = $db->query($query);
    $indexes = $stmt->fetchAll();
    
    if (count($indexes) < 2) {
        $table = explode(' ', $query)[3];
        $recommendations[] = "Add more indexes to $table table for better query performance";
    }
}

// 7. Apply Automatic Optimizations
echo "\n7. Applying Automatic Optimizations...\n";

try {
    // Add recommended indexes if they don't exist
    $indexCommands = [
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
        "CREATE INDEX IF NOT EXISTS idx_users_role_id ON users(role_id)",
        "CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_applications_status ON applications(status)",
        "CREATE INDEX IF NOT EXISTS idx_applications_submitted_at ON applications(submitted_at)",
        "CREATE INDEX IF NOT EXISTS idx_programs_status ON programs(status)",
        "CREATE INDEX IF NOT EXISTS idx_programs_category_id ON programs(category_id)",
        "CREATE INDEX IF NOT EXISTS idx_events_event_date ON events(event_date)",
        "CREATE INDEX IF NOT EXISTS idx_events_status ON events(status)"
    ];
    
    foreach ($indexCommands as $command) {
        try {
            $db->exec($command);
            echo "   ✓ Applied index optimization\n";
        } catch (Exception $e) {
            // Index might already exist, which is fine
        }
    }
    
    // Optimize tables
    $tables = ['users', 'applications', 'programs', 'events', 'admin_logs'];
    foreach ($tables as $table) {
        try {
            $db->exec("OPTIMIZE TABLE $table");
            echo "   ✓ Optimized table: $table\n";
        } catch (Exception $e) {
            echo "   ⚠ Could not optimize table: $table\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ✗ Error applying optimizations: " . $e->getMessage() . "\n";
}

// 8. Generate Performance Report
echo "\n8. Generating Performance Report...\n";

$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'memory_usage' => [
        'current' => $memoryUsage,
        'peak' => $peakMemory
    ],
    'query_performance' => $queryTimes,
    'cache_performance' => [
        'write_time' => $cacheWriteTime,
        'read_time' => $cacheReadTime
    ],
    'recommendations' => $recommendations,
    'cache_stats' => $cache->getStats()
];

// Save report
$reportFile = __DIR__ . '/../logs/performance_report_' . date('Y-m-d_H-i-s') . '.json';
file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));

echo "   ✓ Performance report saved to: $reportFile\n";

// Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "PERFORMANCE OPTIMIZATION SUMMARY\n";
echo str_repeat("=", 60) . "\n";

echo "Memory Usage: " . number_format($memoryUsage, 2) . " MB\n";
echo "Average Query Time: " . number_format(array_sum($queryTimes) / count($queryTimes), 4) . "s\n";
echo "Cache Performance: " . ($cacheReadTime < 0.05 ? 'Good' : 'Needs Improvement') . "\n";
echo "Recommendations: " . count($recommendations) . " items\n\n";

if (!empty($recommendations)) {
    echo "RECOMMENDATIONS:\n";
    foreach ($recommendations as $rec) {
        echo "  • $rec\n";
    }
    echo "\n";
}

echo "NEXT STEPS:\n";
echo "  1. Review the detailed performance report\n";
echo "  2. Implement caching for frequently accessed data\n";
echo "  3. Monitor query performance regularly\n";
echo "  4. Consider upgrading hardware if needed\n";
echo "  5. Set up automated optimization cron jobs\n\n";

echo "Performance optimization completed!\n";
