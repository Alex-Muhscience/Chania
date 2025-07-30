-- Create User Activity Logs table for Digital Empowerment Network
CREATE TABLE IF NOT EXISTS user_activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    session_id VARCHAR(128) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_session_id (session_id),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample user activity log entries
INSERT INTO user_activity_logs (user_id, action, description, ip_address) VALUES
(1, 'LOGIN', 'User logged into the system', '127.0.0.1'),
(1, 'PROFILE_UPDATE', 'Updated profile information', '127.0.0.1'),
(1, 'APPLICATION_SUBMIT', 'Submitted application for program', '127.0.0.1'),
(1, 'PASSWORD_CHANGE', 'Changed account password', '127.0.0.1'),
(1, 'LOGOUT', 'User logged out of the system', '127.0.0.1');
