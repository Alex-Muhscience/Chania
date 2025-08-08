-- ==============================================
-- CHANIA SKILLS FOR AFRICA - ADMIN PRIVILEGES SCRIPT
-- Grant Full Admin Privileges to Database User
-- ==============================================

-- WARNING: Run this script as MySQL root user or with administrative privileges
-- This script grants full privileges to your application database user

-- ==============================================
-- CONFIGURATION - UPDATE THESE VALUES
-- ==============================================

-- Replace these values with your actual database details:
-- 'chania_user' = Your database username
-- 'your_password' = Your database user password
-- 'chania_db' = Your database name
-- 'localhost' = Your database host (usually localhost for shared hosting)

-- ==============================================
-- 1. CREATE DATABASE USER (if not exists)
-- ==============================================

-- Create the database user with a strong password
-- Replace 'chania_user' with your desired username
-- Replace 'your_strong_password_here' with a secure password
CREATE USER IF NOT EXISTS 'chania_user'@'localhost' IDENTIFIED BY 'your_strong_password_here';

-- ==============================================
-- 2. GRANT ALL PRIVILEGES ON SPECIFIC DATABASE
-- ==============================================

-- Grant all privileges on the chania database to the user
-- Replace 'chania_db' with your actual database name
GRANT ALL PRIVILEGES ON `chania_db`.* TO 'chania_user'@'localhost';

-- ==============================================
-- 3. GRANT SPECIFIC PRIVILEGES (Alternative approach)
-- ==============================================

-- If you prefer to grant specific privileges instead of ALL, use these:
-- Uncomment the lines below and comment out the GRANT ALL line above

-- GRANT SELECT ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT INSERT ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT UPDATE ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT DELETE ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT CREATE ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT DROP ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT INDEX ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT ALTER ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT CREATE TEMPORARY TABLES ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT LOCK TABLES ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT EXECUTE ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT CREATE VIEW ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT SHOW VIEW ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT CREATE ROUTINE ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT ALTER ROUTINE ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT EVENT ON `chania_db`.* TO 'chania_user'@'localhost';
-- GRANT TRIGGER ON `chania_db`.* TO 'chania_user'@'localhost';

-- ==============================================
-- 4. FLUSH PRIVILEGES
-- ==============================================

-- Apply the privilege changes
FLUSH PRIVILEGES;

-- ==============================================
-- 5. VERIFY PRIVILEGES (Optional)
-- ==============================================

-- Show the privileges for the user to verify they were granted
-- Uncomment the line below to see the granted privileges
-- SHOW GRANTS FOR 'chania_user'@'localhost';

-- ==============================================
-- FOR HOSTAFRICA HOSTING - ADDITIONAL NOTES
-- ==============================================

-- If you're using HostAfrica shared hosting:
-- 1. The database user might be prefixed with your cPanel username
--    Example: 'cpanel_username_chania_user'
-- 2. You might need to create the user through cPanel MySQL Databases section
-- 3. Some shared hosting providers don't allow CREATE USER via SQL
-- 4. You may need to use the hosting control panel to assign user to database

-- ==============================================
-- CPANEL MYSQL DATABASES ALTERNATIVE
-- ==============================================

-- If this SQL script doesn't work, follow these steps in cPanel:
-- 1. Go to cPanel → MySQL Databases
-- 2. Under "MySQL Users", create a new user if needed
-- 3. Under "Add User To Database", select:
--    - User: your_database_user
--    - Database: your_database_name
-- 4. Click "Add" and then check "ALL PRIVILEGES" on the next page
-- 5. Click "Make Changes"

-- ==============================================
-- XAMPP LOCAL DEVELOPMENT
-- ==============================================

-- For XAMPP local development, you can also use:
GRANT ALL PRIVILEGES ON `chania_db`.* TO 'chania_user'@'127.0.0.1';
GRANT ALL PRIVILEGES ON `chania_db`.* TO 'chania_user'@'::1';

-- ==============================================
-- SECURITY RECOMMENDATIONS
-- ==============================================

-- 1. Use strong passwords (at least 16 characters with mixed case, numbers, symbols)
-- 2. Limit privileges to only what's needed for the application
-- 3. Consider using SSL connections for production:
--    REQUIRE SSL when creating users in production
-- 4. Regularly rotate database passwords
-- 5. Monitor database access logs

-- ==============================================
-- TROUBLESHOOTING
-- ==============================================

-- If you get "Access denied" errors:
-- 1. Make sure you're running this as MySQL root user
-- 2. Check if the database name and user name are correct
-- 3. Verify the host (localhost vs 127.0.0.1 vs domain name)
-- 4. For shared hosting, use the hosting control panel instead

-- If privileges still don't show up:
-- 1. Check the mysql.user table for the user entry
-- 2. Check the mysql.db table for database-specific privileges
-- 3. Try logging out and back into phpMyAdmin
-- 4. Clear browser cache and refresh phpMyAdmin

-- ==============================================
-- EXAMPLE FOR PRODUCTION
-- ==============================================

/*
-- Example for production setup (replace values as needed):

CREATE USER IF NOT EXISTS 'chania_prod_user'@'localhost' 
IDENTIFIED BY 'Super$ecureP@ssw0rd2024!';

GRANT ALL PRIVILEGES ON `chania_skills_africa`.* TO 'chania_prod_user'@'localhost';

FLUSH PRIVILEGES;

-- Verify the grants
SHOW GRANTS FOR 'chania_prod_user'@'localhost';
*/

-- ==============================================
-- END OF SCRIPT
-- ==============================================
