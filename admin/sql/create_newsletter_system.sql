-- Create Newsletter System tables for Digital Empowerment Network

-- Add columns to existing email_templates table if they don't exist
ALTER TABLE email_templates 
ADD COLUMN IF NOT EXISTS template_type ENUM('newsletter', 'announcement', 'notification') DEFAULT 'newsletter' AFTER body,
ADD COLUMN IF NOT EXISTS created_by INT DEFAULT 1 AFTER is_active;

-- Email Campaigns table
CREATE TABLE IF NOT EXISTS email_campaigns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    template_id INT DEFAULT NULL,
    recipient_type ENUM('all_users', 'newsletter_subscribers', 'applicants', 'active_users', 'custom') NOT NULL,
    recipient_filter JSON DEFAULT NULL,
    status ENUM('draft', 'scheduled', 'sending', 'sent', 'failed') DEFAULT 'draft',
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    total_recipients INT DEFAULT 0,
    sent_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_recipient_type (recipient_type),
    INDEX idx_created_by (created_by),
    FOREIGN KEY (template_id) REFERENCES email_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Email Campaign Recipients table
CREATE TABLE IF NOT EXISTS email_campaign_recipients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campaign_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    user_id INT DEFAULT NULL,
    status ENUM('pending', 'sent', 'failed', 'bounced') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    error_message TEXT DEFAULT NULL,
    
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    FOREIGN KEY (campaign_id) REFERENCES email_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- SMS Templates table
CREATE TABLE IF NOT EXISTS sms_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    template_type ENUM('notification', 'reminder', 'announcement') DEFAULT 'notification',
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_template_type (template_type),
    INDEX idx_created_by (created_by),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- SMS Campaigns table
CREATE TABLE IF NOT EXISTS sms_campaigns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    template_id INT DEFAULT NULL,
    recipient_type ENUM('all_users', 'applicants', 'active_users', 'custom') NOT NULL,
    recipient_filter JSON DEFAULT NULL,
    status ENUM('draft', 'scheduled', 'sending', 'sent', 'failed') DEFAULT 'draft',
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    total_recipients INT DEFAULT 0,
    sent_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_recipient_type (recipient_type),
    INDEX idx_created_by (created_by),
    FOREIGN KEY (template_id) REFERENCES sms_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- SMS Campaign Recipients table
CREATE TABLE IF NOT EXISTS sms_campaign_recipients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campaign_id INT NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    user_id INT DEFAULT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    error_message TEXT DEFAULT NULL,
    
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    FOREIGN KEY (campaign_id) REFERENCES sms_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reports table for analytics
CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    report_type ENUM('users', 'applications', 'programs', 'events', 'custom') NOT NULL,
    query_config JSON NOT NULL,
    filters JSON DEFAULT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_report_type (report_type),
    INDEX idx_created_by (created_by),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample email templates
INSERT INTO email_templates (name, subject, body, template_type, created_by) VALUES
('Welcome Newsletter', 'Welcome to Digital Empowerment Network', 
 '<h1>Welcome!</h1><p>Thank you for subscribing to our newsletter. We will keep you updated with the latest programs and opportunities.</p>', 
 'newsletter', 1),
('Program Announcement', 'New Program Available: {{program_name}}', 
 '<h2>New Program Alert!</h2><p>We are excited to announce a new program: <strong>{{program_name}}</strong></p><p>{{program_description}}</p>', 
 'announcement', 1),
('Application Reminder', 'Reminder: Complete Your Application', 
 '<h2>Don\'t Miss Out!</h2><p>This is a friendly reminder to complete your application for {{program_name}}. The deadline is {{deadline}}.</p>', 
 'notification', 1);

-- Insert sample SMS templates
INSERT INTO sms_templates (name, content, template_type, created_by) VALUES
('Welcome SMS', 'Welcome to Digital Empowerment Network! Stay tuned for updates about our programs.', 'notification', 1),
('Program Alert', 'New program available: {{program_name}}. Apply now at {{website_url}}', 'announcement', 1),
('Application Reminder', 'Reminder: Complete your application for {{program_name}} by {{deadline}}.', 'reminder', 1);
