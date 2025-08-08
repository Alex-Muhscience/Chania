# Admin Security Enhancements

This document explains the security measures implemented for the admin area to prevent unauthorized access and make it difficult to find admin URLs through simple URL guessing.

## Security Features Implemented

### 1. Multi-layer .htaccess Protection

**Main Admin Directory (.htaccess)**
- Prevents directory listing
- Blocks suspicious user agents (bots, scanners, crawlers)
- Implements rate limiting to prevent brute force attacks
- Blocks direct access to sensitive directories (classes, controllers, includes, sql, views, etc.)
- Prevents access to backup files and configuration files
- Implements security headers
- Blocks common attack vectors and SQL injection attempts
- Redirects all admin root access to the public directory

**Admin Public Directory (.htaccess)**
- Additional user agent filtering
- Query string validation to block suspicious requests
- Session security measures
- Enhanced security headers

### 2. Secure Admin Access Controller

**File: `secure-admin-access.php`**
- Provides an additional authentication layer before admin access
- Rate limiting with IP-based lockout
- Security event logging
- Session-based access control with timeout
- Clean, professional access form

### 3. Directory Structure Protection

- **Logs Directory**: Protected with dedicated .htaccess to prevent web access
- **Backup Files**: Automatically blocked by .htaccess rules
- **Configuration Files**: Protected from direct web access
- **Sensitive Directories**: Classes, includes, controllers are blocked from direct access

## How to Use the Enhanced Security

### Option 1: Direct Admin Access (Standard)
Access admin through: `http://yourdomain.com/admin/`
- Will be redirected to `admin/public/`
- Protected by .htaccess security rules

### Option 2: Secure Access Controller (Recommended for Production)
Access admin through: `http://yourdomain.com/admin/secure-admin-access.php`
- Requires access key: `euroafrique-corporate-skills#2025!` (change this in production!)
- Provides additional security layer
- Logs all access attempts
- Rate limiting protection

## Configuration Steps

### 1. Change the Access Key
Edit `secure-admin-access.php` and change the `ADMIN_ACCESS_KEY`:
```php
define('ADMIN_ACCESS_KEY', 'your_unique_secure_key_here');
```

### 2. Enable IP-Based Access Control (Optional)
In `admin/.htaccess`, uncomment and configure IP restrictions:
```apache
<RequireAll>
    Require ip 127.0.0.1
    Require ip 192.168.1.0/24
    Require ip YOUR_OFFICE_IP_HERE
</RequireAll>
```

### 3. Enable Time-Based Access Control (Optional)
In `admin/.htaccess`, uncomment to restrict access hours:
```apache
RewriteCond %{TIME_HOUR} !^(08|09|10|11|12|13|14|15|16|17)$
RewriteRule ^.*$ - [F,L]
```

### 4. Enable Referrer-Based Protection (Optional)
In `admin/public/.htaccess`, uncomment and configure:
```apache
RewriteCond %{HTTP_REFERER} !^https?://(.*\.)?yourdomain\.com [NC]
RewriteCond %{REQUEST_URI} !^/admin/public/login\.php$ [NC]
RewriteRule ^.*$ - [F,L]
```

## Security Features Explained

### Rate Limiting
- Maximum 5 failed attempts per IP
- 5-minute lockout period after exceeding attempts
- Automatic reset after lockout period

### User Agent Filtering
- Blocks empty user agents
- Blocks common scanning tools (nikto, sqlmap, nmap, etc.)
- Blocks automated bots and crawlers

### Query String Validation
- Blocks SQL injection attempts
- Prevents path traversal attacks
- Blocks null byte attacks
- Prevents session fixation

### Security Headers
- X-Frame-Options: Prevents clickjacking
- X-XSS-Protection: Enables XSS filtering
- X-Content-Type-Options: Prevents MIME sniffing
- Referrer-Policy: Controls referrer information
- Content-Security-Policy: Restricts resource loading

### Logging
All security events are logged to `admin/logs/security.log` including:
- Access attempts
- Failed authentication
- Security violations
- IP addresses and user agents

## Best Practices

1. **Change Default Access Key**: Always change the default access key in production
2. **Monitor Logs**: Regularly check security logs for suspicious activity
3. **Use HTTPS**: Ensure admin area is only accessible via HTTPS
4. **IP Restrictions**: Consider implementing IP-based access control
5. **Regular Updates**: Keep security configurations updated
6. **Backup Configurations**: Backup your .htaccess files regularly

## Files Modified/Created

- `admin/.htaccess` - Main admin security configuration
- `admin/public/.htaccess` - Enhanced with additional security
- `admin/secure-admin-access.php` - Secure access controller
- `admin/logs/.htaccess` - Logs directory protection
- `admin/SECURITY_README.md` - This documentation

## Troubleshooting

### Access Denied Errors
1. Check if your IP is being blocked
2. Verify user agent is not flagged as suspicious
3. Check security logs for specific error details
4. Ensure access key is correct (if using secure access)

### Rate Limiting Issues
1. Wait for lockout period to expire (5 minutes)
2. Check logs to see failed attempt details
3. Consider adjusting MAX_LOGIN_ATTEMPTS in secure-admin-access.php

### File Permission Issues
1. Ensure logs directory has write permissions
2. Check .htaccess files are readable by web server
3. Verify PHP can write to session directory
