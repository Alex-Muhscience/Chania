-- ==============================================
-- CHANIA SKILLS FOR AFRICA - HOSTAFRICA DEPLOYMENT
-- Database Setup Script for Production Environment
-- ==============================================

-- ===============================
-- 1. CREATE DATABASE
-- ===============================
-- Run this first in phpMyAdmin or via hosting control panel
CREATE DATABASE IF NOT EXISTS chania_skills_africa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE chania_skills_africa;

-- Enable strict mode for better data integrity
SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

-- ===============================
-- 2. CORE TABLES
-- ===============================

-- Users table with enhanced security features
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    bio TEXT,
    role ENUM('admin', 'editor', 'moderator') DEFAULT 'editor',
    status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255),
    profile_image VARCHAR(255),
    last_login TIMESTAMP NULL,
    last_activity TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    password_changed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_users_email (email),
    INDEX idx_users_username (username),
    INDEX idx_users_status (status),
    INDEX idx_users_role (role),
    INDEX idx_users_uuid (uuid),
    INDEX idx_users_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Programs table with enhanced fields
CREATE TABLE programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    short_description VARCHAR(500) NOT NULL,
    category VARCHAR(100) NOT NULL,
    subcategory VARCHAR(100),
    duration VARCHAR(50) NOT NULL,
    duration_type ENUM('hours', 'days', 'weeks', 'months') DEFAULT 'weeks',
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    requirements TEXT,
    benefits TEXT,
    curriculum TEXT,
    prerequisites TEXT,
    certification_available BOOLEAN DEFAULT FALSE,
    fee DECIMAL(10, 2) DEFAULT 0.00,
    max_participants INT UNSIGNED,
    min_participants INT UNSIGNED DEFAULT 1,
    start_date DATE,
    end_date DATE,
    registration_deadline DATE,
    image_path VARCHAR(255),
    gallery_images JSON,
    video_url VARCHAR(255),
    brochure_path VARCHAR(255),
    instructor_name VARCHAR(100),
    instructor_bio TEXT,
    instructor_image VARCHAR(255),
    location VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    is_online BOOLEAN DEFAULT FALSE,
    tags JSON,
    meta_title VARCHAR(255),
    meta_description VARCHAR(500),
    view_count INT UNSIGNED DEFAULT 0,
    application_count INT UNSIGNED DEFAULT 0,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_programs_slug (slug),
    INDEX idx_programs_category (category),
    INDEX idx_programs_featured (is_featured),
    INDEX idx_programs_active (is_active),
    INDEX idx_programs_start_date (start_date),
    INDEX idx_programs_created_by (created_by),
    INDEX idx_programs_deleted_at (deleted_at),
    FULLTEXT idx_programs_search (title, description, short_description),

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table with enhanced features
CREATE TABLE events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    short_description VARCHAR(500) NOT NULL,
    event_type ENUM('workshop', 'seminar', 'conference', 'networking', 'training', 'other') DEFAULT 'workshop',
    event_date DATETIME NOT NULL,
    end_date DATETIME,
    location VARCHAR(255) NOT NULL,
    venue_details TEXT,
    max_attendees INT UNSIGNED,
    current_attendees INT UNSIGNED DEFAULT 0,
    registration_fee DECIMAL(10, 2) DEFAULT 0.00,
    registration_deadline DATETIME,
    image_path VARCHAR(255),
    gallery_images JSON,
    speaker_name VARCHAR(100),
    speaker_bio TEXT,
    speaker_image VARCHAR(255),
    agenda TEXT,
    requirements TEXT,
    contact_info VARCHAR(255),
    external_link VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    is_virtual BOOLEAN DEFAULT FALSE,
    meeting_link VARCHAR(255),
    tags JSON,
    meta_title VARCHAR(255),
    meta_description VARCHAR(500),
    view_count INT UNSIGNED DEFAULT 0,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_events_slug (slug),
    INDEX idx_events_date (event_date),
    INDEX idx_events_type (event_type),
    INDEX idx_events_featured (is_featured),
    INDEX idx_events_active (is_active),
    INDEX idx_events_created_by (created_by),
    INDEX idx_events_deleted_at (deleted_at),
    FULLTEXT idx_events_search (title, description, short_description),

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Applications table with enhanced tracking
CREATE TABLE applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    program_id INT UNSIGNED NOT NULL,
    application_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say'),
    nationality VARCHAR(100),
    id_number VARCHAR(50),
    address TEXT NOT NULL,
    city VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Kenya',
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relationship VARCHAR(50),
    education_level ENUM('primary', 'secondary', 'diploma', 'degree', 'masters', 'phd', 'other'),
    education_details TEXT NOT NULL,
    work_experience TEXT,
    skills TEXT,
    motivation TEXT NOT NULL,
    expectations TEXT,
    how_did_you_hear ENUM('website', 'social_media', 'friend', 'advertisement', 'event', 'other'),
    special_needs TEXT,
    documents JSON,
    entity ENUM('individual', 'organization') DEFAULT 'individual',
    organization_name VARCHAR(255),
    organization_type VARCHAR(100),
    organization_size VARCHAR(50),
    organization_contact_person VARCHAR(100),
    number_of_participants INT DEFAULT 1,
    status ENUM('pending', 'under_review', 'approved', 'rejected', 'withdrawn', 'waitlisted') DEFAULT 'pending',
    status_reason TEXT,
    reviewed_by INT UNSIGNED,
    reviewed_at TIMESTAMP NULL,
    priority ENUM('low', 'normal', 'high') DEFAULT 'normal',
    notes TEXT,
    interview_scheduled BOOLEAN DEFAULT FALSE,
    interview_date DATETIME NULL,
    interview_notes TEXT,
    payment_status ENUM('pending', 'paid', 'partial', 'refunded') DEFAULT 'pending',
    payment_amount DECIMAL(10, 2) DEFAULT 0.00,
    payment_reference VARCHAR(100),
    confirmation_sent BOOLEAN DEFAULT FALSE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_applications_program (program_id),
    INDEX idx_applications_email (email),
    INDEX idx_applications_status (status),
    INDEX idx_applications_number (application_number),
    INDEX idx_applications_reviewed_by (reviewed_by),
    INDEX idx_applications_submitted_date (submitted_at),
    INDEX idx_applications_entity (entity),
    INDEX idx_applications_deleted_at (deleted_at),
    FULLTEXT idx_applications_search (first_name, last_name, email, organization_name),

    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testimonials table with enhanced features
