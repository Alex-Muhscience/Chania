-- Create team_members table for the Chania application

CREATE TABLE IF NOT EXISTS `team_members` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `position` varchar(255) NOT NULL,
    `bio` text DEFAULT NULL,
    `email` varchar(255) DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `social_links` text DEFAULT NULL COMMENT 'JSON format social media links',
    `image_path` varchar(500) DEFAULT NULL,
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `status` (`status`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample data
INSERT INTO `team_members` (`name`, `position`, `bio`, `email`, `phone`, `social_links`, `status`) VALUES
('John Kamau', 'Executive Director', 'John leads our organization with over 10 years of experience in educational development and youth empowerment across Africa.', 'john.kamau@skillsforafrica.org', '+254 700 123 001', '{"linkedin": "https://linkedin.com/in/johnkamau", "twitter": "@johnkamau"}', 'active'),
('Sarah Mwangi', 'Program Manager', 'Sarah oversees our training programs and ensures quality delivery of digital skills courses to African youth.', 'sarah.mwangi@skillsforafrica.org', '+254 700 123 002', '{"linkedin": "https://linkedin.com/in/sarahmwangi"}', 'active'),
('David Ochieng', 'Technical Lead', 'David manages our technical infrastructure and develops innovative learning platforms for our students.', 'david.ochieng@skillsforafrica.org', '+254 700 123 003', '{"linkedin": "https://linkedin.com/in/davidochieng", "github": "https://github.com/davidochieng"}', 'active'),
('Grace Akinyi', 'Operations Manager', 'Grace handles day-to-day operations and ensures smooth running of all organizational activities.', 'grace.akinyi@skillsforafrica.org', '+254 700 123 004', '{"linkedin": "https://linkedin.com/in/graceakinyi"}', 'active'),
('Michael Kiprop', 'Partnerships Director', 'Michael builds strategic partnerships with organizations and institutions to expand our reach and impact.', 'michael.kiprop@skillsforafrica.org', '+254 700 123 005', '{"linkedin": "https://linkedin.com/in/michaelkiprop"}', 'active');
