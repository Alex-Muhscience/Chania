<?php
$pageTitle = 'Skills for Africa - Empowering African Youth with Digital Skills';
$pageDescription = 'Transform your future with our premium digital skills training programs designed for African youth. Join thousands who have launched successful careers in technology.';
$activePage = 'home';

require_once __DIR__ . '/../includes/config.php';

// Initialize arrays with fallback data
$featuredPrograms = [];
$upcomingEvents = [];
$testimonials = [];

try {
    // Fetch featured programs with error handling
    $stmt = $db->query("
        SELECT id, title, short_description, image_path, category, duration, is_featured 
        FROM programs 
        WHERE is_featured = 1 AND deleted_at IS NULL 
        LIMIT 3
    ");
    $featuredPrograms = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Featured programs fetch error: " . $e->getMessage());
    // Fallback data
    $featuredPrograms = [
        [
            'id' => 1,
            'title' => 'Web Development Bootcamp',
            'short_description' => 'Learn modern web development with HTML, CSS, JavaScript, and React.',
            'image_path' => 'placeholder.jpg',
            'category' => 'Technology',
            'duration' => '12 weeks'
        ],
        [
            'id' => 2,
            'title' => 'Digital Marketing Mastery',
            'short_description' => 'Master digital marketing strategies and tools for the modern business world.',
            'image_path' => 'placeholder.jpg',
            'category' => 'Marketing',
            'duration' => '8 weeks'
        ],
        [
            'id' => 3,
            'title' => 'Data Analytics Fundamentals',
            'short_description' => 'Learn to analyze data and make data-driven decisions using modern tools.',
            'image_path' => 'placeholder.jpg',
            'category' => 'Data Science',
            'duration' => '10 weeks'
        ]
    ];
}

try {
    // Fetch upcoming events with error handling
    $stmt = $db->query("
        SELECT id, title, short_description, image_path, event_date, location 
        FROM events 
        WHERE event_date >= NOW() AND is_active = 1 AND deleted_at IS NULL 
        ORDER BY event_date ASC 
        LIMIT 2
    ");
    $upcomingEvents = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Upcoming events fetch error: " . $e->getMessage());
    // Fallback data
    $upcomingEvents = [
        [
            'id' => 1,
            'title' => 'Tech Career Workshop',
            'short_description' => 'Learn about career opportunities in the tech industry.',
            'image_path' => 'placeholder.jpg',
            'event_date' => date('Y-m-d H:i:s', strtotime('+1 week')),
            'location' => 'Nairobi, Kenya'
        ],
        [
            'id' => 2,
            'title' => 'Digital Skills Bootcamp',
            'short_description' => 'Intensive training on essential digital skills for the workforce.',
            'image_path' => 'placeholder.jpg',
            'event_date' => date('Y-m-d H:i:s', strtotime('+2 weeks')),
            'location' => 'Online'
        ]
    ];
}

try {
    // Fetch featured testimonials with error handling
    $stmt = $db->query("
        SELECT author_name, author_title, content, image_path 
        FROM testimonials 
        WHERE is_featured = 1 AND deleted_at IS NULL 
        LIMIT 5
    ");
    $testimonials = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Testimonials fetch error: " . $e->getMessage());
    // Fallback data
    $testimonials = [
        [
            'author_name' => 'Sarah Mwangi',
            'author_title' => 'Web Developer at TechCorp',
            'content' => 'The program transformed my career. I went from having no tech experience to landing my dream job as a web developer.',
            'image_path' => 'placeholder.jpg'
        ],
        [
            'author_name' => 'James Ochieng',
            'author_title' => 'Digital Marketing Specialist',
            'content' => 'The digital marketing course gave me practical skills that I use every day in my current role.',
            'image_path' => 'placeholder.jpg'
        ],
        [
            'author_name' => 'Grace Akinyi',
            'author_title' => 'Data Analyst at FinTech Ltd',
            'content' => 'I learned so much about data analysis and visualization. The instructors were amazing and very supportive.',
            'image_path' => 'placeholder.jpg'
        ]
    ];
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Premium Styling -->
<style>
:root {
    --color-primary: #D8C99B;
    --color-secondary: #273E47;
    --color-accent: #D8973C;
    --color-white: #FFFFFF;
    --color-light: #F8F9FA;
    --color-dark: #1a1a1a;
    --color-success: #28a745;
    --color-info: #17a2b8;
    --color-warning: #ffc107;
    --color-danger: #dc3545;

    --gradient-primary: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);
    --gradient-secondary: linear-gradient(135deg, var(--color-secondary) 0%, #1a2a32 100%);
    --gradient-overlay: linear-gradient(135deg, rgba(39, 62, 71, 0.9) 0%, rgba(26, 42, 50, 0.8) 100%);

    --shadow-sm: 0 2px 4px rgba(39, 62, 71, 0.1);
    --shadow-md: 0 4px 12px rgba(39, 62, 71, 0.15);
    --shadow-lg: 0 8px 25px rgba(39, 62, 71, 0.2);
    --shadow-xl: 0 12px 40px rgba(39, 62, 71, 0.25);

    --border-radius: 12px;
    --border-radius-lg: 20px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: var(--color-secondary);
    overflow-x: hidden;
}

/* Premium Buttons */
.btn-premium {
    padding: 14px 32px;
    border: none;
    border-radius: var(--border-radius);
    font-weight: 600;
    font-size: 16px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    position: relative;
    overflow: hidden;
    transition: var(--transition);
    cursor: pointer;
    backdrop-filter: blur(10px);
}

.btn-premium::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.6s;
}

.btn-premium:hover::before {
    left: 100%;
}

.btn-primary-premium {
    background: var(--gradient-primary);
    color: var(--color-secondary);
    box-shadow: var(--shadow-md);
}

.btn-primary-premium:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    color: var(--color-secondary);
}

.btn-secondary-premium {
    background: var(--gradient-secondary);
    color: var(--color-white);
    box-shadow: var(--shadow-md);
}

.btn-secondary-premium:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    color: var(--color-white);
}

.btn-outline-premium {
    background: rgba(216, 201, 155, 0.1);
    color: var(--color-primary);
    border: 2px solid var(--color-primary);
    backdrop-filter: blur(10px);
}

.btn-outline-premium:hover {
    background: var(--color-primary);
    color: var(--color-secondary);
    transform: translateY(-2px);
}

/* Premium Cards */
.card-premium {
    background: var(--color-white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(216, 201, 155, 0.2);
    overflow: hidden;
    transition: var(--transition);
    position: relative;
}

.card-premium::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.card-premium:hover::before {
    transform: scaleX(1);
}

.card-premium:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

/* Search Bar Styles */
.search-container {
    position: relative;
    max-width: 600px;
    margin: 0 auto;
}

.search-input {
    width: 100%;
    padding: 16px 60px 16px 24px;
    border: 2px solid rgba(216, 201, 155, 0.3);
    border-radius: 50px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    font-size: 16px;
    color: var(--color-secondary);
    transition: var(--transition);
}

.search-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 4px rgba(216, 201, 155, 0.2);
}

.search-btn {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--gradient-primary);
    border: none;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-secondary);
    cursor: pointer;
    transition: var(--transition);
}

