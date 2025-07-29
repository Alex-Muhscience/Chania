-- Sample Data Insert Script for Chania Skills for Africa
-- Database: chania_db
-- Tables: partners, team_members

USE chania_db;

-- Clear existing data (optional - uncomment if you want to start fresh)
-- DELETE FROM partners;
-- DELETE FROM team_members;
-- ALTER TABLE partners AUTO_INCREMENT = 1;
-- ALTER TABLE team_members AUTO_INCREMENT = 1;

-- Insert sample partners
INSERT INTO partners (name, description, website_url, logo_path, contact_person, contact_email, contact_phone, partnership_type, status, created_at, updated_at) VALUES
('Kenya Association of Manufacturers', 'Leading industrial association promoting manufacturing excellence in Kenya. They provide training opportunities and job placements for our graduates in the manufacturing sector.', 'https://kam.co.ke', 'uploads/partners/kam_logo.png', 'Sarah Mwangi', 'partnerships@kam.co.ke', '+254712345678', 'Training Partner', 'active', NOW(), NOW()),

('Safaricom Foundation', 'The corporate social investment arm of Safaricom, focused on education, health, and economic empowerment initiatives across Kenya.', 'https://safaricomfoundation.org', 'uploads/partners/safaricom_foundation.png', 'John Kiprotich', 'programs@safaricomfoundation.org', '+254722345678', 'Funding Partner', 'active', NOW(), NOW()),

('Technical University of Kenya', 'Premier technical university offering cutting-edge engineering and technology programs. Our academic partner for advanced skill certification.', 'https://tukenya.ac.ke', 'uploads/partners/tuk_logo.png', 'Dr. Margaret Chemutai', 'm.chemutai@tukenya.ac.ke', '+254733456789', 'Academic Partner', 'active', NOW(), NOW()),

('Microsoft Kenya', 'Technology leader providing digital skills training and certification programs. Partnership includes Azure cloud training and Microsoft Office certifications.', 'https://microsoft.com/kenya', 'uploads/partners/microsoft_logo.png', 'David Ochieng', 'partnerships@microsoft.com', '+254744567890', 'Technology Partner', 'active', NOW(), NOW()),

('Equity Bank Foundation', 'Financial inclusion champion supporting entrepreneurship and skills development programs across East Africa.', 'https://equitybankfoundation.com', 'uploads/partners/equity_foundation.png', 'Grace Wanjiku', 'foundation@equitybank.co.ke', '+254755678901', 'Funding Partner', 'active', NOW(), NOW()),

('Kenya Private Sector Alliance', 'Umbrella body representing private sector interests and facilitating public-private partnerships for economic development.', 'https://kepsa.or.ke', 'uploads/partners/kepsa_logo.png', 'Robert Mwangi', 'partnerships@kepsa.or.ke', '+254766789012', 'Industry Partner', 'active', NOW(), NOW()),

('Google Developer Community', 'Global network of developers and tech enthusiasts. Partnership includes Android development training and Google Cloud certifications.', 'https://developers.google.com/community', 'uploads/partners/google_dev.png', 'Alice Njeri', 'community@google.com', '+254777890123', 'Technology Partner', 'active', NOW(), NOW()),

('UN Women Kenya', 'United Nations entity dedicated to gender equality and women empowerment. Focus on supporting women in technology and entrepreneurship.', 'https://kenya.unwomen.org', 'uploads/partners/unwomen_logo.png', 'Dr. Fatuma Hassan', 'partnerships@unwomen.org', '+254788901234', 'Development Partner', 'active', NOW(), NOW());

-- Insert sample team members
INSERT INTO team_members (name, position, bio, email, phone, social_links, image_path, status, created_at, updated_at) VALUES
('Dr. James Mwangi', 'Executive Director', 'Dr. Mwangi brings over 15 years of experience in vocational training and skills development. He holds a PhD in Education Policy from the University of Nairobi and has worked extensively with international development organizations across East Africa. His vision is to bridge the skills gap and create sustainable employment opportunities for African youth.', 'j.mwangi@skillsforafrica.org', '+254712000001', '{"linkedin": "https://linkedin.com/in/james-mwangi-skills", "twitter": "https://twitter.com/jamesmwangi_ed"}', 'uploads/team/james_mwangi.jpg', 'active', NOW(), NOW()),

