-- Program Categories table for the admin panel
-- Add this to your existing database

CREATE TABLE IF NOT EXISTS `program_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (UUID()),
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `icon` varchar(100) DEFAULT 'fas fa-folder',
  `color` varchar(7) DEFAULT '#3498db',
  `sort_order` int(10) unsigned DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_uuid` (`uuid`),
  UNIQUE KEY `unique_slug` (`slug`),
  KEY `idx_name` (`name`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_deleted` (`deleted_at`),
  FOREIGN KEY (`parent_id`) REFERENCES `program_categories` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample categories
INSERT IGNORE INTO `program_categories` (`name`, `slug`, `description`, `icon`, `color`, `sort_order`, `is_active`, `is_featured`, `created_at`) VALUES
('Technology', 'technology', 'Programs focused on technology and digital skills', 'fas fa-laptop-code', '#3498db', 1, 1, 1, NOW()),
('Business', 'business', 'Business development and entrepreneurship programs', 'fas fa-briefcase', '#e74c3c', 2, 1, 1, NOW()),
('Health', 'health', 'Healthcare and wellness programs', 'fas fa-heart', '#27ae60', 3, 1, 0, NOW()),
('Education', 'education', 'Educational and training programs', 'fas fa-graduation-cap', '#f39c12', 4, 1, 1, NOW()),
('Arts & Culture', 'arts-culture', 'Creative arts and cultural programs', 'fas fa-palette', '#9b59b6', 5, 1, 0, NOW()),
('Agriculture', 'agriculture', 'Agricultural and farming programs', 'fas fa-seedling', '#2ecc71', 6, 1, 0, NOW());
