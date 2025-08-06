-- Enhanced Program Management Schema
-- This adds comprehensive scheduling, curriculum, and fee management

-- Enhanced program_sessions table (if not exists, otherwise modify existing)
CREATE TABLE IF NOT EXISTS program_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL DEFAULT 'Session',
    start_date DATE NOT NULL,
    end_date DATE,
    location VARCHAR(255) DEFAULT 'Online',
    delivery_mode ENUM('online', 'physical') DEFAULT 'online',
    max_participants INT NULL,
    current_participants INT DEFAULT 0,
    online_fee DECIMAL(10,2) DEFAULT 0.00,
    physical_fee DECIMAL(10,2) DEFAULT 0.00,
    registration_deadline DATE NULL,
    is_active BOOLEAN DEFAULT 1,
    is_open_for_registration BOOLEAN DEFAULT 1,
    instructor_name VARCHAR(255) NULL,
    session_notes TEXT NULL,
    meeting_link VARCHAR(500) NULL,
    venue_address TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    INDEX idx_program_schedules_program_id (program_id),
    INDEX idx_program_schedules_start_date (start_date),
    INDEX idx_program_schedules_active (is_active),
    INDEX idx_program_schedules_registration (is_open_for_registration)
);

-- Program curriculum table
CREATE TABLE IF NOT EXISTS program_curriculum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    module_title VARCHAR(255) NOT NULL,
    module_description TEXT,
    module_order INT DEFAULT 1,
    duration_hours INT DEFAULT 0,
    learning_objectives TEXT,
    prerequisites TEXT,
    resources TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    INDEX idx_curriculum_program_id (program_id),
    INDEX idx_curriculum_order (module_order)
);

-- Program additional information table
CREATE TABLE IF NOT EXISTS program_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    requirements TEXT,
    benefits TEXT,
    target_audience TEXT,
    career_outcomes TEXT,
    certification_details TEXT,
    materials_included TEXT,
    support_provided TEXT,
    refund_policy TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_program_info (program_id)
);

-- Sample data for testing
INSERT INTO program_schedules (program_id, title, start_date, end_date, location, delivery_mode, online_fee, physical_fee, max_participants, registration_deadline) VALUES
-- Assuming program ID 1 exists
(1, 'March 2024 Cohort', '2024-03-15', '2024-06-15', 'Online', 'online', 4500.00, 6500.00, 30, '2024-03-10'),
(1, 'April 2024 Cohort', '2024-04-20', '2024-07-20', 'Nairobi Campus', 'physical', 4500.00, 6500.00, 25, '2024-04-15'),
(1, 'May 2024 Cohort', '2024-05-10', '2024-08-10', 'Online', 'online', 4500.00, 6500.00, 35, '2024-05-05');

-- Sample curriculum data
INSERT INTO program_curriculum (program_id, module_title, module_description, module_order, duration_hours, learning_objectives) VALUES
(1, 'Introduction & Foundations', 'Getting started with fundamental concepts and setting up the development environment', 1, 20, 'Understand basic concepts, Set up development environment, Complete first practical exercises'),
(1, 'Core Skills Development', 'Deep dive into essential skills and practical applications', 2, 30, 'Master core techniques, Build practical projects, Apply industry best practices'),
(1, 'Advanced Applications', 'Advanced topics and real-world project implementation', 3, 25, 'Implement complex solutions, Work on capstone project, Prepare for certification'),
(1, 'Final Project & Assessment', 'Capstone project presentation and final assessment', 4, 15, 'Complete final project, Present to panel, Receive certification');

-- Sample program additional info
INSERT INTO program_info (program_id, requirements, benefits, target_audience, career_outcomes, certification_details, materials_included) VALUES
(1, 
'Basic computer literacy, High school education or equivalent, Reliable internet connection for online sessions',
'Industry-recognized certification, Hands-on practical experience, Job placement assistance, Lifetime access to course materials, Community support network',
'Recent graduates, Career changers, Working professionals seeking upskilling, Entrepreneurs, Students',
'Software Developer, Technical Consultant, Project Manager, Technical Lead, Freelance Developer',
'Upon successful completion, participants receive a verified certificate from Chania Skills for Africa. Certificate includes unique verification code and can be verified online.',
'All course materials (digital), Software licenses, Project templates, Resource library access, Support forum access'
);
