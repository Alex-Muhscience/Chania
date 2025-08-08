# Chania Skills for Africa - HostAfrica Deployment Guide

This guide provides step-by-step instructions for deploying your Chania Skills for Africa project on HostAfrica hosting.

## 📋 Pre-Deployment Checklist

### 1. HostAfrica Account Setup
- [ ] Purchased hosting plan with PHP 8.1+ and MySQL 8.0+ support
- [ ] Obtained cPanel/hosting control panel access credentials
- [ ] Verified domain name is properly configured
- [ ] SSL certificate is installed and active

### 2. Database Preparation
- [ ] Database name decided: `chania_skills_africa` (or as per hosting limits)
- [ ] Database user created with full privileges
- [ ] Strong database password generated
- [ ] Database connection details documented

### 3. Email Configuration
- [ ] Professional email accounts created (info@yourdomain.com, noreply@yourdomain.com)
- [ ] SMTP settings obtained from HostAfrica
- [ ] Email passwords generated and stored securely

## 🗄️ Database Setup

### Step 1: Create Database
1. Log into your HostAfrica cPanel
2. Navigate to **MySQL Databases**
3. Create a new database: `chania_skills_africa`
4. Create a database user with a strong password
5. Assign the user to the database with **ALL PRIVILEGES**

### Step 2: Import Database Schema
1. Open **phpMyAdmin** from cPanel
2. Select your newly created database
3. Click **Import** tab
4. Upload the `database-setup.sql` file from this deployment folder
5. Click **Go** to execute the import
6. Verify all tables have been created successfully

### Step 3: Update Database Configuration
1. Update your configuration files with the actual database details:
   ```php
   define('DB_HOST', 'localhost'); // Usually 'localhost' for HostAfrica
   define('DB_NAME', 'your_actual_database_name');
   define('DB_USER', 'your_database_username');
   define('DB_PASS', 'your_database_password');
   ```

## 📁 File Upload & Configuration

### Step 1: Upload Project Files
1. Compress your entire project folder (excluding development files)
2. Upload via cPanel **File Manager** or FTP client
3. Extract files to your domain's public folder (usually `public_html`)

