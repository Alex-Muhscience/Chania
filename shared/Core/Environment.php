<?php
class Environment {
    private static $loaded = false;
    
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }
        
        if ($path === null) {
            $path = __DIR__ . '/../../.env';
        }
        
        if (!file_exists($path)) {
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Skip comments
            }
            
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes if present
                if (($value[0] === '"' && $value[strlen($value) - 1] === '"') ||
                    ($value[0] === "'" && $value[strlen($value) - 1] === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv("{$name}={$value}");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
        
        self::$loaded = true;
    }
    
    public static function get($key, $default = null) {
        $value = $_ENV[$key] ?? getenv($key) ?: $default;
        
        // Convert string booleans to actual booleans
        if (in_array(strtolower($value), ['true', 'false'])) {
            return strtolower($value) === 'true';
        }
        
        // Convert string numbers to integers
        if (is_numeric($value)) {
            return is_float($value + 0) ? (float)$value : (int)$value;
        }
        
        return $value;
    }
    
    /**
     * Get JWT configuration
     */
    public static function getJwtSecret() {
        return self::get('JWT_SECRET_KEY', 'default_secret_change_in_production');
    }
    
    public static function getJwtExpiry() {
        return (int) self::get('JWT_EXPIRY', 86400);
    }
    
    /**
     * Get rate limiting configuration
     */
    public static function getRateLimitRequests() {
        return (int) self::get('RATE_LIMIT_REQUESTS', 100);
    }
    
    public static function getRateLimitWindow() {
        return (int) self::get('RATE_LIMIT_WINDOW', 3600);
    }
    
    /**
     * Get cache configuration
     */
    public static function isCacheEnabled() {
        return filter_var(self::get('CACHE_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN);
    }
    
    public static function getCacheTtl() {
        return (int) self::get('CACHE_TTL', 3600);
    }
    
    /**
     * Get security configuration
     */
    public static function getBcryptRounds() {
        return (int) self::get('BCRYPT_ROUNDS', 12);
    }
    
    /**
     * Check if debug mode is enabled
     */
    public static function isDebugMode() {
        return filter_var(self::get('DEBUG_MODE', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Check if in development environment
     */
    public static function isDevelopment() {
        return self::get('APP_ENV', 'production') === 'development';
    }
}
?>
