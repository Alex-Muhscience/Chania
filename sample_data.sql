-- Sample data for the Chania Database
-- This script adds realistic sample data to partners and team_members tables

USE chania_db;

-- Insert sample partners
INSERT INTO partners (name, logo_url, website_url, description, display_order, is_active, created_at, updated_at) VALUES
('Tech Innovations Corp', 'assets/images/partners/tech-innovations.png', 'https://techinnovations.com', 'Leading technology solutions provider specializing in digital transformation and software development.', 1, TRUE, NOW(), NOW()),
('Global Education Network', 'assets/images/partners/global-education.png', 'https://globaleducation.org', 'International educational organization promoting learning opportunities and cultural exchange programs.', 2, TRUE, NOW(), NOW()),
('Mediterranean Business Alliance', 'assets/images/partners/med-business.png', 'https://medalliance.com', 'Regional business network connecting entrepreneurs and companies across the Mediterranean region.', 3, TRUE, NOW(), NOW()),
('Green Future Foundation', 'assets/images/partners/green-future.png', 'https://greenfuture.org', 'Environmental organization dedicated to sustainability initiatives and climate action programs.', 4, TRUE, NOW(), NOW()),
('Digital Marketing Hub', 'assets/images/partners/digital-hub.png', 'https://digitalhub.com', 'Professional marketing agency specializing in digital strategies and online brand development.', 5, TRUE, NOW(), NOW()),
('Innovation Labs Ltd', 'assets/images/partners/innovation-labs.png', 'https://innovationlabs.com', 'Research and development company focusing on emerging technologies and startup incubation.', 6, TRUE, NOW(), NOW());

-- Insert sample team members
INSERT INTO team_members (first_name, last_name, position, bio, photo_url, email, linkedin_url, display_order, is_active, created_at, updated_at) VALUES
('Maria', 'Alexandrou', 'Executive Director', 'Maria brings over 15 years of experience in international business development and has led numerous successful projects across Europe and the Mediterranean. She holds an MBA from Athens University of Economics and is passionate about fostering cross-cultural business relationships.', 'assets/images/team/maria-alexandrou.jpg', 'maria@chania.com', 'https://linkedin.com/in/maria-alexandrou', 1, TRUE, NOW(), NOW()),
('Dimitris', 'Kostas', 'Program Manager', 'Dimitris specializes in program development and implementation with a focus on educational initiatives. With a background in project management and a Master\'s degree in Education, he ensures all programs meet the highest standards of quality and effectiveness.', 'assets/images/team/dimitris-kostas.jpg', 'dimitris@chania.com', 'https://linkedin.com/in/dimitris-kostas', 2, TRUE, NOW(), NOW()),
('Elena', 'Papadopoulos', 'Marketing Director', 'Elena leads our marketing efforts with creativity and strategic insight. She has a proven track record in digital marketing and brand management, helping organizations reach their target audiences effectively across multiple channels.', 'assets/images/team/elena-papadopoulos.jpg', 'elena@chania.com', 'https://linkedin.com/in/elena-papadopoulos', 3, TRUE, NOW(), NOW()),
('Andreas', 'Michaelis', 'Technology Coordinator', 'Andreas oversees all technical aspects of our operations, from website management to digital infrastructure. With expertise in web development and IT systems, he ensures our technology supports our mission efficiently and securely.', 'assets/images/team/andreas-michaelis.jpg', 'andreas@chania.com', 'https://linkedin.com/in/andreas-michaelis', 4, TRUE, NOW(), NOW()),
('Sofia', 'Georgiou', 'Community Outreach Specialist', 'Sofia manages community engagement and stakeholder relationships. Her background in communications and public relations helps build strong connections with partners, participants, and the broader community we serve.', 'assets/images/team/sofia-georgiou.jpg', 'sofia@chania.com', 'https://linkedin.com/in/sofia-georgiou', 5, TRUE, NOW(), NOW()),
('Nikos', 'Stavros', 'Financial Advisor', 'Nikos handles financial planning and budget management with precision and expertise. As a certified accountant with experience in non-profit organizations, he ensures our financial resources are managed responsibly and transparently.', 'assets/images/team/nikos-stavros.jpg', 'nikos@chania.com', 'https://linkedin.com/in/nikos-stavros', 6, TRUE, NOW(), NOW());

-- Verify the inserts
SELECT 'Partners inserted:' as Info, COUNT(*) as Count FROM partners WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE);
SELECT 'Team members inserted:' as Info, COUNT(*) as Count FROM team_members WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE);
