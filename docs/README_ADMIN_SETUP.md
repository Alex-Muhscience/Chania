# Chania Admin Panel Setup

This document provides instructions for setting up the Chania Skills for Africa admin panel.

## Files Created/Updated

### 1. New Admin Files Created:
- **`admin/public/contacts.php`** - Complete contact messages management with search, filter, bulk actions, and modal view
- **`admin/public/program_category_add.php`** - Add new program categories with preview and validation
- **`admin/public/program_category_edit.php`** - Edit existing program categories
- **`database/program_categories_table.sql`** - Database schema for program categories

### 2. Updated Files:
- **`admin/public/program_categories.php`** - Fixed category-program relationship query
- **`admin/includes/header.php`** - Added Program Categories navigation link
- **`shared/Core/Utilities.php`** - Added `generateSlug()` method

## Database Setup

1. Run the SQL script to create the program_categories table:
   ```sql
   -- Execute the contents of database/program_categories_table.sql
   ```

2. The script will create the `program_categories` table with sample data including:
   - Technology
   - Business
   - Health
   - Education
   - Arts & Culture
   - Agriculture

## Features Implemented

### Contact Messages Management (`contacts.php`)
- **Statistics Dashboard** -Total, unread, and read message counts
- **Advanced Search** - Search across name, email, subject, and message content
- **Status Filtering** - Filter by read/unread status
- **Bulk Actions** - Mark multiple contacts as read/unread or delete
- **Modal View** - View full contact details in popup
- **Auto-mark Read** - Automatically mark messages as read when viewed
- **Pagination** - Efficient handling of large contact lists
- **Export Ready** - Link to export contacts (requires export.php implementation)

### Program Categories Management
- **Category Listing** (`program_categories.php`) - View all categories with search and pagination
- **Add Categories** (`program_category_add.php`) - Create new categories with:
  - Name, slug, description
  - Parent category selection (for subcategories)
  - Icon and color customization
  - Sort order and status settings
  - SEO meta fields
  - Live preview of category appearance
- **Edit Categories** (`program_category_edit.php`) - Update existing categories
- **Category Features**:
  - Hierarchical structure (parent/child categories)
  - FontAwesome icon support
  - Custom color themes
  - SEO optimization
  - Active/inactive status
  - Featured category marking
  - Soft delete functionality

## Admin Panel Navigation

The navigation now includes:
- **Dashboard** - Main admin dashboard
- **User Management** - Users with new user notifications
- **Programs & Applications**:
  - Programs
  - **Program Categories** (NEW)
  - Applications with pending notifications
- **Events & Communication**:
  - Events
  - Event Registrations
  - **Contacts** (UPDATED) - with unread message notifications
  - Newsletter
- **Content Management** - Testimonials, Files
- **System** - Settings, System Monitor, Activity Logs

## Security Features

- **Authentication Required** - All admin pages require login
- **CSRF Protection** - Forms protected against cross-site request forgery
- **Input Validation** - Server-side validation for all user inputs
- **SQL Injection Prevention** - Prepared statements for all database queries
- **XSS Protection** - HTML encoding of user output
- **Soft Deletes** - Data preservation with recovery capability

## Responsive Design

- **Mobile Friendly** - Responsive design works on all devices
- **Bootstrap 4** - Professional admin theme
- **FontAwesome Icons** - Consistent iconography
- **Modern UI** - Clean, professional interface
- **Interactive Elements** - Modals, tooltips, and animations

## Usage Instructions

### Managing Contacts
1. Navigate to **Contacts** in the sidebar
2. Use the search bar to find specific messages
3. Filter by status (All, Unread, Read)
4. Click the eye icon to view full message details
5. Use bulk actions for multiple operations
6. Export data using the Export button

### Managing Program Categories
1. Navigate to **Program Categories** in the sidebar
2. Click **Add Category** to create new categories
3. Fill in required fields (Name, Description)
4. Customize icon, color, and other settings
5. Use the preview panel to see how it will appear
6. Save the category
7. Edit existing categories by clicking the edit icon
8. Toggle status or delete categories as needed

## Next Steps

1. **Database Migration** - Run the SQL script to create the program_categories table
2. **Test Functionality** - Verify all features work correctly
3. **Data Migration** - Import existing contact and category data if needed
4. **User Training** - Train admin users on new features
5. **Backup Strategy** - Implement regular database backups

## Troubleshooting

### Common Issues:
1. **Database Connection** - Ensure database credentials in config.php are correct
2. **Permission Errors** - Check file permissions for uploads and logs directories
3. **Session Issues** - Verify session configuration in PHP
4. **Missing Dependencies** - Ensure all required PHP extensions are installed

### File Permissions:
```bash
chmod 755 admin/public/
chmod 644 admin/public/*.php
chmod 755 database/
chmod 644 database/*.sql
```

## Support

For technical support or questions about the admin panel implementation, refer to the inline code comments or contact the development team.
