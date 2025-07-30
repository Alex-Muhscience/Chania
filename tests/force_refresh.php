<?php
// Force refresh web environment
echo "Forcing web environment refresh...\n";

// Clear file stat cache
clearstatcache();
echo "✓ File stat cache cleared\n";

// Force garbage collection
if (function_exists('gc_collect_cycles')) {
    $collected = gc_collect_cycles();
    echo "✓ Garbage collection: $collected cycles collected\n";
}

// Clear any include/require cache by touching files
$filesToTouch = [
    __DIR__ . '/../client/src/Models/ApplicationModel.php',
    __DIR__ . '/../client/src/Services/submit_application.php',
    __DIR__ . '/../shared/Core/Database.php'
];

foreach ($filesToTouch as $file) {
    if (file_exists($file)) {
        touch($file);
        echo "✓ Touched: " . basename($file) . "\n";
    }
}

// Output PHP configuration info
echo "\nPHP Info:\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "OPcache enabled: " . (function_exists('opcache_get_status') ? 'Yes' : 'No') . "\n";

if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status && $status['opcache_enabled']) {
        echo "OPcache memory usage: " . number_format($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
    }
}

echo "\nRefresh completed. Please test the application submission again.\n";
?>
