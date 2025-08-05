# Database Schema & Scripts

This directory contains the database schema, migration scripts, and database-related documentation for the Digital Empowerment Network platform.

## 📊 Database Overview

The database is designed to support a comprehensive digital empowerment platform with:
- **User management** with role-based permissions
- **Program and event management** with applications and registrations
- **Content management** for dynamic pages and blog posts
- **Communication systems** for emails and newsletters
- **Administrative tools** with logging and reporting

## 🗂️ Directory Structure

```
database/
├── schema.sql            # Complete database schema
├── sample_data.sql       # Sample data for development
├── migrations/           # Database migration files
│   ├── 001_initial_schema.sql
│   ├── 002_add_user_roles.sql
│   └── ...
├── procedures/           # Stored procedures and functions
├── views/               # Database views
├── triggers/            # Database triggers
└── backups/             # Automated backup storage
```

## 📋 Database Schema

### Core Tables

#### Users & Authentication
- **`users`** - User accounts and profiles
- **`user_roles`** - User role definitions
- **`user_permissions`** - Permission assignments
- **`user_sessions`** - Active user sessions
- **`password_resets`** - Password reset tokens

#### Programs & Events
- **`programs`** - Training programs and courses
- **`program_categories`** - Program categorization
- **`events`** - Events and workshops
- **`event_registrations`** - Event registration tracking
- **`applications`** - Program applications

#### Content Management
- **`pages`** - Dynamic pages
- **`blog_posts`** - Blog articles and news
- **`faqs`** - Frequently asked questions
- **`testimonials`** - User testimonials
- **`media_library`** - File and media management

#### Communication
- **`email_templates`** - Email template storage
- **`email_campaigns`** - Email campaign management
- **`newsletter_subscribers`** - Newsletter subscription management
- **`contacts`** - Contact form submissions
- **`notifications`** - System notifications

#### System & Logging
- **`admin_logs`** - Administrative activity logs
- **`security_logs`** - Security event logging
- **`system_settings`** - Application configuration
- **`reports`** - Generated reports storage

## 🔧 Setup Instructions

### Initial Setup

1. **Create Database**
   ```sql
   CREATE DATABASE chania_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import Schema**
   ```bash
   mysql -u username -p chania_db < database/schema.sql
   ```

3. **Load Sample Data** (Optional for development)
   ```bash
   mysql -u username -p chania_db < database/sample_data.sql
   ```

### Database User Setup

```sql
-- Create dedicated database user
CREATE USER 'chania_user'@'localhost' IDENTIFIED BY 'secure_password';

-- Grant necessary permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON chania_db.* TO 'chania_user'@'localhost';

-- For admin operations (migrations, backups)
GRANT CREATE, ALTER, DROP, INDEX ON chania_db.* TO 'chania_admin'@'localhost';

FLUSH PRIVILEGES;
```

## 📈 Database Design Principles

### Normalization
- **Third Normal Form (3NF)** compliance for most tables
- **Balanced approach** between normalization and performance
- **Strategic denormalization** for reporting and analytics tables

### Indexing Strategy
- **Primary keys** on all tables with auto-increment IDs
- **Foreign key indexes** for relationship constraints
- **Composite indexes** for common query patterns
- **Full-text indexes** for search functionality

### Data Integrity
- **Foreign key constraints** to maintain referential integrity
- **Check constraints** for data validation
- **NOT NULL constraints** for required fields
- **Unique constraints** for business rules

## 🔐 Security Considerations

### Access Control
- **Principle of least privilege** for database users
- **Separate users** for different application components
- **Read-only users** for reporting and analytics
- **Admin users** restricted to specific operations

### Data Protection
- **Sensitive data encryption** for passwords and personal information
- **Audit trails** for all data modifications
- **Soft deletes** for important records to prevent data loss
- **Data retention policies** for compliance

## 📊 Performance Optimization

### Query Optimization
- **Proper indexing** for frequently queried columns
- **Query analysis** using EXPLAIN for slow queries
- **Composite indexes** for multi-column searches
- **Covering indexes** for read-heavy operations

### Table Optimization
- **Appropriate data types** for storage efficiency
- **Table partitioning** for large tables (future consideration)
- **Archive tables** for historical data
- **Regular maintenance** with OPTIMIZE TABLE

## 🔄 Migration System

### Migration Files
Located in `migrations/` directory with naming convention:
- `001_initial_schema.sql` - Initial database schema
- `002_add_user_roles.sql` - Add role-based permissions
- `003_create_events_table.sql` - Event management tables
- `004_add_email_templates.sql` - Email template system

### Migration Process
1. **Version tracking** in `schema_migrations` table
2. **Sequential execution** based on version numbers
3. **Rollback capability** for reversible migrations
4. **Backup creation** before major migrations

### Running Migrations
```bash
# Run pending migrations
php scripts/migrate.php

# Rollback last migration
php scripts/migrate.php --rollback

# Reset database (development only)
php scripts/migrate.php --reset
```

## 💾 Backup & Recovery

### Automated Backups
- **Daily backups** using mysqldump
- **Compressed storage** to save disk space
- **Retention policy** (30 days for daily, 12 months for monthly)
- **Backup verification** to ensure integrity

### Backup Script Example
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/path/to/backups"
DB_NAME="chania_db"

mysqldump -u backup_user -p$DB_PASS \
  --single-transaction \
  --routines \
  --triggers \
  $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz
```

### Recovery Procedures
1. **Stop application** to prevent new data
2. **Restore from backup** using mysql command
3. **Verify data integrity** after restoration
4. **Update configuration** if necessary
5. **Restart application** and monitor

## 📋 Maintenance Tasks

### Regular Maintenance
- **Weekly OPTIMIZE TABLE** for heavily modified tables
- **Monthly ANALYZE TABLE** to update statistics
- **Quarterly index analysis** and optimization
- **Annual schema review** for improvements

### Monitoring
- **Slow query log** analysis
- **Table size monitoring** for growth trends
- **Index usage statistics** for optimization
- **Connection monitoring** for performance

## 🧪 Development Tools

### Database Seeding
```bash
# Seed development data
php database/seeders/DatabaseSeeder.php

# Seed specific tables
php database/seeders/UserSeeder.php
php database/seeders/ProgramSeeder.php
```

### Schema Changes
1. **Create migration file** with descriptive name
2. **Test on development** environment first
3. **Review with team** before production
4. **Create rollback plan** for major changes

## 📚 Documentation

### Entity Relationship Diagram (ERD)
- **Visual representation** of table relationships
- **Updated with schema changes** for accuracy
- **Available in docs/** directory as PDF/PNG

### Data Dictionary
- **Complete field documentation** in `docs/data-dictionary.md`
- **Business rules** and constraints documented
- **Sample data** and valid values listed

## 🔍 Troubleshooting

### Common Issues

#### Connection Problems
- **Check credentials** in configuration files
- **Verify database server** is running
- **Test network connectivity** to database host
- **Review firewall settings** for database port

#### Performance Issues
- **Analyze slow queries** using slow query log
- **Check index usage** with EXPLAIN statements
- **Monitor table locks** and blocking queries
- **Review connection pool** settings

#### Data Integrity Issues
- **Check foreign key constraints** for violations
- **Verify data types** match application expectations
- **Review recent migrations** for potential issues
- **Analyze error logs** for constraint violations

### Debug Mode
Enable database debugging by setting in configuration:
```php
define('DB_DEBUG', true);
```

This enables:
- Query logging to debug log file
- Execution time tracking
- Error message details
- Connection statistics

---

**Database Schema v2.0** - Robust, scalable data foundation for the Digital Empowerment Network platform
