-- ============================================== 
-- XAMPP QUICK DATABASE SETUP
-- Run this as MySQL root user in XAMPP
-- ==============================================

-- Create the database
CREATE DATABASE IF NOT EXISTS `chania_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Create user with password
CREATE USER IF NOT EXISTS 'chania_user'@'localhost' 
IDENTIFIED BY 'chania_password_123';

-- Grant all privileges
GRANT ALL PRIVILEGES ON `chania_db`.* TO 'chania_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Show success message
SELECT 'XAMPP Database Setup Complete!' as status;
SELECT 'Database: chania_db' as db_info;
SELECT 'User: chania_user' as user_info;
SELECT 'Password: chania_password_123' as pass_info;

-- Verify the setup
SHOW GRANTS FOR 'chania_user'@'localhost';
