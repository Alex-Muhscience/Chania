# Shared Components

This directory contains shared classes, utilities, and core functionality used across both the admin panel and client frontend. These components provide common functionality to avoid code duplication and ensure consistency.

## 🏗️ Architecture

### Directory Structure
```
shared/
├── Core/                 # Core system classes
│   ├── Database.php      # Database connection and utilities
│   ├── User.php          # User management and authentication
│   ├── Utilities.php     # Common utility functions
│   ├── AdminLogger.php   # Activity logging system
│   ├── EmailTemplate.php # Email template processing
│   ├── Page.php          # Dynamic page management
│   └── Security/         # Security-related classes
│       ├── Auth.php      # Authentication handler
│       ├── CSRF.php      # CSRF protection
│       └── RateLimit.php # Rate limiting functionality
├── Models/               # Data models (if using ORM)
├── Services/             # Business logic services
└── Helpers/              # Helper functions and utilities
```

## 🔧 Core Classes

### Database.php
**Purpose**: Centralized database connection and query utilities
**Features**:
- PDO connection management with connection pooling
- Query builder for common operations
- Transaction management
- Error handling and logging
- Connection retry logic

```php
// Example usage
$db = new Database();
$users = $db->select('users', ['status' => 'active']);
```

### User.php
**Purpose**: User management and authentication across the platform
**Features**:
- User registration and login
- Password hashing and verification
- Role and permission management
- Session handling
- Two-factor authentication support

```php
// Example usage
$user = new User();
$authenticated = $user->login($email, $password);
```

### Utilities.php
**Purpose**: Common utility functions used throughout the application
**Features**:
- Input sanitization and validation
- Date/time formatting
- File upload handling
- String manipulation functions
- Array and object utilities

```php
// Example usage
$cleanInput = Utilities::sanitizeInput($_POST['data']);
$formattedDate = Utilities::formatDate($timestamp, 'Y-m-d H:i:s');
```

### AdminLogger.php
**Purpose**: Comprehensive logging system for admin activities
**Features**:
- Activity logging with user context
- Security event logging
- Error and exception logging
- Log rotation and archival
- Configurable log levels

```php
// Example usage
AdminLogger::logActivity($userId, 'user_created', 'Created new user: ' . $newUserId);
AdminLogger::logSecurity('failed_login', $ipAddress, $attemptedEmail);
```

### EmailTemplate.php
**Purpose**: Email template management and sending
**Features**:
- Template processing with variables
- HTML and plain text support
- SMTP configuration management
- Email queue management
- Bounce handling and tracking

```php
// Example usage
$emailTemplate = new EmailTemplate();
$emailTemplate->send('welcome', $userEmail, ['name' => $userName]);
```

## 🔐 Security Components

### Auth.php
**Purpose**: Centralized authentication handling
**Features**:
- JWT token management
- Session validation
- Role-based access control
- Multi-factor authentication
- Single sign-on (SSO) support

### CSRF.php
**Purpose**: Cross-Site Request Forgery protection
**Features**:
- Token generation and validation
- Form protection middleware
- AJAX request protection
- Token refresh functionality

### RateLimit.php
**Purpose**: Request rate limiting and abuse prevention
**Features**:
- IP-based rate limiting
- User-based rate limiting
- Configurable limits per endpoint
- Redis/Memory cache integration
- Blocked IP management

## 📊 Models & Services

### Models Directory
Contains data models for ORM integration:
- **User.php** - User data model
- **Program.php** - Training program model
- **Event.php** - Event data model
- **Application.php** - Application data model

### Services Directory
Business logic services:
- **NotificationService.php** - Notification management
- **PaymentService.php** - Payment processing
- **FileService.php** - File management
- **ReportService.php** - Report generation

## 🛠️ Configuration

### Environment Variables
Shared configuration through environment variables:
```php
// Database configuration
DB_HOST=localhost
DB_NAME=chania_db
DB_USER=username
DB_PASS=password

// Email configuration
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=noreply@example.com
SMTP_PASS=password

// Security settings
JWT_SECRET=your-jwt-secret
SESSION_TIMEOUT=7200
ENABLE_2FA=true
```

### Config Files
- **database.php** - Database connection settings
- **mail.php** - Email configuration
- **security.php** - Security-related settings
- **app.php** - Application-wide settings

## 🔌 Integration

### Admin Panel Integration
The shared components integrate seamlessly with the admin panel:
- User authentication and session management
- Activity logging for all admin actions
- Email notifications for system events
- Database operations with proper logging

### Client Frontend Integration
Frontend pages utilize shared components for:
- User registration and login
- Contact form processing
- Newsletter subscription management
- Dynamic page content loading

## 📈 Performance Optimization

### Caching
- **Query result caching** for frequently accessed data
- **Template caching** for improved rendering performance
- **Session caching** using Redis or Memcached
- **File caching** for static content

### Database Optimization
- **Connection pooling** for efficient database usage
- **Query optimization** with proper indexing
- **Prepared statement caching** for repeated queries
- **Transaction batching** for bulk operations

## 🐛 Error Handling

### Exception Management
- **Custom exception classes** for different error types
- **Global exception handler** with proper logging
- **User-friendly error messages** without exposing system details
- **Error reporting** to administrators

### Logging System
- **Multi-level logging** (DEBUG, INFO, WARN, ERROR, FATAL)
- **Contextual logging** with user and request information
- **Log rotation** to prevent disk space issues
- **Log aggregation** for analysis and monitoring

## 🧪 Testing

### Unit Tests
Located in `tests/Unit/`:
- Database connection tests
- User authentication tests
- Utility function tests
- Security component tests

### Integration Tests
Located in `tests/Integration/`:
- Email sending tests
- File upload tests
- API endpoint tests
- Database transaction tests

## 📚 Usage Guidelines

### Best Practices
1. **Always use shared components** instead of duplicating code
2. **Handle exceptions properly** with appropriate logging
3. **Validate input data** using Utilities::sanitizeInput()
4. **Log significant actions** using AdminLogger
5. **Use consistent error handling** patterns

### Code Examples

#### Database Operations
```php
$db = new Database();

// Simple select
$users = $db->select('users', ['status' => 'active']);

// Complex query with joins
$result = $db->query("
    SELECT u.*, p.name as program_name 
    FROM users u 
    LEFT JOIN programs p ON u.program_id = p.id 
    WHERE u.created_at > ?
", [$dateFilter]);
```

#### User Authentication
```php
$user = new User();

// Register new user
$userId = $user->register($email, $password, $userData);

// Login user
if ($user->login($email, $password)) {
    // Redirect to dashboard
} else {
    // Show error message
}

// Check permissions
if ($user->hasPermission('users.edit')) {
    // Allow editing
}
```

#### Email Sending
```php
$email = new EmailTemplate();

// Send welcome email
$email->send('welcome', $userEmail, [
    'name' => $userName,
    'activation_link' => $activationUrl
]);

// Send bulk email
$email->sendBulk('newsletter', $subscriberEmails, [
    'subject' => 'Monthly Newsletter',
    'content' => $newsletterContent
]);
```

## 🔍 Troubleshooting

### Common Issues
- **Database connection errors**: Check connection parameters in config
- **Email sending failures**: Verify SMTP settings and credentials
- **Permission denied errors**: Check file permissions and ownership
- **Session issues**: Verify session configuration and storage

### Debug Mode
Enable debug mode in configuration to:
- Display detailed error messages
- Enable query logging
- Show performance metrics
- Log additional debug information

---

**Shared Components v2.0** - Providing robust, reusable functionality for the Digital Empowerment Network platform
