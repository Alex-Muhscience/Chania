# Digital Empowerment Network - Admin Panel

A comprehensive, production-ready admin panel built with PHP, MySQL, and modern web technologies for managing digital empowerment programs, users, and organizational activities.

## 🌟 Features

### **Core Management**
- **User Management** - Complete CRUD operations with role-based permissions
- **Program Management** - Handle training programs with categories and enrollment
- **Event Management** - Organize and track events with registration capabilities
- **Application Processing** - Manage program applications with status tracking
- **Content Management** - Dynamic pages, blog posts, FAQs, and testimonials

### **Communication & Marketing**
- **Email Templates** - Rich text email templates with variable support
- **Email Campaigns** - Bulk email campaigns with scheduling
- **SMS Templates** - SMS messaging system integration
- **Newsletter Management** - Subscriber management and campaigns
- **Contact Management** - Handle contact form submissions

### **Reports & Analytics**
- **Advanced Dashboard** - Real-time statistics and charts
- **Custom Report Builder** - Build reports with drag-and-drop interface
- **Data Export Tools** - Export data in CSV/JSON formats
- **Activity Timeline** - Complete audit trail of admin actions
- **Security Monitoring** - Security logs and breach detection

### **Media & Files**
- **Media Library** - Upload, organize, and manage media files
- **File Manager** - Advanced file management with filtering
- **Bulk Operations** - Mass upload/delete capabilities
- **File Security** - Secure file storage and access controls

### **System Administration**
- **Role-Based Access Control** - Granular permission system
- **Security Features** - 2FA, session management, brute force protection
- **System Monitoring** - Performance metrics and health checks
- **Backup Management** - Automated backup and restore capabilities
- **Settings Management** - Site-wide configuration options

## 🛡️ Security Features

### **Authentication & Authorization**
- **Two-Factor Authentication (2FA)** using TOTP
- **Role-based permissions** with granular control
- **Session security** with HTTP-only cookies
- **Brute force protection** with rate limiting
- **Password complexity requirements**

### **Data Protection**
- **XSS Protection** - All outputs properly sanitized
- **SQL Injection Prevention** - Prepared statements throughout
- **CSRF Protection** - Token-based form validation
- **Input Validation** - Comprehensive server-side validation
- **File Upload Security** - Type and size restrictions

### **Audit & Monitoring**
- **Admin Activity Logging** - Complete audit trail
- **Security Event Logging** - Failed logins, suspicious activity
- **Real-time Monitoring** - System health and performance
- **Error Tracking** - Comprehensive error logging

## 🏗️ Architecture

### **Backend Architecture**
```
admin/
├── classes/           # Core business logic classes
├── controllers/       # MVC controllers for each module
├── views/            # HTML templates with PHP
├── includes/         # Common includes and configuration
├── public/           # Public-facing admin pages
└── actions/          # Form processing scripts

shared/Core/          # Shared core classes
├── Database.php      # Database connection and utilities
├── User.php          # User management
├── Utilities.php     # Common utility functions
├── AdminLogger.php   # Activity logging
└── Security/         # Security-related classes
```

### **MVC Pattern Implementation**
- **Models** - Data access and business logic in `classes/`
- **Views** - HTML templates in `views/`
- **Controllers** - Request handling in `controllers/`
- **BaseController** - Common functionality for all controllers

## 🚀 Installation

### **Prerequisites**
- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.2+
- Apache web server (with mod_rewrite)
- Composer (optional, for dependencies)

### **Setup Instructions**

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-org/digital-empowerment-network.git
   cd digital-empowerment-network
   ```

2. **Configure the database**
   - Create a MySQL database named `chania_db`
   - Import the database schema from `database/schema.sql`
   - Update database credentials in `admin/includes/config.php`

3. **Set file permissions**
   ```bash
   chmod -R 755 uploads/
   chmod -R 755 backups/
   chmod 644 admin/includes/config.php
   ```

4. **Configure virtual host (Apache)**
   ```apache
   <VirtualHost *:80>
       ServerName den-admin.local
       DocumentRoot "/path/to/chania"
       DirectoryIndex index.php
       
       <Directory "/path/to/chania">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

5. **Default admin account**
   - Username: `admin`
   - Password: `admin123` (change immediately)
   - Role: Administrator

## ⚙️ Configuration

