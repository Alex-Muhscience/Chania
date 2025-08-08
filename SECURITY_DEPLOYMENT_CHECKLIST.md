# 🔒 Security Deployment Checklist

## Critical Security Steps Before Deployment

### ⚠️ MANDATORY: Update Environment Variables
Before pushing to production, you MUST update the following files with real values:

#### 1. `.env.production`
```bash
# Update these placeholder values with real credentials:
DB_NAME="your_production_database_name"          # Real database name
DB_USER="your_production_database_user"          # Real database username  
DB_PASS="your_secure_database_password"          # Real database password

# Generate secure keys (minimum 64 characters):
ADMIN_ACCESS_KEY="generate_a_very_secure_64_character_key_here_for_admin_access"
JWT_SECRET_KEY="generate_a_unique_jwt_secret_key_minimum_64_characters_long"

# Update email credentials:
SMTP_USERNAME="your-email@yourdomain.com"        # Real email address
SMTP_PASSWORD="your-secure-app-password"         # Real app password
```

#### 2. `test-db-connection.php` (if using)
```php
// Update these before testing:
$dbname = 'your_database_name';      // Real database name
$username = 'your_database_user';    // Real database username
$password = 'your_database_password'; // Real database password
```

### 🗑️ Files to DELETE After Deployment

These files contain sensitive information or are only for development:

```bash
# Delete these files from production server:
- deploy-setup.php           # Contains setup logic
- export-database.php        # Contains database export logic
- test-db-connection.php     # Contains connection testing
- info.php                   # Contains server information
- server-info.php           # Contains server diagnostics
- database_exports/         # Contains database backups
- admin - Shortcut.lnk      # Development shortcuts
```

### 🛡️ Security Keys Generation

Use these methods to generate secure keys:

#### Option 1: OpenSSL (recommended)
```bash
# Generate 64-character random key:
openssl rand -hex 32

# Generate base64 key:
openssl rand -base64 48
```

#### Option 2: Online Generator
- Visit: https://randomkeygen.com/
- Use "504-bit WPA Key" for maximum security

#### Option 3: PHP Script
```php
// Generate secure random key:
echo bin2hex(random_bytes(32));
```

### 📝 Environment File Security

1. **Never commit `.env` with real secrets**
2. **Use `.env.example` for templates**  
3. **Set proper file permissions:**
   ```bash
   chmod 600 .env.production
   chmod 644 .env.example
   ```

### 🚫 Git Security Check

Verify these files are in `.gitignore`:
```gitignore
# Environment files
.env
.env.*
!.env.example

# Deployment scripts
deploy-setup.php
export-database.php
test-db-connection.php
info.php
server-info.php

# Database exports
database_exports/
*.sql
```

### 🔍 Pre-Deployment Verification

Run this checklist before deploying:

- [ ] All placeholder values replaced in `.env.production`
- [ ] Secure keys generated (minimum 64 characters)
- [ ] Database credentials updated and tested
- [ ] Email credentials updated and tested  
- [ ] Development files removed from production
- [ ] `.gitignore` includes all sensitive files
- [ ] No hardcoded secrets in code
- [ ] All debug modes disabled in production
- [ ] HTTPS configured for production domain

### 🎯 Post-Deployment Actions

After successful deployment:

1. **Delete setup files** from server
2. **Test admin access** with new credentials
3. **Verify email functionality**
4. **Test database connections**
5. **Check error logs** for issues
6. **Enable SSL certificate**
7. **Set up regular backups**

### 📞 Emergency Contacts

If you encounter issues:
- Check server logs: `/logs/error.log`
- Database issues: Check hosting provider's MySQL logs
- Email issues: Verify SMTP settings with hosting provider

---

**⚠️ CRITICAL WARNING:**
Never commit files containing real passwords, database credentials, or API keys to version control. Always use placeholder values in repositories.
