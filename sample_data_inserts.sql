-- Sample Data Inserts for Partners and Team Members Tables
-- This script provides realistic sample data for both tables

-- Insert sample data into partners table
INSERT INTO partners (name, description, website_url, logo_path, is_active, created_at, updated_at) VALUES
('Tourism Board of Crete', 'Official tourism organization promoting Crete as a premier destination with comprehensive travel resources and local insights.', 'https://www.incrediblecrete.gr', 'assets/images/partners/crete_tourism_board.png', 1, NOW(), NOW()),

('Chania Port Authority', 'Managing maritime operations and cruise ship arrivals in the historic Venetian harbor of Chania.', 'https://www.chaniaport.gr', 'assets/images/partners/chania_port.png', 1, NOW(), NOW()),

('Greek National Tourism Organization', 'The official body responsible for promoting Greece worldwide as a tourist destination.', 'https://www.visitgreece.gr', 'assets/images/partners/gnto_logo.png', 1, NOW(), NOW()),

('Chania Chamber of Commerce', 'Supporting local businesses and economic development in the Chania region.', 'https://www.chaniachamber.gr', 'assets/images/partners/chamber_commerce.png', 1, NOW(), NOW()),

('Aegean Airlines', 'Greece''s largest airline connecting Chania to major European destinations with regular flights.', 'https://www.aegeanair.com', 'assets/images/partners/aegean_airlines.png', 1, NOW(), NOW()),

('Balos Lagoon Tours', 'Premium boat excursions to the world-famous Balos Lagoon and Gramvousa Island.', 'https://www.balostours.com', 'assets/images/partners/balos_tours.png', 1, NOW(), NOW()),

('Minoan Lines', 'Ferry services connecting Crete to mainland Greece and other Greek islands.', 'https://www.minoan.gr', 'assets/images/partners/minoan_lines.png', 1, NOW(), NOW()),

('Chania Wine Roads', 'Promoting local wineries and wine tourism experiences in the Chania region.', 'https://www.chaniawineroads.gr', 'assets/images/partners/wine_roads.png', 1, NOW(), NOW()),

('Municipality of Chania', 'Local government body overseeing city development and cultural events.', 'https://www.chania.gr', 'assets/images/partners/municipality_chania.png', 1, NOW(), NOW()),

('Crete Golf Club', 'Premium golf resort offering championship courses with stunning Mediterranean views.', 'https://www.crete-golf.com', 'assets/images/partners/crete_golf.png', 1, NOW(), NOW()),

('Samaria Gorge National Park', 'Protected natural area featuring Europe''s longest gorge and unique biodiversity.', 'https://www.samaria-gorge.gr', 'assets/images/partners/samaria_park.png', 1, NOW(), NOW()),

('Chania Archaeological Museum', 'Preserving and showcasing the rich archaeological heritage of western Crete.', 'https://www.chaniamuseum.gr', 'assets/images/partners/archaeological_museum.png', 1, NOW(), NOW());

-- Insert sample data into team_members table
INSERT INTO team_members (name, position, bio, email, phone, social_links, status, created_at, updated_at) VALUES
('Maria Papadakis', 'Tourism Director', 'With over 15 years of experience in sustainable tourism development, Maria leads our strategic initiatives to promote Chania as a world-class destination while preserving its cultural heritage and natural beauty.', 'maria.papadakis@chania-tourism.gr', '+30 28210 92845', '{"linkedin": "https://linkedin.com/in/mariapapadakis", "twitter": "@MariaChania"}', 'active', NOW(), NOW()),

('Dimitris Stavrakakis', 'Cultural Heritage Manager', 'A passionate historian and archaeologist, Dimitris oversees the preservation and promotion of Chania''s rich Venetian and Ottoman heritage. He holds a PhD in Byzantine History from the University of Athens.', 'dimitris.stavrakakis@chania-tourism.gr', '+30 28210 92847', '{"linkedin": "https://linkedin.com/in/dimitrisstavrakakis", "academia": "https://athens.academia.edu/DimitrisStavrakakis"}', 'active', NOW(), NOW()),

('Elena Koutsoudaki', 'Marketing & Communications Specialist', 'Elena develops innovative digital marketing strategies to showcase Chania''s unique attractions. Her expertise in social media and content creation has significantly increased our international visibility.', 'elena.koutsoudaki@chania-tourism.gr', '+30 28210 92849', '{"linkedin": "https://linkedin.com/in/elenakoutsoudaki", "instagram": "@ChaniaOfficial", "facebook": "ChaniaGreece"}', 'active', NOW(), NOW()),

('Nikos Manolakis', 'Sustainable Tourism Coordinator', 'Nikos champions eco-friendly tourism practices and works closely with local communities to ensure tourism development benefits everyone. He specializes in environmental impact assessment and green certification programs.', 'nikos.manolakis@chania-tourism.gr', '+30 28210 92851', '{"linkedin": "https://linkedin.com/in/nikosmanolakis", "twitter": "@EcoTourismCrete"}', 'active', NOW(), NOW()),

