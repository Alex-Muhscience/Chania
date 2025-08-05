<?php
/**
 * Simple autoloader for the Chania API system
 */

spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('App' . DIRECTORY_SEPARATOR, 'app' . DIRECTORY_SEPARATOR, $file) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Include existing shared classes
require_once __DIR__ . '/shared/Core/Database.php';
require_once __DIR__ . '/shared/Core/ApiConfig.php';
