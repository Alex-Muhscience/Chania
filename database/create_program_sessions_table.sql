-- Create program_sessions table to store program schedules and session dates
-- This table manages when programs are offered and tracks enrollment

CREATE TABLE IF NOT EXISTS program_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    location VARCHAR(255) NULL DEFAULT 'Online',
    max_participants INT NULL,
    current_participants INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    is_open_for_registration BOOLEAN DEFAULT 1,
    session_notes TEXT NULL,
    instructor_name VARCHAR(255) NULL,
    session_fee DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Foreign key constraint
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    
    -- Indexes for better performance
    INDEX idx_program_sessions_program_id (program_id),
    INDEX idx_program_sessions_start_date (start_date),
    INDEX idx_program_sessions_active (is_active),
    INDEX idx_program_sessions_registration (is_open_for_registration),
    INDEX idx_program_sessions_deleted (deleted_at)
);

-- Insert some sample program sessions for existing programs
-- Note: Make sure to replace program IDs with actual IDs from your programs table

INSERT INTO program_sessions (program_id, start_date, end_date, location, max_participants, current_participants, is_active, is_open_for_registration, session_notes) VALUES
-- Data Science Program sessions
(1, '2024-02-15', '2024-05-15', 'Cape Town, South Africa', 25, 8, 1, 1, 'Intensive 3-month program with hands-on projects'),
(1, '2024-04-01', '2024-07-01', 'Online', 50, 12, 1, 1, 'Virtual classroom with live sessions'),
(1, '2024-06-15', '2024-09-15', 'Lagos, Nigeria', 30, 0, 1, 1, 'In-person bootcamp format'),

-- Web Development sessions  
(2, '2024-01-20', '2024-04-20', 'Nairobi, Kenya', 20, 15, 1, 1, 'Full-stack development focus'),
(2, '2024-03-10', '2024-06-10', 'Online', 40, 22, 1, 1, 'Evening classes available'),
(2, '2024-05-05', '2024-08-05', 'Accra, Ghana', 25, 5, 1, 1, 'Weekend intensive program'),

-- Digital Marketing sessions
(3, '2024-02-01', '2024-03-15', 'Online', 60, 45, 1, 1, '6-week accelerated course'),
(3, '2024-03-20', '2024-05-05', 'Johannesburg, South Africa', 35, 28, 1, 1, 'In-person program with practical workshops'),
(3, '2024-04-15', '2024-05-30', 'Online', 80, 15, 1, 1, 'Self-paced with mentor support'),

-- Financial Literacy sessions
(4, '2024-01-15', '2024-02-15', 'Kigali, Rwanda', 40, 32, 1, 1, 'Community-focused program'),
(4, '2024-03-01', '2024-04-01', 'Online', 100, 67, 1, 1, 'Mobile-friendly platform'),
(4, '2024-04-20', '2024-05-20', 'Kampala, Uganda', 50, 18, 1, 1, 'Local language support available'),

-- Leadership Training sessions
(5, '2024-02-10', '2024-02-25', 'Online', 30, 24, 1, 1, 'Executive leadership track'),
(5, '2024-04-05', '2024-04-20', 'Cape Town, South Africa', 25, 10, 1, 1, 'In-person workshop intensive'),
(5, '2024-06-01', '2024-06-16', 'Online', 45, 8, 1, 1, 'Mid-level management focus');

-- Add some closed/past sessions for testing
INSERT INTO program_sessions (program_id, start_date, end_date, location, max_participants, current_participants, is_active, is_open_for_registration, session_notes) VALUES
(1, '2023-10-01', '2024-01-01', 'Online', 40, 40, 1, 0, 'Completed cohort - Full enrollment'),
(2, '2023-09-15', '2023-12-15', 'Lagos, Nigeria', 30, 28, 1, 0, 'Successfully completed program'),
(3, '2023-11-01', '2023-12-15', 'Online', 50, 47, 1, 0, 'High completion rate cohort');

-- You can also add future sessions that are not yet open for registration
INSERT INTO program_sessions (program_id, start_date, end_date, location, max_participants, is_active, is_open_for_registration, session_notes) VALUES
(1, '2024-08-01', '2024-11-01', 'Dar es Salaam, Tanzania', 25, 1, 0, 'Registration opens March 2024'),
(2, '2024-09-01', '2024-12-01', 'Online', 60, 1, 0, 'Advanced track - Prerequisites required'),
(4, '2024-07-15', '2024-08-15', 'Addis Ababa, Ethiopia', 35, 1, 0, 'Partnership with local universities');
