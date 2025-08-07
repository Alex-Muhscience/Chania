-- Add More Program Schedules for Sample Programs
-- This script adds comprehensive schedules for all existing programs with various dates, locations, and modes

-- Full Stack Web Development (ID: 5)
INSERT INTO program_schedules (program_id, title, start_date, end_date, location, delivery_mode, online_fee, physical_fee, max_participants, current_participants, registration_deadline, instructor_name, session_notes, meeting_link, venue_address) VALUES
(5, 'January 2024 Bootcamp', '2024-01-15', '2024-04-15', 'Online', 'online', 15000.00, 20000.00, 30, 18, '2024-01-10', 'John Kiprotich', 'Intensive full-stack bootcamp with React and Node.js', 'https://meet.chania.africa/fullstack-jan2024', NULL),
(5, 'February Physical Cohort', '2024-02-01', '2024-05-01', 'Nairobi Innovation Hub', 'physical', 15000.00, 18000.00, 25, 22, '2024-01-25', 'Sarah Wanjiku', 'In-person intensive with project-based learning', NULL, 'Nairobi Innovation Hub, Bishops Road, Nairobi'),
(5, 'March Hybrid Program', '2024-03-10', '2024-06-10', 'Mombasa Tech Center', 'physical', 15000.00, 19000.00, 20, 12, '2024-03-05', 'Ahmed Hassan', 'Weekend intensive program with industry projects', NULL, 'Mombasa Tech Center, Nyerere Avenue, Mombasa'),
(5, 'April Evening Cohort', '2024-04-15', '2024-07-15', 'Online', 'online', 15000.00, 20000.00, 40, 8, '2024-04-10', 'Grace Mutindi', 'Evening classes for working professionals', 'https://meet.chania.africa/fullstack-evening-apr2024', NULL),
(5, 'May Advanced Track', '2024-05-20', '2024-08-20', 'Kisumu Innovation Center', 'physical', 18000.00, 22000.00, 15, 3, '2024-05-15', 'Peter Ochieng', 'Advanced full-stack with cloud deployment', NULL, 'Kisumu Innovation Center, Oginga Odinga Street, Kisumu'),
(5, 'June Remote Global', '2024-06-01', '2024-09-01', 'Online', 'online', 15000.00, 20000.00, 50, 15, '2024-05-25', 'Mary Njoki', 'Global remote cohort with international mentors', 'https://meet.chania.africa/fullstack-global-jun2024', NULL),

-- Data Science & Analytics (ID: 6)
(6, 'February Data Intensive', '2024-02-05', '2024-05-05', 'Online', 'online', 20000.00, 25000.00, 25, 20, '2024-01-30', 'Dr. Samuel Kimani', 'Python, R, and machine learning focus', 'https://meet.chania.africa/datascience-feb2024', NULL),
(6, 'March Analytics Bootcamp', '2024-03-01', '2024-06-01', 'University of Nairobi', 'physical', 20000.00, 24000.00, 30, 25, '2024-02-25', 'Prof. Jane Wanjiru', 'University partnership program with certification', NULL, 'University of Nairobi, College of Biological and Physical Sciences'),
(6, 'April Business Analytics', '2024-04-10', '2024-07-10', 'Online', 'online', 18000.00, 23000.00, 35, 12, '2024-04-05', 'Michael Omondi', 'Business-focused analytics and visualization', 'https://meet.chania.africa/business-analytics-apr2024', NULL),
(6, 'May Machine Learning Track', '2024-05-15', '2024-08-15', 'JKUAT', 'physical', 22000.00, 26000.00, 20, 8, '2024-05-10', 'Dr. Ruth Wario', 'Advanced machine learning and AI', NULL, 'Jomo Kenyatta University of Agriculture and Technology, Juja'),
(6, 'June Data Engineering', '2024-06-20', '2024-09-20', 'Online', 'online', 21000.00, 26000.00, 25, 5, '2024-06-15', 'Evans Kiprotich', 'Big data and cloud data engineering', 'https://meet.chania.africa/data-eng-jun2024', NULL),

