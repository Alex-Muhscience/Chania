<?php

namespace App\Core;

class NotificationService
{
    private $db;
    private $logFile;

    public function __construct()
    {
        $this->db = (new \Database())->connect();
        $this->logFile = __DIR__ . '/../../logs/notifications.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    public function sendNotification($data)
    {
        // For now, let's log notifications and store them in database
        $this->logNotification($data);
        $this->storeNotification($data);
        
        // TODO: Implement real-time WebSocket notifications later
        return true;
    }
    
    private function logNotification($data)
    {
        $logEntry = date('Y-m-d H:i:s') . ' - ' . json_encode($data) . PHP_EOL;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    private function storeNotification($data)
    {
        try {
            // Check if notifications table exists, create if not
            $this->createNotificationsTable();
            
            $stmt = $this->db->prepare("
                INSERT INTO admin_notifications (type, title, message, data, created_at, is_read)
                VALUES (?, ?, ?, ?, NOW(), 0)
            ");
            
            $stmt->execute([
                $data['type'] ?? 'general',
                $data['title'] ?? 'New Notification',
                $data['message'] ?? '',
                json_encode($data['data'] ?? [])
            ]);
            
        } catch (\Exception $e) {
            error_log('Failed to store notification: ' . $e->getMessage());
        }
    }
    
    private function createNotificationsTable()
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS admin_notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    type VARCHAR(50) NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    message TEXT,
                    data JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    is_read BOOLEAN DEFAULT FALSE,
                    INDEX idx_type (type),
                    INDEX idx_created_at (created_at),
                    INDEX idx_is_read (is_read)
                )
            ");
        } catch (\Exception $e) {
            error_log('Failed to create notifications table: ' . $e->getMessage());
        }
    }
}