CREATE TABLE testimonials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    author_name VARCHAR(100) NOT NULL,
    author_title VARCHAR(100) NOT NULL,
    author_company VARCHAR(100),
    content TEXT NOT NULL,
    rating INT UNSIGNED DEFAULT 5,
    program_id INT UNSIGNED,
    image_path VARCHAR(255),
    video_url VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_testimonials_featured (is_featured),
    INDEX idx_testimonials_approved (is_approved),
    INDEX idx_testimonials_active (is_active),
    INDEX idx_testimonials_program (program_id),
    INDEX idx_testimonials_order (display_order),
    INDEX idx_testimonials_created_by (created_by),
    INDEX idx_testimonials_deleted_at (deleted_at),

    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact messages table with enhanced tracking
CREATE TABLE contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    category ENUM('general', 'programs', 'events', 'partnerships', 'support', 'other') DEFAULT 'general',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('new', 'in_progress', 'resolved', 'closed') DEFAULT 'new',
    assigned_to INT UNSIGNED,
    response TEXT,
    responded_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    spam_score DECIMAL(3, 2) DEFAULT 0.00,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_contacts_email (email),
    INDEX idx_contacts_status (status),
    INDEX idx_contacts_category (category),
    INDEX idx_contacts_priority (priority),
    INDEX idx_contacts_assigned_to (assigned_to),
    INDEX idx_contacts_submitted_date (submitted_at),
    INDEX idx_contacts_deleted_at (deleted_at),
    FULLTEXT idx_contacts_search (name, email, subject, message),

    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Team members table
