# Chania Skills for Africa - HostAfrica Deployment Guide

## Pre-Deployment Checklist

### 1. Files to Upload
Upload all files except:
- [ ] `admin/test-access.php` (delete this file)
- [ ] `deploy-setup.php` (delete after running setup)
- [ ] `.git/` directory (if exists)
- [ ] Local database backups
- [ ] Any temporary files

### 2. HostAfrica Account Setup
- [ ] Domain configured and pointing to HostAfrica
- [ ] cPanel access working
- [ ] MySQL database created
- [ ] Database user created with full privileges
- [ ] Email accounts set up (if using custom domain email)
- [ ] SSL certificate installed (Let's Encrypt or premium)

## Deployment Steps

### Step 1: Upload Files
1. **Via File Manager or FTP:**
   - Upload all project files to `public_html/` directory
   - Ensure all files maintain their directory structure
   - Verify `.htaccess` files are uploaded (may be hidden files)

### Step 2: Configure Database
1. **In HostAfrica cPanel:**
   - Go to MySQL Databases
   - Create database: `your_account_database_name`
   - Create user with secure password
   - Grant all privileges to user for the database
   - Note down: hostname, database name, username, password

### Step 3: Run Deployment Setup
1. **Access setup script:**
   ```
   https://www.euroafriquecorporateskills.com/deploy-setup.php
   ```
2. **Follow the setup wizard:**
   - Creates necessary directories
   - Sets proper file permissions
   - Copies production environment settings
   - Tests basic functionality

### Step 4: Configure Environment
1. **Edit `.env` file with your details:**
   ```bash
   # Update these values
   APP_BASE_URL="https://www.euroafriquecorporateskills.com"
   ADMIN_URL="https://www.euroafriquecorporateskills.com/admin"
   
   DB_HOST="localhost"
   DB_NAME="euroafr1_chania_db"
   DB_USER="euroafr1_chania_db"
   DB_PASS="7Nv2vMrxF5DSabXZJ6UR"
   
   SMTP_USERNAME="admin@euroafriquecorporateskills.com"
   SMTP_PASSWORD="your-email-password"
   SMTP_FROM_EMAIL="noreply@euroafriquecorporateskills.com"
   
   ADMIN_ACCESS_KEY="your-very-secure-access-key"
   ```

### Step 5: Import Database
1. **Via phpMyAdmin:**
   - Access phpMyAdmin in cPanel
   - Select your database
   - Import your database dump file
   - Verify all tables are created successfully

### Step 6: Test Website
1. **Frontend Testing:**
   - [ ] Homepage loads correctly
   - [ ] Navigation works
   - [ ] Contact forms work
   - [ ] Application forms work
   - [ ] Event registration works

2. **Admin Testing:**
   - [ ] Access: `https://www.euroafriquecorporateskills.com/admin/secure-admin-access.php`
   - [ ] Enter your secure access key
   - [ ] Login with admin credentials
   - [ ] Test admin functionality

### Step 7: Security Configuration
1. **Admin Access:**
   - Change default admin access key
   - Test secure admin access
   - Consider IP whitelisting if needed

2. **File Permissions:**
   - Ensure sensitive directories are protected
   - Verify .htaccess files are working
   - Test file upload functionality

### Step 8: SSL Configuration
1. **Enable HTTPS:**
   - Install SSL certificate (Let's Encrypt recommended)
   - Update `.env` file URLs to use `https://`
   - Enable HSTS header in `.htaccess`:
     ```apache
     Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
     ```

### Step 9: Final Cleanup
1. **Delete temporary files:**
   - [ ] Delete `deploy-setup.php`
   - [ ] Delete `admin/test-access.php`
   - [ ] Delete `.setup_complete` (if exists)
   - [ ] Remove any backup files

## Post-Deployment Configuration

### Email Configuration
1. **SMTP Settings:**
   - Use HostAfrica SMTP or Gmail SMTP
   - Configure SPF records for your domain
   - Set up DKIM if available
   - Test email functionality

### Backup Setup
1. **Database Backups:**
   - Set up automated database backups in cPanel
   - Configure retention policy
   - Test backup restoration

2. **File Backups:**
   - Use HostAfrica backup features
   - Consider additional cloud backup

### Performance Optimization
1. **Caching:**
   - Enable caching in `.env` file
   - Configure cache directory permissions
   - Test cache functionality

2. **Compression:**
   - Enable gzip compression in cPanel
   - Optimize images for web

### Monitoring Setup
1. **Error Monitoring:**
   - Monitor error logs regularly
   - Set up log rotation
   - Configure alert thresholds

2. **Security Monitoring:**
   - Monitor admin access logs
   - Set up intrusion detection
   - Regular security updates

## Troubleshooting Common Issues

### Database Connection Issues
- **Problem:** "Database Error: Connection failed"
- **Solution:** Verify database credentials in `.env` file
- **Check:** Database exists, user has privileges, hostname is correct

### File Permission Issues
- **Problem:** "Permission denied" errors
- **Solution:** Set proper permissions via File Manager
- **Directories:** 755, **Files:** 644, **Logs/Cache:** 755

### Admin Access Issues
- **Problem:** "Invalid Access Key" or "Access Denied"
- **Solution:** Check `ADMIN_ACCESS_KEY` in `.env` file
- **Verify:** Security logs in `admin/logs/security.log`

### Email Not Working
- **Problem:** Emails not being sent
- **Solution:** Verify SMTP settings in `.env` file
- **Test:** Use cPanel email test functionality

### SSL Certificate Issues
- **Problem:** "Not Secure" warning in browser
- **Solution:** Install SSL certificate via cPanel
- **Update:** Change all URLs to `https://` in `.env`

## Security Best Practices

### Regular Maintenance
- [ ] Update PHP version regularly
- [ ] Monitor security logs
- [ ] Update database regularly
- [ ] Check for file integrity
- [ ] Review user access logs

### Access Control
- [ ] Use strong passwords
- [ ] Enable two-factor authentication
- [ ] Limit admin access by IP (optional)
- [ ] Regular password changes
- [ ] Monitor failed login attempts

### Backup Strategy
- [ ] Daily database backups
- [ ] Weekly full site backups
- [ ] Test backup restoration monthly
- [ ] Store backups offsite
- [ ] Document recovery procedures

## Support Resources

### HostAfrica Support
- **Support Portal:** Available in cPanel
- **Documentation:** HostAfrica knowledge base
- **Contact:** Support ticket system

### Application Support
- **Admin Guide:** `docs/ADMIN_GUIDE.md`
- **API Documentation:** `api/README.md`
- **Database Schema:** `database/README.md`

## Deployment Completion Checklist

- [ ] All files uploaded successfully
- [ ] Database configured and imported
- [ ] Environment variables configured
- [ ] SSL certificate installed
- [ ] Email functionality tested
- [ ] Admin access working
- [ ] Frontend functionality tested
- [ ] Backups configured
- [ ] Monitoring set up
- [ ] Temporary files cleaned up
- [ ] Documentation updated

---

**Important:** Keep this deployment guide and your `.env` file secure. Never commit sensitive configuration to version control.

For additional support or questions about the deployment, refer to the documentation in the `docs/` directory.
