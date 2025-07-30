# Chania Skills for Africa - Tests & Development Tools

This directory contains all testing, debugging, and development utility files for the Chania Skills for Africa application.

## 📁 Directory Contents

### 🧪 **Test Files** (`test_*.php`, `*_test.php`)
These files are used for testing various components of the application:

- `admin_test.php` - Admin panel functionality tests
- `login_test.php` - User authentication testing
- `page_access_test.php` - Page access control testing
- `test_admin_final.php` - Final admin system tests
- `test_admin_routes.php` - Admin routing tests
- `test_comprehensive.php` - Comprehensive system tests
- `test_connection.php` - Database connection tests
- `test_dashboard.php` - Dashboard functionality tests
- `test_dashboard_raw_data.php` - Dashboard data validation tests
- `test_db_connection.php` - Database connectivity tests
- `test_logging.php` - Logging system tests
- `test_login_flow.php` - Login process flow tests
- `test_newsletter_stats.php` - Newsletter statistics tests
- `test_password.php` - Password functionality tests
- `test_session.php` - Session management tests
- `test_system_monitor.php` - System monitoring tests
- `test_web_functionality.php` - Web interface tests
- `admin_public_test.php` - Admin public interface tests

### 🐛 **Debug Files** (`debug_*.php`)
Debugging utilities for troubleshooting:

- `debug_admin_logs.php` - Admin logs debugging
- `debug_database.php` - Database debugging utilities
- `debug_pages.php` - Page rendering debugging

### ✅ **Check Files** (`check_*.php`)
System validation and verification scripts:

- `check_admin_logs.php` - Verify admin logging functionality
- `check_admin_logs_table.php` - Validate admin logs table structure
- `check_db.php` - Database connection and structure check
- `check_events_table.php` - Events table validation
- `check_schema.php` - Database schema validation
- `check_tables.php` - General table structure checks
- `check_user_roles.php` - User roles system validation
- `check_users_table.php` - Users table structure validation

### 🔍 **Diagnostic Files**
System diagnostic and analysis tools:

- `admin_diagnostic.php` - Admin system diagnostics
- `system_check.php` - Overall system health check
- `database_health_check.php` - Database health monitoring
- `temp_describe.php` - Temporary database description utility
- `verify_password.php` - Password verification utility

### 📊 **Sample Data Files** (`*.sql`)
SQL files containing sample/test data:

- `sample_data.sql` - Sample application data
- `sample_data_inserts.sql` - Sample data insertion scripts
- `insert_sample_data.sql` - Sample data insertion utility

## 🚀 **Usage Guidelines**

### Running Tests
1. Ensure your local development environment is set up
2. Configure database connection in the main `.env` file
3. Run individual test files through your web browser or PHP CLI
4. Check logs and output for test results

### Debugging
1. Use debug files when troubleshooting specific components
2. Check files help validate system state and configuration
3. Always run system checks after major changes

### Sample Data
1. Use sample data files to populate development databases
2. Run sample data scripts in a development environment only
3. Never use sample data in production

## ⚠️ **Important Notes**

- **Development Only**: These files are for development and testing purposes only
- **Security**: Never deploy these files to production servers
- **Database**: Always use a separate test database when running these scripts
- **Backup**: Backup your database before running any test or sample data scripts

## 🔧 **Environment Requirements**

- PHP 7.4+ or 8.x
- MySQL/MariaDB database
- Apache/Nginx web server
- All dependencies listed in the main project requirements

For questions about specific test files or debugging procedures, refer to the individual file comments or contact the development team.

# Tests Directory

This directory contains all the test files for the Chania Project. These files help ensure that your application is working correctly.

## What are Test Files?

Test files are like "health checks" for your application. They automatically verify that different parts of your system are working as expected. Think of them like a doctor's checkup for your website!

## Directory Contents (Explained Simply)

### 🔍 **System Check Files**
These files check if your basic setup is working:

- **`system_check.php`**: Tests overall system health
- **`test_db_connection.php`**: Checks if your database connection works
- **`check_db.php`**: Verifies database structure and content
- **`test_connection.php`**: Tests general connectivity

### 🗄️ **Database Test Files**
These files make sure your database is set up correctly:

