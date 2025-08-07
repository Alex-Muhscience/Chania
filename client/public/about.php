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

// Fetch achievements from the achievements table
try {
    $achievements_query = "SELECT * FROM achievements WHERE is_active = 1 AND deleted_at IS NULL ORDER BY display_order ASC, created_at ASC";
    $dynamic_achievements = $db->query($achievements_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dynamic_achievements = [];
}

// Fetch system stats for fallback
try {
    $stats_query = "
        SELECT 
            (SELECT COUNT(*) FROM programs WHERE is_active = 1 AND deleted_at IS NULL) as total_programs,
            (SELECT COUNT(*) FROM applications WHERE status IN ('approved', 'completed') AND deleted_at IS NULL) as successful_graduates,
            (SELECT COUNT(*) FROM events WHERE is_active = 1 AND deleted_at IS NULL) as events_conducted,
            (SELECT COUNT(DISTINCT program_id) FROM applications WHERE deleted_at IS NULL) as programs_with_students
    ";
    $system_stats = $db->query($stats_query)->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $system_stats = [
        'total_programs' => 25,
        'successful_graduates' => 5000,
        'events_conducted' => 150,
        'programs_with_students' => 20
    ];
}

// Fetch impact blogs for the Our Impact section
try {
    $impact_blogs_query = "
        SELECT id, title, slug, excerpt, content, category, featured_image, video_url, 
               video_embed_code, stats_data, author_name, published_at, view_count
        FROM impact_blogs 
        WHERE is_active = 1 AND deleted_at IS NULL 
        ORDER BY sort_order ASC, created_at DESC 
        LIMIT 6
    ";
    $impact_blogs = $db->query($impact_blogs_query)->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode JSON data
    foreach ($impact_blogs as $key => $blog) {
        if ($blog['stats_data']) {
            $impact_blogs[$key]['stats_data'] = json_decode($blog['stats_data'], true);
        }
    }
    // Important: Clear the reference to prevent issues
    unset($blog);
} catch (PDOException $e) {
    $impact_blogs = [];
}

// Fetch partners
try {
    $partners_query = "
        SELECT id, name, description, logo_path, website_url, partnership_type, 
               partnership_level, is_featured, display_order
        FROM partners 
        WHERE is_active = 1 AND deleted_at IS NULL 
        ORDER BY is_featured DESC, partnership_level ASC, display_order ASC, created_at DESC
    ";
    $partners = $db->query($partners_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $partners = [];
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
                    <img src="https://images.unsplash.com/photo-1544027993-37dbfe43562a?w=600&h=400&fit=crop&crop=faces" 
                         alt="African students in classroom learning skills development" 
                         class="img-fluid rounded-3 shadow-lg main-image" 
                         style="width: 100%; height: 400px; object-fit: cover; position: relative; z-index: 1;">
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-primary rounded-3" style="opacity: 0.1; z-index: 2;"></div>
                    <!-- Mission highlight card -->
                    <div class="position-absolute bottom-0 end-0 translate-middle bg-white rounded-3 shadow-lg p-3 me-3" style="width: 200px; z-index: 3;">
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
                    <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop&crop=faces" 
                         alt="African professionals in business meeting planning future development" 
                         class="img-fluid rounded-3 shadow-lg main-image" 
                         style="width: 100%; height: 400px; object-fit: cover; position: relative; z-index: 1;">
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-secondary rounded-3" style="opacity: 0.1; z-index: 2;"></div>
                    <!-- Vision highlight card -->
                    <div class="position-absolute top-0 start-0 translate-middle bg-white rounded-3 shadow-lg p-3 ms-3" style="width: 180px; z-index: 3;">
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

<!-- Achievements Section -->
<?php if (!empty($dynamic_achievements)): ?>
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="badge bg-white text-primary mb-3 px-3 py-2">
                    <i class="fas fa-trophy me-2"></i>Our Impact
                </div>
                <h2 class="display-6 fw-bold mb-4">By the Numbers</h2>
                <p class="fs-5 mb-0 opacity-90">
                    Our achievements speak for themselves - transforming lives and communities across Africa through quality skills development.
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($dynamic_achievements as $index => $achievement): ?>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <div class="achievement-card text-center h-100">
                        <div class="achievement-icon mb-4">
                            <div class="icon-wrapper bg-white text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;">
                                <i class="<?= htmlspecialchars($achievement['icon'] ?? 'fas fa-trophy') ?> fa-2x"></i>
                            </div>
                        </div>
                        <div class="achievement-content">
                            <div class="achievement-number display-4 fw-bold mb-2">
                                <?= htmlspecialchars($achievement['stat_value']) ?><?= htmlspecialchars($achievement['stat_unit']) ?>
                            </div>
                            <h5 class="achievement-title fw-bold mb-3"><?= htmlspecialchars($achievement['title']) ?></h5>
                            <?php if (!empty($achievement['description'])): ?>
                                <p class="achievement-description text-white-75 small mb-0">
                                    <?= htmlspecialchars($achievement['description']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php if ($achievement['is_featured']): ?>
                            <div class="achievement-badge position-absolute top-0 end-0 m-3">
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-star me-1"></i>Featured
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Achievement CTA -->
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up" data-aos-delay="600">
                <div class="achievement-cta">
                    <p class="fs-5 mb-4 opacity-90">
                        Be part of the transformation. Whether you're looking to develop new skills or support our cause, there's a place for you in our community.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                        <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-light btn-lg">
                            <i class="fas fa-graduation-cap me-2"></i>Browse Programs
                        </a>
                        <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-envelope me-2"></i>Get Involved
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

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
        
        <?php if (!empty($impact_blogs)): ?>
            <?php foreach ($impact_blogs as $index => $blog): ?>
                <?php 
                    $isEven = $index % 2 == 0;
                    $categoryColors = [
                        'agriculture' => ['success', 'seedling'],
                        'technology' => ['secondary', 'code'],
                        'business' => ['warning', 'briefcase'],
                        'healthcare' => ['info', 'heartbeat'],
                        'education' => ['secondary', 'graduation-cap'],
                        'environment' => ['success', 'leaf']
                    ];
                    $categoryInfo = $categoryColors[$blog['category']] ?? ['secondary', 'bookmark'];
                    $badgeColor = $categoryInfo[0];
                    $iconClass = $categoryInfo[1];
                ?>
                <div class="row align-items-center <?= $index < count($impact_blogs) - 1 ? 'mb-5' : '' ?>">
                    <?php if ($isEven): ?>
                        <!-- Image on left, content on right -->
                        <div class="col-lg-7" data-aos="fade-right">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($blog['featured_image'] ?: 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=700&h=450&fit=crop&crop=center') ?>" 
                                     alt="<?= htmlspecialchars($blog['title']) ?>" 
                                     class="img-fluid rounded-3 shadow-lg w-100" 
                                     style="height: 400px; object-fit: cover;">
                                
                                <?php if ($blog['video_url'] || $blog['video_embed_code']): ?>
                                    <!-- Play button overlay -->
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <button class="btn btn-primary btn-lg rounded-circle video-play-btn" 
                                                data-video="<?= htmlspecialchars($blog['video_url']) ?>"
                                                style="width: 80px; height: 80px;">
                                            <i class="fas fa-play fs-4"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Success badge -->
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-<?= $badgeColor ?> px-3 py-2 rounded-pill">
                                        <i class="fas fa-check me-1"></i>Success Story
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5" data-aos="fade-left" data-aos-delay="200">
                            <div class="ps-lg-4">
                                <div class="badge bg-<?= $badgeColor ?> bg-opacity-10 text-<?= $badgeColor ?> mb-3 px-3 py-2">
                                    <i class="fas fa-<?= $iconClass ?> me-2"></i><?= ucwords($blog['category']) ?> Program
                                </div>
                                <h3 class="mb-4"><?= htmlspecialchars($blog['title']) ?></h3>
                                <p class="text-muted mb-4">
                                    <?= htmlspecialchars($blog['excerpt']) ?>
                                </p>
                                
                                <?php if ($blog['stats_data'] && is_array($blog['stats_data'])): ?>
                                    <div class="row g-3 mb-4">
                                        <?php $statCount = 0; foreach ($blog['stats_data'] as $label => $value): 
                                            if ($statCount >= 2) break; ?>
                                            <div class="col-6">
                                                <div class="p-3 bg-white rounded-3 text-center">
                                                    <div class="text-<?= $badgeColor ?> fw-bold fs-4"><?= htmlspecialchars($value) ?></div>
                                                    <small class="text-muted"><?= ucwords(str_replace('_', ' ', $label)) ?></small>
                                                </div>
                                            </div>
                                        <?php $statCount++; endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex align-items-center gap-3">
                                    <a href="#blog-<?= $blog['id'] ?>" class="btn btn-<?= $badgeColor ?>" onclick="showFullStory(<?= $blog['id'] ?>)">
                                        <i class="fas fa-arrow-right me-2"></i>Read Full Story
                                    </a>
                                    <?php if ($blog['author_name']): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>by <?= htmlspecialchars($blog['author_name']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Content on left, image on right -->
                        <div class="col-lg-5 order-lg-1" data-aos="fade-right">
                            <div class="pe-lg-4">
                                <div class="badge bg-<?= $badgeColor ?> bg-opacity-10 text-<?= $badgeColor ?> mb-3 px-3 py-2">
                                    <i class="fas fa-<?= $iconClass ?> me-2"></i><?= ucwords($blog['category']) ?> Program
                                </div>
                                <h3 class="mb-4"><?= htmlspecialchars($blog['title']) ?></h3>
                                <p class="text-muted mb-4">
                                    <?= htmlspecialchars($blog['excerpt']) ?>
                                </p>
                                
                                <?php if ($blog['stats_data'] && is_array($blog['stats_data'])): ?>
                                    <div class="row g-3 mb-4">
                                        <?php $statCount = 0; foreach ($blog['stats_data'] as $label => $value): 
                                            if ($statCount >= 2) break; ?>
                                            <div class="col-6">
                                                <div class="p-3 bg-white rounded-3 text-center">
                                                    <div class="text-<?= $badgeColor ?> fw-bold fs-4"><?= htmlspecialchars($value) ?></div>
                                                    <small class="text-muted"><?= ucwords(str_replace('_', ' ', $label)) ?></small>
                                                </div>
                                            </div>
                                        <?php $statCount++; endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex align-items-center gap-3">
                                    <a href="#blog-<?= $blog['id'] ?>" class="btn btn-<?= $badgeColor ?>" onclick="showFullStory(<?= $blog['id'] ?>)">
                                        <i class="fas fa-arrow-right me-2"></i>Read Full Story
                                    </a>
                                    <?php if ($blog['author_name']): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>by <?= htmlspecialchars($blog['author_name']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7 order-lg-2" data-aos="fade-left" data-aos-delay="200">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($blog['featured_image'] ?: 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=700&h=450&fit=crop&crop=center') ?>" 
                                     alt="<?= htmlspecialchars($blog['title']) ?>" 
                                     class="img-fluid rounded-3 shadow-lg w-100" 
                                     style="height: 400px; object-fit: cover;">
                                
                                <?php if ($blog['video_url'] || $blog['video_embed_code']): ?>
                                    <!-- Play button overlay -->
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <button class="btn btn-primary btn-lg rounded-circle video-play-btn" 
                                                data-video="<?= htmlspecialchars($blog['video_url']) ?>"
                                                style="width: 80px; height: 80px;">
                                            <i class="fas fa-play fs-4"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Achievement badge -->
                                <div class="position-absolute bottom-0 start-0 m-3">
                                    <div class="bg-white rounded-3 shadow-sm p-3" style="width: 200px;">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-<?= $badgeColor ?> text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="fas fa-trophy"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">Impact Story</div>
                                                <small class="text-muted"><?= ucwords($blog['category']) ?> Success</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback content if no blogs -->
            <div class="text-center py-5">
                <i class="fas fa-blog fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Impact Stories Coming Soon</h5>
                <p class="text-muted">We're working on sharing our latest impact stories with you.</p>
            </div>
        <?php endif; ?>
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
                                    <?php if (!empty($member['image'])): ?>
                                        <img src="<?php echo BASE_URL; ?>/uploads/team/<?php echo htmlspecialchars($member['image']); ?>" 
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
                                
                                <?php 
                                $social_links = [];
                                if (!empty($member['social_links'])) {
                                    $social_links = json_decode($member['social_links'], true) ?: [];
                                }
                                ?>
                                
                                <?php if (!empty($social_links)): ?>
                                    <div class="social-links">
                                        <?php foreach ($social_links as $platform => $url): ?>
                                            <?php if (!empty($url)): ?>
                                                <a href="<?php echo htmlspecialchars($url); ?>" class="btn btn-outline-primary btn-sm me-2" target="_blank" rel="noopener noreferrer">
                                                    <?php
                                                    $platform_lower = strtolower($platform);
                                                    $icon_class = 'fab fa-' . $platform_lower;
                                                    
                                                    // Map common platform names to Font Awesome icons
                                                    $icon_map = [
                                                        'website' => 'fas fa-globe',
                                                        'email' => 'fas fa-envelope',
                                                        'phone' => 'fas fa-phone',
                                                        'whatsapp' => 'fab fa-whatsapp',
                                                        'x' => 'fab fa-x-twitter',
                                                        'twitter' => 'fab fa-twitter',
                                                        'instagram' => 'fab fa-instagram',
                                                        'facebook' => 'fab fa-facebook',
                                                        'linkedin' => 'fab fa-linkedin',
                                                        'youtube' => 'fab fa-youtube',
                                                        'github' => 'fab fa-github',
                                                        'portfolio' => 'fas fa-briefcase'
                                                    ];
                                                    
                                                    $icon_class = $icon_map[$platform_lower] ?? 'fas fa-link';
                                                    ?>
                                                    <i class="<?php echo $icon_class; ?>"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
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

<!-- Our Partners Section -->
<?php if (!empty($partners)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="badge bg-primary bg-opacity-10 text-white mb-3 px-3 py-2">
                    <i class="fas fa-handshake me-2"></i>Trusted Partners
                </div>
                <h2 class="display-6 fw-bold mb-4">Working Together for Impact</h2>
                <p class="fs-5 text-muted mb-0">
                    We collaborate with leading organizations, institutions, and foundations to maximize our impact and reach across Africa.
                </p>
            </div>
        </div>
        
        <!-- Featured Partners Grid -->
        <?php 
        $featured_partners = array_filter($partners, function($p) { return $p['is_featured'] == 1; });
        if (!empty($featured_partners)): 
        ?>
        <div class="row mb-5" data-aos="fade-up" data-aos-delay="200">
            <div class="col-12">
                <h4 class="text-center mb-4 text-muted">Strategic Partners</h4>
                <div class="row justify-content-center g-4">
                    <?php foreach ($featured_partners as $index => $partner): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="<?php echo 100 + ($index * 100); ?>">
                            <div class="partner-card text-center">
                                <?php if (!empty($partner['logo_path'])): ?>
                                    <div class="partner-logo-wrapper">
                                        <img src="<?php echo ASSETS_URL; ?>images/partners/<?php echo $partner['logo_path']; ?>" 
                                             alt="<?php echo htmlspecialchars($partner['name']); ?>" 
                                             class="partner-logo-featured" 
                                             title="<?php echo htmlspecialchars($partner['name']); ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="partner-logo-placeholder">
                                        <span><?php echo htmlspecialchars($partner['name']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <h6 class="mt-3 mb-2"><?php echo htmlspecialchars($partner['name']); ?></h6>
                                <p class="text-muted small mb-0"><?php echo htmlspecialchars($partner['description']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- All Partners Carousel -->
        <div class="partners-carousel-section" data-aos="fade-up" data-aos-delay="400">
            <h4 class="text-center mb-4 text-muted">All Partners</h4>
            <div class="partners-carousel-wrapper">
                <div class="partners-carousel" id="partnersCarousel">
                    <div class="partners-track">
                        <?php 
                        // Duplicate partners array for seamless loop
                        $partnersForCarousel = array_merge($partners, $partners);
                        foreach ($partnersForCarousel as $partner): 
                        ?>
                            <div class="partner-slide">
                                <?php if (!empty($partner['logo_path'])): ?>
                                    <img src="<?php echo ASSETS_URL; ?>images/partners/<?php echo $partner['logo_path']; ?>" 
                                         alt="<?php echo htmlspecialchars($partner['name']); ?>" 
                                         class="partner-logo" 
                                         title="<?php echo htmlspecialchars($partner['name']); ?>">
                                <?php else: ?>
                                    <div class="partner-placeholder">
                                        <span><?php echo htmlspecialchars($partner['name']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Partnership CTA -->
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up" data-aos-delay="600">
                <div class="bg-white rounded-3 shadow-sm p-4">
                    <h5 class="mb-3">Interested in Partnering with Us?</h5>
                    <p class="text-muted mb-4">Join our network of partners and help us create lasting impact across Africa.</p>
                    <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-primary">
                        <i class="fas fa-envelope me-2"></i>Get in Touch
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

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

<!-- Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="videoModalLabel">Impact Story</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <iframe id="videoFrame" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Blog Detail Modal -->
<div class="modal fade" id="blogDetailModal" tabindex="-1" aria-labelledby="blogDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="blogDetailModalLabel">Full Story</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="blogDetailContent">
                <!-- Blog content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// YouTube Video Functionality
function getYouTubeVideoId(url) {
    const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
    const match = url.match(regex);
    return match ? match[1] : null;
}

// Handle video play button clicks
document.addEventListener('DOMContentLoaded', function() {
    const videoPlayBtns = document.querySelectorAll('.video-play-btn');
    const videoModal = new bootstrap.Modal(document.getElementById('videoModal'));
    const videoFrame = document.getElementById('videoFrame');
    const blogDetailModal = new bootstrap.Modal(document.getElementById('blogDetailModal'));
    
    videoPlayBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const videoUrl = this.getAttribute('data-video');
            if (videoUrl) {
                const videoId = getYouTubeVideoId(videoUrl);
                if (videoId) {
                    videoFrame.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
                    videoModal.show();
                }
            }
        });
    });
    
    // Clear video when modal is closed
    document.getElementById('videoModal').addEventListener('hidden.bs.modal', function() {
        videoFrame.src = '';
    });
});

// Show full story functionality
function showFullStory(blogId) {
    const blogDetailModal = new bootstrap.Modal(document.getElementById('blogDetailModal'));
    const content = document.getElementById('blogDetailContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading story...</p>
        </div>
    `;
    
    blogDetailModal.show();
    
    // Fetch blog details
    fetch(`${window.location.origin}/chania/client/api/get_blog_details.php?id=${blogId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const blog = data.blog;
                const categoryColors = {
                    'agriculture': ['success', 'seedling'],
                    'technology': ['primary', 'code'],
                    'business': ['warning', 'briefcase'],
                    'healthcare': ['info', 'heartbeat'],
                    'education': ['secondary', 'graduation-cap'],
                    'environment': ['success', 'leaf']
                };
                const categoryInfo = categoryColors[blog.category] || ['secondary', 'bookmark'];
                const badgeColor = categoryInfo[0];
                const iconClass = categoryInfo[1];
                
                let videoSection = '';
                if (blog.video_url) {
                    const videoId = getYouTubeVideoId(blog.video_url);
                    if (videoId) {
                        videoSection = `
                            <div class="ratio ratio-16x9 mb-4">
                                <iframe src="https://www.youtube.com/embed/${videoId}" 
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                        allowfullscreen></iframe>
                            </div>
                        `;
                    }
                }
                
                let statsSection = '';
                if (blog.stats_data && Object.keys(blog.stats_data).length > 0) {
                    statsSection = `
                        <div class="row g-3 mb-4">
                            ${Object.entries(blog.stats_data).map(([label, value]) => `
                                <div class="col-md-3 col-6">
                                    <div class="card bg-light border-0 text-center h-100">
                                        <div class="card-body py-3">
                                            <div class="text-${badgeColor} fw-bold fs-4">${value}</div>
                                            <small class="text-muted">${label.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</small>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                }
                
                content.innerHTML = `
                    <div class="mb-4">
                        <div class="badge bg-${badgeColor} bg-opacity-10 text-${badgeColor} mb-3 px-3 py-2">
                            <i class="fas fa-${iconClass} me-2"></i>${blog.category.charAt(0).toUpperCase() + blog.category.slice(1)} Program
                        </div>
                        <h2 class="mb-3">${blog.title}</h2>
                        <p class="text-muted mb-0">
                            <i class="fas fa-calendar me-2"></i>Published: ${new Date(blog.published_at).toLocaleDateString()}
                            ${blog.author_name ? `<span class="ms-3"><i class="fas fa-user me-2"></i>by ${blog.author_name}</span>` : ''}
                        </p>
                    </div>
                    
                    ${blog.featured_image ? `
                        <img src="${blog.featured_image}" alt="${blog.title}" class="img-fluid rounded-3 shadow-sm mb-4 w-100" style="max-height: 400px; object-fit: cover;">
                    ` : ''}
                    
                    ${videoSection}
                    
                    <div class="mb-4">
                        <h5 class="mb-3">Story Overview</h5>
                        <p class="lead text-muted">${blog.excerpt}</p>
                    </div>
                    
                    ${statsSection}
                    
                    <div class="mb-4">
                        <h5 class="mb-3">Full Story</h5>
                        <div class="content">${blog.content}</div>
                    </div>
                    
                    ${blog.tags && blog.tags.length > 0 ? `
                        <div class="mt-4 pt-4 border-top">
                            <h6 class="mb-3">Tags</h6>
                            ${blog.tags.map(tag => `<span class="badge bg-light text-dark me-2 mb-2">#${tag}</span>`).join('')}
                        </div>
                    ` : ''}
                `;
                
                // Update modal title
                document.getElementById('blogDetailModalLabel').textContent = blog.title;
            } else {
                content.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5>Story Not Found</h5>
                        <p class="text-muted">Sorry, we couldn't load this story. Please try again later.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading blog details:', error);
            content.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5>Error Loading Story</h5>
                    <p class="text-muted">There was an error loading this story. Please try again later.</p>
                </div>
            `;
        });
}
</script>

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

.video-play-btn {
    backdrop-filter: blur(10px);
    background-color: rgba(0,0,0,0.7) !important;
    border: 3px solid white;
    transition: all 0.3s ease;
}

.video-play-btn:hover {
    transform: scale(1.1);
    background-color: rgba(0,0,0,0.9) !important;
}

.content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1rem 0;
}

.content p {
    margin-bottom: 1rem;
    line-height: 1.7;
}

.content h3, .content h4, .content h5 {
    margin-top: 2rem;
    margin-bottom: 1rem;
}

/* Partners Carousel Styles */
.partners-carousel-wrapper {
    overflow: hidden;
    position: relative;
    padding: 2rem 0;
}

.partners-carousel {
    width: 100%;
    overflow: hidden;
}

.partners-track {
    display: flex;
    animation: partnersScroll 30s linear infinite;
    width: calc(200px * 20); /* Adjust based on number of partners */
}

.partner-slide {
    flex: 0 0 auto;
    width: 200px;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.partner-logo {
    max-width: 120px;
    max-height: 80px;
    object-fit: contain;
    filter: grayscale(0.7);
    opacity: 0.8;
    transition: all 0.3s ease;
}

.partner-slide:hover .partner-logo {
    filter: grayscale(0);
    opacity: 1;
    transform: scale(1.05);
}

.partner-placeholder {
    width: 120px;
    height: 80px;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    font-size: 0.85rem;
    color: #6c757d;
    padding: 0.5rem;
    transition: all 0.3s ease;
}

.partner-slide:hover .partner-placeholder {
    border-color: #007bff;
    color: #007bff;
    background: #f0f8ff;
}

@keyframes partnersScroll {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

/* Pause animation on hover */
.partners-carousel-wrapper:hover .partners-track {
    animation-play-state: paused;
}

/* Featured Partners Grid Styles */
.partner-card {
    padding: 2rem 1rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
    border: 1px solid #f0f0f0;
}

.partner-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border-color: #007bff;
}

.partner-logo-wrapper {
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
}

.partner-logo-featured {
    max-width: 140px;
    max-height: 80px;
    object-fit: contain;
    transition: all 0.3s ease;
}

.partner-card:hover .partner-logo-featured {
    transform: scale(1.05);
}

.partner-logo-placeholder {
    width: 140px;
    height: 80px;
    background: #e9ecef;
    border: 2px dashed #adb5bd;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    font-size: 0.9rem;
    color: #6c757d;
    padding: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.partner-card:hover .partner-logo-placeholder {
    border-color: #007bff;
    color: #007bff;
    background: #f0f8ff;
}

.partners-carousel-section {
    background: rgba(0,123,255,0.02);
    border-radius: 15px;
    padding: 2rem 1rem;
    margin-top: 2rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .partner-slide {
        width: 150px;
        padding: 0 0.5rem;
    }
    
    .partner-logo {
        max-width: 100px;
        max-height: 60px;
    }
    
    .partner-placeholder {
        width: 100px;
        height: 60px;
        font-size: 0.75rem;
    }
    
    .partners-track {
        animation-duration: 40s; /* Slower on mobile */
    }
    
    .partner-card {
        padding: 1.5rem 0.5rem;
    }
    
    .partner-logo-wrapper {
        height: 80px;
        padding: 0.5rem;
    }
    
    .partner-logo-featured {
        max-width: 100px;
        max-height: 60px;
    }
    
    .partner-logo-placeholder {
        width: 100px;
        height: 60px;
        font-size: 0.8rem;
    }
}
</style>
