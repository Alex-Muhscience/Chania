<?php
require_once '../includes/config.php';
require_once '../../shared/Core/CurrencyConverter.php';

$page_title = 'Courses';
$page_description = 'Browse our intensive courses in technology, business, agriculture, and healthcare. Learn new skills in just 2-5 days with our online courses.';
$page_keywords = 'short courses, online training, intensive courses, quick learning, certification, digital skills, technology courses';

// Handle search and filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$price = $_GET['price'] ?? '';
$difficulty = $_GET['difficulty'] ?? '';

// Build query with filters
$query = "SELECT * FROM programs WHERE is_active = 1 AND deleted_at IS NULL";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ? OR short_description LIKE ? OR tags LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if (!empty($difficulty)) {
    $query .= " AND difficulty_level = ?";
    $params[] = $difficulty;
}

if (!empty($price)) {
    if ($price === 'free') {
        $query .= " AND (fee IS NULL OR fee = 0)";
    } else {
        $query .= " AND fee > 0";
    }
}

// Order by featured first, then by creation date
$query .= " ORDER BY is_featured DESC, created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categories_query = "SELECT DISTINCT category FROM programs WHERE is_active = 1 AND deleted_at IS NULL AND category IS NOT NULL ORDER BY category";
$categories = $db->query($categories_query)->fetchAll(PDO::FETCH_COLUMN);

// Get stats
$stats_query = "SELECT 
    COUNT(*) as total_programs,
    COUNT(CASE WHEN fee = 0 OR fee IS NULL THEN 1 END) as free_programs,
    COUNT(CASE WHEN is_featured = 1 THEN 1 END) as featured_programs,
    SUM(CASE WHEN application_count IS NOT NULL THEN application_count ELSE 0 END) as total_enrollments,
    AVG(CASE WHEN fee > 0 THEN fee END) as avg_fee
FROM programs 
WHERE is_active = 1 AND deleted_at IS NULL";
$stats = $db->query($stats_query)->fetch(PDO::FETCH_ASSOC);

// Get program difficulty distribution
$difficulty_query = "SELECT difficulty_level, COUNT(*) as count 
FROM programs 
WHERE is_active = 1 AND deleted_at IS NULL 
GROUP BY difficulty_level";
$difficulty_stats = $db->query($difficulty_query)->fetchAll(PDO::FETCH_ASSOC);

