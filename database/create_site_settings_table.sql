-- Create site_settings table for admin settings management
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `setting_type` enum('text','textarea','file','boolean','number','email','url','select','json') DEFAULT 'text',
  `setting_group` varchar(50) DEFAULT 'general',
  `setting_label` varchar(200) NOT NULL,
  `setting_description` text DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_key` (`setting_key`),
  KEY `idx_group` (`setting_group`),
  KEY `idx_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_label`, `setting_description`, `is_required`, `display_order`) VALUES
('site_name', 'Chania Skills for Africa', 'text', 'general', 'Site Name', 'The name of your website', 1, 1),
('site_description', 'Empowering communities through skills development and training programs', 'textarea', 'general', 'Site Description', 'A brief description of your website', 1, 2),
('site_url', '', 'url', 'general', 'Site URL', 'The main URL of your website (e.g., https://example.com)', 0, 3),
('site_logo', '', 'file', 'general', 'Site Logo', 'Upload your site logo', 0, 4),
('site_favicon', '', 'file', 'general', 'Site Favicon', 'Upload your site favicon', 0, 5),
('contact_email', 'info@skillsforafrica.org', 'email', 'contact', 'Contact Email', 'Main contact email address', 1, 1),
('contact_phone', '+254 123 456 789', 'text', 'contact', 'Contact Phone', 'Main contact phone number', 1, 2),
('contact_address', 'Chania, Thika, Kenya', 'textarea', 'contact', 'Contact Address', 'Physical address of your organization', 0, 3),
('social_facebook', '', 'url', 'social', 'Facebook URL', 'Link to your Facebook page', 0, 1),
('social_twitter', '', 'url', 'social', 'Twitter URL', 'Link to your Twitter profile', 0, 2),
('social_linkedin', '', 'url', 'social', 'LinkedIn URL', 'Link to your LinkedIn page', 0, 3),
('social_instagram', '', 'url', 'social', 'Instagram URL', 'Link to your Instagram profile', 0, 4),
('maintenance_mode', '0', 'boolean', 'system', 'Maintenance Mode', 'Enable maintenance mode to show maintenance page to visitors', 0, 1),
('maintenance_message', 'We are currently performing maintenance. Please check back soon.', 'textarea', 'system', 'Maintenance Message', 'Message to display when maintenance mode is enabled', 0, 2),
('max_file_upload_size', '10485760', 'number', 'system', 'Max File Upload Size', 'Maximum file upload size in bytes (default: 10MB)', 0, 3),
('timezone', 'Africa/Nairobi', 'text', 'system', 'Timezone', 'Application timezone', 0, 4),
('date_format', 'Y-m-d', 'text', 'system', 'Date Format', 'Default date format for display', 0, 5),
('time_format', 'H:i:s', 'text', 'system', 'Time Format', 'Default time format for display', 0, 6),
('default_logo', '', 'url', 'media', 'Default Logo URL', 'URL for the default site logo', 0, 1),
('default_favicon', '', 'url', 'media', 'Default Favicon URL', 'URL for the default site favicon', 0, 2),
('hero_background_url', '', 'url', 'media', 'Hero Background Image URL', 'URL for the hero section background image', 0, 3);