CREATE TABLE team_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100),
    photo VARCHAR(255) NOT NULL,
    bio TEXT NOT NULL,
    qualifications TEXT,
    experience_years INT UNSIGNED,
    specialties JSON,
    email VARCHAR(100),
    phone VARCHAR(20),
    linkedin VARCHAR(255),
    twitter VARCHAR(255),
    facebook VARCHAR(255),
    instagram VARCHAR(255),
    personal_website VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    position_order INT DEFAULT 0,
    hire_date DATE,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_team_featured (is_featured),
    INDEX idx_team_active (is_active),
    INDEX idx_team_department (department),
    INDEX idx_team_order (position_order),
    INDEX idx_team_created_by (created_by),
    INDEX idx_team_deleted_at (deleted_at),

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Partners table
CREATE TABLE partners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    logo_path VARCHAR(255) NOT NULL,
    website_url VARCHAR(255),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    contact_person VARCHAR(100),
    partnership_type ENUM('funding', 'training', 'employment', 'resource', 'technology', 'other') DEFAULT 'other',
    partnership_level ENUM('strategic', 'standard', 'supporter') DEFAULT 'standard',
    start_date DATE,
    end_date DATE,
    is_featured BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_partners_slug (slug),
    INDEX idx_partners_featured (is_featured),
    INDEX idx_partners_active (is_active),
    INDEX idx_partners_type (partnership_type),
    INDEX idx_partners_level (partnership_level),
    INDEX idx_partners_order (display_order),
    INDEX idx_partners_created_by (created_by),
    INDEX idx_partners_deleted_at (deleted_at),

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- 3. ADMIN & MANAGEMENT TABLES
-- ===============================

-- Admin activity logs
CREATE TABLE admin_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT UNSIGNED,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(128),
    severity ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_admin_logs_user (user_id),
    INDEX idx_admin_logs_action (action),
    INDEX idx_admin_logs_entity (entity_type, entity_id),
    INDEX idx_admin_logs_created_at (created_at),
    INDEX idx_admin_logs_severity (severity),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System settings table
CREATE TABLE system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json', 'text') DEFAULT 'string',
    is_public BOOLEAN DEFAULT FALSE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_settings_key (setting_key),
    INDEX idx_settings_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site settings table (specific to site customization)
CREATE TABLE site_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json', 'text', 'image', 'url') DEFAULT 'string',
    category VARCHAR(50) DEFAULT 'general',
    is_public BOOLEAN DEFAULT TRUE,
    display_name VARCHAR(255),
    description TEXT,
    validation_rules TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_site_settings_key (setting_key),
    INDEX idx_site_settings_category (category),
    INDEX idx_site_settings_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- 4. NEWSLETTER & COMMUNICATION TABLES
-- ===============================