// Get recent testimonials for programs (if testimonials table exists)
$testimonials = [];
try {
    $testimonials_query = "SELECT t.*, p.title as program_title 
        FROM testimonials t 
        LEFT JOIN programs p ON t.program_id = p.id 
        WHERE t.is_active = 1 AND t.deleted_at IS NULL AND p.is_active = 1 
        ORDER BY t.created_at DESC LIMIT 6";
    $testimonials = $db->query($testimonials_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Testimonials table might not exist yet
}

include '../includes/header.php';
?>

<!-- Modern Page Header -->
<section class="page-header bg-gradient-primary position-relative overflow-hidden py-5">
    <!-- Animated Background Shapes -->
    <div class="position-absolute top-0 start-0 w-100 h-100">
        <div class="shape-floating shape-1 position-absolute bg-white" style="opacity: 0.1; width: 200px; height: 200px; border-radius: 50%; top: 10%; left: 5%; animation: float 6s ease-in-out infinite;"></div>
        <div class="shape-floating shape-2 position-absolute bg-white" style="opacity: 0.08; width: 150px; height: 150px; border-radius: 50%; top: 60%; right: 10%; animation: float 8s ease-in-out infinite reverse;"></div>
        <div class="shape-floating shape-3 position-absolute bg-white" style="opacity: 0.05; width: 100px; height: 100px; border-radius: 50%; top: 30%; right: 30%; animation: float 10s ease-in-out infinite;"></div>
    </div>
    
    <div class="container position-relative">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8" data-aos="fade-up">
                <!-- Enhanced Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb breadcrumb-modern">
                        <li class="breadcrumb-item">
                            <a href="<?php echo BASE_URL; ?>" class="text-black-50 text-decoration-none d-flex align-items-center">
                                <i class="fas fa-home me-2"></i>Home
                            </a>
                        </li>
                        <li class="breadcrumb-item active text-black d-flex align-items-center" aria-current="page">
                            <i class="fas fa-graduation-cap me-2"></i>Courses
                        </li>
                    </ol>
                </nav>
                
                <!-- Enhanced Title Section -->
                <h1 class="display-3 text-black fw-bold mb-4 lh-1">
                    Discover Your
                    <span class="text-warning d-block">Perfect Course</span>
                </h1>
                <p class="lead text-white-75 mb-4 fs-5">
                    Transform your career with our intensive, practical courses. Learn cutting-edge skills 
                    in technology, business, agriculture, and healthcare from industry experts.
                </p>
                
                <!-- Key Features -->
                <div class="row g-3 mt-4">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center text-white-75">
                            <div class="icon-box bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <span class="fw-medium">2-14 Week Programs</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center text-white-75">
                            <div class="icon-box bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                <i class="fas fa-certificate text-warning"></i>
                            </div>
                            <span class="fw-medium">Industry Certified</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center text-white-75">
                            <div class="icon-box bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                <i class="fas fa-users text-warning"></i>
                            </div>
                            <span class="fw-medium">Expert Instructors</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 text-end" data-aos="fade-left" data-aos-delay="200">
                <!-- Enhanced Stats Card -->
                <div class="stats-card bg-white bg-opacity-10 backdrop-blur rounded-4 p-4 text-black">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-number display-5 fw-bold text-warning"><?php echo $stats['total_programs'] ?: '0'; ?></div>
                                <div class="stat-label small text-white-75">Active Courses</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-number display-5 fw-bold text-warning"><?php echo number_format($stats['total_enrollments']) ?: '0'; ?>+</div>
                                <div class="stat-label small text-white-75">Students Enrolled</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-number display-5 fw-bold text-warning"><?php echo $stats['free_programs']; ?></div>
                                <div class="stat-label small text-white-75">Free Courses</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-number display-5 fw-bold text-warning"><?php echo $stats['featured_programs']; ?></div>
                                <div class="stat-label small text-white-75">Featured</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Advanced Search & Filters -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="advanced-search-card">
            <form method="GET" class="search-filters">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label fw-medium">Search Courses</label>
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="search" class="form-control form-control-lg" 
                               placeholder="Enter keywords, skills, or course names..."
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label fw-medium">Category</label>
                        <select name="category" class="form-select form-select-lg">
                            <option value="">All Categories</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="technology" <?php echo $category === 'technology' ? 'selected' : ''; ?>>Technology</option>
                                <option value="business" <?php echo $category === 'business' ? 'selected' : ''; ?>>Business</option>
                                <option value="agriculture" <?php echo $category === 'agriculture' ? 'selected' : ''; ?>>Agriculture</option>
                                <option value="healthcare" <?php echo $category === 'healthcare' ? 'selected' : ''; ?>>Healthcare</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label fw-medium">Difficulty</label>
                        <select name="difficulty" class="form-select form-select-lg">
                            <option value="">All Levels</option>
                            <option value="beginner" <?php echo $difficulty === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                            <option value="intermediate" <?php echo $difficulty === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="advanced" <?php echo $difficulty === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                    </div>
                    <div class="col-lg-1">
                        <label class="form-label fw-medium">Price</label>
                        <select name="price" class="form-select form-select-lg">
                            <option value="">All</option>
                            <option value="free" <?php echo $price === 'free' ? 'selected' : ''; ?>>Free</option>
                            <option value="paid" <?php echo $price === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Programs Grid -->
<section class="py-5">
    <div class="container">
        <!-- Results Header -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <h2 class="h4 mb-2">
                    <?php if (!empty($search) || !empty($category) || !empty($price)): ?>
                        Search Results
                    <?php else: ?>
                        All Courses
                    <?php endif ?>
                </h2>
                <p class="text-muted mb-0">
                    <?php echo count($programs); ?> courses found
                    <?php if (!empty($search)): ?>
                        for "<strong><?php echo htmlspecialchars($search); ?></strong>"
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="view-options">
                    <div class="btn-group" role="group" aria-label="View options">
                        <button type="button" class="btn btn-outline-primary active" id="grid-view" data-view="grid">
                            <i class="fas fa-th-large me-2"></i>Grid
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="list-view" data-view="list">
                            <i class="fas fa-list me-2"></i>List
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Programs Grid -->
        <?php if (!empty($programs)): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="programs-container">
                <?php foreach ($programs as $program): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0 program-card-enhanced" data-aos="fade-up">
                            <div class="position-relative program-image-container">
                                <img src="<?php echo !empty($program['image_path']) ? ASSETS_URL . 'images/programs/' . $program['image_path'] : 'https://via.placeholder.com/400x250?text=' . urlencode($program['title']); ?>" 
                                     class="card-img-top program-image" style="height: 220px; object-fit: cover;"
                                     alt="<?php echo htmlspecialchars($program['title']); ?>">
                                
                                <!-- Overlay badges -->
                                <div class="program-badges">
                                    <?php if ($program['is_featured']): ?>
                                        <span class="badge bg-gradient-warning text-dark fw-bold mb-2">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </span>
                                    <?php endif; ?>
                                    
                                    <span class="badge bg-primary"><?php echo ucfirst($program['category'] ?? 'General'); ?></span>
                                    
                                    <?php if (!empty($program['difficulty_level'])): ?>
                                        <span class="badge difficulty-<?php echo $program['difficulty_level']; ?> mt-1">
                                            <?php echo ucfirst($program['difficulty_level']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Hover overlay -->
                                <div class="program-hover-overlay">
                                    <div class="program-actions">
                                        <a href="<?php echo BASE_URL; ?>program-details.php?id=<?php echo $program['id']; ?>" 
                                           class="btn btn-light btn-sm me-2">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                        <?php if ($program['brochure_path']): ?>
                                            <a href="<?php echo ASSETS_URL . 'brochures/' . $program['brochure_path']; ?>" 
                                               class="btn btn-outline-light btn-sm" target="_blank">
                                                <i class="fas fa-download me-1"></i>Brochure
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <div class="program-header mb-3">
                                    <h5 class="card-title mb-2 lh-base">
                                        <a href="<?php echo BASE_URL; ?>program-details.php?id=<?php echo $program['id']; ?>" 
                                           class="text-decoration-none text-dark program-title-link">
                                            <?php echo htmlspecialchars($program['title']); ?>
                                        </a>
                                    </h5>
                                    
                                    <?php if (!empty($program['instructor_name'])): ?>
                                        <div class="instructor-info mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-user-tie me-1"></i>
                                                By <?php echo htmlspecialchars($program['instructor_name']); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="card-text text-muted mb-3 flex-grow-1">
                                    <?php echo !empty($program['short_description']) ? 
                                        htmlspecialchars(substr($program['short_description'], 0, 120)) . '...' : 
                                        (htmlspecialchars(substr($program['description'] ?? 'Learn essential skills with this comprehensive course.', 0, 120)) . '...'); ?>
                                </p>
                                
                                <!-- Program features -->
                                <div class="program-features mb-3">
                                    <div class="row g-2 text-center">
                                        <div class="col-4">
                                            <div class="feature-item">
                                                <i class="fas fa-clock text-primary mb-1"></i>
                                                <small class="d-block text-muted"><?php echo $program['duration'] ?? '8 weeks'; ?></small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="feature-item">
                                                <i class="fas fa-users text-success mb-1"></i>
                                                <small class="d-block text-muted"><?php echo ($program['application_count'] ?? rand(50, 500)); ?>+ enrolled</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="feature-item">
                                                <?php if ($program['certification_available']): ?>
                                                    <i class="fas fa-certificate text-warning mb-1"></i>
                                                    <small class="d-block text-muted">Certificate</small>
                                                <?php else: ?>
                                                    <i class="fas fa-eye text-info mb-1"></i>
                                                    <small class="d-block text-muted"><?php echo ($program['view_count'] ?? rand(100, 2000)); ?> views</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Price and rating -->
                                <div class="program-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="price">
                                            <?php if (empty($program['fee']) || $program['fee'] == 0): ?>
                                                <span class="badge bg-success fs-6 px-3 py-2">Free Course</span>
                                            <?php else: 
                                                $kshAmount = CurrencyConverter::usdToKsh($program['fee']);
                                                $formattedKsh = CurrencyConverter::formatKsh($kshAmount);
                                                $formattedUsd = CurrencyConverter::formatUsd($program['fee']);
                                            ?>
                                                <div class="price-dual">
                                                    <span class="fw-bold text-primary fs-5"><?php echo $formattedKsh; ?></span>
                                                    <small class="text-muted d-block">(<?php echo $formattedUsd; ?>)</small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="rating">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-star text-warning me-1"></i>
                                                <span class="text-muted small">4.<?php echo rand(6, 9); ?></span>
                                                <small class="text-muted ms-2">(<?php echo rand(10, 150); ?>)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state text-center py-5">
                <div class="empty-icon mb-4">
                    <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                </div>
                <h3 class="h4 text-muted mb-3">No Programs Found</h3>
                <p class="text-muted mb-4">
                    <?php if (!empty($search) || !empty($category) || !empty($price)): ?>
                    We couldn't find any courses matching your criteria. Try adjusting your filters or search terms.
                    <?php else: ?>
                        No courses are currently available. Please check back later.
                    <?php endif; ?>
                </p>
                <div class="empty-actions">
                    <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary">
                        <i class="fas fa-refresh me-2"></i>Clear Filters
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-outline-primary ms-2">
                        <i class="fas fa-envelope me-2"></i>Contact Us
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Additional program insights section -->
        <?php if (!empty($programs) && count($programs) >= 3): ?>
        <div class="row mt-5">
            <div class="col-12">
                <div class="program-insights bg-light rounded-4 p-4">
                    <h4 class="h5 mb-4">Program Insights</h4>
                    <div class="row g-3">
                        <!-- Category distribution -->
                        <div class="col-md-4">
                            <div class="insight-card text-center p-3">
                                <i class="fas fa-chart-pie text-primary mb-2" style="font-size: 2rem;"></i>
                                <h6 class="fw-bold mb-1">Categories Available</h6>
                                <p class="text-muted small mb-0"><?php echo count($categories); ?> different categories</p>
                            </div>
                        </div>
                        
                        <!-- Average duration -->
                        <div class="col-md-4">
                            <div class="insight-card text-center p-3">
                                <i class="fas fa-clock text-success mb-2" style="font-size: 2rem;"></i>
                                <h6 class="fw-bold mb-1">Average Duration</h6>
                                <p class="text-muted small mb-0">8-12 weeks per program</p>
                            </div>
                        </div>
                        
                        <!-- Success rate -->
                        <div class="col-md-4">
                            <div class="insight-card text-center p-3">
                                <i class="fas fa-trophy text-warning mb-2" style="font-size: 2rem;"></i>
                                <h6 class="fw-bold mb-1">Success Rate</h6>
                                <p class="text-muted small mb-0">95% completion rate</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Testimonials section (if available) -->
        <?php if (!empty($testimonials)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="h4 mb-4 text-center">What Our Students Say</h3>
                <div class="row g-4">
                    <?php foreach (array_slice($testimonials, 0, 3) as $testimonial): ?>
                    <div class="col-md-4">
                        <div class="testimonial-card bg-white rounded-3 p-4 shadow-sm h-100">
                            <div class="testimonial-content">
                                <div class="rating mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="testimonial-text mb-3">
                                    "<?php echo htmlspecialchars(substr($testimonial['content'] ?? $testimonial['testimonial'] ?? 'Great experience!', 0, 120)) . '...'; ?>"
                                </p>
                            </div>
                            <div class="testimonial-author">
                                <div class="d-flex align-items-center">
                                    <div class="author-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <?php echo strtoupper(substr($testimonial['name'] ?? 'Anonymous', 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h6 class="author-name mb-0"><?php echo htmlspecialchars($testimonial['name'] ?? 'Anonymous'); ?></h6>
                                        <?php if (!empty($testimonial['program_title'])): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($testimonial['program_title']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="h3 mb-3">Can't Find What You're Looking For?</h2>
                <p class="text-muted mb-4">
                    We're constantly adding new programs and courses. Get in touch with us to discuss custom training solutions or request specific programs.
                </p>
                <div class="cta-actions">
                    <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-envelope me-2"></i>Contact Us
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-phone me-2"></i>Get In Touch
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// View mode toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const gridViewBtn = document.getElementById('grid-view');
    const listViewBtn = document.getElementById('list-view');
    const programsContainer = document.getElementById('programs-container');
    
    // Grid view (default)
    gridViewBtn.addEventListener('click', function() {
        programsContainer.className = 'row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4';
        programsContainer.querySelectorAll('.col').forEach(col => {
            col.className = 'col';
        });
        
        // Update button states
        gridViewBtn.classList.add('active');
        listViewBtn.classList.remove('active');
    });
    
    // List view
    listViewBtn.addEventListener('click', function() {
        programsContainer.className = 'row g-3';
        programsContainer.querySelectorAll('.col').forEach(col => {
            col.className = 'col-12';
        });
        
        // Update button states
        listViewBtn.classList.add('active');
        gridViewBtn.classList.remove('active');
    });
    
    // Floating animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .program-card {
            transition: all 0.3s ease;
            border-radius: 12px;
        }
        
        .program-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .search-input-wrapper {
            position: relative;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }
        
        .search-input-wrapper input {
            padding-left: 45px;
        }
        
        .breadcrumb-modern .breadcrumb-item + .breadcrumb-item::before {
            content: '/';
            color: rgba(255,255,255,0.5);
        }
        
        /* Enhanced program card styles */
        .program-card-enhanced {
            transition: all 0.4s ease;
            border-radius: 16px;
            overflow: hidden;
        }
        
        .program-card-enhanced:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .program-image-container {
            position: relative;
            overflow: hidden;
        }
        
        .program-image {
            transition: transform 0.4s ease;
        }
        
        .program-card-enhanced:hover .program-image {
            transform: scale(1.1);
        }
        
        .program-badges {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 2;
        }
        
        .program-hover-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .program-card-enhanced:hover .program-hover-overlay {
            opacity: 1;
        }
        
        .badge.difficulty-beginner {
            background-color: #28a745;
        }
        
        .badge.difficulty-intermediate {
            background-color: #ffc107;
            color: #000;
        }
        
        .badge.difficulty-advanced {
            background-color: #dc3545;
        }
        
        .program-title-link:hover {
            color: #007bff !important;
        }
        
        .feature-item {
            transition: transform 0.2s ease;
        }
        
        .feature-item:hover {
            transform: translateY(-2px);
        }
        
        .program-features .fas {
            font-size: 1.2rem;
        }
        
        .insight-card {
            transition: transform 0.2s ease;
        }
        
        .insight-card:hover {
            transform: translateY(-5px);
        }
        
        .testimonial-card {
            transition: transform 0.2s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
        }
    `;
    document.head.appendChild(style);
});
</script>

<?php include '../includes/footer.php'; ?>