.search-btn:hover {
    transform: translateY(-50%) scale(1.1);
}

/* Hero Section */
.hero-section {
    min-height: 100vh;
    background: var(--gradient-secondary);
    position: relative;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23D8C99B" opacity="0.1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    position: relative;
    z-index: 2;
    padding: 80px 0;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(216, 201, 155, 0.2);
    color: var(--color-primary);
    padding: 8px 20px;
    border-radius: 50px;
    border: 1px solid rgba(216, 201, 155, 0.3);
    backdrop-filter: blur(10px);
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 24px;
    animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(216, 201, 155, 0.4); }
    50% { box-shadow: 0 0 0 10px rgba(216, 201, 155, 0); }
}

.hero-title {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 800;
    line-height: 1.2;
    color: var(--color-white);
    margin-bottom: 24px;
}

.hero-title .accent {
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-description {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 32px;
    max-width: 600px;
}

.hero-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 32px;
    margin: 48px 0;
}

.stat-item {
    text-align: center;
    padding: 24px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(216, 201, 155, 0.2);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--color-primary);
    margin-bottom: 8px;
}

.stat-label {
    color: rgba(255, 255, 255, 0.7);
    font-size: 14px;
    font-weight: 500;
}

.hero-image {
    position: relative;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}

.hero-image img {
    width: 100%;
    height: auto;
    display: block;
}

