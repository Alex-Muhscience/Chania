<?php

use Models\ApplicationModel;

require_once __DIR__ . '/../shared/Core/Database.php';

echo "=== APPLICATION ERROR DIAGNOSIS ===\n\n";

// 1. Check database connection
try {
    echo "1. Testing database connection...\n";
    $db = (new Database())->connect();
    echo "✓ Database connection successful\n";
    
    // Check which database we're connected to
    $stmt = $db->query("SELECT DATABASE()");
    $dbName = $stmt->fetchColumn();
    echo "✓ Connected to database: $dbName\n\n";
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Verify applications table structure
echo "2. Verifying applications table structure...\n";
try {
    $stmt = $db->query("DESCRIBE applications");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredColumns = ['entity_type', 'entity_id', 'education_details', 'work_experience'];
    $foundColumns = array_column($columns, 'Field');
    
    foreach ($requiredColumns as $col) {
        if (in_array($col, $foundColumns)) {
            echo "✓ Column '$col' exists\n";
        } else {
            echo "✗ Column '$col' missing\n";
        }
    }
    echo "\n";
    
} catch (PDOException $e) {
    echo "✗ Error checking table structure: " . $e->getMessage() . "\n";
}

// 3. Test ApplicationModel class
echo "3. Testing ApplicationModel class...\n";
try {
    require_once __DIR__ . '/../client/src/Models/ApplicationModel.php';
    $model = new ApplicationModel($db);
    echo "✓ ApplicationModel class loaded successfully\n";
    
    // Test data
    $testData = [
        'program_id' => 1,
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'phone' => '+1234567890',
        'address' => '123 Test Street',
        'education' => 'Test education',
        'experience' => 'Test experience',
        'motivation' => 'Test motivation'
    ];
    
    // Check if method exists
    if (method_exists($model, 'submitApplication')) {
        echo "✓ submitApplication method exists\n";
        
        // Try to call the method (but don't actually execute)
        $reflection = new ReflectionMethod($model, 'submitApplication');
        echo "✓ Method is callable\n";
    } else {
        echo "✗ submitApplication method not found\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "✗ Error with ApplicationModel: " . $e->getMessage() . "\n";
}

// 4. Check for file timestamps and modification
echo "4. Checking file modification times...\n";
$files = [
    __DIR__ . '/../client/src/Models/ApplicationModel.php',
    __DIR__ . '/../client/src/Services/submit_application.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $mtime = filemtime($file);
        $timeAgo = time() - $mtime;
        echo "✓ " . basename($file) . " modified " . $timeAgo . " seconds ago\n";
    } else {
        echo "✗ " . basename($file) . " not found\n";
    }
}
echo "\n";

// 5. Check error logs for recent entries
echo "5. Checking recent error log entries...\n";
$logFile = __DIR__ . '/../logs/error.log';
if (file_exists($logFile)) {
    $handle = fopen($logFile, 'r');
    if ($handle) {
        fseek($handle, -2000, SEEK_END); // Read last 2000 bytes
        $content = fread($handle, 2000);
        fclose($handle);
        
        $lines = explode("\n", $content);
        $recentErrors = array_filter($lines, function($line) {
            return strpos($line, 'Application') !== false && strpos($line, 'entity_id') !== false;
        });
        
        if (!empty($recentErrors)) {
            echo "Recent entity_id errors found:\n";
            foreach (array_slice($recentErrors, -3) as $error) {
                echo "  " . trim($error) . "\n";
            }
        } else {
            echo "✓ No recent entity_id errors in log\n";
        }
    }
} else {
    echo "✗ Error log file not found\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";
echo "If errors persist, please restart your web server (Apache) and try again.\n";
?>
