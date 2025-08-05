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
<section class="hero-section" style="background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1920&h=1080&fit=crop&crop=center'); background-size: cover; background-position: center; background-attachment: fixed; min-height: 75vh; display: flex; align-items: center; border: none; margin-top: -1px; padding-top: 0;">
    <div class="container">
        <div class="row justify-content-center text-center text-white">
            <div class="col-lg-10">
                <div class="badge bg-white bg-opacity-20 text-black px-4 py-2 rounded-pill mt-4 mb-5 border border-white border-opacity-30">
                    <i class="fas fa-crown me-2"></i>
                    <?php echo lang('platform'); ?>
                </div>
                
                <h1 class="hero-title display-2 fw-bold mb-4 text-white">
                    <?php echo lang('transform_career_in'); ?> 
                    <span class="text-warning"><?php echo lang('just_days'); ?></span>
                    <br><?php echo lang('not_months'); ?>
                </h1>
                
                <p class="hero-subtitle lead mb-5 text-white-75" style="font-size: 1.25rem; max-width: 700px; margin: 0 auto 2rem;">
                    <?php echo lang('join_professionals'); ?>
                </p>
                
                <!-- Search Form -->
                <div class="mb-5">
                    <form action="<?php echo BASE_URL; ?>programs.php" method="GET" class="d-flex flex-column flex-lg-row gap-3 justify-content-center align-items-stretch" style="max-width: 800px; margin: 0 auto;">
                        <div class="input-group flex-grow-1" style="box-shadow: 0 6px 20px rgba(0,0,0,0.1); border-radius: 12px; overflow: hidden; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); min-width: 300px; height: 60px;">
                            <span class="input-group-text bg-transparent border-0 ps-4" style="border-radius: 12px 0 0 12px;"><i class="fas fa-search text-primary" style="font-size: 1.1rem;"></i></span>
                            <input type="text" 
                                   name="search" 
                                   class="form-control form-control-lg border-0 py-3 px-3" 
                                   placeholder="<?php echo lang('skills_placeholder'); ?>"
                                   style="border-radius: 0; font-size: 1rem; background: transparent; height: 56px;"
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        <select name="category" class="form-select form-select-lg border-0 py-3 px-4" style="min-width: 240px; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.1); font-size: 1rem; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); height: 60px;">
                            <option value=""><?php echo lang('all_categories'); ?></option>
                            <option value="technology"><?php echo lang('technology'); ?></option>
                            <option value="business"><?php echo lang('business'); ?></option>
                            <option value="agriculture"><?php echo lang('agriculture'); ?></option>
                            <option value="healthcare"><?php echo lang('healthcare'); ?></option>
                        </select>
                        <button type="submit" class="btn btn-warning btn-lg px-5 py-3 fw-bold" style="border-radius: 12px; box-shadow: 0 6px 20px rgba(255,193,7,0.3); min-width: 240px; font-size: 1rem; transition: all 0.3s ease; height: 60px; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); border: none;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(255,193,7,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 6px 20px rgba(255,193,7,0.3)'">
                            <span><?php echo lang('explore_now'); ?></span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </form>
                    
                    <!-- Enhanced Trending Section -->
                    <div class="mt-5 text-center">
                        <div class="mb-3">
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" style="font-size: 0.9rem; box-shadow: 0 4px 15px rgba(255,193,7,0.3);">
                                <i class="fas fa-fire me-2"></i><?php echo lang('trending_now'); ?>
                            </span>
                        </div>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="<?php echo BASE_URL; ?>programs.php?search=web+development" class="trending-tag" style="text-decoration: none; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); color: white; padding: 0.6rem 1.2rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s ease; font-size: 0.9rem; font-weight: 500;" onmouseover="this.style.background='rgba(255,255,255,0.25)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                <i class="fas fa-code me-2"></i>Web Development
                            </a>
                            <a href="<?php echo BASE_URL; ?>programs.php?search=digital+marketing" class="trending-tag" style="text-decoration: none; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); color: white; padding: 0.6rem 1.2rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s ease; font-size: 0.9rem; font-weight: 500;" onmouseover="this.style.background='rgba(255,255,255,0.25)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                <i class="fas fa-chart-line me-2"></i>Digital Marketing
                            </a>
                            <a href="<?php echo BASE_URL; ?>programs.php?search=data+science" class="trending-tag" style="text-decoration: none; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); color: white; padding: 0.6rem 1.2rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s ease; font-size: 0.9rem; font-weight: 500;" onmouseover="this.style.background='rgba(255,255,255,0.25)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                <i class="fas fa-database me-2"></i>Data Science
                            </a>
                            <a href="<?php echo BASE_URL; ?>programs.php?search=mobile+development" class="trending-tag" style="text-decoration: none; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); color: white; padding: 0.6rem 1.2rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s ease; font-size: 0.9rem; font-weight: 500;" onmouseover="this.style.background='rgba(255,255,255,0.25)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                <i class="fas fa-mobile-alt me-2"></i>Mobile Apps
                            </a>
                            <a href="<?php echo BASE_URL; ?>programs.php?search=cyber+security" class="trending-tag" style="text-decoration: none; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); color: white; padding: 0.6rem 1.2rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s ease; font-size: 0.9rem; font-weight: 500;" onmouseover="this.style.background='rgba(255,255,255,0.25)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                <i class="fas fa-shield-alt me-2"></i>Cyber Security
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-5">
                    <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-warning btn-lg px-5 fw-bold">
                        <i class="fas fa-rocket me-2"></i><?php echo lang('start_learning_today'); ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-outline-light btn-lg px-5 fw-bold">
                        <i class="fas fa-phone me-2"></i><?php echo lang('talk_to_advisor'); ?>
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="row text-center">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="bg-white bg-opacity-20 rounded-3 p-3 border border-white border-opacity-30">
                            <div class="h2 fw-bold text-dark mb-0">15K+</div>
                            <small class="text-dark"><?php echo lang('students'); ?></small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="bg-white bg-opacity-20 rounded-3 p-3 border border-white border-opacity-30">
                            <div class="h2 fw-bold text-dark mb-0">2-5</div>
                            <small class="text-dark"><?php echo lang('days_only'); ?></small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="bg-white bg-opacity-20 rounded-3 p-3 border border-white border-opacity-30">
                            <div class="h2 fw-bold text-dark mb-0">95%</div>
                            <small class="text-dark"><?php echo lang('success_rate'); ?></small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="bg-white bg-opacity-20 rounded-3 p-3 border border-white border-opacity-30">
                            <div class="h2 fw-bold text-dark mb-0">24/7</div>
                            <small class="text-dark"><?php echo lang('support'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); margin-top: -1px;" data-aos="fade-up">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title mb-3" style="color: #2c3e50; font-weight: 700;">Trusted by Thousands Across Africa</h2>
                <p class="section-subtitle text-muted" style="font-size: 1.1rem;">Join our growing community of successful professionals who have transformed their careers</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-item-modern text-center p-4" style="background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease; height: 100%;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 35px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.08)'">
                    <div class="stat-icon mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <i class="fas fa-graduation-cap" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div class="stat-number" style="font-size: 2.5rem; font-weight: 800; color: #2c3e50; margin-bottom: 0.5rem;" data-count="25000">0</div>
                    <div class="stat-label" style="color: #6c757d; font-weight: 500; font-size: 1.1rem;">Students Trained</div>
                    <div class="stat-subtext mt-2" style="color: #9ca3af; font-size: 0.9rem;">Since 2020</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-item-modern text-center p-4" style="background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease; height: 100%;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 35px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.08)'">
                    <div class="stat-icon mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #059669 0%, #10b981 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <i class="fas fa-trophy" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div class="stat-number" style="font-size: 2.5rem; font-weight: 800; color: #2c3e50; margin-bottom: 0.5rem;" data-count="18500">0</div>
                    <div class="stat-label" style="color: #6c757d; font-weight: 500; font-size: 1.1rem;">Successful Graduates</div>
                    <div class="stat-subtext mt-2" style="color: #9ca3af; font-size: 0.9rem;">94% Success Rate</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-item-modern text-center p-4" style="background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease; height: 100%;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 35px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.08)'">
                    <div class="stat-icon mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <i class="fas fa-briefcase" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div class="stat-number" style="font-size: 2.5rem; font-weight: 800; color: #2c3e50; margin-bottom: 0.5rem;" data-count="12300">0</div>
                    <div class="stat-label" style="color: #6c757d; font-weight: 500; font-size: 1.1rem;">Career Placements</div>
                    <div class="stat-subtext mt-2" style="color: #9ca3af; font-size: 0.9rem;">Job Secured</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-item-modern text-center p-4" style="background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease; height: 100%;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 35px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.08)'">
                    <div class="stat-icon mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <i class="fas fa-globe-africa" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <div class="stat-number" style="font-size: 2.5rem; font-weight: 800; color: #2c3e50; margin-bottom: 0.5rem;" data-count="42">0</div>
                    <div class="stat-label" style="color: #6c757d; font-weight: 500; font-size: 1.1rem;">African Countries</div>
                    <div class="stat-subtext mt-2" style="color: #9ca3af; font-size: 0.9rem;">Coverage</div>
                </div>
            </div>
        </div>
        
        <!-- Additional Stats Row -->
        <div class="row g-4 mt-3">
            <div class="col-lg-4 col-md-6 mx-auto" data-aos="fade-up" data-aos-delay="500">
                <div class="stat-item-highlight text-center p-4" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border-radius: 16px; color: white; box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 35px rgba(79, 70, 229, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(79, 70, 229, 0.3)'">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-star me-2" style="font-size: 1.5rem;"></i>
                        <span class="stat-number" style="font-size: 2.2rem; font-weight: 700;" data-count="4.9">0</span>
                        <span style="font-size: 1.2rem; margin-left: 0.5rem;">/5.0</span>
                    </div>
                    <div class="stat-label" style="font-size: 1.1rem; font-weight: 500;">Student Satisfaction Rating</div>
                    <div class="stat-subtext mt-2" style="opacity: 0.9; font-size: 0.9rem;">Based on 15,000+ Reviews</div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mx-auto" data-aos="fade-up" data-aos-delay="600">
                <div class="stat-item-highlight text-center p-4" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border-radius: 16px; color: white; box-shadow: 0 8px 25px rgba(5, 150, 105, 0.3); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 35px rgba(5, 150, 105, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(5, 150, 105, 0.3)'">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-chart-line me-2" style="font-size: 1.5rem;"></i>
                        <span class="stat-number" style="font-size: 2.2rem; font-weight: 700;" data-count="85">0</span>
                        <span style="font-size: 1.2rem; margin-left: 0.5rem;">%</span>
                    </div>
                    <div class="stat-label" style="font-size: 1.1rem; font-weight: 500;">Average Salary Increase</div>
                    <div class="stat-subtext mt-2" style="opacity: 0.9; font-size: 0.9rem;">Within 6 Months</div>
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
<section class="py-5 programs-premium">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-10 mx-auto text-center" data-aos="fade-up">
                <div class="section-badge mb-4">
                    <i class="fas fa-fire"></i>
                    <span>Most Popular</span>
                </div>
                <h2 class="section-title-premium">Transform Your Career With Our
                    <span class="text-gradient-section">Top-Rated Courses</span>
                </h2>
                <p class="section-subtitle-premium">
                    Master in-demand skills in just days with our proven short courses.
                </p>
            </div>
        </div>
        
        <div class="row">
            <?php if (!empty($popular_programs)): ?>
                <?php foreach ($popular_programs as $index => $program): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                        <div class="card program-card h-100 border-0 shadow-sm" style="border-radius: 12px; transition: all 0.3s ease; overflow: hidden;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 35px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.08)'">
                            <div class="position-relative">
                                <img src="<?php echo !empty($program['image_url']) ? ASSETS_URL . 'images/programs/' . $program['image_url'] : 'https://via.placeholder.com/400x200?text=' . urlencode($program['title']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-primary rounded-pill">Popular</span>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column p-4">
                                <h5 class="card-title fw-bold mb-2" style="color: #2c3e50; font-size: 1.1rem;"><?php echo htmlspecialchars($program['title']); ?></h5>
                                <p class="card-text text-muted small mb-3" style="line-height: 1.4;"><?php echo htmlspecialchars(substr($program['description'], 0, 65)) . '...'; ?></p>
                                
                                <div class="mt-auto">
                                    <a href="<?php echo BASE_URL; ?>program-details.php?id=<?php echo $program['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4" style="font-weight: 500;">
                                        View Details <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Sample Popular Courses -->
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card program-card h-100 border-0 shadow-sm" style="border-radius: 12px; transition: all 0.3s ease; overflow: hidden;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 35px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.08)'">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/400x200?text=Web+Development" alt="Web Development" class="card-img-top" style="height: 200px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-primary rounded-pill">Popular</span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title fw-bold mb-2" style="color: #2c3e50; font-size: 1.1rem;">Full Stack Web Development</h5>
                            <p class="card-text text-muted small mb-3" style="line-height: 1.4;">Master modern web development with HTML, CSS, JavaScript...</p>
                            
                            <div class="mt-auto">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-outline-primary btn-sm rounded-pill px-4" style="font-weight: 500;">
                                    View Details <i class="fas fa-arrow-right ms-1"></i>
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
<section class="py-5" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); position: relative;">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-10 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">What Our Students Say</h2>
                <p class="section-subtitle text-muted">
                    Real stories from real students who have transformed their careers with our programs
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($testimonials) && count($testimonials) >= 3): ?>
                <?php foreach (array_slice($testimonials, 0, 6) as $index => $testimonial): ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                        <div class="card h-100 shadow-sm border-0 bg-white">
                            <div class="card-body p-4">
                                <div class="mb-3 text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="card-text text-muted mb-4 fst-italic">"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <?php if (!empty($testimonial['avatar'])): ?>
                                            <img src="<?php echo ASSETS_URL; ?>images/testimonials/<?php echo $testimonial['avatar']; ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="rounded-circle" width="50" height="50" style="object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-weight: bold;">
                                                <?php echo strtoupper(substr($testimonial['name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($testimonial['position'] ?? $testimonial['program_title'] ?? 'Graduate'); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Sample Testimonials -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="card h-100 shadow-sm border-0 bg-white">
                        <div class="card-body p-4">
                            <div class="mb-3 text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="card-text text-muted mb-4 fst-italic">"The Web Development program completely transformed my career. I went from having no coding experience to landing a job as a full-stack developer at a tech startup in Nairobi. The instructors were incredibly supportive and the hands-on projects prepared me for real-world challenges."</p>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-weight: bold;">
                                        A
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Amina Ochieng</h6>
                                    <small class="text-muted">Full Stack Developer at TechFlow Kenya</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="card h-100 shadow-sm border-0 bg-white">
                        <div class="card-body p-4">
                            <div class="mb-3 text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="card-text text-muted mb-4 fst-italic">"I never thought I could start my own business, but the Digital Marketing program gave me all the tools I needed. Now I'm running a successful e-commerce store and helping other small businesses grow their online presence. The ROI has been incredible!"</p>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-weight: bold;">
                                        J
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">James Wanjiku</h6>
                                    <small class="text-muted">E-commerce Entrepreneur</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="card h-100 shadow-sm border-0 bg-white">
                        <div class="card-body p-4">
                            <div class="mb-3 text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="card-text text-muted mb-4 fst-italic">"The Smart Agriculture program revolutionized how I approach farming. I've increased my crop yields by 40% and reduced water usage by 30% using the techniques I learned. This knowledge is invaluable for sustainable farming in Kenya."</p>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-weight: bold;">
                                        M
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Mary Kiprotich</h6>
                                    <small class="text-muted">Smart Farmer & Agricultural Consultant</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="card h-100 shadow-sm border-0 bg-white">
                        <div class="card-body p-4">
                            <div class="mb-3 text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="card-text text-muted mb-4 fst-italic">"As a healthcare professional, the Healthcare Management program helped me advance to a leadership role. The curriculum was practical and relevant to the African healthcare context. I now manage a team of 15 healthcare workers."</p>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-weight: bold;">
                                        D
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Dr. David Mwangi</h6>
                                    <small class="text-muted">Healthcare Administrator</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="card h-100 shadow-sm border-0 bg-white">
                        <div class="card-body p-4">
                            <div class="mb-3 text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="card-text text-muted mb-4 fst-italic">"The Data Science program opened up a whole new career path for me. I love how they made complex concepts easy to understand. Now I'm working as a data analyst and contributing to evidence-based decision making in government."</p>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-weight: bold;">
                                        S
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Sarah Nyong'o</h6>
                                    <small class="text-muted">Government Data Analyst</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                    <div class="card h-100 shadow-sm border-0 bg-white">
                        <div class="card-body p-4">
                            <div class="mb-3 text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="card-text text-muted mb-4 fst-italic">"The flexibility of the program allowed me to balance my studies with work and family commitments. The weekend sessions were perfect, and the online resources were comprehensive. I achieved my certification without disrupting my life."</p>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-weight: bold;">
                                        P
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Peter Otieno</h6>
                                    <small class="text-muted">Mobile App Developer</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-graduation-cap me-2"></i>Explore Our Courses
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-5 bg-light position-relative overflow-hidden">
    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3">Ready to Transform Your Future?</h2>
                <p class="section-subtitle text-muted mb-4 fs-5">
                    Join thousands of students who have already started their journey to success. Take the first step today.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary btn-lg px-4 py-3 rounded-pill shadow-sm cta-btn-hover">
                        <i class="fas fa-graduation-cap me-2"></i>Browse Courses
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-outline-primary btn-lg px-4 py-3 rounded-pill shadow-sm cta-btn-hover">
                        <i class="fas fa-phone me-2"></i>Get In Touch
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Decorative elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <div class="position-absolute top-0 end-0 text-primary" style="font-size: 8rem; transform: rotate(15deg); margin-top: -2rem; margin-right: -2rem;">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="position-absolute bottom-0 start-0 text-primary" style="font-size: 6rem; transform: rotate(-15deg); margin-bottom: -1rem; margin-left: -1rem;">
            <i class="fas fa-book-open"></i>
        </div>
    </div>
</section>

<style>
/* CTA Button Hover Effects */
.cta-btn-hover {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.cta-btn-hover::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: all 0.5s ease;
}

.cta-btn-hover:hover::before {
    left: 100%;
}

.cta-btn-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.btn-primary.cta-btn-hover:hover {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.btn-outline-primary.cta-btn-hover:hover {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}

/* Back to top button styling */
#back-to-top-footer {
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

#back-to-top-footer:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,123,255,0.4);
}
</style>

<script>
// Back to Top Button Functionality
document.addEventListener('DOMContentLoaded', function() {
    const backToTopBtn = document.getElementById('back-to-top-footer');
    
    if (backToTopBtn) {
        backToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