('Grace Wanjiru', 'Programs Director', 'Grace is a seasoned program manager with expertise in curriculum development and partnership management. She has successfully managed skills training programs reaching over 5,000 beneficiaries. Grace holds an MSc in Development Studies and is passionate about youth empowerment through practical skills training.', 'g.wanjiru@skillsforafrica.org', '+254723000002', '{"linkedin": "https://linkedin.com/in/grace-wanjiru-dev", "facebook": "https://facebook.com/grace.wanjiru.programs"}', 'uploads/team/grace_wanjiru.jpg', 'active', NOW(), NOW()),

('Michael Ochieng', 'Technical Training Manager', 'Michael is a certified master trainer with expertise in automotive, welding, and electrical installations. With over 12 years in technical education, he has trained hundreds of young people who are now successfully employed. He holds certifications from City & Guilds and is committed to maintaining international training standards.', 'm.ochieng@skillsforafrica.org', '+254734000003', '{"linkedin": "https://linkedin.com/in/michael-ochieng-technical"}', 'uploads/team/michael_ochieng.jpg', 'active', NOW(), NOW()),

('Sarah Akinyi', 'Finance & Operations Manager', 'Sarah oversees all financial operations and ensures efficient program delivery. She is a CPA with 10 years of experience in non-profit financial management. Sarah has implemented robust financial systems that have improved transparency and accountability in our programs.', 's.akinyi@skillsforafrica.org', '+254745000004', '{"linkedin": "https://linkedin.com/in/sarah-akinyi-cpa"}', 'uploads/team/sarah_akinyi.jpg', 'active', NOW(), NOW()),

('David Kiplagat', 'ICT Training Specialist', 'David leads our digital skills training programs including web development, mobile app development, and digital marketing. He holds certifications in multiple programming languages and cloud platforms. David has trained over 800 young people in ICT skills, with 85% finding employment within 6 months of graduation.', 'd.kiplagat@skillsforafrica.org', '+254756000005', '{"linkedin": "https://linkedin.com/in/david-kiplagat-ict", "github": "https://github.com/davidkiplagat", "twitter": "https://twitter.com/davidkiplagat_tech"}', 'uploads/team/david_kiplagat.jpg', 'active', NOW(), NOW()),

('Lucy Wambui', 'Career Counselor & Job Placement Officer', 'Lucy specializes in career guidance and job placement services. She has established strong networks with over 200 employers and maintains an 80% job placement rate for our graduates. Lucy holds a degree in Psychology and certification in career counseling.', 'l.wambui@skillsforafrica.org', '+254767000006', '{"linkedin": "https://linkedin.com/in/lucy-wambui-careers"}', 'uploads/team/lucy_wambui.jpg', 'active', NOW(), NOW()),

('Peter Mutua', 'Business Skills Trainer', 'Peter leads our entrepreneurship and business skills programs. He has helped over 300 young entrepreneurs start and grow their businesses. With an MBA in Entrepreneurship and 8 years of experience in SME development, Peter brings practical business insights to our training programs.', 'p.mutua@skillsforafrica.org', '+254778000007', '{"linkedin": "https://linkedin.com/in/peter-mutua-business", "twitter": "https://twitter.com/petermutua_biz"}', 'uploads/team/peter_mutua.jpg', 'active', NOW(), NOW()),

('Rebecca Chebet', 'Marketing & Communications Manager', 'Rebecca manages all marketing communications and stakeholder engagement. She has over 7 years of experience in digital marketing and public relations. Rebecca has successfully increased our program visibility and attracted partnerships worth over $500,000.', 'r.chebet@skillsforafrica.org', '+254789000008', '{"linkedin": "https://linkedin.com/in/rebecca-chebet-comms", "twitter": "https://twitter.com/rebeccachebet_pr", "instagram": "https://instagram.com/skillsforafrica_rebecca"}', 'uploads/team/rebecca_chebet.jpg', 'active', NOW(), NOW());

-- Verify the insertions
SELECT 'Partners inserted:' as Info;
SELECT COUNT(*) as Total_Partners FROM partners;
SELECT name, partnership_type, status FROM partners;

SELECT 'Team Members inserted:' as Info;
SELECT COUNT(*) as Total_Team_Members FROM team_members;
SELECT name, position, status FROM team_members;

-- End of script
