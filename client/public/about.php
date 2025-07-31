<?php
require_once '../includes/config.php';

$page_title = 'About Us';
$page_description = 'Learn about Chania Skills for Africa\'s mission to empower communities through transformative skills development and training programs across Africa.';
$page_keywords = 'about, mission, vision, team, skills training, Africa, education, empowerment';

// Fetch team members (with fallback data if database is empty)
try {
    $team_query = "SELECT * FROM team_members WHERE is_active = 1 AND deleted_at IS NULL ORDER BY sort_order ASC, created_at ASC";
    $team_members = $db->query($team_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $team_members = [];
}

// Fetch achievements/stats
try {
    $achievements_query = "
        SELECT 
            (SELECT COUNT(*) FROM programs WHERE is_active = 1 AND deleted_at IS NULL) as total_programs,
            (SELECT COUNT(*) FROM applications WHERE status IN ('approved', 'completed') AND deleted_at IS NULL) as successful_graduates,
            (SELECT COUNT(*) FROM events WHERE is_active = 1 AND deleted_at IS NULL) as events_conducted,
            (SELECT COUNT(DISTINCT program_id) FROM applications WHERE deleted_at IS NULL) as programs_with_students
    ";
    $achievements = $db->query($achievements_query)->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $achievements = [
        'total_programs' => 25,
        'successful_graduates' => 5000,
        'events_conducted' => 150,
        'programs_with_students' => 20
    ];
}

include '../includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="header-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>
    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-4" data-aos="fade-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">About Us</li>
                    </ol>
                </nav>
                <h1 class="text-white mb-4" data-aos="fade-up">About Chania Skills for Africa</h1>
                <p class="text-white-50 fs-5 mb-0" data-aos="fade-up" data-aos-delay="200">
                    Empowering communities across Africa through transformative skills development, innovative training programs, and sustainable capacity building initiatives.
                </p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $achievements['total_programs'] ?: '25'; ?>+</div>
                        <div class="stat-label">Training Programs</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo number_format($achievements['successful_graduates'] ?: 5000); ?>+</div>
                        <div class="stat-label">Lives Transformed</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $achievements['events_conducted'] ?: '150'; ?>+</div>
                        <div class="stat-label">Events Conducted</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Countries Served</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="pe-lg-4">
                    <h2 class="section-title mb-4">Our Mission</h2>
                    <p class="fs-5 text-muted mb-4">
                        To empower communities across Africa by providing accessible, high-quality skills training that transforms lives, creates opportunities, and drives sustainable economic development.
                    </p>
                    <p class="mb-4">
                        We believe that every individual deserves the opportunity to unlock their potential through education and skills development. Our comprehensive programs are designed to bridge the skills gap in critical sectors including technology, business, agriculture, and healthcare.
                    </p>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-target fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Impact-Driven Approach</h6>
                            <p class="text-muted mb-0">Every program is designed with measurable outcomes and real-world applications.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                <div class="position-relative">
                    <img src="https://via.placeholder.com/600x400?text=Our+Mission" alt="Our Mission" class="img-fluid rounded-3 shadow-lg">
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-primary opacity-10 rounded-3"></div>
                </div>
            </div>
        </div>
        
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2" data-aos="fade-left">
                <div class="ps-lg-4">
                    <h2 class="section-title mb-4">Our Vision</h2>
                    <p class="fs-5 text-muted mb-4">
                        To be Africa's leading skills development organization, creating a continent where every individual has access to quality education and training opportunities that enable them to thrive in the global economy.
                    </p>
                    <p class="mb-4">
                        We envision a future where skills gaps are eliminated, communities are self-sufficient, and African talent contributes significantly to global innovation and economic growth.
                    </p>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded-3">
                                <i class="fas fa-globe-africa text-primary fa-2x mb-2"></i>
                                <h6 class="mb-1">Pan-African</h6>
                                <small class="text-muted">Reach</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded-3">
                                <i class="fas fa-users text-primary fa-2x mb-2"></i>
                                <h6 class="mb-1">Community</h6>
                                <small class="text-muted">Focused</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 order-lg-1" data-aos="fade-right" data-aos-delay="200">
                <div class="position-relative">
                    <img src="https://via.placeholder.com/600x400?text=Our+Vision" alt="Our Vision" class="img-fluid rounded-3 shadow-lg">
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-secondary opacity-10 rounded-3"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-5 bg-light-gray">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">Our Core Values</h2>
                <p class="section-subtitle text-muted">
                    The principles that guide everything we do and shape our approach to skills development
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-heart fa-2x"></i>
                        </div>
                        <h5 class="card-title">Excellence</h5>
                        <p class="card-text text-muted">
                            We strive for the highest standards in everything we do, from curriculum design to student support and outcomes measurement.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body text-center p-4">
                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-hands-helping fa-2x"></i>
                        </div>
                        <h5 class="card-title">Inclusivity</h5>
                        <p class="card-text text-muted">
                            We believe in equal access to opportunities regardless of background, ensuring our programs are accessible to all communities.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body text-center p-4">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-seedling fa-2x"></i>
                        </div>
                        <h5 class="card-title">Sustainability</h5>
                        <p class="card-text text-muted">
                            Our programs are designed to create lasting impact, building capacity within communities for long-term growth and development.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body text-center p-4">
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-lightbulb fa-2x"></i>
                        </div>
                        <h5 class="card-title">Innovation</h5>
                        <p class="card-text text-muted">
                            We embrace cutting-edge teaching methods and technologies to deliver engaging, effective learning experiences.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body text-center p-4">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-handshake fa-2x"></i>
                        </div>
                        <h5 class="card-title">Integrity</h5>
                        <p class="card-text text-muted">
                            We operate with transparency, honesty, and accountability in all our relationships and operations.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body text-center p-4">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-rocket fa-2x"></i>
                        </div>
                        <h5 class="card-title">Empowerment</h5>
                        <p class="card-text text-muted">
                            We focus on building confidence, capabilities, and opportunities that enable individuals to take control of their futures.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">Meet Our Team</h2>
                <p class="section-subtitle text-muted">
                    Passionate professionals dedicated to transforming lives through skills development
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($team_members)): ?>
                <?php foreach ($team_members as $index => $member): ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                        <div class="card border-0 shadow-sm hover-shadow h-100">
                            <div class="card-body text-center p-4">
                                <div class="position-relative mb-3">
                                    <?php if (!empty($member['photo'])): ?>
                                        <img src="<?php echo ASSETS_URL; ?>images/team/<?php echo $member['photo']; ?>" 
                                             alt="<?php echo htmlspecialchars($member['name']); ?>" 
                                             class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                                            <span class="fs-2 fw-bold"><?php echo strtoupper(substr($member['name'], 0, 1)); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($member['name']); ?></h5>
                                <p class="text-primary fw-semibold"><?php echo htmlspecialchars($member['position']); ?></p>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($member['bio'] ?? 'Dedicated team member committed to our mission of skills development.'); ?></p>
                                <?php if (!empty($member['linkedin']) || !empty($member['twitter'])): ?>
                                    <div class="social-links">
                                        <?php if (!empty($member['linkedin'])): ?>
                                            <a href="<?php echo htmlspecialchars($member['linkedin']); ?>" class="btn btn-outline-primary btn-sm me-2" target="_blank">
                                                <i class="fab fa-linkedin"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($member['twitter'])): ?>
                                            <a href="<?php echo htmlspecialchars($member['twitter']); ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Sample Team Members -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="card border-0 shadow-sm hover-shadow h-100">
                        <div class="card-body text-center p-4">
                            <div class="position-relative mb-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                                    <span class="fs-2 fw-bold">J</span>
                                </div>
                            </div>
                            <h5 class="card-title">John Kamau</h5>
                            <p class="text-primary fw-semibold">Executive Director</p>
                            <p class="card-text text-muted">John brings over 15 years of experience in education and community development, leading our strategic vision and program implementation.</p>
                            <div class="social-links">
                                <a href="#" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="card border-0 shadow-sm hover-shadow h-100">
                        <div class="card-body text-center p-4">
                            <div class="position-relative mb-3">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                                    <span class="fs-2 fw-bold">S</span>
                                </div>
                            </div>
                            <h5 class="card-title">Sarah Wanjiku</h5>
                            <p class="text-primary fw-semibold">Programs Director</p>
                            <p class="card-text text-muted">Sarah oversees curriculum development and program delivery, ensuring quality and relevance across all our training initiatives.</p>
                            <div class="social-links">
                                <a href="#" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="card border-0 shadow-sm hover-shadow h-100">
                        <div class="card-body text-center p-4">
                            <div class="position-relative mb-3">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                                    <span class="fs-2 fw-bold">M</span>
                                </div>
                            </div>
                            <h5 class="card-title">Michael Ochieng</h5>
                            <p class="text-primary fw-semibold">Technology Lead</p>
                            <p class="card-text text-muted">Michael leads our technology programs and digital transformation initiatives, bringing cutting-edge skills to our communities.</p>
                            <div class="social-links">
                                <a href="#" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="card border-0 shadow-sm hover-shadow h-100">
                        <div class="card-body text-center p-4">
                            <div class="position-relative mb-3">
                                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                                    <span class="fs-2 fw-bold">A</span>
                                </div>
                            </div>
                            <h5 class="card-title">Amina Hassan</h5>
                            <p class="text-primary fw-semibold">Community Outreach Manager</p>
                            <p class="card-text text-muted">Amina coordinates our community engagement efforts and ensures our programs reach underserved populations across Africa.</p>
                            <div class="social-links">
                                <a href="#" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="card border-0 shadow-sm hover-shadow h-100">
                        <div class="card-body text-center p-4">
                            <div class="position-relative mb-3">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                                    <span class="fs-2 fw-bold">D</span>
                                </div>
                            </div>
                            <h5 class="card-title">David Mwangi</h5>
                            <p class="text-primary fw-semibold">Research & Development</p>
                            <p class="card-text text-muted">David leads our research initiatives and impact measurement, ensuring our programs are evidence-based and effective.</p>
                            <div class="social-links">
                                <a href="#" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                    <div class="card border-0 shadow-sm hover-shadow h-100">
                        <div class="card-body text-center p-4">
                            <div class="position-relative mb-3">
                                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                                    <span class="fs-2 fw-bold">G</span>
                                </div>
                            </div>
                            <h5 class="card-title">Grace Nyong'o</h5>
                            <p class="text-primary fw-semibold">Student Success Manager</p>
                            <p class="card-text text-muted">Grace ensures our students receive the support they need to succeed, from enrollment through graduation and beyond.</p>
                            <div class="social-links">
                                <a href="#" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="mb-4">Join Our Mission</h2>
                <p class="fs-5 mb-4 opacity-90">
                    Be part of the transformation. Whether you're looking to develop new skills or support our cause, there's a place for you in our community.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-graduation-cap me-2"></i>Browse Programs
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-envelope me-2"></i>Get Involved
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.social-links .btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
</style>