### **Core Configuration (`admin/includes/config.php`)**
```php
// Database Settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'chania_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// Application Settings
define('BASE_URL', 'http://localhost/chania');
define('SITE_NAME', 'Digital Empowerment Network');
define('ITEMS_PER_PAGE', 20);

// Security Settings
define('ENABLE_2FA', true);
define('SESSION_TIMEOUT', 7200); // 2 hours
define('MAX_LOGIN_ATTEMPTS', 5);
```

### **Email Configuration**
Update SMTP settings in `shared/Core/EmailTemplate.php`:
```php
$mail->isSMTP();
$mail->Host = 'smtp.your-provider.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@domain.com';
$mail->Password = 'your-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

## 👥 User Roles & Permissions

### **Default Roles**
- **Administrator** - Full system access (*)
- **Manager** - Limited admin access
- **Editor** - Content management only
- **User** - Basic user privileges

### **Available Permissions**
- `users` - User management
- `programs` - Program management
- `applications` - Application processing
- `events` - Event management
- `pages` - Page management
- `media` - Media library access
- `files` - File management
- `reports` - Report generation
- `settings` - System settings
- `roles` - Role management
- `templates` - Email templates
- `*` - Full access (admin only)

## 📊 Dashboard Features

### **Statistics Overview**
- Total users, programs, applications, events
- Monthly growth metrics
- Application status breakdown
- User activity metrics

### **Quick Actions**
- Add new program/event/user
- Process pending applications
- Send bulk communications
- Generate reports

### **Recent Activity**
- Latest user registrations
- Recent application submissions
- System activity timeline
- Notification center

## 📱 Mobile Responsiveness

- **Fully responsive design** works on all device sizes
- **Mobile-optimized tables** with horizontal scrolling
- **Touch-friendly interface** with appropriate button sizes
- **Responsive navigation** with collapsible sidebar
- **Mobile-specific features** like swipe gestures

## 🎨 UI/UX Features

### **Modern Design**
- **Professional color scheme** with customizable themes
- **Dark mode support** with toggle functionality
- **Consistent typography** using modern font stacks
- **Clean, minimal interface** focusing on usability

### **User Experience**
- **Intuitive navigation** with logical grouping
- **Real-time notifications** with badge counters
- **Advanced search** across all modules
- **Bulk operations** for efficiency
- **Keyboard shortcuts** for power users

### **Accessibility**
- **WCAG 2.1 AA compliant** design
- **Screen reader friendly** markup
- **High contrast mode** support
- **Keyboard navigation** throughout

## 🔧 Development

### **Code Standards**
- **PSR-4 autoloading** for classes
- **Consistent naming conventions** throughout
- **Comprehensive error handling** with logging
- **Security-first development** approach
- **Clean, maintainable code** structure

### **Testing**
- **Unit tests** for core classes
- **Integration tests** for critical workflows
- **Security testing** for vulnerabilities
- **Performance testing** for optimization

### **Contributing**
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📈 Performance

### **Optimization Features**
- **Database query optimization** with proper indexing
- **Efficient pagination** for large datasets
- **Image optimization** with automatic resizing
- **CDN support** for static assets
- **Caching mechanisms** for frequently accessed data

### **System Requirements**
- **Minimum**: 1GB RAM, 2GB storage
- **Recommended**: 4GB RAM, 10GB storage
- **Concurrent users**: Up to 100 simultaneous users
- **Database size**: Optimized for millions of records

## 🔒 Security Compliance

### **Standards Compliance**
- **OWASP Top 10** protection
- **GDPR compliance** features
- **Data encryption** at rest and in transit
- **Regular security updates** and patches
- **Penetration testing** recommendations

### **Backup & Recovery**
- **Automated daily backups**
- **Point-in-time recovery**
- **Database replication** support
- **Disaster recovery** procedures

## 📞 Support

### **Documentation**
- **API Documentation** - Complete API reference
- **User Manual** - Step-by-step guides
- **Video Tutorials** - Visual learning materials
- **FAQ Section** - Common questions and answers

### **Getting Help**
- **Issue Tracker** - Report bugs and feature requests
- **Community Forum** - Ask questions and share knowledge
- **Email Support** - Direct support for critical issues
- **Professional Services** - Custom development and consulting

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Bootstrap** - Responsive CSS framework
- **Font Awesome** - Icon library
- **Chart.js** - Data visualization
- **TinyMCE** - Rich text editor
- **PHPMailer** - Email functionality
- **DataTables** - Advanced table features

---

**Digital Empowerment Network Admin Panel v2.0**  
*Empowering organizations to manage their digital transformation initiatives effectively.*

For more information, visit our [website](https://digital-empowerment.network) or contact us at admin@digital-empowerment.network.