-- Newsletter subscribers
CREATE TABLE newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    email VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100),
    status ENUM('subscribed', 'unsubscribed', 'bounced', 'complained') DEFAULT 'subscribed',
    interests JSON,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    verification_token VARCHAR(255),
    verified_at TIMESTAMP NULL,

    INDEX idx_newsletter_email (email),
    INDEX idx_newsletter_status (status),
    INDEX idx_newsletter_subscribed_at (subscribed_at),
    INDEX idx_newsletter_verification (verification_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- FAQs table
CREATE TABLE faqs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'general',
    priority INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    view_count INT UNSIGNED DEFAULT 0,
    helpful_yes INT UNSIGNED DEFAULT 0,
    helpful_no INT UNSIGNED DEFAULT 0,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_faqs_category (category),
    INDEX idx_faqs_featured (is_featured),
    INDEX idx_faqs_active (is_active),
    INDEX idx_faqs_priority (priority),
    INDEX idx_faqs_created_by (created_by),
    INDEX idx_faqs_deleted_at (deleted_at),
    FULLTEXT idx_faqs_search (question, answer),

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- 5. SECURITY & SESSION TABLES
-- ===============================

-- Password reset tokens
CREATE TABLE password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_password_resets_token (token),
    INDEX idx_password_resets_user (user_id),
    INDEX idx_password_resets_expires (expires_at),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email verification tokens
CREATE TABLE email_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    verified_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_email_verifications_token (token),
    INDEX idx_email_verifications_user (user_id),
    INDEX idx_email_verifications_expires (expires_at),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT UNSIGNED,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_last_activity (last_activity),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- 6. FILE MANAGEMENT TABLE
-- ===============================

-- File uploads table
CREATE TABLE file_uploads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_type ENUM('image', 'document', 'video', 'audio', 'other') NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT UNSIGNED,
    uploaded_by INT UNSIGNED,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_uploads_entity (entity_type, entity_id),
    INDEX idx_uploads_uploaded_by (uploaded_by),
    INDEX idx_uploads_type (file_type),
    INDEX idx_uploads_deleted_at (deleted_at),

    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event registrations table
CREATE TABLE event_registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    event_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    organization VARCHAR(100),
    position VARCHAR(100),
    dietary_requirements TEXT,
    accessibility_needs TEXT,
    status ENUM('registered', 'confirmed', 'cancelled', 'attended', 'no_show') DEFAULT 'registered',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    payment_amount DECIMAL(10, 2) DEFAULT 0.00,
    payment_reference VARCHAR(100),
    confirmation_sent BOOLEAN DEFAULT FALSE,
    reminder_sent BOOLEAN DEFAULT FALSE,
    feedback_rating INT UNSIGNED,
    feedback_comment TEXT,
    ip_address VARCHAR(45),
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_event_registrations_event (event_id),
    INDEX idx_event_registrations_email (email),
    INDEX idx_event_registrations_status (status),
    INDEX idx_event_registrations_registered_at (registered_at),

    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- 7. INSERT DEFAULT DATA
-- ===============================

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, is_public, description) VALUES
('site_name', 'Chania Skills for Africa', 'string', TRUE, 'Website name'),
('site_description', 'Empowering communities through skills development', 'string', TRUE, 'Website description'),
('contact_email', 'info@skillsforafrica.org', 'string', TRUE, 'Main contact email'),
('contact_phone', '+254 123 456 789', 'string', TRUE, 'Main contact phone'),
('office_address', 'Chania, Thika, Kenya', 'string', TRUE, 'Office address'),
('max_file_size', '10485760', 'number', FALSE, 'Maximum file upload size in bytes'),
('allowed_file_types', '["jpg", "jpeg", "png", "gif", "pdf", "doc", "docx"]', 'json', FALSE, 'Allowed file extensions'),
('email_notifications', 'true', 'boolean', FALSE, 'Enable email notifications'),
('maintenance_mode', 'false', 'boolean', FALSE, 'Enable maintenance mode'),
('google_analytics_id', '', 'string', FALSE, 'Google Analytics tracking ID'),
('facebook_url', '', 'string', TRUE, 'Facebook page URL'),
('twitter_url', '', 'string', TRUE, 'Twitter profile URL'),
('linkedin_url', '', 'string', TRUE, 'LinkedIn profile URL'),
('instagram_url', '', 'string', TRUE, 'Instagram profile URL');

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, category, is_public, display_name, description) VALUES
('hero_title', 'Empowering Skills for Africa', 'string', 'homepage', TRUE, 'Hero Section Title', 'Main headline for the homepage hero section'),
('hero_subtitle', 'Transform your career with our comprehensive training programs', 'string', 'homepage', TRUE, 'Hero Section Subtitle', 'Subtitle text for the homepage hero section'),
('about_excerpt', 'We are committed to empowering individuals and communities across Africa through skills development and training programs that create sustainable opportunities.', 'text', 'about', TRUE, 'About Us Excerpt', 'Short description about the organization'),
('default_logo', 'assets/images/logo.png', 'image', 'branding', TRUE, 'Website Logo', 'Main website logo'),
('default_favicon', 'assets/images/favicon.ico', 'image', 'branding', TRUE, 'Website Favicon', 'Website favicon'),
('hero_background_url', 'assets/images/hero-bg.jpg', 'image', 'homepage', TRUE, 'Hero Background Image', 'Background image for hero section'),
('phone_primary', '+254724213764', 'string', 'contact', TRUE, 'Primary Phone', 'Main contact phone number'),
('email_primary', 'info@skillsforafrica.org', 'string', 'contact', TRUE, 'Primary Email', 'Main contact email address'),
('address_primary', 'Chania, Thika, Kenya', 'string', 'contact', TRUE, 'Primary Address', 'Main office address'),
('whatsapp_number', '+254724213764', 'string', 'contact', TRUE, 'WhatsApp Number', 'WhatsApp contact number'),
('social_facebook', '', 'url', 'social', TRUE, 'Facebook URL', 'Facebook page URL'),
('social_twitter', '', 'url', 'social', TRUE, 'Twitter URL', 'Twitter profile URL'),
('social_linkedin', '', 'url', 'social', TRUE, 'LinkedIn URL', 'LinkedIn profile URL'),
('social_instagram', '', 'url', 'social', TRUE, 'Instagram URL', 'Instagram profile URL'),
('social_youtube', '', 'url', 'social', TRUE, 'YouTube URL', 'YouTube channel URL');

