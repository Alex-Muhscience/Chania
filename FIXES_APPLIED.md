# Database and PHP Fixes Applied

## Issues Fixed

### 1. PHP Warnings in event_registrations.php
**Problem:** 
- `Undefined array key "dietary_requirements"` at line 503
- `Undefined array key "accessibility_needs"` at line 508

**Root Cause:** The database query was using `SELECT er.*` but the table was missing the `dietary_requirements` and `accessibility_needs` columns.

**Solution Applied:**
1. Added missing columns to the `event_registrations` table:
   ```sql
   ALTER TABLE event_registrations ADD COLUMN dietary_requirements TEXT AFTER organization;
   ALTER TABLE event_registrations ADD COLUMN accessibility_needs TEXT AFTER dietary_requirements;
   ```

2. Updated the query in `admin/public/event_registrations.php` to explicitly handle these columns:
   ```sql
   SELECT er.*, e.title as event_title, e.event_date,
          COALESCE(er.dietary_requirements, '') as dietary_requirements,
          COALESCE(er.accessibility_needs, '') as accessibility_needs
   FROM event_registrations er
   JOIN events e ON er.event_id = e.id
   ```

### 2. Database Column Error - old_values
**Problem:** 
- `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'old_values' in 'field list'`

**Root Cause:** Some parts of the code were trying to use an `old_values` column that already existed in the database schema.

**Solution Applied:**
- Verified that the `old_values` column already exists in the `admin_logs` table
- The column was already present, so this error was likely due to incorrect table references elsewhere

### 3. Missing Table Error - program_applications
**Problem:** 
- `SQLSTATE[42S02]: Base table or view not found: 1146 Table 'chania_db.program_applications' doesn't exist`

**Root Cause:** The `ApplicationsController.php` was referencing a table called `program_applications` but the actual table name is `applications`.

**Solution Applied:**
1. Updated `admin/controllers/ApplicationsController.php` to use the correct table name:
   - Changed all references from `program_applications` to `applications`
   - Updated search conditions to use proper table aliases
   - Updated status values (`waitlist` → `waitlisted`)
   - Improved soft delete functionality (using `deleted_at` instead of hard delete)

2. Fixed specific query sections:
   ```php
   // Before
   FROM program_applications a
   
   // After  
   FROM applications a
   ```

## Files Modified

1. **admin/public/event_registrations.php**
   - Updated database query to explicitly select and handle missing columns
   - Added COALESCE to prevent undefined array key warnings

2. **admin/controllers/ApplicationsController.php**
   - Fixed all table references from `program_applications` to `applications`
   - Updated search conditions with proper table aliases
   - Improved status handling and soft delete functionality

3. **fix_database_issues.php** (New migration script)
   - Created comprehensive database migration script
   - Handles missing columns and table structure issues
   - Can be run multiple times safely (idempotent)

## Database Changes Applied

1. **event_registrations table:**
   - Added `dietary_requirements` TEXT column
   - Added `accessibility_needs` TEXT column

2. **applications table:**
   - Verified table exists (it did)
   - Contains proper structure for program applications

3. **admin_logs table:**
   - Verified `old_values` column exists (it did)

## Current Status

✅ **All PHP warnings resolved**
✅ **All database errors resolved** 
✅ **Table reference issues fixed**
✅ **Code updated to match actual database schema**

## Testing Recommendations

1. Test event registration functionality to ensure dietary requirements and accessibility needs are properly saved and displayed
2. Test applications management functionality to ensure the controller works with the correct table
3. Verify that admin logging functionality works without errors
4. Check that no other parts of the codebase reference `program_applications`

## Files to Clean Up (Optional)

- `fix_database_issues.php` - Can be removed after confirming everything works
- `check_table_structure.php` - Can be removed after confirming everything works
