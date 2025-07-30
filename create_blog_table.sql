-- Create blog_posts table
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    body TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'General',
    excerpt TEXT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    status ENUM('draft','published') DEFAULT 'draft',
    published_at DATETIME NULL,
    author_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_published (published_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert example post
tRUNCATE TABLE blog_posts;
INSERT INTO blog_posts (title,slug,body,category,excerpt,is_featured,status,published_at,author_id) VALUES
('Welcome to Our New Blog!','welcome-to-our-new-blog','This is your first post! Stay tuned for more news and updates.','General','This is your first post!','1','published',NOW(),1),
('July 2025 Community Events','july-2025-community-events','We have a host of new digital skills, coding, and design workshops lined up in July. Register soon!','Events','Upcoming events for July.','0','published',NOW(),1),
('Maintenance Downtime Notice','maintenance-downtime-notice','We will be performing site maintenance on August 5th from 12:00 AM to 6:00 AM. Thank you for your understanding.','Updates','Site maintenance notice','0','draft',NULL,1);

