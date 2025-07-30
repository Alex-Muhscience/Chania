--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `is_default`) VALUES
(1, 'Admin', 'Has all permissions.', 0),
(2, 'Editor', 'Can publish and manage posts, including the posts of other users.', 0),
(3, 'Author', 'Can publish and manage their own posts.', 0),
(4, 'Contributor', 'Can write and manage their own posts but cannot publish them.', 0),
(5, 'Subscriber', 'Can only manage their profile.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'e.g., manage_users, edit_posts',
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT 'General',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `category`) VALUES
(1, '*', 'All Permissions', 'System'),
(2, 'dashboard', 'Access the admin dashboard', 'System'),
(3, 'settings', 'Manage site-wide settings', 'System'),
(4, 'users', 'Manage all users', 'Users'),
(5, 'roles', 'Manage user roles and permissions', 'Users'),
(6, 'pages', 'Manage static pages', 'Content'),
(7, 'blog', 'Manage all blog posts', 'Content'),
(8, 'faqs', 'Manage FAQs', 'Content'),
(9, 'media', 'Manage media library', 'Content'),
(10, 'templates', 'Manage email templates', 'Communication');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(3, 7),
(4, 7);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

-- Update users table to use role_id
ALTER TABLE `users` ADD COLUMN `role_id` INT(11) NULL DEFAULT 5 AFTER `role`;
UPDATE `users` SET `role_id` = 1 WHERE `role` = 'admin';
UPDATE `users` SET `role_id` = 5 WHERE `role` = 'user';
ALTER TABLE `users` ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL;