-- Mobile App Development (ID: 7)
(7, 'January Mobile Bootcamp', '2024-01-20', '2024-04-20', 'Online', 'online', 16000.00, 21000.00, 30, 22, '2024-01-15', 'Daniel Muthui', 'React Native and Flutter focus', 'https://meet.chania.africa/mobile-jan2024', NULL),
(7, 'February Android Track', '2024-02-15', '2024-05-15', 'Strathmore University', 'physical', 16000.00, 19000.00, 25, 18, '2024-02-10', 'Catherine Njeri', 'Native Android development with Kotlin', NULL, 'Strathmore University, Ole Sangale Road, Nairobi'),
(7, 'March iOS Development', '2024-03-20', '2024-06-20', 'Online', 'online', 17000.00, 22000.00, 20, 14, '2024-03-15', 'James Wainaina', 'Swift and iOS native development', 'https://meet.chania.africa/ios-mar2024', NULL),
(7, 'April Cross-Platform', '2024-04-25', '2024-07-25', 'Eldoret Polytechnic', 'physical', 16000.00, 20000.00, 22, 6, '2024-04-20', 'Alice Chepchumba', 'Flutter and React Native comparison', NULL, 'Eldoret National Polytechnic, Uganda Road, Eldoret'),
(7, 'May Game Development', '2024-05-10', '2024-08-10', 'Online', 'online', 18000.00, 23000.00, 15, 3, '2024-05-05', 'Brian Kibet', 'Unity and mobile game development', 'https://meet.chania.africa/mobile-games-may2024', NULL),

-- Digital Marketing Mastery (ID: 8)
(8, 'January Marketing Intensive', '2024-01-10', '2024-03-10', 'Online', 'online', 12000.00, 16000.00, 40, 35, '2024-01-05', 'Linda Akinyi', 'Complete digital marketing strategy', 'https://meet.chania.africa/marketing-jan2024', NULL),
(8, 'February Social Media Focus', '2024-02-20', '2024-04-20', 'Creative Economy Hub', 'physical', 12000.00, 15000.00, 30, 28, '2024-02-15', 'Mark Kiptoo', 'Social media marketing and content creation', NULL, 'Creative Economy Hub, Wood Avenue, Nairobi'),
(8, 'March E-commerce Marketing', '2024-03-15', '2024-05-15', 'Online', 'online', 13000.00, 17000.00, 35, 20, '2024-03-10', 'Eunice Wambui', 'E-commerce and online business marketing', 'https://meet.chania.africa/ecommerce-mar2024', NULL),
(8, 'April SEO & SEM Masterclass', '2024-04-05', '2024-06-05', 'Meru University', 'physical', 14000.00, 18000.00, 25, 15, '2024-03-30', 'Stephen Mwangi', 'Search engine optimization and marketing', NULL, 'Meru University of Science & Technology, Meru'),
(8, 'May Content Marketing', '2024-05-25', '2024-07-25', 'Online', 'online', 12000.00, 16000.00, 45, 10, '2024-05-20', 'Patricia Nyambura', 'Content strategy and brand storytelling', 'https://meet.chania.africa/content-may2024', NULL),
(8, 'June Analytics & ROI', '2024-06-10', '2024-08-10', 'Kenyatta University', 'physical', 13000.00, 17000.00, 30, 8, '2024-06-05', 'Robert Kamau', 'Marketing analytics and ROI measurement', NULL, 'Kenyatta University, School of Business, Nairobi'),

