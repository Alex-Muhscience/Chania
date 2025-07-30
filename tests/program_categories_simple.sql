-- Create program_categories table without foreign keys
USE chania_db;

CREATE TABLE `program_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data
INSERT INTO `program_categories` (`id`, `name`, `description`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Undergraduate Programs', 'Bachelor degree programs offered by the university', 'active', 1, '2023-10-01 10:00:00', '2023-10-01 10:00:00'),
(2, 'Graduate Programs', 'Master and PhD programs for advanced studies', 'active', 1, '2023-10-01 10:05:00', '2023-10-01 10:05:00'),
(3, 'Certificate Programs', 'Short-term professional certification courses', 'active', 1, '2023-10-01 10:10:00', '2023-10-01 10:10:00'),
(4, 'Exchange Programs', 'International student exchange opportunities', 'active', 1, '2023-10-01 10:15:00', '2023-10-01 10:15:00'),
(5, 'Research Programs', 'Research-focused academic programs', 'active', 1, '2023-10-01 10:20:00', '2023-10-01 10:20:00');
