<?php
require_once '../includes/config.php';

$page_title = 'Home';
$page_description = 'Master new skills in just days with our intensive online short courses. Quick, practical training programs in technology, business, agriculture, and healthcare.';
$page_keywords = 'short courses, online training, quick skills, intensive courses, certification, digital learning, Africa';

// Fetch featured programs
$featured_programs_query = "SELECT * FROM programs WHERE is_featured = 1 AND is_active = 1 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 6";
$featured_programs = $db->query($featured_programs_query)->fetchAll(PDO::FETCH_ASSOC);

// Fetch popular programs (most applied)
$popular_programs_query = "
    SELECT p.*, COUNT(a.id) as application_count 
    FROM programs p 
    LEFT JOIN applications a ON p.id = a.program_id 
    WHERE p.is_active = 1 AND p.deleted_at IS NULL 
    GROUP BY p.id 
    ORDER BY application_count DESC, p.created_at DESC 
    LIMIT 6
";
$popular_programs = $db->query($popular_programs_query)->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming events
$upcoming_events_query = "SELECT * FROM events WHERE event_date >= NOW() AND is_active = 1 AND deleted_at IS NULL ORDER BY event_date ASC LIMIT 4";
$upcoming_events = $db->query($upcoming_events_query)->fetchAll(PDO::FETCH_ASSOC);

// Fetch statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM programs WHERE is_active = 1 AND deleted_at IS NULL) as total_programs,
        (SELECT COUNT(*) FROM applications WHERE status IN ('approved', 'under_review') AND deleted_at IS NULL) as total_applications,
        (SELECT COUNT(*) FROM events WHERE is_active = 1 AND deleted_at IS NULL) as total_events,
        (SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'subscribed') as newsletter_subscribers
";
$stats = $db->query($stats_query)->fetch(PDO::FETCH_ASSOC);

