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
                <h1 class="text-black mb-4" data-aos="fade-up">About Chania Skills for Africa</h1>
                <p class="text-black-50 fs-5 mb-0" data-aos="fade-up" data-aos-delay="200">
                    Empowering communities across Africa through transformative skills development, innovative training programs, and sustainable capacity building initiatives.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Hero Story Section -->
<section class="py-5 bg-light-gray">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="pe-lg-4">
                    <div class="badge bg-primary bg-opacity-10 text-black mb-3 px-3 py-2">
                        <i class="fas fa-heart me-2"></i>Our Story
                    </div>
                    <h2 class="display-6 fw-bold mb-4">Transforming Lives Through Skills Development</h2>
                    <p class="fs-5 text-muted mb-4">
                        Founded with a simple yet powerful belief: every individual deserves access to quality skills training that can transform their future and uplift entire communities across Africa.
                    </p>
                    <p class="mb-4">
                        Since our inception, we've been bridging the critical skills gap in technology, business, agriculture, and healthcare sectors, creating pathways to prosperity for thousands of African youth and professionals.
                    </p>
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Established</small>
                                    <strong>2015</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Headquarters</small>
                                    <strong>Nairobi, Kenya</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                <div class="position-relative">
                    <div class="row g-3">
                        <div class="col-12">
                            <img src="https://images.unsplash.com/photo-1544717297-fa95b6ee9643?w=600&h=300&fit=crop&crop=center" 
                                 alt="Students in training session" 
                                 class="img-fluid rounded-3 shadow-lg w-100" 
                                 style="height: 250px; object-fit: cover;">
                        </div>
                        <div class="col-6">
                            <img src="https://images.unsplash.com/photo-1573164713988-8665fc963095?w=300&h=200&fit=crop&crop=center" 
                                 alt="Technology training" 
                                 class="img-fluid rounded-3 shadow-sm w-100" 
                                 style="height: 150px; object-fit: cover;">
                        </div>
                        <div class="col-6">
                            <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=300&h=200&fit=crop&crop=center" 
                                 alt="Collaborative learning" 
                                 class="img-fluid rounded-3 shadow-sm w-100" 
                                 style="height: 150px; object-fit: cover;">
                        </div>
                    </div>
                    <!-- Floating stats card -->
                    <div class="position-absolute bottom-0 start-0 translate-middle-x bg-white rounded-3 shadow-lg p-4" style="width: 250px;">
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="text-primary fw-bold fs-4">9+</div>
                                <small class="text-muted">Years Impact</small>
                            </div>
                            <div class="col-6">
                                <div class="text-success fw-bold fs-4">15+</div>
                                <small class="text-muted">Countries</small>
                            </div>
                        </div>
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
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary text-black rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-bullseye fa-lg"></i>
                        </div>
                        <div>
                            <h2 class="section-title mb-1">Our Mission</h2>
                            <p class="text-primary mb-0">Empowering Communities</p>
                        </div>
                    </div>
                    <p class="fs-5 text-muted mb-4">
                        To empower communities across Africa by providing accessible, high-quality skills training that transforms lives, creates opportunities, and drives sustainable economic development.
                    </p>
                    <p class="mb-4">
                        We believe that every individual deserves the opportunity to unlock their potential through education and skills development. Our comprehensive programs are designed to bridge the skills gap in critical sectors.
                    </p>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                    <i class="fas fa-target"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Impact-Driven Approach</h6>
                                    <small class="text-muted">Measurable outcomes and real-world applications</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                <div class="position-relative">
                    <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?w=600&h=400&fit=crop&crop=center" 
                         alt="Students collaborating on projects" 
                         class="img-fluid rounded-3 shadow-lg main-image">
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-primary opacity-10 rounded-3"></div>
                    <!-- Mission highlight card -->
                    <div class="position-absolute bottom-0 end-0 translate-middle bg-white rounded-3 shadow-lg p-3 me-3" style="width: 200px;">
                        <div class="text-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 50px; height: 50px;">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="text-primary fw-bold fs-5">5,000+</div>
                            <small class="text-muted">Lives Transformed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2" data-aos="fade-left">
                <div class="ps-lg-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-eye fa-lg"></i>
                        </div>
                        <div>
                            <h2 class="section-title mb-1">Our Vision</h2>
                            <p class="text-secondary mb-0">Leading Africa's Future</p>
                        </div>
                    </div>
                    <p class="fs-5 text-muted mb-4">
                        To be Africa's leading skills development organization, creating a continent where every individual has access to quality education and training opportunities that enable them to thrive in the global economy.
                    </p>
                    <p class="mb-4">
                        We envision a future where skills gaps are eliminated, communities are self-sufficient, and African talent contributes significantly to global innovation and economic growth.
                    </p>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded-3 h-100">
                                <i class="fas fa-globe-africa text-primary fa-2x mb-2"></i>
                                <h6 class="mb-1">Pan-African</h6>
                                <small class="text-muted">Reach</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded-3 h-100">
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
                    <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=600&h=400&fit=crop&crop=center" 
                         alt="African students in technology lab" 
                         class="img-fluid rounded-3 shadow-lg main-image">
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-secondary opacity-10 rounded-3"></div>
                    <!-- Vision highlight card -->
                    <div class="position-absolute top-0 start-0 translate-middle bg-white rounded-3 shadow-lg p-3 ms-3" style="width: 180px;">
                        <div class="text-center">
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 50px; height: 50px;">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div class="text-secondary fw-bold fs-5">25+</div>
                            <small class="text-muted">Programs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Impact Showcase Section -->
