<?php
class Settings {
    private static $settings = null;
    private static $db = null;
    
    /**
     * Initialize the settings system
     */
    private static function init() {
        if (self::$db === null) {
            self::$db = (new Database())->connect();
        }
        
        if (self::$settings === null) {
            self::loadSettings();
        }
    }
    
    /**
     * Load all settings from database
     */
    private static function loadSettings() {
        try {
            $stmt = self::$db->query("SELECT setting_key, setting_value FROM site_settings ORDER BY setting_group, display_order");
            $results = $stmt->fetchAll();
            
            self::$settings = [];
            foreach ($results as $row) {
                self::$settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Settings load error: " . $e->getMessage());
            self::$settings = [];
        }
    }
    
    /**
     * Get a setting value
     */
    public static function get($key, $default = null) {
        self::init();
        return self::$settings[$key] ?? $default;
    }
    
    /**
     * Set a setting value
     */
    public static function set($key, $value) {
        self::init();
        
        try {
            $stmt = self::$db->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
            $result = $stmt->execute([$value, $key]);
            
            if ($result) {
                self::$settings[$key] = $value;
                return true;
            }
        } catch (Exception $e) {
            error_log("Settings set error: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Get all settings grouped by category
     */
    public static function getAllGrouped() {
        self::init();
        
        try {
            $stmt = self::$db->query("
                SELECT setting_key, setting_value, setting_type, setting_group, 
                       setting_label, setting_description, is_required, display_order
                FROM site_settings 
                ORDER BY setting_group, display_order
            ");
            $results = $stmt->fetchAll();
            
            $grouped = [];
            foreach ($results as $row) {
                $grouped[$row['setting_group']][] = $row;
            }
            
            return $grouped;
        } catch (Exception $e) {
            error_log("Settings getAllGrouped error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update multiple settings at once
     */
    public static function updateMultiple($settings) {
        self::init();
        
        try {
            self::$db->beginTransaction();
            
            $stmt = self::$db->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
            
            foreach ($settings as $key => $value) {
                $stmt->execute([$value, $key]);
                self::$settings[$key] = $value;
            }
            
            self::$db->commit();
            return true;
        } catch (Exception $e) {
            self::$db->rollBack();
            error_log("Settings updateMultiple error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get settings for a specific group
     */
    public static function getGroup($group) {
        self::init();
        
        try {
            $stmt = self::$db->prepare("
                SELECT setting_key, setting_value, setting_type, setting_group, 
                       setting_label, setting_description, is_required, display_order
                FROM site_settings 
                WHERE setting_group = ?
                ORDER BY display_order
            ");
            $stmt->execute([$group]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Settings getGroup error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if maintenance mode is enabled
     */
    public static function isMaintenanceMode() {
        return self::get('maintenance_mode', '0') === '1';
    }
    
    /**
     * Get site URL (construct from server info if not set)
     */
    public static function getSiteUrl() {
        $siteUrl = self::get('site_url');
        if (!$siteUrl) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $siteUrl = $protocol . $host;
        }
        return rtrim($siteUrl, '/');
    }
    
    /**
     * Clear settings cache (force reload from database)
     */
    public static function clearCache() {
        self::$settings = null;
    }
}
?>
