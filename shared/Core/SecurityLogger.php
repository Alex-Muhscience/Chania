<?php

class SecurityLogger {
    private $db;

    public function __construct($db) {
        $this->db = $db;
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
    
    public function getLogs($limit = 50, $offset = 0, $filters = []) {
        $sql = "SELECT l.*, u.username as resolved_by_username
                FROM security_audit_logs l
                LEFT JOIN users u ON l.resolved_by = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['event_type'])) {
            $sql .= " AND l.event_type = ?";
            $params[] = $filters['event_type'];
        }

        if (!empty($filters['severity'])) {
            $sql .= " AND l.severity = ?";
            $params[] = $filters['severity'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND l.user_id = ?";
            $params[] = $filters['user_id'];
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

?>