<section class="py-5 bg-light-gray">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">Our Impact in Action</h2>
                <p class="section-subtitle text-muted">
                    See how we're transforming communities across Africa through skills development
                </p>
            </div>
        </div>
        
        <div class="row align-items-center mb-5">
            <div class="col-lg-7" data-aos="fade-right">
                <div class="position-relative">
                    <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?w=700&h=450&fit=crop&crop=center" 
                         alt="Agricultural training program in rural Kenya" 
                         class="img-fluid rounded-3 shadow-lg w-100" 
                         style="height: 400px; object-fit: cover;">
                    <!-- Play button overlay -->
                    <div class="position-absolute top-50 start-50 translate-middle">
                        <button class="btn btn-primary btn-lg rounded-circle" style="width: 80px; height: 80px;">
                            <i class="fas fa-play fs-4"></i>
                        </button>
                    </div>
                    <!-- Success badge -->
                    <div class="position-absolute top-0 end-0 m-3">
                        <span class="badge bg-success px-3 py-2 rounded-pill">
                            <i class="fas fa-check me-1"></i>Success Story
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-5" data-aos="fade-left" data-aos-delay="200">
                <div class="ps-lg-4">
                    <div class="badge bg-success bg-opacity-10 text-success mb-3 px-3 py-2">
                        <i class="fas fa-seedling me-2"></i>Agriculture Program
                    </div>
                    <h3 class="mb-4">Transforming Rural Farming in Kenya</h3>
                    <p class="text-muted mb-4">
                        Our agricultural training program has helped over 2,000 farmers in rural Kenya adopt modern farming techniques, increasing their yield by an average of 40% and improving their livelihoods significantly.
                    </p>
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-3 bg-white rounded-3 text-center">
                                <div class="text-success fw-bold fs-4">2,000+</div>
                                <small class="text-muted">Farmers Trained</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-white rounded-3 text-center">
                                <div class="text-success fw-bold fs-4">40%</div>
                                <small class="text-muted">Yield Increase</small>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="btn btn-success">
                        <i class="fas fa-arrow-right me-2"></i>Read Full Story
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row align-items-center">
            <div class="col-lg-5 order-lg-1" data-aos="fade-right">
                <div class="pe-lg-4">
                    <div class="badge bg-primary bg-opacity-10 text-black mb-3 px-3 py-2">
                        <i class="fas fa-code me-2"></i>Technology Program
                    </div>
                    <h3 class="mb-4">Building Tech Talent in Nigeria</h3>
                    <p class="text-muted mb-4">
                        Our coding bootcamp in Lagos has graduated over 1,500 software developers, with 85% securing employment within 6 months of completion. These graduates are now contributing to Nigeria's growing tech ecosystem.
                    </p>
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-3 bg-white rounded-3 text-center">
                                <div class="text-primary fw-bold fs-4">1,500+</div>
                                <small class="text-muted">Developers</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-white rounded-3 text-center">
                                <div class="text-primary fw-bold fs-4">85%</div>
                                <small class="text-muted">Employment Rate</small>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-arrow-right me-2"></i>View Program
                    </a>
                </div>
            </div>
            <div class="col-lg-7 order-lg-2" data-aos="fade-left" data-aos-delay="200">
                <div class="position-relative">
                    <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?w=700&h=450&fit=crop&crop=center" 
                         alt="Software development training in Lagos, Nigeria" 
                         class="img-fluid rounded-3 shadow-lg w-100" 
                         style="height: 400px; object-fit: cover;">
                    <!-- Achievement badge -->
                    <div class="position-absolute bottom-0 start-0 m-3">
                        <div class="bg-white rounded-3 shadow-sm p-3" style="width: 200px;">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Award Winner</div>
                                    <small class="text-muted">Best Tech Program 2023</small>
                                </div>
                            </div>
                        </div>
                    </div>
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