-- Insert default admin user (password: admin123 - CHANGE THIS IN PRODUCTION!)
INSERT INTO users (username, email, password_hash, full_name, role, status, email_verified, created_at) VALUES
('admin', 'admin@skillsforafrica.org', '$2y$12$LQv3c1ydiCSmqmdHyoWqrOYSGH.wJWjzT.8.hDy4LDXTfaD.C/uW.', 'System Administrator', 'admin', 'active', TRUE, NOW());

-- ===============================
-- 8. CREATE VIEWS FOR COMMON QUERIES
-- ===============================

CREATE VIEW active_programs AS
SELECT
    p.*,
    u.full_name as created_by_name,
    (SELECT COUNT(*) FROM applications WHERE program_id = p.id AND deleted_at IS NULL) as current_applications
FROM programs p
LEFT JOIN users u ON p.created_by = u.id
WHERE p.is_active = TRUE AND p.deleted_at IS NULL;

CREATE VIEW recent_applications AS
SELECT
    a.*,
    p.title as program_title,
    p.category as program_category,
    CONCAT(a.first_name, ' ', a.last_name) as full_name
FROM applications a
JOIN programs p ON a.program_id = p.id
WHERE a.deleted_at IS NULL
ORDER BY a.submitted_at DESC;

CREATE VIEW dashboard_stats AS
SELECT
    (SELECT COUNT(*) FROM programs WHERE is_active = TRUE AND deleted_at IS NULL) as active_programs,
    (SELECT COUNT(*) FROM applications WHERE status = 'pending' AND deleted_at IS NULL) as pending_applications,
    (SELECT COUNT(*) FROM events WHERE event_date >= CURDATE() AND is_active = TRUE AND deleted_at IS NULL) as upcoming_events,
    (SELECT COUNT(*) FROM contacts WHERE status = 'new' AND deleted_at IS NULL) as new_contacts,
    (SELECT COUNT(*) FROM users WHERE status = 'active' AND deleted_at IS NULL) as active_users;

-- ===============================
-- 9. STORED PROCEDURES
-- ===============================

DELIMITER //

CREATE PROCEDURE GetApplicationStats(IN prog_id INT)
BEGIN
    SELECT
        COUNT(*) as total_applications,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM applications
    WHERE program_id = prog_id AND deleted_at IS NULL;
END//

CREATE PROCEDURE CleanupExpiredTokens()
BEGIN
    DELETE FROM password_resets WHERE expires_at < NOW();
    DELETE FROM email_verifications WHERE expires_at < NOW();
    DELETE FROM sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY);
END//

DELIMITER ;

-- ===============================
-- 10. SECURITY SETUP
-- ===============================

-- Create triggers for automatic logging (optional - can be added later)
-- These will help track important changes in the system

DELIMITER //

CREATE TRIGGER programs_insert_log AFTER INSERT ON programs
FOR EACH ROW BEGIN
    INSERT INTO admin_logs (user_id, action, entity_type, entity_id, new_values, ip_address)
    VALUES (NEW.created_by, 'CREATE', 'program', NEW.id, JSON_OBJECT('title', NEW.title, 'category', NEW.category), @user_ip);
END//

CREATE TRIGGER applications_status_log AFTER UPDATE ON applications
FOR EACH ROW BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO admin_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address)
        VALUES (NEW.reviewed_by, 'STATUS_CHANGE', 'application', NEW.id,
                JSON_OBJECT('status', OLD.status),
                JSON_OBJECT('status', NEW.status),
                @user_ip);
    END IF;
END//

DELIMITER ;

-- ===============================
-- DATABASE SETUP COMPLETED
-- ===============================

-- Important Notes:
-- 1. Change the default admin password immediately after deployment
-- 2. Update all email addresses and contact information
-- 3. Configure proper SSL certificates for production
-- 4. Set up regular database backups
-- 5. Monitor database performance and optimize as needed
-- 6. Update the site settings through the admin panel after deployment

SELECT 'Database setup completed successfully!' as message;