-- Project Management Professional (ID: 9)
(9, 'February PMP Certification', '2024-02-10', '2024-04-10', 'Online', 'online', 25000.00, 30000.00, 20, 18, '2024-02-05', 'Margaret Chepkemei', 'PMP exam preparation and certification', 'https://meet.chania.africa/pmp-feb2024', NULL),
(9, 'March Agile PM Track', '2024-03-05', '2024-05-05', 'Kenya School of Government', 'physical', 25000.00, 28000.00, 25, 22, '2024-02-28', 'David Mutua', 'Agile and Scrum project management', NULL, 'Kenya School of Government, Lower Kabete, Nairobi'),
(9, 'April IT Project Management', '2024-04-15', '2024-06-15', 'Online', 'online', 24000.00, 29000.00, 30, 12, '2024-04-10', 'Susan Wairimu', 'IT project management specialization', 'https://meet.chania.africa/itpm-apr2024', NULL),
(9, 'May Construction PM', '2024-05-01', '2024-07-01', 'Technical University of Kenya', 'physical', 26000.00, 31000.00, 18, 7, '2024-04-25', 'Engineer Paul Kiprotich', 'Construction and infrastructure PM', NULL, 'Technical University of Kenya, Haile Selassie Avenue, Nairobi'),
(9, 'June Healthcare PM', '2024-06-15', '2024-08-15', 'Online', 'online', 25000.00, 30000.00, 22, 5, '2024-06-10', 'Dr. Mary Waithaka', 'Healthcare project management', 'https://meet.chania.africa/healthcare-pm-jun2024', NULL),

-- Entrepreneurship & Business Development (ID: 10)
(10, 'January Startup Accelerator', '2024-01-25', '2024-04-25', 'iHub Nairobi', 'physical', 18000.00, 22000.00, 30, 25, '2024-01-20', 'Anthony Kuria', 'Startup development and funding', NULL, 'iHub, Senteu Plaza, Nairobi'),
(10, 'February Business Planning', '2024-02-12', '2024-04-12', 'Online', 'online', 15000.00, 20000.00, 40, 32, '2024-02-07', 'Grace Wanjala', 'Business plan development and strategy', 'https://meet.chania.africa/bizplan-feb2024', NULL),
(10, 'March Women in Business', '2024-03-08', '2024-05-08', 'Women Enterprise Fund', 'physical', 16000.00, 19000.00, 35, 30, '2024-03-03', 'Joyce Muthoni', 'Women entrepreneurship and leadership', NULL, 'Women Enterprise Fund, Anniversary Towers, Nairobi'),
(10, 'April Tech Entrepreneurship', '2024-04-20', '2024-06-20', 'Online', 'online', 19000.00, 24000.00, 25, 15, '2024-04-15', 'Kevin Njuguna', 'Technology startup development', 'https://meet.chania.africa/tech-entrepreneur-apr2024', NULL),
(10, 'May Social Enterprise', '2024-05-15', '2024-07-15', 'Aga Khan University', 'physical', 17000.00, 21000.00, 28, 10, '2024-05-10', 'Fatuma Ahmed', 'Social impact and sustainable business', NULL, 'Aga Khan University, 3rd Parklands Avenue, Nairobi'),
(10, 'June Rural Business Development', '2024-06-05', '2024-08-05', 'Online', 'online', 14000.00, 19000.00, 50, 8, '2024-05-30', 'Peter Mwangi', 'Rural entrepreneurship and agribusiness', 'https://meet.chania.africa/rural-biz-jun2024', NULL),

-- Smart Agriculture & IoT (ID: 11)
(11, 'February AgTech Bootcamp', '2024-02-01', '2024-04-01', 'ICIPE', 'physical', 20000.00, 24000.00, 20, 16, '2024-01-25', 'Dr. Joseph Macharia', 'IoT applications in agriculture', NULL, 'ICIPE - International Centre of Insect Physiology and Ecology, Nairobi'),
(11, 'March Precision Farming', '2024-03-10', '2024-05-10', 'Online', 'online', 18000.00, 23000.00, 30, 22, '2024-03-05', 'Prof. Sarah Kiprotich', 'GPS and drone technology in farming', 'https://meet.chania.africa/precision-farming-mar2024', NULL),
(11, 'April Livestock Tech', '2024-04-08', '2024-06-08', 'ILRI', 'physical', 19000.00, 23000.00, 25, 12, '2024-04-03', 'Dr. Michael Njoroge', 'Technology for livestock management', NULL, 'ILRI - International Livestock Research Institute, Nairobi'),
(11, 'May Greenhouse Automation', '2024-05-20', '2024-07-20', 'Online', 'online', 20000.00, 25000.00, 22, 8, '2024-05-15', 'Engineer Grace Chepkemei', 'Automated greenhouse systems', 'https://meet.chania.africa/greenhouse-auto-may2024', NULL),
(11, 'June Climate Smart Agriculture', '2024-06-15', '2024-08-15', 'CIAT', 'physical', 21000.00, 25000.00, 18, 5, '2024-06-10', 'Dr. Jane Wanjiru', 'Climate adaptation in agriculture', NULL, 'CIAT - International Center for Tropical Agriculture, Nairobi'),

