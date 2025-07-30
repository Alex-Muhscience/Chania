-- Create FAQs table for Digital Empowerment Network
CREATE TABLE IF NOT EXISTS faqs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'General',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT DEFAULT NULL,
    updated_by INT DEFAULT NULL,
    
    INDEX idx_category (category),
    INDEX idx_active (is_active),
    INDEX idx_order (display_order),
    INDEX idx_created_at (created_at)
);

-- Insert sample FAQs
INSERT INTO faqs (question, answer, category, display_order) VALUES
('What is the Digital Empowerment Network?', 
 'The Digital Empowerment Network is a non-profit organization dedicated to bridging the digital divide by providing technology education, resources, and support to underserved communities.',
 'About Us', 1),

('How can I apply for a program?', 
 'You can apply for our programs by visiting the Programs page on our website and clicking the "Apply Now" button for your desired program. Fill out the application form completely and submit it for review.',
 'Programs', 2),

('Are the programs free?', 
 'Yes, all our programs are completely free of charge. We are committed to removing financial barriers to digital education and empowerment.',
 'Programs', 3),

('What are the requirements to join a program?', 
 'Requirements vary by program, but generally include basic computer literacy, commitment to attend sessions, and meeting age requirements. Specific requirements are listed on each program''s detail page.',
 'Programs', 4),

('How long do programs typically last?', 
 'Program duration varies depending on the specific course. Most programs range from 4-12 weeks, with sessions held 2-3 times per week. Duration is specified in each program description.',
 'Programs', 5),

('Can I volunteer with your organization?', 
 'Absolutely! We welcome volunteers who are passionate about digital empowerment. Please visit our Contact page to get in touch with our volunteer coordinator.',
 'Volunteering', 6),

('Do you provide certificates upon completion?', 
 'Yes, participants who successfully complete our programs receive a certificate of completion that can be used to demonstrate their newly acquired digital skills.',
 'Programs', 7),

('Where are your programs located?', 
 'We offer both in-person and online programs. In-person sessions are held at our community centers and partner locations. Online programs can be accessed from anywhere with an internet connection.',
 'General', 8),

('How do I contact support?', 
 'You can contact our support team through the Contact page on our website, by email, or by phone during business hours. We strive to respond to all inquiries within 24 hours.',
 'Support', 9),

('Do you offer programs in languages other than English?', 
 'We are working to expand our multilingual offerings. Currently, we provide materials in Spanish and are developing resources in additional languages based on community needs.',
 'General', 10);
