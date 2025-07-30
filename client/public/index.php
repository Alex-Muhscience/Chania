<?php
require_once '../includes/config.php';

$page_title = 'Home';
$page_description = 'Empowering communities through skills development and training programs. Join our transformative programs in technology, business, agriculture, and healthcare.';
$page_keywords = 'skills training, education, Africa, programs, technology, business, agriculture, healthcare';

// Initialize variables with empty arrays
$featured_programs = [];
$upcoming_events = [];
$stats = ['total_programs' => 0, 'total_applications' => 0, 'total_events' => 0, 'newsletter_subscribers' => 0];
$testimonials = [];

// Fetch data with error handling (database tables may not exist yet)
try {
    // Fetch featured programs
    $featured_programs_query = "SELECT * FROM programs WHERE is_featured = 1 AND is_active = 1 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 6";
    $featured_programs = $db->query($featured_programs_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table doesn't exist or has different structure - use empty array
    $featured_programs = [];
}

try {
    // Fetch upcoming events
    $upcoming_events_query = "SELECT * FROM events WHERE event_date >= NOW() AND is_active = 1 AND deleted_at IS NULL ORDER BY event_date ASC LIMIT 4";
    $upcoming_events = $db->query($upcoming_events_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table doesn't exist - use empty array
    $upcoming_events = [];
}

try {
    // Fetch statistics
    $stats_query = "
        SELECT 
            (SELECT COUNT(*) FROM programs WHERE is_active = 1 AND deleted_at IS NULL) as total_programs,
            (SELECT COUNT(*) FROM applications WHERE status IN ('approved', 'under_review') AND deleted_at IS NULL) as total_applications,
            (SELECT COUNT(*) FROM events WHERE is_active = 1 AND deleted_at IS NULL) as total_events,
            (SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'subscribed') as newsletter_subscribers
    ";
    $stats = $db->query($stats_query)->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tables don't exist - use default values
    $stats = ['total_programs' => 15, 'total_applications' => 250, 'total_events' => 8, 'newsletter_subscribers' => 1250];
}

try {
    // Fetch testimonials
    $testimonials_query = "SELECT * FROM testimonials WHERE is_active = 1 AND is_featured = 1 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 3";
    $testimonials = $db->query($testimonials_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table doesn't exist - use empty array
    $testimonials = [];
}

include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" data-aos="fade-in">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content">
                <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">
                    Empowering <span class="text-warning">Communities</span> Through Skills Development
                </h1>
                <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="400">
                    Transform your future with our comprehensive training programs in technology, business, agriculture, and healthcare. Join thousands who have already started their journey to success.
                </p>
                <div class="hero-actions" data-aos="fade-up" data-aos-delay="600">
                    <a href="<?php echo BASE_URL; ?>programs" class="btn btn-warning btn-lg me-3">
                        <i class="fas fa-graduation-cap me-2"></i>Explore Programs
                    </a>
                    <a href="<?php echo BASE_URL; ?>apply" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Apply Now
                    </a>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="800">
                <div class="hero-image">
                    <img src="<?php echo ASSETS_URL; ?>images/hero-image.jpg" alt="Students learning" class="img-fluid rounded-lg shadow-lg">
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
                <h2 class="section-title">Featured Programs</h2>
                <p class="section-subtitle text-muted">
                    Discover our most popular and impactful training programs designed to equip you with in-demand skills.
                </p>
            </div>
        </div>
        
        <div class="row">
            <!-- Sample Program 1 -->
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card program-card h-100">
                    <div class="card-header">
                        <span class="featured-badge">Featured</span>
                        <span class="difficulty-badge difficulty-beginner">Beginner</span>
                        <img src="https://via.placeholder.com/400x200?text=Web+Development" alt="Web Development" class="card-img-top">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Full Stack Web Development</h5>
                        <p class="card-text text-muted">Learn modern web development with HTML, CSS, JavaScript, PHP, and MySQL. Build real-world projects.</p>
                        
                        <div class="program-meta">
                            <span><i class="fas fa-clock"></i> 12 weeks</span>
                            <span><i class="fas fa-tag"></i> Technology</span>
                            <span class="text-success"><i class="fas fa-gift"></i> Free</span>
                        </div>
                        
                        <div class="mt-auto pt-3">
                            <div class="d-flex gap-2">
                                <a href="<?php echo BASE_URL; ?>programs" class="btn btn-primary flex-fill">
                                    <i class="fas fa-info-circle me-1"></i>Learn More
                                </a>
                                <a href="<?php echo BASE_URL; ?>apply" class="btn btn-outline-primary">
                                    <i class="fas fa-user-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sample Program 2 -->
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="card program-card h-100">
                    <div class="card-header">
                        <span class="difficulty-badge difficulty-intermediate">Intermediate</span>
                        <img src="https://via.placeholder.com/400x200?text=Digital+Marketing" alt="Digital Marketing" class="card-img-top">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Digital Marketing & E-commerce</h5>
                        <p class="card-text text-muted">Master social media marketing, SEO, Google Ads, and online business strategies.</p>
                        
                        <div class="program-meta">
                            <span><i class="fas fa-clock"></i> 8 weeks</span>
                            <span><i class="fas fa-tag"></i> Business</span>
                            <span><i class="fas fa-money-bill"></i> KSh 15,000</span>
                        </div>
                        
                        <div class="mt-auto pt-3">
                            <div class="d-flex gap-2">
                                <a href="<?php echo BASE_URL; ?>programs" class="btn btn-primary flex-fill">
                                    <i class="fas fa-info-circle me-1"></i>Learn More
                                </a>
                                <a href="<?php echo BASE_URL; ?>apply" class="btn btn-outline-primary">
                                    <i class="fas fa-user-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sample Program 3 -->
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                <div class="card program-card h-100">
                    <div class="card-header">
                        <span class="featured-badge">Featured</span>
                        <span class="difficulty-badge difficulty-beginner">Beginner</span>
                        <img src="https://via.placeholder.com/400x200?text=Smart+Agriculture" alt="Smart Agriculture" class="card-img-top">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Smart Agriculture & Farming</h5>
                        <p class="card-text text-muted">Learn modern farming techniques, crop management, and sustainable agriculture practices.</p>
                        
                        <div class="program-meta">
                            <span><i class="fas fa-clock"></i> 6 weeks</span>
                            <span><i class="fas fa-tag"></i> Agriculture</span>
                            <span class="text-success"><i class="fas fa-gift"></i> Free</span>
                        </div>
                        
                        <div class="mt-auto pt-3">
                            <div class="d-flex gap-2">
                                <a href="<?php echo BASE_URL; ?>programs" class="btn btn-primary flex-fill">
                                    <i class="fas fa-info-circle me-1"></i>Learn More
                                </a>
                                <a href="<?php echo BASE_URL; ?>apply" class="btn btn-outline-primary">
                                    <i class="fas fa-user-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-center" data-aos="fade-up">
                <a href="<?php echo BASE_URL; ?>programs" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-th-large me-2"></i>View All Programs
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Why Choose Us Section -->
<section class="py-5 bg-light-gray">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">Why Choose Chania Skills for Africa?</h2>
                <p class="section-subtitle text-muted">
                    We're committed to providing world-class training that transforms lives and empowers communities.
                </p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-chalkboard-teacher text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4>Expert Instructors</h4>
                    <p class="text-muted">Learn from industry professionals with years of real-world experience and proven track records.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-hands-helping text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4>Hands-on Learning</h4>
                    <p class="text-muted">Engage in practical, project-based learning that prepares you for real-world challenges.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-certificate text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4>Recognized Certification</h4>
                    <p class="text-muted">Earn certificates that are recognized by employers and industry bodies across Africa.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="800">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-users text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4>Community Support</h4>
                    <p class="text-muted">Join a vibrant community of learners and alumni who support each other's growth.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="1000">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-briefcase text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4>Career Placement</h4>
                    <p class="text-muted">Access our job placement services and connect with employers looking for skilled professionals.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="1200">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-clock text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4>Flexible Schedule</h4>
                    <p class="text-muted">Choose from full-time, part-time, and weekend programs that fit your lifestyle.</p>
                </div>
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
                    <a href="<?php echo BASE_URL; ?>apply" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Apply Now
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-phone me-2"></i>Get In Touch
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
