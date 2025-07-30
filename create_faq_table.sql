-- Create FAQ table
CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'General',
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_active (is_active),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample FAQs
INSERT INTO faqs (question, answer, category, is_active, display_order, created_by) VALUES
('What is this platform about?', 'This platform provides digital literacy training and skills development programs for community members. We offer various courses and workshops to help people improve their technology skills.', 'General', 1, 1, 1),
('How do I apply for a program?', 'You can apply for any of our programs by visiting the Applications section and filling out the application form. Make sure to provide all required information and documents.', 'Applications', 1, 1, 1),
('Are the programs free?', 'Most of our basic digital literacy programs are offered free of charge to community members. Some specialized courses may have a nominal fee to cover materials.', 'General', 1, 2, 1),
('What are the requirements to join?', 'The basic requirement is being a community member aged 16 or above. Some advanced programs may have prerequisite skills or completed basic courses.', 'Applications', 1, 2, 1),
('How long are the programs?', 'Program duration varies from 2-week intensive courses to 3-month comprehensive programs. Each program listing shows the specific duration and schedule.', 'Programs', 1, 1, 1),
('Can I get a certificate?', 'Yes, participants who successfully complete our programs receive a certificate of completion that can be used for employment or further education.', 'Programs', 1, 2, 1);
