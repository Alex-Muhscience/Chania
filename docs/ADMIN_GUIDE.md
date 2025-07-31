# Digital Empowerment Network - Admin Guide

## Table of Contents
1. [Getting Started](#getting-started)
2. [User Management](#user-management)
3. [Program Management](#program-management)
4. [Application Processing](#application-processing)
5. [Event Management](#event-management)
6. [Content Management](#content-management)
7. [Communication Tools](#communication-tools)
8. [Reports & Analytics](#reports--analytics)
9. [System Administration](#system-administration)
10. [Security & Permissions](#security--permissions)
11. [Maintenance & Troubleshooting](#maintenance--troubleshooting)

## Getting Started

### Initial Setup
1. Access the admin panel at `/admin/`
2. Log in with your administrator credentials
3. Change the default admin password immediately
4. Configure site settings under **System > Site Settings**
5. Set up user roles and permissions under **Users > Roles**

### Dashboard Overview
The admin dashboard provides:
- **Quick Statistics**: Users, programs, applications, events
- **Recent Activity**: Latest admin actions and system events
- **Performance Metrics**: System health and database statistics
- **Quick Actions**: Direct links to common admin tasks

## User Management

### Adding Users
1. Navigate to **Users > All Users**
2. Click **Add New User**
3. Fill in required information:
   - Username (unique)
   - Email address (unique)
   - First and Last name
   - Password (meets complexity requirements)
   - Role assignment
4. Set user status (Active/Inactive)
5. Click **Save User**

### Managing User Roles
1. Go to **Users > Roles**
2. Create new roles or edit existing ones
3. Assign permissions to roles:
   - `users` - User management access
   - `programs` - Program management
   - `applications` - Application processing
   - `events` - Event management
   - `pages` - Content management
   - `media` - Media library access
   - `reports` - Report generation
   - `settings` - System configuration
   - `*` - Full administrator access

### User Export
1. Navigate to **Users > Export Users**
2. Configure filters:
   - Search by name/email/username
   - Filter by role
   - Filter by status (Active/Inactive)
   - Date range for registration
3. Select export fields
4. Choose format (CSV, Excel, JSON, XML)
5. Click **Export Users**

## Program Management

### Creating Programs
1. Go to **Programs > All Programs**
2. Click **Add New Program**
3. Fill in program details:
   - Title and description
   - Category selection
   - Duration and capacity
   - Requirements and objectives
   - Location and schedule
4. Set program status
5. Save the program

### Program Categories
1. Navigate to **Programs > Categories**
2. Add new categories for better organization
3. Assign programs to appropriate categories
4. Use categories for filtering and reporting

### Managing Applications
Applications are automatically created when users apply for programs:
1. Go to **Applications > All Applications**
2. Review application details
3. Update application status:
   - Pending
   - Under Review
   - Approved
   - Rejected
   - Completed
4. Add notes and comments
5. Export application data for analysis

## Event Management

### Creating Events
1. Navigate to **Events > All Events**
2. Click **Add New Event**
3. Configure event details:
   - Title and description
   - Date and time
   - Location (physical or virtual)
   - Capacity limits
   - Registration requirements
4. Set event status and visibility
5. Save the event

### Event Registration Management
1. View registered participants
2. Manage waitlists for full events
3. Send confirmation emails
4. Track attendance and no-shows
5. Generate attendance reports

## Content Management

### Page Management
1. Go to **Content > Pages**
2. Create static pages with rich text editor
3. Configure SEO settings:
   - Meta title and description
   - URL slug customization
   - Header tags optimization
4. Set page templates and publishing status
5. Preview pages before publishing

### Blog Management
1. Navigate to **Content > Blog**
2. Create blog posts with featured images
3. Organize posts by categories and tags
4. Schedule post publication
5. Manage comments and engagement

### FAQ Management
1. Go to **Content > FAQs**
2. Create frequently asked questions
3. Organize by categories
4. Set display order and status
5. Provide detailed answers with formatting

### Media Library
1. Access **Media > Library**
2. Upload files (images, documents, videos)
3. Organize with folders and tags
4. Set file permissions and access levels
5. Use search and filtering tools
6. Bulk operations for efficiency

## Communication Tools

### Email Templates
1. Navigate to **Communication > Email Templates**
2. Create reusable email templates
3. Use variables for personalization:
   - `{{name}}` - User's full name
   - `{{email}}` - User's email
   - `{{site_name}}` - Site name
   - `{{program_title}}` - Program name
4. Test templates before use
5. Activate/deactivate as needed

### Email Campaigns
1. Go to **Communication > Email Campaigns**
2. Create targeted campaigns
3. Select recipient groups:
   - All users
   - Specific roles
   - Program participants
   - Event attendees
4. Schedule send times
5. Track delivery and engagement

### Contact Management
1. Access **Communication > Contacts**
2. Review contact form submissions
3. Mark messages as read/unread
4. Respond to inquiries
5. Archive or delete old messages

## Reports & Analytics

### Dashboard Analytics
The main dashboard displays:
- User registration trends
- Application status distribution
- Event attendance metrics
- Program enrollment statistics

### Custom Report Builder
1. Navigate to **Reports > Report Builder**
2. Select data source (users, applications, events, etc.)
3. Choose fields to include
4. Apply filters and conditions
5. Set sorting and grouping options
6. Generate and export reports

### Available Reports
- **User Reports**: Registration trends, activity levels, role distribution
- **Program Reports**: Enrollment statistics, completion rates, feedback analysis
- **Application Reports**: Application volume, approval rates, processing times
- **Event Reports**: Attendance tracking, registration trends, capacity utilization
- **Financial Reports**: Revenue tracking, cost analysis, budget reports

### Data Export Tools
1. Go to **Reports > Data Export**
2. Select data type to export
3. Configure export parameters
4. Choose format (CSV, JSON, Excel)
5. Schedule automated exports (if needed)

## System Administration

### Site Settings
1. Access **System > Site Settings**
2. Configure global settings:
   - Site information
   - Contact details
   - Email settings
   - Security preferences
   - Feature toggles
3. Save changes and test functionality

### User Role Management
1. Navigate to **System > Roles**
2. Create custom roles
3. Assign specific permissions
4. Test role functionality
5. Update existing user role assignments

### Security Configuration
1. Go to **System > Security Settings**
2. Configure password policies
3. Set session timeout limits
4. Enable two-factor authentication
5. Configure login attempt limits
6. Review security logs regularly

### System Monitoring
1. Access **System > System Monitor**
2. View performance metrics
3. Check database health
4. Monitor storage usage
5. Review error logs
6. Run system diagnostics

## Security & Permissions

### Permission System
The admin panel uses a role-based permission system:

**Administrator (`*` permission)**
- Full access to all system features
- User and role management
- System configuration
- Security settings

**Manager Role**
- User management (limited)
- Program and event management
- Report generation
- Content management

**Editor Role**
- Content creation and editing
- Media library access
- FAQ management
- Limited user interaction

**Custom Roles**
- Define specific permission combinations
- Assign to users based on responsibilities
- Regular review and updates

### Security Best Practices
1. **Strong Passwords**: Enforce complexity requirements
2. **Regular Updates**: Keep system and dependencies updated
3. **Access Reviews**: Regularly review user access and permissions
4. **Audit Logs**: Monitor admin actions and security events
5. **Backup Strategy**: Maintain regular backups
6. **SSL/TLS**: Use HTTPS for all admin access

### Activity Monitoring
1. Navigate to **System > Activity Logs**
2. Review admin actions
3. Monitor login attempts
4. Track data changes
5. Investigate suspicious activity
6. Export logs for analysis

## Maintenance & Troubleshooting

### System Health Checks
Run regular health checks:
```bash
php admin/debug/system_health_check.php
```

This script checks:
- Database connectivity
- Required PHP extensions
- File permissions
- Core class functionality
- Security configuration
- Performance metrics

### Common Issues

#### Login Problems
- **Forgot Password**: Use password reset functionality
- **Account Locked**: Check failed login attempts in security logs
- **Session Issues**: Clear browser cache and cookies

#### Performance Issues
- **Slow Loading**: Check database query performance
- **High Memory Usage**: Review memory limits and usage
- **Timeout Errors**: Increase script execution time

#### Database Issues
- **Connection Errors**: Verify database credentials
- **Table Missing**: Check database schema integrity
- **Data Corruption**: Restore from backup if necessary

### Backup Procedures
1. **Database Backup**:
   ```bash
   mysqldump -u username -p database_name > backup.sql
   ```

2. **File Backup**:
   - Upload directories
   - Configuration files
   - Custom modifications

3. **Automated Backups**:
   - Set up cron jobs for regular backups
   - Store backups in secure, offsite location
   - Test backup restoration procedures

### Log Management
Monitor these log files:
- **Admin Activity**: `admin/logs/admin_activity.log`
- **Security Events**: `admin/logs/security.log`
- **Error Log**: `admin/logs/error.log`
- **System Log**: `admin/logs/system.log`

### Performance Optimization
1. **Database Optimization**:
   - Regular table optimization
   - Index optimization
   - Query performance analysis

2. **Caching**:
   - Enable OPcache for PHP
   - Implement query result caching
   - Use browser caching for static assets

3. **File Management**:
   - Regular cleanup of temporary files
   - Compress and optimize images
   - Remove unused media files

## Support & Resources

### Getting Help
- Check this documentation first
- Review error logs for specific issues
- Run system health check script
- Contact system administrator

### Regular Maintenance Tasks
- [ ] Review user accounts and permissions monthly
- [ ] Check system logs weekly
- [ ] Update software and security patches
- [ ] Backup data regularly
- [ ] Monitor performance metrics
- [ ] Clean up old files and logs

### Version Updates
When updating the system:
1. Backup all data and files
2. Test updates in staging environment
3. Review changelog for breaking changes
4. Update configuration files if needed
5. Run database migrations
6. Test all functionality after update

---

**Last Updated**: January 2025  
**Version**: 2.0.0  
**Documentation Maintainer**: System Administrator
