<?php
/**
 * Simple file-based caching system
 */
require_once __DIR__ . '/Environment.php';

class Cache {
    private static $cacheDir = null;
    private static $enabled = null;
    private static $defaultTtl = null;
    
    /**
     * Initialize cache settings
     */
    private static function init() {
        if (self::$cacheDir === null) {
            self::$cacheDir = __DIR__ . '/../../cache/';
            self::$enabled = Environment::isCacheEnabled();
            self::$defaultTtl = Environment::getCacheTtl();
            
            // Create cache directory if it doesn't exist
            if (self::$enabled && !is_dir(self::$cacheDir)) {
                mkdir(self::$cacheDir, 0755, true);
            }
        }
    }
    
    /**
     * Generate cache key
     */
    private static function generateKey($key) {
        return md5($key);
    }
    
    /**
     * Get cache file path
     */
    private static function getFilePath($key) {
        $hashedKey = self::generateKey($key);
        return self::$cacheDir . $hashedKey . '.cache';
    }
    
    /**
     * Store data in cache
     */
    public static function set($key, $data, $ttl = null) {
        self::init();
        
        if (!self::$enabled) {
            return false;
        }
        
        $ttl = $ttl ?: self::$defaultTtl;
        $filePath = self::getFilePath($key);
        
        $cacheData = [
            'data' => $data,
            'expires_at' => time() + $ttl,
            'created_at' => time()
        ];
        
        return file_put_contents($filePath, serialize($cacheData)) !== false;
    }
    
    /**
     * Get data from cache
     */
    public static function get($key, $default = null) {
        self::init();
        
        if (!self::$enabled) {
            return $default;
        }
        
        $filePath = self::getFilePath($key);
        
        if (!file_exists($filePath)) {
            return $default;
        }
        
        $cacheData = unserialize(file_get_contents($filePath));
        
        if (!$cacheData || !isset($cacheData['expires_at'])) {
            self::delete($key);
            return $default;
        }
        
        // Check if expired
        if (time() > $cacheData['expires_at']) {
            self::delete($key);
            return $default;
        }
        
        return $cacheData['data'];
    }
    
    /**
     * Check if key exists and is not expired
     */
    public static function has($key) {
        self::init();
        
        if (!self::$enabled) {
            return false;
        }
        
        $filePath = self::getFilePath($key);
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        $cacheData = unserialize(file_get_contents($filePath));
        
        if (!$cacheData || !isset($cacheData['expires_at'])) {
            self::delete($key);
            return false;
        }
        
        if (time() > $cacheData['expires_at']) {
            self::delete($key);
            return false;
        }
        
        return true;
    }
    
    /**
     * Delete cache entry
     */
    public static function delete($key) {
        self::init();
        
        $filePath = self::getFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     */
    public static function clear() {
        self::init();
        
        if (!self::$enabled || !is_dir(self::$cacheDir)) {
            return true;
        }
        
        $files = glob(self::$cacheDir . '*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Get or set cache with callback
     */
    public static function remember($key, $callback, $ttl = null) {
        $data = self::get($key);
        
        if ($data !== null) {
            return $data;
        }
        
        $data = $callback();
        self::set($key, $data, $ttl);
        
        return $data;
    }
    
    /**
     * Clean up expired cache files
     */
    public static function cleanup() {
        self::init();
        
        if (!self::$enabled || !is_dir(self::$cacheDir)) {
            return;
        }
        
        $files = glob(self::$cacheDir . '*.cache');
        $cleanedCount = 0;
        
        foreach ($files as $file) {
            $cacheData = unserialize(file_get_contents($file));
            
            if (!$cacheData || !isset($cacheData['expires_at']) || time() > $cacheData['expires_at']) {
                unlink($file);
                $cleanedCount++;
            }
        }
        
        return $cleanedCount;
    }
    
    /**
     * Get cache statistics
     */
    public static function getStats() {
        self::init();
        
        $stats = [
            'enabled' => self::$enabled,
            'total_files' => 0,
            'total_size' => 0,
            'expired_files' => 0
        ];
        
        if (!self::$enabled || !is_dir(self::$cacheDir)) {
            return $stats;
        }
        
        $files = glob(self::$cacheDir . '*.cache');
        $stats['total_files'] = count($files);
        
        foreach ($files as $file) {
            $stats['total_size'] += filesize($file);
            
            $cacheData = unserialize(file_get_contents($file));
            if (!$cacheData || !isset($cacheData['expires_at']) || time() > $cacheData['expires_at']) {
                $stats['expired_files']++;
            }
        }
        
        return $stats;
    }
}
?>
