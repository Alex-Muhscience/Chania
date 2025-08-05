<?php
/**
 * Shared API Configuration for Client-Admin Synchronization
 * This file defines API endpoints and common settings used by both client and admin
 */

class ApiConfig {
    // API Base URL
    const API_BASE_URL = '/api';
    
    // API Endpoints
    const NEWSLETTER_ENDPOINT = '/api/subscriptions/newsletter';
    const COURSE_APPLICATION_ENDPOINT = '/api/applications/course';
    const CONTACT_ENDPOINT = '/api/contact';
    const EVENT_REGISTRATION_ENDPOINT = '/api/registrations/event';
    
    // WebSocket Configuration
    const WEBSOCKET_HOST = 'localhost';
    const WEBSOCKET_PORT = 8080;
    
    // Response Status Codes
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_INFO = 'info';
    const STATUS_WARNING = 'warning';
    
    // Notification Types
    const NOTIFICATION_NEWSLETTER = 'newsletter_subscription';
    const NOTIFICATION_CONTACT = 'contact_submission';
    const NOTIFICATION_APPLICATION = 'application_submission';
    const NOTIFICATION_EVENT = 'event_registration';
    
    // Notification Settings
    const NOTIFICATION_SOUND_ENABLED = true;
    const NOTIFICATION_SOUND_FILE = '/admin/public/assets/sounds/notification.mp3';
    const NOTIFICATION_DISPLAY_DURATION = 5000; // milliseconds
    const NOTIFICATION_MAX_DISPLAY = 5; // maximum notifications to show at once
    
    // SSE Configuration
    const SSE_MAX_EXECUTION_TIME = 30; // seconds
    const SSE_HEARTBEAT_INTERVAL = 3; // seconds
    
    /**
     * Get full URL for an API endpoint
     */
    public static function getEndpointUrl($endpoint, $baseUrl = null) {
        if ($baseUrl === null) {
            $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/chania';
        }
        return rtrim($baseUrl, '/') . $endpoint;
    }
    
    /**
     * Get notification configuration
     */
    public static function getNotificationConfig($type) {
        $configs = [
            self::NOTIFICATION_NEWSLETTER => [
                'icon' => 'fas fa-envelope',
                'color' => 'success',
                'title' => 'New Newsletter Subscription',
                'sound' => true
            ],
            self::NOTIFICATION_CONTACT => [
                'icon' => 'fas fa-comments',
                'color' => 'info',
                'title' => 'New Contact Message',
                'sound' => true
            ],
            self::NOTIFICATION_APPLICATION => [
                'icon' => 'fas fa-graduation-cap',
                'color' => 'primary',
                'title' => 'New Program Application',
                'sound' => true
            ],
            self::NOTIFICATION_EVENT => [
                'icon' => 'fas fa-calendar-check',
                'color' => 'warning',
                'title' => 'New Event Registration',
                'sound' => true
            ]
        ];
        
        return $configs[$type] ?? [
            'icon' => 'fas fa-bell',
            'color' => 'secondary',
            'title' => 'New Notification',
            'sound' => false
        ];
    }
    
    /**
     * Create standardized API response
     */
    public static function createResponse($status, $message, $data = null, $errorCode = null) {
        $response = [
            'status' => $status,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        if ($errorCode !== null) {
            $response['error_code'] = $errorCode;
        }
        
        return $response;
    }
    
    /**
     * Create notification data structure
     */
    public static function createNotification($type, $id, $message, $email = null, $timestamp = null, $additionalData = []) {
        $config = self::getNotificationConfig($type);
        
        $notification = [
            'id' => $type . '_' . $id,
            'type' => $type,
            'title' => $config['title'],
            'message' => $message,
            'timestamp' => $timestamp ?? date('Y-m-d H:i:s'),
            'icon' => $config['icon'],
            'color' => $config['color'],
            'sound' => $config['sound']
        ];
        
        if ($email) {
            $notification['email'] = $email;
        }
        
        return array_merge($notification, $additionalData);
    }
    
    /**
     * Validate API request
     */
    public static function validateRequest($method = 'POST', $requiredFields = []) {
        // Check request method
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            http_response_code(405);
            return self::createResponse(
                self::STATUS_ERROR, 
                "Method not allowed. Please use {$method}.",
                null,
                'METHOD_NOT_ALLOWED'
            );
        }
        
        // Check required fields
        $data = $method === 'GET' ? $_GET : $_POST;
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            http_response_code(400);
            return self::createResponse(
                self::STATUS_ERROR,
                'Missing required fields: ' . implode(', ', $missingFields),
                null,
                'MISSING_FIELDS'
            );
        }
        
        return null; // Valid request
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIp() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Log activity for synchronization
     */
    public static function logActivity($db, $action, $entityType, $entityId, $details, $userId = null) {
        try {
            // Check if admin_logs table exists
            $stmt = $db->query("SHOW TABLES LIKE 'admin_logs'");
            if ($stmt->rowCount() > 0) {
                $logStmt = $db->prepare("
                    INSERT INTO admin_logs (user_id, action, entity_type, entity_id, details, ip_address, created_at, source)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), 'system')
                ");
                $logStmt->execute([
                    $userId,
                    $action,
                    $entityType,
                    $entityId,
                    $details,
                    self::getClientIp()
                ]);
                return true;
            }
        } catch (Exception $e) {
            error_log('Activity logging error: ' . $e->getMessage());
        }
        return false;
    }
}
?>
