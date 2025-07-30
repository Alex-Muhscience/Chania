<?php
echo "Clearing PHP opcode cache...\n";

// Clear OPcache if enabled
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "OPcache cleared successfully.\n";
    } else {
        echo "Failed to clear OPcache.\n";
    }
} else {
    echo "OPcache is not available.\n";
}

// Clear APCu cache if available
if (function_exists('apcu_clear_cache')) {
    if (apcu_clear_cache()) {
        echo "APCu cache cleared successfully.\n";
    } else {
        echo "Failed to clear APCu cache.\n";
    }
} else {
    echo "APCu is not available.\n";
}

// Clear file-based cache if it exists
$cacheDir = __DIR__ . '/../cache/';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "File cache cleared.\n";
} else {
    echo "No file cache directory found.\n";
}

echo "Cache clearing completed.\n";
?>
