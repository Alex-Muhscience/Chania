USE chania_db;

-- Create login_attempts table
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(255),
    reason VARCHAR(100),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Create programs table
CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500),
    category VARCHAR(100),
    duration VARCHAR(50),
    difficulty_level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
    fee DECIMAL(10,2) DEFAULT 0.00,
    max_participants INT,
    start_date DATE,
    end_date DATE,
    image_path VARCHAR(255),
    instructor_name VARCHAR(100),
    location VARCHAR(255),
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    is_online TINYINT(1) DEFAULT 0,
    view_count INT DEFAULT 0,
    application_count INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Create applications table
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    application_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male','female','other','prefer_not_to_say'),
    address TEXT NOT NULL,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Kenya',
    education_level ENUM('primary','secondary','diploma','degree','masters','phd','other'),
    education_details TEXT NOT NULL,
    work_experience TEXT,
    motivation TEXT NOT NULL,
    status ENUM('pending','under_review','approved','rejected','withdrawn','waitlisted') DEFAULT 'pending',
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    notes TEXT,
    payment_status ENUM('pending','paid','partial','refunded') DEFAULT 'pending',
    ip_address VARCHAR(45),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Create events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    short_description VARCHAR(500),
    event_type ENUM('workshop','seminar','conference','networking','training','other') DEFAULT 'workshop',
    event_date DATETIME NOT NULL,
    end_date DATETIME,
    location VARCHAR(255) NOT NULL,
    max_attendees INT,
    current_attendees INT DEFAULT 0,
    registration_fee DECIMAL(10,2) DEFAULT 0.00,
    registration_deadline DATETIME,
    image_path VARCHAR(255),
    speaker_name VARCHAR(100),
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    is_virtual TINYINT(1) DEFAULT 0,
    view_count INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Create event_registrations table
CREATE TABLE event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    organization VARCHAR(255),
    status ENUM('pending','confirmed','cancelled','attended') DEFAULT 'pending',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create contacts table
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    replied_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Create testimonials table
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    position VARCHAR(150),
    company VARCHAR(150),
    testimonial TEXT NOT NULL,
    rating INT DEFAULT 5,
    image_path VARCHAR(255),
    is_featured TINYINT(1) DEFAULT 0,
    is_approved TINYINT(1) DEFAULT 0,
    program_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Create partners table
CREATE TABLE partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    logo_path VARCHAR(255),
    website_url VARCHAR(255),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Create team_members table
CREATE TABLE team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(150) NOT NULL,
    bio TEXT,
    email VARCHAR(100),
    phone VARCHAR(20),
    image_path VARCHAR(255),
    linkedin_url VARCHAR(255),
    twitter_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Create newsletter_subscribers table
CREATE TABLE newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100),
    status ENUM('subscribed','unsubscribed','bounced') DEFAULT 'subscribed',
    verification_token VARCHAR(255),
    verified_at TIMESTAMP NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    source VARCHAR(100) DEFAULT 'website',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Create file_uploads table
CREATE TABLE file_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    uploaded_by INT,
    is_public TINYINT(1) DEFAULT 0,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Insert some sample data
INSERT INTO programs (title, slug, description, short_description, category, duration, difficulty_level, is_active, is_featured) VALUES
('Web Development Fundamentals', 'web-development-fundamentals', 'Learn the basics of web development including HTML, CSS, and JavaScript.', 'Master the fundamentals of modern web development.', 'Technology', '12 weeks', 'beginner', 1, 1),
('Digital Marketing Mastery', 'digital-marketing-mastery', 'Comprehensive course on digital marketing strategies and tools.', 'Become a digital marketing expert.', 'Marketing', '8 weeks', 'intermediate', 1, 1);

INSERT INTO events (title, slug, description, short_description, event_type, event_date, location, is_active, is_featured) VALUES
('Tech Career Fair 2025', 'tech-career-fair-2025', 'Annual technology career fair connecting students with top employers.', 'Connect with leading tech companies.', 'networking', '2025-08-15 10:00:00', 'Nairobi Convention Centre', 1, 1),
('AI Workshop Series', 'ai-workshop-series', 'Hands-on workshop series on artificial intelligence and machine learning.', 'Learn AI and ML fundamentals.', 'workshop', '2025-08-20 14:00:00', 'Online via Zoom', 1, 1);

INSERT INTO testimonials (name, position, company, testimonial, rating, is_approved, is_featured) VALUES
('John Doe', 'Software Developer', 'Tech Solutions Ltd', 'The web development program completely transformed my career. The instructors were knowledgeable and the curriculum was comprehensive.', 5, 1, 1),
('Jane Smith', 'Marketing Manager', 'Digital Agency Pro', 'Excellent digital marketing course! I learned practical skills that I immediately applied in my job.', 5, 1, 1);

INSERT INTO partners (name, description, website_url, contact_email, is_active) VALUES
('TechCorp Kenya', 'Leading technology company in East Africa', 'https://techcorp.co.ke', 'info@techcorp.co.ke', 1),
('Innovation Hub', 'Supporting startups and entrepreneurs', 'https://innovationhub.co.ke', 'contact@innovationhub.co.ke', 1);

INSERT INTO team_members (name, position, bio, email, is_active) VALUES
('Alex Johnson', 'Executive Director', 'Passionate about empowering youth through technology education and skills development.', 'alex@skillsforafrica.org', 1),
('Sarah Williams', 'Program Coordinator', 'Experienced educator with 10+ years in curriculum development and student support.', 'sarah@skillsforafrica.org', 1);

INSERT INTO newsletter_subscribers (email, name, status) VALUES
('subscriber1@example.com', 'Test Subscriber 1', 'subscribed'),
('subscriber2@example.com', 'Test Subscriber 2', 'subscribed');

-- Create some sample applications
INSERT INTO applications (program_id, application_number, first_name, last_name, email, phone, address, education_details, motivation, status) VALUES
(1, 'APP-001-2025', 'Michael', 'Brown', 'michael@example.com', '+254700000001', '123 Main St, Nairobi', 'Bachelor of Computer Science', 'I want to become a professional web developer', 'pending'),
(2, 'APP-002-2025', 'Grace', 'Wanjiku', 'grace@example.com', '+254700000002', '456 Market St, Mombasa', 'Diploma in Business Administration', 'Looking to transition into digital marketing', 'approved');

-- Create sample contacts
INSERT INTO contacts (name, email, subject, message, is_read) VALUES
('David Kimani', 'david@example.com', 'Inquiry about Web Development Program', 'I would like to know more about the enrollment process.', 0),
('Mary Achieng', 'mary@example.com', 'Partnership Opportunity', 'We are interested in partnering with your organization.', 1);