/* Floating Elements */
.floating-element {
    position: absolute;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    padding: 16px;
    box-shadow: var(--shadow-lg);
    animation: float 6s ease-in-out infinite;
}

.floating-element-1 {
    top: 20%;
    right: -20px;
    animation-delay: 0s;
}

.floating-element-2 {
    bottom: 30%;
    left: -20px;
    animation-delay: 2s;
}

.floating-element-3 {
    top: 60%;
    right: -30px;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

/* Section Styling */
.section-premium {
    padding: 100px 0;
    position: relative;
}

.section-title {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 800;
    color: var(--color-secondary);
    margin-bottom: 16px;
    text-align: center;
}

.section-subtitle {
    font-size: 18px;
    color: var(--color-secondary);
    opacity: 0.7;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 16px;
    text-align: center;
}

.section-description {
    font-size: 1.125rem;
    color: var(--color-secondary);
    opacity: 0.8;
    text-align: center;
    max-width: 600px;
    margin: 0 auto 60px;
}

/* Program Cards */
.program-card {
    height: 100%;
    transition: var(--transition);
}

.program-image {
    height: 240px;
    object-fit: cover;
    width: 100%;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.program-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    background: var(--gradient-primary);
    color: var(--color-secondary);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.program-duration {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(216, 201, 155, 0.1);
    color: var(--color-accent);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

/* Event Cards */
.event-card {
    border-left: 4px solid var(--color-primary);
    transition: var(--transition);
}

.event-card:hover {
    border-left-color: var(--color-accent);
    transform: translateX(8px);
}

.event-date {
    background: var(--gradient-primary);
    color: var(--color-secondary);
    border-radius: var(--border-radius);
    padding: 16px;
    text-align: center;
    font-weight: 700;
}

.event-day {
    font-size: 2rem;
    line-height: 1;
}

.event-month {
    font-size: 14px;
    text-transform: uppercase;
}

/* Testimonial Carousel */
.testimonial-section {
    background: var(--color-light);
    position: relative;
}

.testimonial-card {
    text-align: center;
    padding: 60px 40px;
    max-width: 800px;
    margin: 0 auto;
}

.testimonial-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 24px;
    border: 4px solid var(--color-primary);
}

.testimonial-quote {
    font-size: 1.25rem;
    font-style: italic;
    color: var(--color-secondary);
    margin-bottom: 32px;
    position: relative;
}

.testimonial-quote::before,
.testimonial-quote::after {
    content: '"';
    font-size: 4rem;
    color: var(--color-primary);
    position: absolute;
    top: -20px;
}

.testimonial-quote::before {
    left: -40px;
}

.testimonial-quote::after {
    right: -40px;
}

.testimonial-author {
    font-weight: 700;
    color: var(--color-secondary);
    margin-bottom: 8px;
}

.testimonial-role {
    color: var(--color-accent);
    font-weight: 600;
}

.testimonial-stars {
    color: var(--color-accent);
    margin-top: 16px;
}

/* CTA Section */
.cta-section {
    background: var(--gradient-secondary);
    color: var(--color-white);
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="cta-gradient"><stop offset="0%" stop-color="%23D8C99B" stop-opacity="0.1"/><stop offset="100%" stop-color="transparent"/></radialGradient></defs><circle cx="500" cy="500" r="400" fill="url(%23cta-gradient)"/></svg>');
}

.cta-content {
    position: relative;
    z-index: 2;
    text-align: center;
}

.cta-title {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 800;
    margin-bottom: 24px;
}

.cta-description {
    font-size: 1.25rem;
    opacity: 0.9;
    margin-bottom: 48px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

/* Newsletter */
.newsletter-section {
    background: var(--color-light);
}

.newsletter-form {
    display: flex;
    gap: 16px;
    max-width: 500px;
    margin: 0 auto;
}

.newsletter-input {
    flex: 1;
    padding: 16px 24px;
    border: 2px solid rgba(216, 201, 155, 0.3);
    border-radius: 50px;
    background: var(--color-white);
    font-size: 16px;
    transition: var(--transition);
}

.newsletter-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 4px rgba(216, 201, 155, 0.2);
}

/* Animations */
.fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
    opacity: 0;
    transform: translateY(30px);
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-delay-1 { animation-delay: 0.1s; }
.animate-delay-2 { animation-delay: 0.2s; }
.animate-delay-3 { animation-delay: 0.3s; }
.animate-delay-4 { animation-delay: 0.4s; }

/* Responsive Design */
@media (max-width: 768px) {
    .hero-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .btn-premium {
        padding: 12px 24px;
        font-size: 14px;
    }

    .newsletter-form {
        flex-direction: column;
    }

    .floating-element {
        display: none;
    }
}

@media (max-width: 576px) {
    .hero-stats {
        grid-template-columns: 1fr;
    }

    .testimonial-quote::before,
    .testimonial-quote::after {
        display: none;
    }
}
</style>

<!-- Premium Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-2 order-lg-1">
                <div class="hero-content">
                    <div class="hero-badge fade-in-up">
                        <i class="fas fa-rocket"></i>
                        <span>Transform Your Future</span>
                    </div>
                    <h1 class="hero-title fade-in-up animate-delay-1">
                        Empowering African Youth for the
                        <span class="accent">Digital Economy</span>
                    </h1>
                    <p class="hero-description fade-in-up animate-delay-2">
                        We provide world-class training programs to equip young Africans with in-demand digital skills through cutting-edge technology and industry expertise.
                    </p>

                    <!-- Premium Search Bar -->
                    <div class="search-container fade-in-up animate-delay-3 mb-4">
                        <form id="programSearchForm" action="<?php echo BASE_URL; ?>/client/public/programs.php" method="GET">
                            <input type="text"
                                   name="search"
                                   class="search-input"
                                   placeholder="Search programs, skills, or categories..."
                                   id="programSearch">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                        <div id="searchSuggestions" class="search-suggestions" style="display: none;"></div>
                    </div>

                    <div class="hero-stats fade-in-up animate-delay-3">
                        <div class="stat-item">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Graduates</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">50+</div>
                            <div class="stat-label">Programs</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">95%</div>
                            <div class="stat-label">Success Rate</div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-3 fade-in-up animate-delay-4">
                        <a href="<?php echo BASE_URL; ?>/client/public/apply.php"
                           class="btn-premium btn-primary-premium">
                            <i class="fas fa-rocket"></i>
                            Apply Now
                        </a>
                        <a href="<?php echo BASE_URL; ?>/client/public/programs.php"
                           class="btn-premium btn-outline-premium">
                            <i class="fas fa-compass"></i>
                            Explore Programs
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 order-1 order-lg-2 mb-4 mb-lg-0">
                <div class="hero-image position-relative fade-in-up animate-delay-2">
                    <img src="<?php echo BASE_URL; ?>/client/public/assets/images/hero-image.jpg"
                         alt="Students learning digital skills"
                         class="img-fluid">

                    <!-- Floating Elements -->
                    <div class="floating-element floating-element-1">
                        <i class="fas fa-code text-primary mb-2 d-block"></i>
                        <small class="fw-bold">Web Development</small>
                    </div>
                    <div class="floating-element floating-element-2">
                        <i class="fas fa-chart-bar text-success mb-2 d-block"></i>
                        <small class="fw-bold">Data Analytics</small>
                    </div>
                    <div class="floating-element floating-element-3">
                        <i class="fas fa-mobile-alt text-info mb-2 d-block"></i>
                        <small class="fw-bold">Mobile Apps</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="section-premium">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="section-subtitle">Our Purpose</div>
                <h2 class="section-title text-start">Empowering Africa's Digital Future</h2>
                <p class="section-description text-start">
                    To bridge the digital skills gap in Africa by providing accessible, high-quality training programs that prepare youth for the jobs of tomorrow.
                </p>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="card-premium text-center p-3">
                            <i class="fas fa-users text-primary mb-2 fa-2x"></i>
                            <h4 class="fw-bold mb-0">1000+</h4>
                            <small class="text-muted">Students Trained</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card-premium text-center p-3">
                            <i class="fas fa-briefcase text-success mb-2 fa-2x"></i>
                            <h4 class="fw-bold mb-0">85%</h4>
                            <small class="text-muted">Job Placement</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row gap-3">
                    <a href="<?php echo BASE_URL; ?>/client/public/about.php"
                       class="btn-premium btn-primary-premium">
                        <i class="fas fa-info-circle"></i>
                        Learn More About Us
                    </a>
                    <a href="<?php echo BASE_URL; ?>/client/public/contact.php"
                       class="btn-premium btn-outline-premium">
                        <i class="fas fa-envelope"></i>
                        Get In Touch
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="position-relative">
                    <div class="card-premium overflow-hidden">
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                                    title="About Skills for Africa"
                                    allowfullscreen
                                    class="rounded"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Programs Section -->
<section class="section-premium" style="background: var(--color-light);">
    <div class="container">
        <div class="section-subtitle">Our Training</div>
        <h2 class="section-title">Featured Programs</h2>
        <p class="section-description">
            Discover our most popular digital skills training programs designed for career transformation
        </p>

        <div class="row g-4">
            <?php foreach ($featuredPrograms as $index => $program): ?>
            <div class="col-lg-4 col-md-6 fade-in-up animate-delay-<?= $index + 1 ?>">
                <div class="card-premium program-card">
                    <div class="position-relative">
                        <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($program['image_path'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                             class="program-image"
                             alt="<?php echo htmlspecialchars($program['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="program-badge">
                            <i class="fas fa-star me-1"></i>Featured
                        </div>
                    </div>
                    <div class="p-4">
                        <h5 class="fw-bold mb-3"><?php echo htmlspecialchars($program['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h5>
                        <p class="text-muted mb-4"><?php echo htmlspecialchars($program['short_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="program-duration">
                                <i class="fas fa-clock"></i>
                                <?php echo htmlspecialchars($program['duration'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <span class="badge" style="background: var(--color-accent); color: var(--color-white);">
                                <?php echo htmlspecialchars($program['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <a href="<?php echo BASE_URL . '/client/public/program_detail.php?id=' . $program['id']; ?>"
                           class="btn-premium btn-secondary-premium w-100">
                            <i class="fas fa-arrow-right"></i>
                            Explore Program
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?php echo BASE_URL; ?>/client/public/programs.php"
               class="btn-premium btn-primary-premium">
                <i class="fas fa-th-large"></i>
                View All Programs
                <span class="badge bg-white text-dark ms-2">50+</span>
            </a>
        </div>
    </div>
</section>

<!-- Upcoming Events Section -->
<section class="section-premium">
    <div class="container">
        <div class="section-subtitle">What's Happening</div>
        <h2 class="section-title">Upcoming Events</h2>
        <p class="section-description">
            Connect, learn, and grow with our community through exclusive events and workshops
        </p>

        <div class="row g-4">
            <?php foreach ($upcomingEvents as $index => $event): ?>
            <div class="col-lg-6 fade-in-up animate-delay-<?= $index + 1 ?>">
                <div class="card-premium event-card h-100">
                    <div class="row g-0 h-100">
                        <div class="col-md-4">
                            <div class="event-date h-100 d-flex flex-column justify-content-center">
                                <div class="event-day"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                <div class="event-month"><?php echo date('M', strtotime($event['event_date'])); ?></div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="p-4 h-100 d-flex flex-column">
                                <div class="mb-3">
                                    <span class="badge" style="background: rgba(40, 167, 69, 0.1); color: var(--color-success);">
                                        <i class="fas fa-clock me-1"></i><?php echo date('H:i', strtotime($event['event_date'])); ?>
                                    </span>
                                    <span class="badge ms-2" style="background: rgba(23, 162, 184, 0.1); color: var(--color-info);">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($event['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                                <h5 class="fw-bold mb-3"><?php echo htmlspecialchars($event['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h5>
                                <p class="text-muted flex-grow-1 mb-4"><?php echo htmlspecialchars($event['short_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <div class="d-flex gap-2">
                                    <a href="<?php echo BASE_URL . '/client/public/event.php?id=' . $event['id']; ?>"
                                       class="btn-premium btn-primary-premium flex-fill">
                                        <i class="fas fa-info-circle"></i>
                                        Learn More
                                    </a>
                                    <button class="btn-premium btn-outline-premium" onclick="shareEvent('<?= htmlspecialchars($event['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>')">
                                        <i class="fas fa-share-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?php echo BASE_URL; ?>/client/public/events.php"
               class="btn-premium btn-primary-premium">
                <i class="fas fa-calendar-check"></i>
                View All Events
                <span class="badge bg-white text-dark ms-2">10+</span>
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section-premium testimonial-section">
    <div class="container">
        <div class="section-subtitle">Student Success</div>
        <h2 class="section-title">Success Stories</h2>
        <p class="section-description">
            Hear from our graduates who have transformed their careers through our programs
        </p>

        <div id="testimonialCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">
            <div class="carousel-inner">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                <div class="carousel-item<?php echo $index === 0 ? ' active' : ''; ?>">
                    <div class="testimonial-card">
                        <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($testimonial['image_path'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                             class="testimonial-avatar"
                             alt="<?php echo htmlspecialchars($testimonial['author_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="testimonial-quote">
                            <?php echo htmlspecialchars($testimonial['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div class="testimonial-author">
                            <?php echo htmlspecialchars($testimonial['author_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div class="testimonial-role">
                            <?php echo htmlspecialchars($testimonial['author_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Custom Navigation -->
            <div class="d-flex justify-content-center gap-2 mt-4">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                <button type="button"
                        data-bs-target="#testimonialCarousel"
                        data-bs-slide-to="<?= $index ?>"
                        class="btn btn-sm <?= $index === 0 ? 'btn-primary' : 'btn-outline-primary' ?> rounded-circle"
                        style="width: 12px; height: 12px; padding: 0;"
                        aria-label="Go to testimonial <?= $index + 1 ?>">
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="section-premium cta-section">
    <div class="container">
        <div class="cta-content">
            <div class="hero-badge mb-4">
                <i class="fas fa-rocket"></i>
                <span>Start Today</span>
            </div>
            <h2 class="cta-title">Ready to Transform Your Future?</h2>
            <p class="cta-description">
                Join thousands of African youth who have acquired digital skills and launched successful careers through our comprehensive training programs.
            </p>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">2,500+</div>
                        <div class="stat-label">Students Enrolled</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">85%</div>
                        <div class="stat-label">Job Placement Rate</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">95%</div>
                        <div class="stat-label">Satisfaction Score</div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row gap-3 justify-content-center mb-5">
                <a href="<?php echo BASE_URL; ?>/client/public/apply.php"
                   class="btn-premium btn-primary-premium">
                    <i class="fas fa-rocket"></i>
                    Start Your Journey
                </a>
                <a href="<?php echo BASE_URL; ?>/client/public/contact.php"
                   class="btn-premium btn-outline-premium">
                    <i class="fas fa-comments"></i>
                    Talk to Us
                </a>
            </div>

            <div class="text-center">
                <p class="small mb-2" style="opacity: 0.7;">Trusted by leading companies:</p>
                <div class="d-flex justify-content-center align-items-center gap-4 flex-wrap">
                    <div style="color: var(--color-primary); font-weight: 600;">Google</div>
                    <div style="color: var(--color-primary); font-weight: 600;">Microsoft</div>
                    <div style="color: var(--color-primary); font-weight: 600;">Amazon</div>
                    <div style="color: var(--color-primary); font-weight: 600;">Meta</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="section-premium newsletter-section">
    <div class="container">
        <div class="text-center">
            <h3 class="fw-bold mb-3">Stay Updated with Our Latest Programs</h3>
            <p class="text-muted mb-4">Get notified about new courses, events, and career opportunities.</p>

            <form id="newsletterForm" class="newsletter-form">
                <input type="email"
                       class="newsletter-input"
                       placeholder="Enter your email address"
                       required>
                <button type="submit" class="btn-premium btn-primary-premium">
                    <i class="fas fa-paper-plane"></i>
                    Subscribe
                </button>
            </form>

            <small class="text-muted mt-3 d-block">We respect your privacy. Unsubscribe at any time.</small>
        </div>
    </div>
</section>

<!-- Enhanced JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality with suggestions
    const searchInput = document.getElementById('programSearch');
    const searchSuggestions = document.getElementById('searchSuggestions');

    // Sample search suggestions - replace with actual data from your database
    const searchData = [
        'Web Development',
        'Digital Marketing',
        'Data Analytics',
        'Mobile App Development',
        'UI/UX Design',
        'Cybersecurity',
        'Cloud Computing',
        'Artificial Intelligence',
        'Blockchain Technology',
        'E-commerce Development'
    ];

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        if (query.length > 0) {
            const suggestions = searchData.filter(item =>
                item.toLowerCase().includes(query)
            ).slice(0, 5);

            if (suggestions.length > 0) {
                searchSuggestions.innerHTML = suggestions.map(suggestion =>
                    `<div class="search-suggestion-item" onclick="selectSuggestion('${suggestion}')">${suggestion}</div>`
                ).join('');
                searchSuggestions.style.display = 'block';
            } else {
                searchSuggestions.style.display = 'none';
            }
        } else {
            searchSuggestions.style.display = 'none';
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            searchSuggestions.style.display = 'none';
        }
    });

    // Newsletter form submission
    document.getElementById('newsletterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value;

        // Add your newsletter subscription logic here
        alert('Thank you for subscribing! We\'ll keep you updated with our latest programs.');
        this.reset();
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);

    // Observe all elements that should animate
    document.querySelectorAll('.card-premium, .stat-item, .testimonial-card').forEach(el => {
        observer.observe(el);
    });

    // Add ripple effect to buttons
    document.querySelectorAll('.btn-premium').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;

            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Parallax effect for hero section
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallax = document.querySelector('.hero-section');
        const speed = scrolled * 0.5;

        if (parallax) {
            parallax.style.transform = `translateY(${speed}px)`;
        }
    });
});

// Search suggestion selection
function selectSuggestion(suggestion) {
    document.getElementById('programSearch').value = suggestion;
    document.getElementById('searchSuggestions').style.display = 'none';
}

// Event sharing function
function shareEvent(eventTitle) {
    if (navigator.share) {
        navigator.share({
            title: eventTitle,
            text: `Check out this event: ${eventTitle}`,
            url: window.location.href
        });
    } else {
        // Fallback for browsers that don't support Web Share API
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent(`Check out this event: ${eventTitle}`);
        window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }

    .search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid var(--color-primary);
        border-top: none;
        border-radius: 0 0 var(--border-radius) var(--border-radius);
        box-shadow: var(--shadow-lg);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
    }

    .search-suggestion-item {
        padding: 12px 24px;
        cursor: pointer;
        transition: var(--transition);
        border-bottom: 1px solid rgba(216, 201, 155, 0.1);
    }

    .search-suggestion-item:hover {
        background: var(--color-primary);
        color: var(--color-secondary);
    }

    .search-suggestion-item:last-child {
        border-bottom: none;
    }
`;
document.head.appendChild(style);
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>