<?php

class SecurityLogger {
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->ensureTableExists();
    }
    
    /**
     * Ensure the security_audit_logs table exists
     */
    private function ensureTableExists() {
        try {
            // Check if table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'security_audit_logs'");
            if (!$stmt->fetch()) {
                // Create the table
                $sql = "
                    CREATE TABLE security_audit_logs (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NULL,
                        username VARCHAR(255) NULL,
                        event_type VARCHAR(100) NOT NULL,
                        severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
                        description TEXT NOT NULL,
                        details JSON NULL,
                        ip_address VARCHAR(45) NULL,
                        user_agent TEXT NULL,
                        session_id VARCHAR(255) NULL,
                        affected_user_id INT NULL,
                        affected_resource VARCHAR(255) NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_event_type (event_type),
                        INDEX idx_severity (severity),
                        INDEX idx_user_id (user_id),
                        INDEX idx_created_at (created_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ";
                $this->db->exec($sql);
            }
        } catch (Exception $e) {
            error_log('SecurityLogger table creation error: ' . $e->getMessage());
        }
    }

    /**
     * Log a security event.
     *
     * @param string $eventType
     * @param string $severity
     * @param string $description
     * @param int|null $userId
     * @param array $details
     */
    public function log($eventType, $severity, $description, $userId = null, $details = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO security_audit_logs (
                    user_id, username, event_type, severity, description, details, 
                    ip_address, user_agent, session_id, affected_user_id, affected_resource
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $username = null;
            if ($userId) {
                $userStmt = $this->db->prepare("SELECT username FROM users WHERE id = ?");
                $userStmt->execute([$userId]);
                $username = $userStmt->fetchColumn();
            }
            
            $stmt->execute([
                $userId,
                $username,
                $eventType,
                $severity,
                $description,
                json_encode($details),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                session_id(),
                $details['affected_user_id'] ?? null,
                $details['affected_resource'] ?? null
            ]);

        } catch (Exception $e) {
            error_log("SecurityLogger Error: " . $e->getMessage());
        }
    }
    
    /**
     * Get security logs with filters and pagination
     * 
     * @param int $limit
     * @param int $offset
     * @param string $eventType
     * @param string $severity
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $searchTerm
     * @return array
     */
    public function getLogs($limit = 50, $offset = 0, $eventType = '', $severity = '', $dateFrom = '', $dateTo = '', $searchTerm = '') {
        $sql = "SELECT * FROM security_audit_logs WHERE 1=1";
        $params = [];

        if (!empty($eventType)) {
            $sql .= " AND event_type = ?";
            $params[] = $eventType;
        }

        if (!empty($severity)) {
            $sql .= " AND severity = ?";
            $params[] = $severity;
        }

        if (!empty($dateFrom)) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }

        if (!empty($searchTerm)) {
            $sql .= " AND (description LIKE ? OR username LIKE ? OR ip_address LIKE ? OR event_type LIKE ?)";
            $searchPattern = '%' . $searchTerm . '%';
            $params[] = $searchPattern;
            $params[] = $searchPattern;
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = (int) $limit;
        $params[] = (int) $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of logs with filters
     * 
     * @param string $eventType
     * @param string $severity
     * @param string $dateFrom
     * @param string $dateTo
     * @return int
     */
    public function getLogsCount($eventType = '', $severity = '', $dateFrom = '', $dateTo = '') {
        $sql = "SELECT COUNT(*) FROM security_audit_logs WHERE 1=1";
        $params = [];

        if (!empty($eventType)) {
            $sql .= " AND event_type = ?";
            $params[] = $eventType;
        }

        if (!empty($severity)) {
            $sql .= " AND severity = ?";
            $params[] = $severity;
        }

        if (!empty($dateFrom)) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Log a security event (legacy method for backward compatibility).
     *
     * @param int|null $userId
     * @param string $username
     * @param string $eventType
     * @param string $severity
     * @param string $description
     * @param array $details
     */
    public function logEvent($userId, $username, $eventType, $severity, $description, $details = []) {
        // Call the main log method with adjusted parameters
        $this->log($eventType, $severity, $description, $userId, $details);
    }
    
    /**
     * Get unique event types from security logs
     * 
     * @return array
     */
    public function getUniqueEventTypes() {
        $sql = "SELECT DISTINCT event_type FROM security_audit_logs WHERE event_type IS NOT NULL ORDER BY event_type";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

?>