// Fetch testimonials (using status instead of is_active if column doesn't exist)
try {
    $testimonials_query = "SELECT * FROM testimonials WHERE is_active = 1 AND is_featured = 1 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 6";
    $testimonials = $db->query($testimonials_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback query if is_active column doesn't exist
    try {
        $testimonials_query = "SELECT * FROM testimonials WHERE status = 'active' AND is_featured = 1 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 6";
        $testimonials = $db->query($testimonials_query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        // Final fallback - get all testimonials
        $testimonials_query = "SELECT * FROM testimonials WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 6";
        $testimonials = $db->query($testimonials_query)->fetchAll(PDO::FETCH_ASSOC);
    }
}

include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-premium" data-aos="fade-in">
    <div class="hero-bg-image"></div>
    <div class="hero-background">
        <div class="hero-gradient"></div>
        <div class="hero-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>
    <div class="container position-relative">
        <div class="row align-items-center min-vh-100 py-5">
            <div class="col-lg-6 hero-content">
                <div class="hero-badge" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-star"></i>
                    <span>Rated #1 Skills Platform in Africa</span>
                </div>
                <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">
                    Master New Skills in 
                    <span class="text-gradient">Just Days</span> 
                    with Online Courses
                </h1>
                <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="400">
                    Join over 10,000+ professionals who have fast-tracked their careers with our intensive short courses. Learn online, get certified quickly, and advance your career today.
                </p>
                
                <!-- Course Search Bar -->
                <div class="hero-search" data-aos="fade-up" data-aos-delay="500">
                    <form class="search-form" action="<?php echo BASE_URL; ?>programs.php" method="GET">
                        <div class="search-input-group">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="search" class="search-input" 
                                   placeholder="Search for courses, skills, or career paths..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <select name="category" class="search-select">
                                <option value="">All Categories</option>
                                <option value="technology">Technology</option>
                                <option value="business">Business</option>
                                <option value="agriculture">Agriculture</option>
                                <option value="healthcare">Healthcare</option>
                            </select>
                            <button type="submit" class="search-btn">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                    <div class="popular-searches">
                        <span class="popular-label">Popular:</span>
                        <a href="<?php echo BASE_URL; ?>programs.php?search=web+development" class="popular-tag">Web Development</a>
                        <a href="<?php echo BASE_URL; ?>programs.php?search=digital+marketing" class="popular-tag">Digital Marketing</a>
                        <a href="<?php echo BASE_URL; ?>programs.php?search=data+science" class="popular-tag">Data Science</a>
                        <a href="<?php echo BASE_URL; ?>programs.php?search=agriculture" class="popular-tag">Smart Farming</a>
                    </div>
                </div>
                
                <div class="hero-actions" data-aos="fade-up" data-aos-delay="600">
                    <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary btn-lg hero-btn-primary">
                        <i class="fas fa-play-circle me-2"></i>Start Learning Today
                    </a>
                    <a href="<?php echo BASE_URL; ?>apply.php" class="btn btn-outline-light btn-lg hero-btn-secondary">
                        <i class="fas fa-rocket me-2"></i>Enroll Now
                    </a>
                </div>
                
                <div class="hero-stats" data-aos="fade-up" data-aos-delay="700">
                    <div class="stat-item">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Students</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">2-5</div>
                        <div class="stat-label">Days Only</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Courses</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Online</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="800">
                <div class="hero-visual">
                    <div class="hero-image-container">
                        <img src="<?php echo ASSETS_URL; ?>images/hero-image.jpg" alt="Students learning at Chania Skills for Africa" class="hero-main-image">
                        
                        <!-- Enhanced Floating Cards -->
                        <div class="floating-card card-1" data-aos="zoom-in" data-aos-delay="1000">
                            <div class="card-icon">
                                <i class="fas fa-code"></i>
                            </div>
                            <div class="card-content">
                                <h6>Web Development</h6>
                                <span>3 Days • Online</span>
                            </div>
                        </div>
                        
                        <div class="floating-card card-2" data-aos="zoom-in" data-aos-delay="1200">
                            <div class="card-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="card-content">
                                <h6>Data Analytics</h6>
                                <span>2 Days • Online</span>
                            </div>
                        </div>
                        
                        <div class="floating-card card-3" data-aos="zoom-in" data-aos-delay="1400">
                            <div class="card-icon">
                                <i class="fas fa-seedling"></i>
                            </div>
                            <div class="card-content">
                                <h6>Smart Agriculture</h6>
                                <span>4 Days • Online</span>
                            </div>
                        </div>
                        
                        <!-- Enhanced Success Badge -->
                        <div class="success-badge" data-aos="bounce-in" data-aos-delay="1600">
                            <i class="fas fa-trophy"></i>
                            <span>5,000+ Graduates</span>
                        </div>
                        
                        <!-- Additional Visual Elements -->
                        <div class="hero-glow-effect"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats" data-aos="fade-up">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-item">
                    <span class="stat-number" data-count="<?php echo $stats['total_programs'] ?: 15; ?>">0</span>
                    <div class="stat-label">Active Programs</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-item">
                    <span class="stat-number" data-count="<?php echo $stats['total_applications'] ?: 250; ?>">0</span>
                    <div class="stat-label">Students Enrolled</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-item">
                    <span class="stat-number" data-count="<?php echo $stats['total_events'] ?: 8; ?>">0</span>
                    <div class="stat-label">Upcoming Events</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-item">
                    <span class="stat-number" data-count="1250">0</span>
                    <div class="stat-label">Success Stories</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (empty($featured_programs)): ?>
<!-- Sample Featured Programs Section (when no data) -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">Featured Short Courses</h2>
                <p class="section-subtitle text-muted">
                    Discover our most popular intensive courses designed to give you practical skills in just a few days.
                </p>
            </div>
        </div>
        
        <div class="row">
            <!-- Sample Program 1 -->
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card program-card h-100">
                    <div class="position-relative">
                        <img src="https://via.placeholder.com/400x200?text=Web+Development" alt="Web Development" class="card-img-top">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Full Stack Web Development</h5>
                        <p class="card-text text-muted">Learn modern web development with HTML, CSS, JavaScript, PHP, and MySQL. Build real-world projects.</p>
                        
                        <div class="mt-auto pt-3">
                            <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary w-100">
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sample Program 2 -->
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="card program-card h-100">
                    <div class="position-relative">
                        <img src="https://via.placeholder.com/400x200?text=Digital+Marketing" alt="Digital Marketing" class="card-img-top">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Digital Marketing & E-commerce</h5>
                        <p class="card-text text-muted">Master social media marketing, SEO, Google Ads, and online business strategies.</p>
                        
                        <div class="mt-auto pt-3">
                            <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary w-100">
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sample Program 3 -->
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                <div class="card program-card h-100">
                    <div class="position-relative">
                        <img src="https://via.placeholder.com/400x200?text=Smart+Agriculture" alt="Smart Agriculture" class="card-img-top">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Smart Agriculture & Farming</h5>
                        <p class="card-text text-muted">Learn modern farming techniques, crop management, and sustainable agriculture practices.</p>
                        
                        <div class="mt-auto pt-3">
                            <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary w-100">
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-center" data-aos="fade-up">
                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-th-large me-2"></i>View All Programs
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Popular Courses Section -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-10 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">Most Popular Short Courses</h2>
                <p class="section-subtitle text-muted">
                    Join thousands of students in our top-rated intensive courses designed by industry experts. Complete in days, not months.
                </p>
            </div>
        </div>
        
        <div class="row">
            <?php if (!empty($popular_programs)): ?>
                <?php foreach ($popular_programs as $index => $program): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                        <div class="card program-card h-100">
                            <div class="position-relative">
                                <img src="<?php echo !empty($program['image_url']) ? ASSETS_URL . 'images/programs/' . $program['image_url'] : 'https://via.placeholder.com/400x200?text=' . urlencode($program['title']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="card-img-top">
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($program['title']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars(substr($program['description'], 0, 120)) . '...'; ?></p>
                                
                                <div class="mt-auto pt-3">
                                    <a href="<?php echo BASE_URL; ?>program.php?id=<?php echo $program['id']; ?>" class="btn btn-primary w-100">
                                        Learn More
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Sample Popular Courses -->
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card program-card h-100">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/400x200?text=Web+Development" alt="Web Development" class="card-img-top">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Full Stack Web Development</h5>
                            <p class="card-text text-muted">Master modern web development with HTML, CSS, JavaScript, React, Node.js, and database management...</p>
                            
                            <div class="mt-auto pt-3">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary w-100">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card program-card h-100">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/400x200?text=Data+Science" alt="Data Science" class="card-img-top">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Data Science & Analytics</h5>
                            <p class="card-text text-muted">Learn Python, machine learning, data visualization, and statistical analysis for data-driven decisions...</p>
                            
                            <div class="mt-auto pt-3">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary w-100">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card program-card h-100">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/400x200?text=Digital+Marketing" alt="Digital Marketing" class="card-img-top">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Digital Marketing Mastery</h5>
                            <p class="card-text text-muted">Complete digital marketing course covering SEO, social media, PPC, email marketing, and analytics...</p>
                            
                            <div class="mt-auto pt-3">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary w-100">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="card program-card h-100">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/400x200?text=Mobile+App+Development" alt="Mobile Development" class="card-img-top">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Mobile App Development</h5>
                            <p class="card-text text-muted">Build native iOS and Android apps using React Native, Flutter, or native development frameworks...</p>
                            
                            <div class="mt-auto pt-3">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary w-100">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="card program-card h-100">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/400x200?text=Smart+Agriculture" alt="Smart Agriculture" class="card-img-top">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Smart Agriculture & IoT</h5>
                            <p class="card-text text-muted">Modern farming techniques using IoT sensors, precision agriculture, and sustainable farming practices...</p>
                            
                            <div class="mt-auto pt-3">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary w-100">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="card program-card h-100">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/400x200?text=Healthcare+Management" alt="Healthcare Management" class="card-img-top">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Healthcare Management</h5>
                            <p class="card-text text-muted">Learn healthcare administration, patient care systems, medical technology, and healthcare policy...</p>
                            
                            <div class="mt-auto pt-3">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary w-100">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-th-large me-2"></i>Browse All Courses
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Short Course Benefits Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title text-white">Why Choose Our Short Courses?</h2>
                <p class="section-subtitle text-white-75">
                    Designed for busy professionals who want to learn new skills quickly and efficiently.
                </p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="benefit-card text-center">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5 class="benefit-title">Quick Learning</h5>
                    <p class="benefit-description">Complete courses in just 2-5 days. Perfect for busy schedules.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="benefit-card text-center">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <h5 class="benefit-title">100% Online</h5>
                    <p class="benefit-description">Learn from anywhere, anytime. All you need is an internet connection.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="benefit-card text-center">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h5 class="benefit-title">Practical Skills</h5>
                    <p class="benefit-description">Hands-on learning with real-world applications you can use immediately.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="benefit-card text-center">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h5 class="benefit-title">Certification</h5>
                    <p class="benefit-description">Earn certificates that showcase your new skills to employers.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.benefit-card {
    padding: 2rem 1rem;
    border-radius: 10px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    transition: transform 0.3s ease;
    height: 100%;
}

.benefit-card:hover {
    transform: translateY(-5px);
    background: rgba(255,255,255,0.15);
}

.benefit-icon {
    font-size: 3rem;
    color: rgba(255,255,255,0.9);
}

.benefit-title {
    color: white;
    margin-bottom: 1rem;
    font-weight: 600;
}

.benefit-description {
    color: rgba(255,255,255,0.8);
    margin: 0;
    line-height: 1.6;
}
</style>

<!-- Testimonials Section -->
<section class="py-5 bg-light-gray">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-10 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">What Our Students Say</h2>
                <p class="section-subtitle text-muted">
                    Real stories from real students who have transformed their careers with our programs
                </p>
            </div>
        </div>
        
        <div class="testimonials-container">
            <?php if (!empty($testimonials) && count($testimonials) >= 3): ?>
                <?php foreach (array_slice($testimonials, 0, 6) as $index => $testimonial): ?>
                    <div class="testimonial-card" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                        <div class="testimonial-content">
                            <div class="testimonial-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <?php if (!empty($testimonial['avatar'])): ?>
                                    <img src="<?php echo ASSETS_URL; ?>images/testimonials/<?php echo $testimonial['avatar']; ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo strtoupper(substr($testimonial['name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="author-info">
                                <h6 class="author-name"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                                <p class="author-role"><?php echo htmlspecialchars($testimonial['position'] ?? $testimonial['program_title'] ?? 'Graduate'); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Sample Testimonials -->
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-content">
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"The Web Development program completely transformed my career. I went from having no coding experience to landing a job as a full-stack developer at a tech startup in Nairobi. The instructors were incredibly supportive and the hands-on projects prepared me for real-world challenges."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <div class="avatar-placeholder">A</div>
                        </div>
                        <div class="author-info">
                            <h6 class="author-name">Amina Ochieng</h6>
                            <p class="author-role">Full Stack Developer at TechFlow Kenya</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-content">
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"I never thought I could start my own business, but the Digital Marketing program gave me all the tools I needed. Now I'm running a successful e-commerce store and helping other small businesses grow their online presence. The ROI has been incredible!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <div class="avatar-placeholder">J</div>
                        </div>
                        <div class="author-info">
                            <h6 class="author-name">James Wanjiku</h6>
                            <p class="author-role">E-commerce Entrepreneur</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-content">
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"The Smart Agriculture program revolutionized how I approach farming. I've increased my crop yields by 40% and reduced water usage by 30% using the techniques I learned. This knowledge is invaluable for sustainable farming in Kenya."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <div class="avatar-placeholder">M</div>
                        </div>
                        <div class="author-info">
                            <h6 class="author-name">Mary Kiprotich</h6>
                            <p class="author-role">Smart Farmer & Agricultural Consultant</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="testimonial-content">
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"As a healthcare professional, the Healthcare Management program helped me advance to a leadership role. The curriculum was practical and relevant to the African healthcare context. I now manage a team of 15 healthcare workers."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <div class="avatar-placeholder">D</div>
                        </div>
                        <div class="author-info">
                            <h6 class="author-name">Dr. David Mwangi</h6>
                            <p class="author-role">Healthcare Administrator</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="testimonial-content">
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"The Data Science program opened up a whole new career path for me. I love how they made complex concepts easy to understand. Now I'm working as a data analyst and contributing to evidence-based decision making in government."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <div class="avatar-placeholder">S</div>
                        </div>
                        <div class="author-info">
                            <h6 class="author-name">Sarah Nyong'o</h6>
                            <p class="author-role">Government Data Analyst</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="600">
                    <div class="testimonial-content">
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"The flexibility of the program allowed me to balance my studies with work and family commitments. The weekend sessions were perfect, and the online resources were comprehensive. I achieved my certification without disrupting my life."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <div class="avatar-placeholder">P</div>
                        </div>
                        <div class="author-info">
                            <h6 class="author-name">Peter Otieno</h6>
                            <p class="author-role">Mobile App Developer</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="<?php echo BASE_URL; ?>apply.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Join Our Success Stories
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">Ready to Transform Your Future?</h2>
                <p class="section-subtitle text-muted mb-4">
                    Join thousands of students who have already started their journey to success. Take the first step today.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="<?php echo BASE_URL; ?>apply.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Apply Now
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-phone me-2"></i>Get In Touch
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
