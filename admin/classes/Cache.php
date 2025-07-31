<?php

/**
 * Cache Management Class
 * Handles caching operations to improve system performance
 */
class Cache
{
    private $cacheDir;
    private $defaultTTL;
    
    public function __construct($cacheDir = null, $defaultTTL = 3600)
    {
        $this->cacheDir = $cacheDir ?? __DIR__ . '/../cache/';
        $this->defaultTTL = $defaultTTL;
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Get cached data
     */
    public function get($key)
    {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }
        
        $data = file_get_contents($filename);
        $cached = json_decode($data, true);
        
        if (!$cached || !isset($cached['expires'], $cached['data'])) {
            return false;
        }
        
        // Check if cache has expired
        if (time() > $cached['expires']) {
            $this->delete($key);
            return false;
        }
        
        return $cached['data'];
    }
    
    /**
     * Store data in cache
     */
    public function set($key, $data, $ttl = null)
    {
        $ttl = $ttl ?? $this->defaultTTL;
        $filename = $this->getCacheFilename($key);
        
        $cacheData = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($filename, json_encode($cacheData)) !== false;
    }
    
    /**
     * Delete cached data
     */
    public function delete($key)
    {
        $filename = $this->getCacheFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     */
    public function clear()
    {
        $files = glob($this->cacheDir . '*.cache');
        $cleared = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $cleared++;
            }
        }
        
        return $cleared;
    }
    
    /**
     * Get cache statistics
     */
    public function getStats()
    {
        $files = glob($this->cacheDir . '*.cache');
        $totalSize = 0;
        $validCount = 0;
        $expiredCount = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $data = file_get_contents($file);
            $cached = json_decode($data, true);
            
            if ($cached && isset($cached['expires'])) {
                if (time() > $cached['expires']) {
                    $expiredCount++;
                } else {
                    $validCount++;
                }
            }
        }
        
        return [
            'total_files' => count($files),
            'valid_cache' => $validCount,
            'expired_cache' => $expiredCount,
            'total_size' => $totalSize,
            'cache_dir' => $this->cacheDir
        ];
    }
    
    /**
     * Clean expired cache entries
     */
    public function cleanup()
    {
        $files = glob($this->cacheDir . '*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = file_get_contents($file);
            $cached = json_decode($data, true);
            
            if (!$cached || !isset($cached['expires']) || time() > $cached['expires']) {
                if (unlink($file)) {
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Cache with callback - get from cache or execute function and cache result
     */
    public function remember($key, $callback, $ttl = null)
    {
        $data = $this->get($key);
        
        if ($data !== false) {
            return $data;
        }
        
        $data = $callback();
        $this->set($key, $data, $ttl);
        
        return $data;
    }
    
    /**
     * Generate cache filename from key
     */
    private function getCacheFilename($key)
    {
        return $this->cacheDir . md5($key) . '.cache';
    }
}
