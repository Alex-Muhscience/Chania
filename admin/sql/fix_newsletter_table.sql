-- Fix newsletter_subscribers table to match PHP code expectations

-- Drop the existing table structure if it exists and recreate with proper fields
DROP TABLE IF EXISTS newsletter_subscribers;

-- Create newsletter_subscribers table with proper structure
CREATE TABLE newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    email VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100),
    status ENUM('subscribed', 'unsubscribed', 'bounced', 'complained') DEFAULT 'subscribed',
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    source VARCHAR(100) DEFAULT 'website_footer',
    verification_token VARCHAR(255),
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_newsletter_email (email),
    INDEX idx_newsletter_status (status),
    INDEX idx_newsletter_subscribed_at (subscribed_at),
    INDEX idx_newsletter_deleted_at (deleted_at),
    INDEX idx_newsletter_verification (verification_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
