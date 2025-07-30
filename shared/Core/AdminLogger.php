<?php

class AdminLogger
{
    private static $db = null;

    /**
     * Initialize database connection
     */
    private static function getDb()
    {
        if (self::$db === null) {
            require_once __DIR__ . '/Database.php';
            $database = new Database();
            self::$db = $database->connect();
        }
        return self::$db;
    }

    /**
     * Log admin activity
     * 
     * @param string $action The action performed
     * @param string|null $entityType The type of entity affected
     * @param int|null $entityId The ID of the entity affected
     * @param array|string|null $details Additional details
     * @param int|null $userId The user ID (if null, will try to get from session)
     */
    public static function log($action, $entityType = null, $entityId = null, $details = null, $userId = null)
    {
        try {
            $db = self::getDb();
            
            // Get user ID from session if not provided
            if ($userId === null && php_sapi_name() !== 'cli') {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $userId = $_SESSION['user_id'] ?? null;
            }
            
            // Get request information
            $ipAddress = self::getClientIpAddress();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $sessionId = session_id();
            $requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
            $requestUri = $_SERVER['REQUEST_URI'] ?? null;
            $responseCode = http_response_code() ?: 200;
            
            // Get execution metrics
            $executionTime = null;
            $memoryUsage = memory_get_usage(true);
            
            // Convert details to JSON if it's an array
            if (is_array($details)) {
                $details = json_encode($details);
            }
            
            // Insert log entry
            $stmt = $db->prepare("
                INSERT INTO admin_logs (
                    user_id, action, entity_type, entity_id, details,
                    ip_address, user_agent, session_id, request_method, request_uri,
                    response_code, execution_time, memory_usage, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                $userId, $action, $entityType, $entityId, $details,
                $ipAddress, $userAgent, $sessionId, $requestMethod, $requestUri,
                $responseCode, $executionTime, $memoryUsage
            ]);
            
            return true;
            
        } catch (Exception $e) {
            // Log error but don't throw to avoid breaking the main functionality
            error_log("AdminLogger error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get client IP address
     */
    private static function getClientIpAddress()
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Get recent admin logs
     * 
     * @param int $limit Number of logs to retrieve
     * @param int $offset Offset for pagination
     * @param string|null $userId Filter by user ID
     * @param string|null $action Filter by action
     * @return array
     */
    public static function getRecentLogs($limit = 50, $offset = 0, $userId = null, $action = null)
    {
        try {
            $db = self::getDb();
            
            $conditions = [];
            $params = [];
            
            if ($userId !== null) {
                $conditions[] = "user_id = ?";
                $params[] = $userId;
            }
            
            if ($action !== null) {
                $conditions[] = "action = ?";
                $params[] = $action;
            }
            
            $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $stmt = $db->prepare("
                SELECT al.*, u.username, u.email
                FROM admin_logs al
                LEFT JOIN users u ON al.user_id = u.id
                {$whereClause}
                ORDER BY al.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("AdminLogger getRecentLogs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get log statistics
     * 
     * @return array
     */
    public static function getStats()
    {
        try {
            $db = self::getDb();
            
            $stats = [];
            
            // Total logs
            $stmt = $db->query("SELECT COUNT(*) as total FROM admin_logs");
            $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Logs today
            $stmt = $db->query("SELECT COUNT(*) as today FROM admin_logs WHERE DATE(created_at) = CURDATE()");
            $stats['today'] = $stmt->fetch(PDO::FETCH_ASSOC)['today'];
            
            // Logs this week
            $stmt = $db->query("SELECT COUNT(*) as week FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['week'] = $stmt->fetch(PDO::FETCH_ASSOC)['week'];
            
            // Most active users
            $stmt = $db->query("
                SELECT u.username, COUNT(*) as log_count
                FROM admin_logs al
                JOIN users u ON al.user_id = u.id
                WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY al.user_id, u.username
                ORDER BY log_count DESC
                LIMIT 5
            ");
            $stats['top_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Most common actions
            $stmt = $db->query("
                SELECT action, COUNT(*) as action_count
                FROM admin_logs
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY action
                ORDER BY action_count DESC
                LIMIT 10
            ");
            $stats['top_actions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("AdminLogger getStats error: " . $e->getMessage());
            return [];
        }
    }
}