-- Organic Farming & Certification (ID: 12)
(12, 'January Organic Basics', '2024-01-15', '2024-03-15', 'KOAN Training Center', 'physical', 12000.00, 15000.00, 35, 28, '2024-01-10', 'Samuel Kiprotich', 'Introduction to organic farming', NULL, 'Kenya Organic Agriculture Network, Nairobi'),
(12, 'February Soil Health', '2024-02-20', '2024-04-20', 'Online', 'online', 10000.00, 14000.00, 40, 30, '2024-02-15', 'Dr. Mary Wanjiku', 'Organic soil management and composting', 'https://meet.chania.africa/soil-health-feb2024', NULL),
(12, 'March Pest Management', '2024-03-25', '2024-05-25', 'KALRO', 'physical', 13000.00, 16000.00, 30, 25, '2024-03-20', 'John Mwangi', 'Organic pest and disease control', NULL, 'KALRO - Kenya Agricultural and Livestock Research Organization'),
(12, 'April Certification Process', '2024-04-10', '2024-06-10', 'Online', 'online', 15000.00, 19000.00, 25, 18, '2024-04-05', 'Grace Mutindi', 'Organic certification standards and process', 'https://meet.chania.africa/organic-cert-apr2024', NULL),
(12, 'May Value Addition', '2024-05-05', '2024-07-05', 'Jomo Kenyatta Foundation', 'physical', 14000.00, 17000.00, 32, 12, '2024-04-30', 'Elizabeth Wambui', 'Organic product processing and marketing', NULL, 'Jomo Kenyatta Foundation, Nairobi'),
(12, 'June Export Markets', '2024-06-20', '2024-08-20', 'Online', 'online', 16000.00, 20000.00, 28, 6, '2024-06-15', 'Patrick Ochieng', 'Exporting organic products', 'https://meet.chania.africa/organic-export-jun2024', NULL),

-- Healthcare Management & Administration (ID: 13)
(13, 'February Healthcare Leadership', '2024-02-05', '2024-05-05', 'Kenyatta National Hospital', 'physical', 22000.00, 26000.00, 25, 20, '2024-01-30', 'Dr. Susan Wairimu', 'Healthcare leadership and governance', NULL, 'Kenyatta National Hospital, Hospital Road, Nairobi'),
(13, 'March Hospital Operations', '2024-03-12', '2024-06-12', 'Online', 'online', 20000.00, 25000.00, 30, 24, '2024-03-07', 'Peter Kamau', 'Hospital operations and quality management', 'https://meet.chania.africa/hospital-ops-mar2024', NULL),
(13, 'April Health Informatics', '2024-04-18', '2024-07-18', 'University of Nairobi', 'physical', 24000.00, 28000.00, 20, 15, '2024-04-13', 'Prof. Jane Kariuki', 'Health information systems and data', NULL, 'University of Nairobi, School of Medicine'),
(13, 'May Healthcare Finance', '2024-05-15', '2024-08-15', 'Online', 'online', 21000.00, 26000.00, 28, 10, '2024-05-10', 'CPA Mary Njoki', 'Healthcare financial management', 'https://meet.chania.africa/health-finance-may2024', NULL),
(13, 'June Public Health Management', '2024-06-10', '2024-09-10', 'Ministry of Health', 'physical', 20000.00, 24000.00, 35, 8, '2024-06-05', 'Dr. James Mwangi', 'Public health systems and policy', NULL, 'Ministry of Health, Afya House, Nairobi'),

