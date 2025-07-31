# Digital Empowerment Network - Installation Guide

## Table of Contents
1. [System Requirements](#system-requirements)
2. [Pre-Installation Setup](#pre-installation-setup)
3. [Installation Steps](#installation-steps)
4. [Database Setup](#database-setup)
5. [Configuration](#configuration)
6. [Initial Setup](#initial-setup)
7. [Security Configuration](#security-configuration)
8. [Performance Optimization](#performance-optimization)
9. [Testing and Verification](#testing-and-verification)
10. [Troubleshooting](#troubleshooting)

## System Requirements

### Minimum Requirements
- **PHP**: 7.4 or higher (8.0+ recommended)
- **MySQL**: 5.7 or higher / MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 256MB PHP memory limit (512MB recommended)
- **Storage**: 1GB free disk space (5GB+ recommended)

### Required PHP Extensions
- PDO and PDO MySQL
- JSON
- MBString
- OpenSSL
- cURL
- GD (for image processing)
- ZIP (for backups)
- BCMath (for calculations)

### Optional but Recommended
- **Redis/Memcached**: For caching
- **Composer**: For dependency management
- **Git**: For version control
- **SSL Certificate**: For HTTPS

## Pre-Installation Setup

### 1. Server Preparation
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install apache2 mysql-server php8.0 php8.0-mysql php8.0-mbstring \
php8.0-curl php8.0-gd php8.0-zip php8.0-bcmath php8.0-json unzip git -y

# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

### 2. MySQL Setup
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE chania_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'chania_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON chania_db.* TO 'chania_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. PHP Configuration
Edit `/etc/php/8.0/apache2/php.ini`:
```ini
memory_limit = 512M
max_execution_time = 300
max_input_vars = 3000
upload_max_filesize = 32M
post_max_size = 32M
date.timezone = Your/Timezone
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

## Installation Steps

### 1. Download and Extract
```bash
# Clone from repository
git clone https://github.com/your-org/digital-empowerment-network.git
cd digital-empowerment-network

# Or download and extract ZIP
wget https://github.com/your-org/digital-empowerment-network/archive/main.zip
unzip main.zip
cd digital-empowerment-network-main
```

### 2. Set File Permissions
```bash
# Make directories writable
chmod -R 755 .
chmod -R 777 uploads/
chmod -R 777 admin/logs/
chmod -R 777 admin/cache/
chmod -R 777 backups/
chmod -R 777 assets/media/

# Secure configuration files
chmod 600 config/config.php
chmod 600 admin/config/config.php
```

### 3. Apache Virtual Host Setup
Create `/etc/apache2/sites-available/chania.conf`:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/chania
    
    <Directory /var/www/chania>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/chania_error.log
    CustomLog ${APACHE_LOG_DIR}/chania_access.log combined
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite chania.conf
sudo systemctl restart apache2
```

### 4. SSL Setup (Recommended)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Get SSL certificate
sudo certbot --apache -d your-domain.com
```

## Database Setup

### 1. Import Database Schema
```bash
# Navigate to project directory
cd /var/www/chania

# Import database structure
mysql -u chania_user -p chania_db < database/schema.sql

# Import sample data (optional)
mysql -u chania_user -p chania_db < database/sample_data.sql
```

### 2. Create Required Tables
If schema file is not available, create tables manually:
```sql
-- Run the SQL commands from database/schema.sql
-- This includes users, roles, programs, applications, events, etc.
```

## Configuration

### 1. Main Configuration
Copy and edit the main configuration file:
```bash
cp config/config.example.php config/config.php
```

Edit `config/config.php`:
```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'chania_db');
define('DB_USER', 'chania_user');
define('DB_PASS', 'secure_password_here');

// Application Configuration
define('BASE_URL', 'https://your-domain.com');
define('SITE_NAME', 'Digital Empowerment Network');
define('ADMIN_EMAIL', 'admin@your-domain.com');

// Security Configuration
define('ENCRYPTION_KEY', 'generate-random-32-character-key');
define('SESSION_TIMEOUT', 7200); // 2 hours
define('ENABLE_2FA', false); // Enable after setup

// File Upload Configuration
define('MAX_UPLOAD_SIZE', 32 * 1024 * 1024); // 32MB
define('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx');

// Email Configuration (optional)
define('SMTP_HOST', 'smtp.your-provider.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@domain.com');
define('SMTP_PASSWORD', 'your-email-password');
define('SMTP_SECURE', 'tls');
?>
```

### 2. Admin Configuration
Copy and edit admin configuration:
```bash
cp admin/config/config.example.php admin/config/config.php
```

### 3. Environment Variables (Optional)
Create `.env` file for sensitive data:
```bash
DB_HOST=localhost
DB_NAME=chania_db
DB_USER=chania_user
DB_PASS=secure_password_here
ENCRYPTION_KEY=your-encryption-key
```

## Initial Setup

### 1. Create Admin User
Run the setup script:
```bash
php admin/setup/create_admin.php
```

Or create manually via database:
```sql
INSERT INTO users (username, email, password, first_name, last_name, role_id, is_active, created_at) 
VALUES ('admin', 'admin@your-domain.com', '$2y$10$hash_here', 'Admin', 'User', 1, 1, NOW());
```

### 2. Initialize System Settings
Access the admin panel at `https://your-domain.com/admin/` and:
1. Log in with admin credentials
2. Go to **System > Site Settings**
3. Configure basic site information
4. Set up email settings
5. Configure security options

### 3. Set Up Roles and Permissions
1. Navigate to **Users > Roles**
2. Review default roles (Admin, Manager, Editor, User)
3. Create custom roles as needed
4. Assign appropriate permissions

## Security Configuration

### 1. File Security
```bash
# Protect sensitive files
echo "deny from all" > config/.htaccess
echo "deny from all" > admin/config/.htaccess
echo "deny from all" > admin/logs/.htaccess

# Set proper ownership
sudo chown -R www-data:www-data /var/www/chania
```

### 2. Database Security
```sql
-- Remove test databases and users
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.user WHERE User='';
FLUSH PRIVILEGES;
```

### 3. PHP Security
Add to `.htaccess`:
```apache
# Disable PHP execution in uploads
<Directory "uploads">
    php_flag engine off
</Directory>

# Hide sensitive files
<Files "*.log">
    Order allow,deny
    Deny from all
</Files>

<Files "config.php">
    Order allow,deny
    Deny from all
</Files>
```

### 4. Enable Security Headers
Add to Apache configuration or `.htaccess`:
```apache
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
Header always set Content-Security-Policy "default-src 'self'"
```

## Performance Optimization

### 1. Enable OPcache
Edit `/etc/php/8.0/apache2/php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. Database Optimization
```sql
-- Add indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role_id ON users(role_id);
CREATE INDEX idx_applications_status ON applications(status);
CREATE INDEX idx_programs_status ON programs(status);
CREATE INDEX idx_events_date ON events(event_date);
```

### 3. Enable Caching
Install Redis (optional):
```bash
sudo apt install redis-server php8.0-redis -y
sudo systemctl enable redis-server
```

### 4. Optimize Apache
Add to Apache configuration:
```apache
# Enable compression
LoadModule deflate_module modules/mod_deflate.so
<Location />
    SetOutputFilter DEFLATE
    SetEnvIfNoCase Request_URI \
        \.(?:gif|jpe?g|png)$ no-gzip dont-vary
    SetEnvIfNoCase Request_URI \
        \.(?:exe|t?gz|zip|bz2|sit|rar)$ no-gzip dont-vary
</Location>

# Enable expires headers
LoadModule expires_module modules/mod_expires.so
ExpiresActive On
ExpiresByType text/css "access plus 1 month"
ExpiresByType application/javascript "access plus 1 month"
ExpiresByType image/png "access plus 1 year"
ExpiresByType image/jpg "access plus 1 year"
ExpiresByType image/gif "access plus 1 year"
```

## Testing and Verification

### 1. Run System Health Check
```bash
php admin/debug/system_health_check.php
```

### 2. Run Performance Test
```bash
php admin/debug/performance_optimizer.php
```

### 3. Test Admin Panel
1. Access `https://your-domain.com/admin/`
2. Log in with admin credentials
3. Test key functionalities:
   - User management
   - Program creation
   - Event management
   - Media upload
   - Report generation

### 4. Test Public Site
1. Access `https://your-domain.com/`
2. Test user registration
3. Test program applications
4. Test contact forms

### 5. Security Test
```bash
# Test file permissions
ls -la config/
ls -la admin/config/
ls -la uploads/

# Test database connectivity
php -r "
require 'config/config.php';
try {
    \$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
    echo 'Database connection successful\n';
} catch (Exception \$e) {
    echo 'Database connection failed: ' . \$e->getMessage() . '\n';
}
"
```

## Troubleshooting

### Common Issues

#### 1. Database Connection Errors
- Verify database credentials in `config/config.php`
- Check MySQL service: `sudo systemctl status mysql`
- Test connection: `mysql -u chania_user -p chania_db`

#### 2. Permission Denied Errors
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/chania
chmod -R 755 /var/www/chania
chmod -R 777 uploads/ admin/logs/ admin/cache/ backups/
```

#### 3. White Screen of Death
- Check PHP error logs: `tail -f /var/log/apache2/error.log`
- Enable error display temporarily:
```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

#### 4. Session Issues
- Check session directory permissions
- Verify session configuration in `php.ini`
- Clear browser cookies and cache

#### 5. Upload Issues
- Check `upload_max_filesize` and `post_max_size` in `php.ini`
- Verify uploads directory permissions
- Check disk space: `df -h`

### Log Files to Monitor
- Apache error log: `/var/log/apache2/error.log`
- Application logs: `admin/logs/`
- PHP error log: `/var/log/php8.0-fpm.log`
- MySQL error log: `/var/log/mysql/error.log`

### Getting Help
1. Check the documentation in `/docs/`
2. Review system health check results
3. Check application logs for specific errors
4. Consult the admin guide: `docs/ADMIN_GUIDE.md`

## Post-Installation Security Checklist

- [ ] Changed default admin password
- [ ] Configured SSL/HTTPS
- [ ] Set up regular backups
- [ ] Configured firewall rules
- [ ] Enabled security headers
- [ ] Protected sensitive directories
- [ ] Set up monitoring and logging
- [ ] Reviewed and configured user roles
- [ ] Tested backup and restore procedures
- [ ] Set up automated security updates

## Maintenance Tasks

### Daily
- Monitor system logs
- Check backup status
- Review security alerts

### Weekly
- Run performance optimization
- Clean up old log files
- Review user activity

### Monthly
- Update system packages
- Review and update user permissions
- Test backup restoration
- Security audit and penetration testing

---

**Installation Complete!** 

Your Digital Empowerment Network admin panel should now be fully functional and secure. For ongoing maintenance and administration, refer to the [Admin Guide](ADMIN_GUIDE.md).
