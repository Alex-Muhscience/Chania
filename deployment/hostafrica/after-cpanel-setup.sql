-- ==============================================
-- CHANIA SKILLS FOR AFRICA - POST cPanel SETUP
-- Run this AFTER creating database and user via cPanel
-- ==============================================

-- This script assumes you've already created:
-- 1. Database via cPanel MySQL Databases
-- 2. User via cPanel MySQL Databases  
-- 3. Assigned user to database with ALL PRIVILEGES

-- Test basic connection
SELECT 'Database connection successful!' as status;

-- Show current user
SELECT USER() as current_user;

-- Show current database
SELECT DATABASE() as current_database;

-- Show available databases (you should see your chania_db)
SHOW DATABASES;

-- Test if you can create a table (this confirms you have the right privileges)
CREATE TABLE IF NOT EXISTS privilege_test (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_message VARCHAR(100) DEFAULT 'Privileges working correctly!',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert test data
INSERT INTO privilege_test (test_message) VALUES ('Test successful - ready to import schema!');

-- Check the test
SELECT * FROM privilege_test;

-- Clean up test table
DROP TABLE privilege_test;

-- Success message
SELECT 'Ready to import your Chania database schema!' as final_status;

-- ==============================================
-- NEXT STEPS AFTER THIS RUNS SUCCESSFULLY:
-- ==============================================
-- 1. Go to phpMyAdmin Import tab
-- 2. Upload the database-setup.sql file
-- 3. Click Go to import your full schema
-- 4. Test your application connection
-- ==============================================
