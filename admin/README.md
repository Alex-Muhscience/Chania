# Admin Panel

The administrative interface for the Digital Empowerment Network platform. This panel provides comprehensive tools for managing users, programs, events, content, and system operations.

## 🏗️ Architecture

### Directory Structure
```
admin/
├── actions/           # Form processing and API endpoints
├── backups/          # Database backup storage
├── classes/          # Business logic and data models
├── controllers/      # MVC controllers for each module
├── includes/         # Common includes and configuration
├── public/           # Public-facing admin pages
└── views/           # HTML templates and components
```

## 🔐 Authentication & Security

### Access Control
- **Role-based permissions** with granular control
- **Two-factor authentication (2FA)** support
- **Session management** with timeout controls
- **Brute force protection** with rate limiting

### Security Features
- **CSRF protection** on all forms
- **XSS prevention** with output sanitization
- **SQL injection protection** using prepared statements
- **File upload validation** with type restrictions
- **Admin activity logging** for audit trails

## 📊 Core Modules

### User Management (`users/`)
- Complete CRUD operations for user accounts
- Role assignment and permission management
- Bulk user operations and import/export
- User activity monitoring

### Program Management (`programs/`)
- Training program creation and management
- Category and skill level organization
- Enrollment tracking and capacity management
- Program analytics and reporting

### Event Management (`events/`)
- Event creation with rich details
- Registration management and tracking
- Calendar integration and scheduling
- Event analytics and attendance reports

### Content Management
- **Pages** - Dynamic page creation and editing
- **Blog** - News and article publishing
- **FAQs** - Frequently asked questions management
- **Testimonials** - User testimonial collection and display

### Communication Tools
- **Email Templates** - Rich text email template management
- **Email Campaigns** - Bulk email sending with scheduling
- **SMS Templates** - SMS message template system
- **Newsletter** - Subscriber management and campaigns

## 🛠️ Technical Components

### Classes (`classes/`)
Core business logic classes:
- `User.php` - User management and authentication
- `Program.php` - Program data and operations
- `Event.php` - Event management functionality
- `EmailTemplate.php` - Email template processing
- `Report.php` - Report generation and data export

### Controllers (`controllers/`)
MVC controllers handling request processing:
- `UserController.php` - User-related operations
- `ProgramController.php` - Program management
- `EventController.php` - Event operations
- `EmailTemplatesController.php` - Email template management

### Views (`views/`)
HTML templates organized by module:
- `users/` - User management templates
- `programs/` - Program management templates
- `events/` - Event management templates
- `shared/` - Reusable components and layouts

## 🎨 UI Framework

### Design System
- **SB Admin 2** - Bootstrap 4 admin template
- **Font Awesome** - Icon library
- **DataTables** - Enhanced table functionality
- **Chart.js** - Data visualization
- **TinyMCE** - Rich text editing

### Responsive Design
- Mobile-first responsive layout
- Touch-friendly interface elements
- Collapsible sidebar navigation
- Mobile-optimized data tables

## 📈 Dashboard Features

### Key Metrics
- Total users, programs, and events
- Application status breakdown
- Monthly growth statistics
- User engagement analytics

### Quick Actions
- Add new users, programs, or events
- Process pending applications
- Send bulk communications
- Generate custom reports

### Real-time Updates
- Live notification system
- Activity feed updates
- System status monitoring
- Performance metrics display

## 🔧 Configuration

### Core Settings (`includes/config.php`)
- Database connection parameters
- Application-wide constants
- Security configuration
- Feature toggles

### Environment Variables
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `BASE_URL`, `SITE_NAME`
- `ENABLE_2FA`, `SESSION_TIMEOUT`
- `MAX_LOGIN_ATTEMPTS`

## 🚀 Getting Started

### Prerequisites
- PHP 7.4+ with required extensions
- MySQL 5.7+ or MariaDB 10.2+
- Apache with mod_rewrite enabled
- Write permissions on uploads/ and backups/

### First-time Setup
1. Configure database connection in `includes/config.php`
2. Import database schema from `../database/schema.sql`
3. Access admin panel at `/admin/`
4. Login with default credentials (admin/admin123)
5. Change default password immediately
6. Configure system settings

### Default Admin Account
- **Username:** admin
- **Password:** admin123 (change immediately)
- **Role:** Administrator
- **Permissions:** Full system access (*)

## 📱 Mobile Support

The admin panel is fully responsive and optimized for mobile devices:
- Responsive navigation with touch-friendly controls
- Mobile-optimized data tables with horizontal scrolling
- Touch gestures for common actions
- Mobile-specific UI adjustments

## 🔍 Development Notes

### Coding Standards
- PSR-4 autoloading for classes
- Consistent naming conventions
- Comprehensive error handling
- Security-first development approach

### Database Integration
- PDO with prepared statements
- Connection pooling and optimization
- Automatic backup functionality
- Migration system support

### Performance Optimization
- Efficient pagination for large datasets
- Query optimization and indexing
- Image compression and optimization
- Caching mechanisms for frequently accessed data

## 🐛 Troubleshooting

### Common Issues
- **Database connection errors**: Check config.php credentials
- **Permission denied**: Verify file/folder permissions
- **Session timeout**: Adjust SESSION_TIMEOUT in config
- **Upload failures**: Check upload directory permissions

### Debug Mode
Enable debug mode by setting `DEBUG_MODE = true` in config.php to:
- Display detailed error messages
- Enable SQL query logging
- Show performance metrics
- Log additional debug information

## 📚 Further Reading

- [Installation Guide](../docs/INSTALLATION.md)
- [Admin User Manual](../docs/ADMIN_GUIDE.md)
- [Security Best Practices](../docs/SECURITY.md)
- [API Documentation](../docs/API.md)

---

**Admin Panel v2.0** - Built with ❤️ for Digital Empowerment Network
