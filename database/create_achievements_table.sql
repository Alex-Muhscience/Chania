-- Achievements table for managing dynamic statistics and achievements
-- This table stores the achievement data that appears on the frontend About page

CREATE TABLE IF NOT EXISTS achievements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    
    -- Content fields
    title VARCHAR(255) NOT NULL,
    description TEXT,
    stat_value VARCHAR(50) NOT NULL, -- The main statistical value (e.g., "2,500")
    stat_unit VARCHAR(20) DEFAULT '', -- Unit suffix (e.g., "+", "K+", "%")
    icon VARCHAR(100) DEFAULT 'fas fa-trophy', -- Font Awesome icon class
    
    -- Organization fields
    category ENUM('general', 'impact', 'programs', 'students', 'partnerships') DEFAULT 'general',
    display_order INT UNSIGNED DEFAULT 0,
    
    -- Status fields
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    
    -- Metadata
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes for performance
    INDEX idx_achievements_active (is_active),
    INDEX idx_achievements_featured (is_featured),
    INDEX idx_achievements_category (category),
    INDEX idx_achievements_display_order (display_order),
    INDEX idx_achievements_deleted_at (deleted_at),
    
    -- Foreign key constraint
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample achievements
INSERT INTO achievements (title, description, stat_value, stat_unit, icon, category, display_order, is_active, is_featured, created_by) VALUES
('Students Trained', 'Young people equipped with digital skills and entrepreneurship training', '2,500', '+', 'fas fa-users', 'students', 1, TRUE, TRUE, 1),
('Programs Delivered', 'Comprehensive training programs successfully completed across various sectors', '150', '+', 'fas fa-graduation-cap', 'programs', 2, TRUE, TRUE, 1),
('Employment Rate', 'Percentage of graduates who found employment or started businesses within 6 months', '85', '%', 'fas fa-briefcase', 'impact', 3, TRUE, FALSE, 1),
('Partner Organizations', 'Strategic partnerships with local and international organizations', '50', '+', 'fas fa-handshake', 'partnerships', 4, TRUE, FALSE, 1),
('Counties Reached', 'Geographic coverage across Kenya through our training programs', '25', '', 'fas fa-map-marker-alt', 'impact', 5, TRUE, FALSE, 1),
('Success Stories', 'Documented cases of transformational impact on individuals and communities', '500', '+', 'fas fa-star', 'impact', 6, TRUE, FALSE, 1);
