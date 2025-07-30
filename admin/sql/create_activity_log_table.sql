-- Create Admin Activity Log table for Digital Empowerment Network
CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    table_name VARCHAR(50) DEFAULT NULL,
    record_id INT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Insert sample activity log entries
INSERT INTO admin_activity_log (admin_id, action, description, table_name, record_id, ip_address) VALUES
(1, 'LOGIN', 'Admin logged into the system', NULL, NULL, '127.0.0.1'),
(1, 'CREATE', 'Created new user account', 'users', 15, '127.0.0.1'),
(1, 'UPDATE', 'Updated program information', 'programs', 3, '127.0.0.1'),
(1, 'DELETE', 'Deleted expired event', 'events', 8, '127.0.0.1'),
(1, 'VIEW', 'Accessed user management page', 'users', NULL, '127.0.0.1'),
(1, 'EXPORT', 'Exported user data to CSV', 'users', NULL, '127.0.0.1'),
(1, 'UPDATE', 'Modified FAQ content', 'faqs', 2, '127.0.0.1'),
(1, 'CREATE', 'Added new event', 'events', 12, '127.0.0.1'),
(1, 'LOGOUT', 'Admin logged out of the system', NULL, NULL, '127.0.0.1');