('Sophia Venetis', 'Events & Festivals Manager', 'Sophia coordinates Chania''s vibrant cultural calendar, from the renowned Film Festival to traditional religious celebrations. Her background in event management and local culture creates unforgettable experiences for visitors.', 'sophia.venetis@chania-tourism.gr', '+30 28210 92853', '{"linkedin": "https://linkedin.com/in/sophiavenetis", "facebook": "ChaniaEvents"}', 'active', NOW(), NOW()),

('Yannis Kazantzakis', 'Business Development Manager', 'Yannis fosters partnerships with hotels, restaurants, and tour operators to enhance visitor experiences. His extensive network in the hospitality industry helps create comprehensive tourism packages.', 'yannis.kazantzakis@chania-tourism.gr', '+30 28210 92855', '{"linkedin": "https://linkedin.com/in/yanniskazantzakis"}', 'active', NOW(), NOW()),

('Christina Alexandrou', 'Visitor Information Coordinator', 'Christina manages our visitor information centers and ensures tourists receive accurate, up-to-date information about attractions, transportation, and local services. She speaks five languages fluently.', 'christina.alexandrou@chania-tourism.gr', '+30 28210 92857', '{"linkedin": "https://linkedin.com/in/christinaalexandrou"}', 'active', NOW(), NOW()),

('Manolis Tsouderos', 'Digital Innovation Manager', 'Manolis leads our digital transformation initiatives, developing mobile apps, virtual tours, and smart city solutions to enhance the visitor experience. He has a background in computer science and UX design.', 'manolis.tsouderos@chania-tourism.gr', '+30 28210 92859', '{"linkedin": "https://linkedin.com/in/manolitsouderos", "github": "mtsouderos", "twitter": "@DigitalChania"}', 'active', NOW(), NOW()),

('Anna Michalogiannaki', 'Research & Development Analyst', 'Anna conducts market research and analyzes tourism trends to guide our strategic planning. Her data-driven insights help optimize marketing campaigns and identify emerging opportunities in the tourism sector.', 'anna.michalogiannaki@chania-tourism.gr', '+30 28210 92861', '{"linkedin": "https://linkedin.com/in/annamichalogiannaki"}', 'active', NOW(), NOW()),

('Kostas Papanikolas', 'Transportation & Logistics Coordinator', 'Kostas works with airlines, ferry companies, and local transport providers to ensure seamless connectivity to and within Chania. His logistics expertise helps visitors navigate the region efficiently.', 'kostas.papanikolas@chania-tourism.gr', '+30 28210 92863', '{"linkedin": "https://linkedin.com/in/kostaspapanikolas"}', 'active', NOW(), NOW());

-- Additional sample entries for testing pagination and filters
INSERT INTO team_members (name, position, bio, email, phone, social_links, status, created_at, updated_at) VALUES
('George Antoniou', 'Junior Marketing Assistant', 'Recent graduate from the University of Crete with a degree in Tourism Management. George assists with social media content creation and market research projects.', 'george.antoniou@chania-tourism.gr', '+30 28210 92865', '{"linkedin": "https://linkedin.com/in/georgeantoniou", "instagram": "@GeorgeChania"}', 'active', NOW() - INTERVAL 3 MONTH, NOW()),

('Irene Papadopoulou', 'Former Cultural Events Manager', 'Irene managed cultural events and festivals for over 8 years before relocating to Athens. Her innovative approaches to cultural promotion helped establish many of our signature events.', 'irene.papadopoulou@gmail.com', '+30 210 1234567', '{"linkedin": "https://linkedin.com/in/irenepapadopoulou"}', 'inactive', NOW() - INTERVAL 6 MONTH, NOW() - INTERVAL 2 MONTH);

-- Update the created_at timestamps to show variety in hiring dates
UPDATE team_members SET created_at = NOW() - INTERVAL 5 YEAR WHERE name = 'Maria Papadakis';
UPDATE team_members SET created_at = NOW() - INTERVAL 4 YEAR WHERE name = 'Dimitris Stavrakakis';
UPDATE team_members SET created_at = NOW() - INTERVAL 3 YEAR WHERE name = 'Elena Koutsoudaki';
UPDATE team_members SET created_at = NOW() - INTERVAL 3 YEAR WHERE name = 'Nikos Manolakis';
UPDATE team_members SET created_at = NOW() - INTERVAL 2 YEAR WHERE name = 'Sophia Venetis';
UPDATE team_members SET created_at = NOW() - INTERVAL 2 YEAR WHERE name = 'Yannis Kazantzakis';
UPDATE team_members SET created_at = NOW() - INTERVAL 1 YEAR WHERE name = 'Christina Alexandrou';
UPDATE team_members SET created_at = NOW() - INTERVAL 1 YEAR WHERE name = 'Manolis Tsouderos';
UPDATE team_members SET created_at = NOW() - INTERVAL 8 MONTH WHERE name = 'Anna Michalogiannaki';
UPDATE team_members SET created_at = NOW() - INTERVAL 6 MONTH WHERE name = 'Kostas Papanikolas';

-- Verify the inserts with sample queries
-- SELECT COUNT(*) as partner_count FROM partners WHERE is_active = 1;
-- SELECT COUNT(*) as active_team_count FROM team_members WHERE status = 'active';
-- SELECT name, position, email FROM team_members WHERE status = 'active' ORDER BY created_at DESC;