### Step 2: Set File Permissions
Set the following permissions via cPanel File Manager:
- **Folders**: 755 (rwxr-xr-x)
- **PHP files**: 644 (rw-r--r--)
- **uploads/** folder: 755 with write permissions
- **logs/** folder: 755 with write permissions
- **cache/** folder: 755 with write permissions

### Step 3: Configuration Files
1. Copy `.env.production` to `.env` and update all placeholder values
2. Update `config.production.php` with your actual hosting details
3. Update both admin and client configuration files

## 🔧 Environment Configuration

### Required Updates in Production Files

#### 1. Database Settings
```php
// In your config files
define('DB_HOST', 'localhost');
define('DB_NAME', 'chania_skills_africa');
define('DB_USER', 'your_cpanel_username_dbuser');
define('DB_PASS', 'your_strong_database_password');
```

#### 2. URL Configuration
```php
// Replace with your actual domain
define('BASE_URL', 'https://yourdomain.com/');
define('ADMIN_URL', 'https://yourdomain.com/admin/');
```

#### 3. Email Configuration
```php
// HostAfrica SMTP settings
define('MAIL_HOST', 'mail.yourdomain.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'info@yourdomain.com');
define('MAIL_PASSWORD', 'your_email_password');
define('MAIL_FROM_EMAIL', 'noreply@yourdomain.com');
```

#### 4. Security Settings
```php
// Generate unique keys for production
define('SECRET_KEY', 'generate-a-64-character-random-string');
define('ENCRYPTION_KEY', 'generate-a-32-character-random-string');
```

## 🔒 Security Configuration

### 1. Change Default Admin Credentials
- Default username: `admin`
- Default password: `admin123`
- **IMMEDIATELY change these after deployment**

### 2. Update Admin User
```sql
-- Run this SQL in phpMyAdmin after deployment
UPDATE users 
SET 
    username = 'your_new_admin_username',
    email = 'your_admin_email@yourdomain.com',
    password_hash = '$2y$12$your_new_hashed_password'
WHERE id = 1;
```

### 3. Generate Secure Passwords
Use these online tools to generate secure keys:
- [Random.org](https://www.random.org/passwords/)
- [LastPass Password Generator](https://www.lastpass.com/password-generator)

### 4. File Security
Create `.htaccess` files in sensitive directories:

**uploads/.htaccess:**
```apache
# Prevent PHP execution in uploads
<Files *.php>
deny from all
</Files>

# Prevent directory listing
Options -Indexes
```

**logs/.htaccess:**
```apache
# Deny access to log files
deny from all
```

## 📧 Email Testing

### Test Email Functionality
1. Log into the admin panel
2. Go to **System Settings**
3. Test email configuration
4. Send a test contact form from the website
5. Verify emails are being sent and received

### Common HostAfrica Email Settings
```
SMTP Host: mail.yourdomain.com
SMTP Port: 587 (or 465 for SSL)
SMTP Security: TLS (or SSL)
SMTP Username: your full email address
SMTP Password: your email password
```

## 🌐 Domain & SSL Configuration

### 1. Domain Setup
- Ensure domain DNS points to HostAfrica servers
- Wait for DNS propagation (up to 48 hours)
- Verify domain is accessible

### 2. SSL Certificate
- Install SSL certificate through cPanel
- Force HTTPS redirects
- Update all internal links to use HTTPS

### 3. .htaccess Configuration
Create/update `.htaccess` in your root directory:

```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>

# PHP Security
<Files ~ "\.php$">
    Header set X-Content-Type-Options nosniff
    Header set X-Frame-Options DENY
</Files>

# Hide sensitive files
<Files ~ "(\.env|config\.php|\.log)$">
    Order allow,deny
    Deny from all
</Files>
```

## 🔍 Post-Deployment Testing

### 1. Basic Functionality Test
- [ ] Website loads correctly
- [ ] All pages are accessible
- [ ] Images and assets load properly
- [ ] Forms work (contact, newsletter, application)
- [ ] Admin panel is accessible
- [ ] Login/logout works
- [ ] Email notifications are sent

### 2. Performance Check
- [ ] Page load times are acceptable
- [ ] Database queries are optimized
- [ ] File uploads work correctly
- [ ] Mobile responsiveness verified

### 3. Security Verification
- [ ] Admin panel requires authentication
- [ ] Sensitive files are not accessible directly
- [ ] SQL injection protection is working
- [ ] XSS protection is active
- [ ] HTTPS is forced

## 🔧 Common HostAfrica Issues & Solutions

### 1. Database Connection Errors
**Issue**: "Database connection failed"
**Solution**: 
- Verify database credentials in cPanel
- Ensure database user has proper privileges
- Check if database server is running
- Contact HostAfrica support if needed

### 2. Email Not Working
**Issue**: Emails not being sent
**Solution**:
- Verify SMTP settings with HostAfrica
- Check email account exists and password is correct
- Ensure SPF records are configured
- Check spam folders

### 3. File Upload Errors
**Issue**: "File upload failed"
**Solution**:
- Check folder permissions (755 for uploads folder)
- Verify PHP upload limits in cPanel
- Ensure sufficient disk space
- Check file size limits

### 4. 500 Internal Server Error
**Issue**: Website shows 500 error
**Solution**:
- Check error logs in cPanel
- Verify file permissions
- Check PHP syntax errors
- Ensure all required PHP extensions are enabled

## 📞 HostAfrica Support Contact

If you encounter issues specific to HostAfrica hosting:

- **Support Portal**: [HostAfrica Customer Portal]
- **Email**: support@hostafrica.com
- **Phone**: Check your hosting account for support numbers
- **Live Chat**: Available through customer portal

## 🔄 Maintenance & Updates

### Regular Maintenance Tasks
1. **Weekly**:
   - Check error logs
   - Monitor disk space usage
   - Review security logs

2. **Monthly**:
   - Update admin passwords
   - Review user accounts
   - Check backup integrity
   - Monitor performance metrics

3. **Quarterly**:
   - Update PHP version if needed
   - Review and update security settings
   - Audit user permissions
   - Performance optimization review

### Backup Strategy
1. **Database Backups**:
   - Set up automated daily backups through cPanel
   - Keep at least 30 days of backup history
   - Store backups in multiple locations

2. **File Backups**:
   - Regular backup of uploads and configuration files
   - Use HostAfrica backup services
   - Consider off-site backup solutions

## ✅ Deployment Completion Checklist

- [ ] Database imported successfully
- [ ] All configuration files updated with production values
- [ ] Files uploaded and permissions set correctly
- [ ] Default admin credentials changed
- [ ] Email functionality tested and working
- [ ] SSL certificate installed and HTTPS forced
- [ ] Website fully functional on production domain
- [ ] Admin panel accessible and secure
- [ ] Performance optimized
- [ ] Security measures implemented
- [ ] Backup strategy configured
- [ ] Documentation updated with production details

## 📚 Additional Resources

- [HostAfrica Documentation](https://www.hostafrica.com/support/)
- [PHP 8.1 Documentation](https://www.php.net/manual/en/)
- [MySQL 8.0 Documentation](https://dev.mysql.com/doc/refman/8.0/en/)
- [cPanel User Guide](https://docs.cpanel.net/)

---

## 🛡️ Security & Performance (.htaccess)

The included `.htaccess` file provides comprehensive security and performance optimizations:

### Features Included
- **Security Headers**: XSS protection, clickjacking prevention, content type sniffing protection
- **HTTPS Enforcement**: Automatic redirect to HTTPS with HSTS
- **File Protection**: Blocks access to sensitive files (.env, .log, .sql, config files)
- **Performance**: Gzip compression, browser caching, ETags
- **URL Rewriting**: Clean URLs and proper routing
- **PHP Security**: Disables dangerous functions, secure session settings

### Deployment Steps
1. Upload the `.htaccess` file to your domain's root directory (public_html)
2. Replace "yourdomain.com" with your actual domain name in the file
3. Test that the security headers are working using online tools
4. Verify HTTPS redirect is functioning properly

### Testing Security Headers
Use these tools to verify your security configuration:
- [Security Headers Checker](https://securityheaders.com/)
- [SSL Labs SSL Test](https://www.ssllabs.com/ssltest/)
- [Mozilla Observatory](https://observatory.mozilla.org/)

## 🔄 Automated Backup & Maintenance

### Backup Script Setup
The `backup-maintenance.sh` script provides automated backup and maintenance:

1. **Upload the script** to your server (e.g., `/home/username/scripts/`)
2. **Set execute permissions**: `chmod +x backup-maintenance.sh`
3. **Update paths** in the script to match your server configuration
4. **Test the script**: `./backup-maintenance.sh health`

### Available Commands
```bash
# Create full backup
./backup-maintenance.sh backup

# Database backup only
./backup-maintenance.sh db-backup

# File backup only
./backup-maintenance.sh file-backup

# Clean up old backups and temp files
./backup-maintenance.sh cleanup

# Full maintenance (backup + cleanup)
./backup-maintenance.sh maintain

# Health check
./backup-maintenance.sh health
```

### Automated Cron Jobs
Add these to your crontab (`crontab -e`) for automation:

```bash
# Daily database backup at 2 AM
0 2 * * * /home/username/scripts/backup-maintenance.sh db-backup

# Weekly full maintenance on Sunday at 3 AM
0 3 * * 0 /home/username/scripts/backup-maintenance.sh maintain

# Daily health check at 6 AM
0 6 * * * /home/username/scripts/backup-maintenance.sh health
```

## 🔐 SSL Certificate Monitoring

### SSL Monitor Setup
The `ssl-monitor.sh` script helps monitor SSL certificate expiry:

1. **Upload the script** to your server
2. **Set execute permissions**: `chmod +x ssl-monitor.sh`
3. **Update domain and email** in the script
4. **Test monitoring**: `./ssl-monitor.sh check yourdomain.com admin@yourdomain.com`

### SSL Monitoring Commands
```bash
# Check certificate status
./ssl-monitor.sh check yourdomain.com admin@yourdomain.com

# Get detailed certificate info
./ssl-monitor.sh info yourdomain.com

# Run monitoring with alerts
./ssl-monitor.sh monitor yourdomain.com admin@yourdomain.com

# Generate monitoring report
./ssl-monitor.sh report yourdomain.com
```

### Automated SSL Monitoring
Add to crontab for automated monitoring:

```bash
# Daily SSL certificate check at 8 AM
0 8 * * * /home/username/scripts/ssl-monitor.sh monitor yourdomain.com admin@yourdomain.com

# Weekly detailed report on Monday at 9 AM
0 9 * * 1 /home/username/scripts/ssl-monitor.sh report yourdomain.com
```

## 📋 Complete Deployment Checklist

### Pre-Deployment
- [ ] Download all deployment files
- [ ] Review and customize configuration files
- [ ] Prepare domain and hosting account
- [ ] Backup any existing data

### Database Setup
- [ ] Create MySQL database in cPanel
- [ ] Import `database-setup.sql` via phpMyAdmin
- [ ] Verify all tables were created
- [ ] Test database connection

### File Upload & Configuration
- [ ] Upload project files to public_html
- [ ] Upload `.htaccess` file to root directory
- [ ] Set correct file permissions (755 for directories, 644 for files)
- [ ] Copy `.env.production` to `.env`
- [ ] Update database credentials in config files
- [ ] Replace domain placeholders in all config files

### Security Configuration
- [ ] Change default admin password
- [ ] Update security keys and salts
- [ ] Configure SSL certificate
- [ ] Test security headers
- [ ] Verify `.htaccess` security rules are working
- [ ] Test file access restrictions

### Automation & Monitoring Setup
- [ ] Upload maintenance scripts to server
- [ ] Set execute permissions on shell scripts (chmod +x)
- [ ] Update script paths and configurations
- [ ] Test backup and monitoring scripts
- [ ] Configure cron jobs for automated tasks
- [ ] Set up SSL certificate monitoring
- [ ] Test email alerts

### Testing & Verification
- [ ] Test website functionality
- [ ] Verify email sending
- [ ] Check file uploads
- [ ] Test admin panel access
- [ ] Validate forms and database operations
- [ ] Test SSL certificate and security headers
- [ ] Verify backup scripts work
- [ ] Check automated monitoring

### Go Live & Documentation
- [ ] Update DNS settings
- [ ] Configure email accounts
- [ ] Set up monitoring alerts
- [ ] Create initial backups
- [ ] Document login credentials and procedures
- [ ] Update team with production access details
- [ ] Schedule regular maintenance tasks

## 📦 Deployment Files Summary

This deployment package includes:

1. **`database-setup.sql`** - Complete database schema and seed data
2. **`.env.production`** - Production environment configuration
3. **`config.production.php`** - PHP production configuration
4. **`.htaccess`** - Security and performance optimizations
5. **`backup-maintenance.sh`** - Automated backup and maintenance
6. **`ssl-monitor.sh`** - SSL certificate monitoring
7. **`DEPLOYMENT_GUIDE.md`** - This comprehensive guide

## 🎯 Next Steps After Deployment

1. **Monitor Performance**: Use tools like Google PageSpeed Insights
2. **Set Up Analytics**: Install Google Analytics or similar
3. **SEO Optimization**: Configure meta tags and sitemaps
4. **Content Management**: Train staff on admin panel usage
5. **Regular Updates**: Keep PHP and dependencies updated
6. **Security Audits**: Regular security assessments
7. **User Training**: Document processes for end users

---

**Important**: Keep all passwords, API keys, and configuration details secure. Never commit sensitive information to version control systems.

**Support**: If you need assistance with this deployment, please contact the development team with specific error messages and hosting environment details.