-- Community Health & Nutrition (ID: 14)
(14, 'January Community Nutrition', '2024-01-20', '2024-03-20', 'Online', 'online', 8000.00, 12000.00, 50, 42, '2024-01-15', 'Nutritionist Grace Wanjiru', 'Community nutrition programs', 'https://meet.chania.africa/community-nutrition-jan2024', NULL),
(14, 'February Maternal Health', '2024-02-25', '2024-04-25', 'AMREF Health Africa', 'physical', 10000.00, 13000.00, 40, 35, '2024-02-20', 'Dr. Ruth Waithaka', 'Maternal and child health', NULL, 'AMREF Health Africa, Langata Road, Nairobi'),
(14, 'March Health Education', '2024-03-18', '2024-05-18', 'Online', 'online', 9000.00, 13000.00, 45, 30, '2024-03-13', 'Mary Chepkemei', 'Community health education and promotion', 'https://meet.chania.africa/health-education-mar2024', NULL),
(14, 'April Disease Prevention', '2024-04-22', '2024-06-22', 'CDC Kenya', 'physical', 11000.00, 14000.00, 35, 25, '2024-04-17', 'Dr. Samuel Kimani', 'Disease prevention and control', NULL, 'CDC Kenya, Embassy of the United States, Nairobi'),
(14, 'May Mental Health', '2024-05-10', '2024-07-10', 'Online', 'online', 12000.00, 16000.00, 30, 18, '2024-05-05', 'Dr. Patricia Njeri', 'Community mental health programs', 'https://meet.chania.africa/mental-health-may2024', NULL),
(14, 'June Nutrition Counseling', '2024-06-25', '2024-08-25', 'Kenya Nutritionist and Dieticians Institute', 'physical', 13000.00, 17000.00, 25, 10, '2024-06-20', 'Nutritionist Alice Wambui', 'Nutrition counseling and therapy', NULL, 'Kenya Nutritionist and Dieticians Institute, Nairobi'),

-- Cybersecurity Fundamentals (ID: 15)
(15, 'February Cyber Basics', '2024-02-15', '2024-05-15', 'Online', 'online', 18000.00, 23000.00, 30, 25, '2024-02-10', 'Michael Omondi', 'Cybersecurity fundamentals and best practices', 'https://meet.chania.africa/cyber-basics-feb2024', NULL),
(15, 'March Ethical Hacking', '2024-03-20', '2024-06-20', 'Strathmore University', 'physical', 22000.00, 26000.00, 20, 18, '2024-03-15', 'Kevin Njuguna', 'Ethical hacking and penetration testing', NULL, 'Strathmore University, Information Technology Department'),
(15, 'April Network Security', '2024-04-10', '2024-07-10', 'Online', 'online', 20000.00, 25000.00, 25, 15, '2024-04-05', 'Engineer Sarah Kiprotich', 'Network security and infrastructure protection', 'https://meet.chania.africa/network-security-apr2024', NULL),
(15, 'May Incident Response', '2024-05-25', '2024-08-25', 'Kenya School of Government', 'physical', 21000.00, 25000.00, 22, 8, '2024-05-20', 'Captain James Mwangi', 'Cyber incident response and forensics', NULL, 'Kenya School of Government, ICT Department'),
(15, 'June Cloud Security', '2024-06-15', '2024-09-15', 'Online', 'online', 23000.00, 28000.00, 18, 4, '2024-06-10', 'Dr. Grace Mutindi', 'Cloud security and compliance', 'https://meet.chania.africa/cloud-security-jun2024', NULL),