- **`check_schema.php`**: Verifies your database structure matches what's expected
- **`check_tables.php`**: Makes sure all required tables exist
- **`check_admin_logs_table.php`**: Tests the admin logging system
- **`check_events_table.php`**: Verifies event data structure
- **`check_users_table.php`**: Tests user data structure
- **`check_user_roles.php`**: Verifies user permission system

### 👤 **Admin System Tests**
These files test the admin panel functionality:

- **`admin_test.php`**: General admin system tests
- **`admin_diagnostic.php`**: Detailed admin system analysis
- **`test_admin_final.php`**: Comprehensive admin functionality test
- **`test_admin_routes.php`**: Tests admin page navigation
- **`debug_pages.php`**: Helps find issues with admin pages
- **`login_test.php`**: Tests the admin login system
- **`page_access_test.php`**: Verifies page permissions work correctly

### 🔐 **Authentication & Security Tests**
These files test login and security features:

- **`test_login_flow.php`**: Tests the complete login process
- **`test_password.php`**: Verifies password security
- **`verify_password.php`**: Tests password validation
- **`test_session.php`**: Tests user session management

### 📊 **Feature-Specific Tests**
These files test specific features of your application:

- **`test_dashboard.php`**: Tests the admin dashboard
- **`test_dashboard_raw_data.php`**: Tests dashboard data accuracy
- **`test_system_monitor.php`**: Tests system monitoring features
- **`test_newsletter_stats.php`**: Tests newsletter functionality
- **`test_logging.php`**: Tests the logging system
- **`test_web_functionality.php`**: Tests general website features

### 🐛 **Debug and Analysis Files**
These files help find and fix problems:

- **`debug_admin_logs.php`**: Analyzes admin activity logs
- **`debug_database.php`**: Finds database issues
- **`temp_describe.php`**: Temporary analysis file
- **`test_comprehensive.php`**: Runs multiple tests at once

## How to Use Test Files

### For Beginners:
1. **Start with basic tests**: Run `system_check.php` first
2. **Check database**: Run `test_db_connection.php` 
3. **Test admin access**: Run `login_test.php`
4. **Look for errors**: Check what each test reports

### Running Tests:
1. **Via Web Browser**: 
   - Navigate to: `http://localhost/chania/tests/system_check.php`
   - Replace `system_check.php` with any test file name

2. **Via Command Line** (if you know how):
   ```bash
   php tests/system_check.php
   ```

## Understanding Test Results

### ✅ **Green/Success Messages**: Everything is working correctly
### ⚠️ **Yellow/Warning Messages**: Something might need attention
### ❌ **Red/Error Messages**: Something is broken and needs fixing

## Common Issues and Solutions

### Database Connection Errors:
- Check your `.env` file has correct database settings
- Verify your database server is running
- Make sure database user has proper permissions

### Admin Login Issues:
- Verify admin user exists in database
- Check password requirements are met
- Ensure session handling is working

### Permission Errors:
- Check file permissions on logs and uploads folders
- Verify web server has read/write access where needed

## When to Run Tests

- **After initial setup**: To verify everything installed correctly
- **Before deploying changes**: To catch issues before they go live
- **When troubleshooting**: To identify what's causing problems
- **Regular maintenance**: Weekly or monthly health checks

## Important Security Note

⚠️ **Never leave test files accessible on a live/production website!** 

These files can reveal sensitive information about your system. They should only be used in development and testing environments.

## Getting Help

If tests fail and you don't understand why:

1. **Check the error logs**: Look in `../logs/error.log`
2. **Start with simple tests**: Fix basic issues first
3. **Read error messages carefully**: They often tell you exactly what's wrong
4. **Check your configuration**: Verify `.env` and config files are correct

## Test File Categories Summary

| Category | Purpose | When to Use |
|----------|---------|-------------|
| System Checks | Verify basic functionality | First setup, regular maintenance |
| Database Tests | Verify data integrity | After database changes |
| Admin Tests | Test admin panel | After admin modifications |
| Security Tests | Verify authentication | After security updates |
| Debug Files | Find specific issues | When troubleshooting problems |

Remember: Tests are your friends! They help catch problems before your users do.