-- Sample Program (ID: 3) - Adding more schedules
(3, 'January Intensive', '2024-01-08', '2024-03-08', 'Online', 'online', 8000.00, 12000.00, 40, 35, '2024-01-03', 'Susan Wanjiku', 'Intensive 8-week program', 'https://meet.chania.africa/sample-jan2024', NULL),
(3, 'February Beginner Track', '2024-02-20', '2024-04-20', 'Nairobi Tech Hub', 'physical', 8000.00, 10000.00, 30, 25, '2024-02-15', 'Peter Kamau', 'Beginner-friendly program', NULL, 'Nairobi Tech Hub, Westlands, Nairobi'),
(3, 'March Advanced Track', '2024-03-15', '2024-05-15', 'Online', 'online', 10000.00, 14000.00, 25, 20, '2024-03-10', 'Mary Njoki', 'Advanced level program', 'https://meet.chania.africa/sample-advanced-mar2024', NULL),
(3, 'April Weekend Program', '2024-04-06', '2024-06-06', 'Mombasa Learning Center', 'physical', 8000.00, 11000.00, 35, 12, '2024-04-01', 'Ahmed Hassan', 'Weekend classes for working professionals', NULL, 'Mombasa Learning Center, Digo Road, Mombasa'),
(3, 'May Evening Classes', '2024-05-15', '2024-07-15', 'Online', 'online', 8000.00, 12000.00, 45, 8, '2024-05-10', 'Grace Chepkemei', 'Evening classes after work hours', 'https://meet.chania.africa/sample-evening-may2024', NULL);

-- Update some registration deadlines to future dates to keep them open
UPDATE program_schedules SET 
    registration_deadline = '2024-12-31', 
    is_open_for_registration = 1 
WHERE start_date > CURDATE() AND registration_deadline < CURDATE();

-- Add some completed past schedules for historical data
INSERT INTO program_schedules (program_id, title, start_date, end_date, location, delivery_mode, online_fee, physical_fee, max_participants, current_participants, registration_deadline, is_active, is_open_for_registration, instructor_name, session_notes) VALUES
-- Past completed programs
(5, 'October 2023 Cohort', '2023-10-15', '2024-01-15', 'Online', 'online', 15000.00, 20000.00, 30, 30, '2023-10-10', 1, 0, 'John Kiprotich', 'Successfully completed cohort with 100% graduation rate'),
(6, 'September 2023 Data Analytics', '2023-09-01', '2023-12-01', 'University of Nairobi', 'physical', 20000.00, 24000.00, 25, 23, '2023-08-25', 1, 0, 'Dr. Samuel Kimani', 'Excellent industry placement rate'),
(8, 'November 2023 Marketing', '2023-11-10', '2024-01-10', 'Online', 'online', 12000.00, 16000.00, 40, 38, '2023-11-05', 1, 0, 'Linda Akinyi', 'High engagement and practical projects'),
(10, 'August 2023 Startup Accelerator', '2023-08-15', '2023-11-15', 'iHub Nairobi', 'physical', 18000.00, 22000.00, 20, 20, '2023-08-10', 1, 0, 'Anthony Kuria', 'Several startups secured funding');

-- Future schedules that are not yet open for registration
INSERT INTO program_schedules (program_id, title, start_date, end_date, location, delivery_mode, online_fee, physical_fee, max_participants, current_participants, registration_deadline, is_active, is_open_for_registration, instructor_name, session_notes) VALUES
(5, 'September 2024 Advanced', '2024-09-15', '2024-12-15', 'Online', 'online', 17000.00, 22000.00, 25, 0, '2024-08-15', 1, 0, 'Senior Developer TBD', 'Advanced track with AI integration - Registration opens July 2024'),
(6, 'October 2024 AI Focus', '2024-10-01', '2025-01-01', 'JKUAT', 'physical', 25000.00, 30000.00, 20, 0, '2024-09-01', 1, 0, 'AI Expert TBD', 'Deep learning and neural networks - Registration opens August 2024'),
(7, 'August 2024 Mobile Games', '2024-08-20', '2024-11-20', 'Online', 'online', 19000.00, 24000.00, 15, 0, '2024-07-20', 1, 0, 'Game Dev Expert TBD', 'Mobile game development specialization - Registration opens June 2024');
