<?php
require_once '../includes/config.php';

$page_title = 'Courses';
$page_description = 'Browse our intensive courses in technology, business, agriculture, and healthcare. Learn new skills in just 2-5 days with our online courses.';
$page_keywords = 'short courses, online training, intensive courses, quick learning, certification, digital skills, technology courses';

// Handle search and filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$price = $_GET['price'] ?? '';

// Build query with filters
$query = "SELECT * FROM programs WHERE is_active = 1 AND deleted_at IS NULL";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}


if (!empty($price)) {
    if ($price === 'free') {
        $query .= " AND (fee_amount IS NULL OR fee_amount = 0)";
    } else {
        $query .= " AND fee_amount > 0";
    }
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categories_query = "SELECT DISTINCT category FROM programs WHERE is_active = 1 AND deleted_at IS NULL AND category IS NOT NULL";
$categories = $db->query($categories_query)->fetchAll(PDO::FETCH_COLUMN);

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
                                <div class="stat-number display-5 fw-bold text-warning"><?php echo count($programs) ?: '50+'; ?></div>
                                <div class="stat-label small text-white-75">Active Courses</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-number display-5 fw-bold text-warning">10K+</div>
                                <div class="stat-label small text-white-75">Students Enrolled</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-number display-5 fw-bold text-warning">95%</div>
                                <div class="stat-label small text-white-75">Completion Rate</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-number display-5 fw-bold text-warning">4.8</div>
                                <div class="stat-label small text-white-75">Average Rating</div>
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
                    <div class="col-lg-3">
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
                        <label class="form-label fw-medium">Price</label>
                        <select name="price" class="form-select form-select-lg">
                            <option value="">All Prices</option>
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
                        <div class="card h-100 shadow-sm border-0 program-card" data-aos="fade-up">
                            <div class="position-relative">
                                <img src="<?php echo !empty($program['image_url']) ? ASSETS_URL . 'images/programs/' . $program['image_url'] : 'https://via.placeholder.com/400x250?text=' . urlencode($program['title']); ?>" 
                                     class="card-img-top" style="height: 200px; object-fit: cover;"
                                     alt="<?php echo htmlspecialchars($program['title']); ?>">
                                <div class="position-absolute top-0 start-0 p-3">
                                    <span class="badge bg-primary"><?php echo ucfirst($program['category'] ?? 'General'); ?></span>
                                </div>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-3">
                                    <a href="<?php echo BASE_URL; ?>program-details.php?id=<?php echo $program['id']; ?>" 
                                       class="text-decoration-none text-dark stretched-link">
                                        <?php echo htmlspecialchars($program['title']); ?>
                                    </a>
                                </h5>
                                
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo !empty($program['description']) ? substr(htmlspecialchars($program['description']), 0, 120) . '...' : 'Learn essential skills with this comprehensive course.'; ?>
                                </p>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="text-muted small">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo $program['duration'] ?? '8 weeks'; ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo rand(50, 500); ?>+ enrolled
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="price">
                                            <?php if (empty($program['fee_amount']) || $program['fee_amount'] == 0): ?>
                                                <span class="badge bg-success fs-6">Free</span>
                                            <?php else: ?>
                                                <span class="fw-bold text-primary fs-5">KSh <?php echo number_format($program['fee_amount']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="rating">
                                            <i class="fas fa-star text-warning"></i>
                                            <span class="text-muted small">4.<?php echo rand(5, 9); ?></span>
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

        <!-- Show sample programs if database is empty -->
        <?php if (empty($programs) && empty($search) && empty($category) && empty($price)): ?>
<div class="row row-cols-1 row-cols-md-3 g-4"
                <!-- Sample Program 1 -->
                <div class="program-card-premium" data-aos="fade-up">
                    <div class="program-image">
                        <img src="https://via.placeholder.com/400x250?text=Web+Development" alt="Web Development">
                        <div class="program-overlay">
                            <span class="badge-featured">Featured</span>
                            <span class="badge-difficulty difficulty-beginner">Beginner</span>
                        </div>
                    </div>
                    <div class="program-content">
                        <div class="program-category">Technology</div>
                        <h3 class="program-title">
                            <a href="#">Full Stack Web Development</a>
                        </h3>
                        <p class="program-description">
                            Master modern web development with HTML, CSS, JavaScript, React, Node.js, and database management...
                        </p>
                        <div class="program-meta">
                            <span class="meta-item">
                                <i class="fas fa-clock text-primary"></i>
                                12 weeks
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-users text-primary"></i>
                                2,500+ enrolled
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-star text-warning"></i>
                                4.9
                            </span>
                        </div>
                        <div class="program-footer">
                            <div class="program-price">
                                <span class="price-free">Free</span>
                            </div>
                            <div class="program-actions">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sample Program 2 -->
                <div class="program-card-premium" data-aos="fade-up" data-aos-delay="100">
                    <div class="program-image">
                        <img src="https://via.placeholder.com/400x250?text=Data+Science" alt="Data Science">
                        <div class="program-overlay">
                            <span class="badge-difficulty difficulty-intermediate">Intermediate</span>
                        </div>
                    </div>
                    <div class="program-content">
                        <div class="program-category">Technology</div>
                        <h3 class="program-title">
                            <a href="#">Data Science & Analytics</a>
                        </h3>
                        <p class="program-description">
                            Learn Python, machine learning, data visualization, and statistical analysis for data-driven decisions...
                        </p>
                        <div class="program-meta">
                            <span class="meta-item">
                                <i class="fas fa-clock text-primary"></i>
                                10 weeks
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-users text-primary"></i>
                                1,800+ enrolled
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-star text-warning"></i>
                                4.8
                            </span>
                        </div>
                        <div class="program-footer">
                            <div class="program-price">
                                <span class="price">KSh 25,000</span>
                            </div>
                            <div class="program-actions">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sample Program 3 -->
                <div class="program-card-premium" data-aos="fade-up" data-aos-delay="200">
                    <div class="program-image">
                        <img src="https://via.placeholder.com/400x250?text=Digital+Marketing" alt="Digital Marketing">
                        <div class="program-overlay">
                            <span class="badge-difficulty difficulty-beginner">Beginner</span>
                        </div>
                    </div>
                    <div class="program-content">
                        <div class="program-category">Business</div>
                        <h3 class="program-title">
                            <a href="#">Digital Marketing Mastery</a>
                        </h3>
                        <p class="program-description">
                            Complete digital marketing course covering SEO, social media, PPC, email marketing, and analytics...
                        </p>
                        <div class="program-meta">
                            <span class="meta-item">
                                <i class="fas fa-clock text-primary"></i>
                                8 weeks
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-users text-primary"></i>
                                3,200+ enrolled
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-star text-warning"></i>
                                4.7
                            </span>
                        </div>
                        <div class="program-footer">
                            <div class="program-price">
                                <span class="price">KSh 15,000</span>
                            </div>
                            <div class="program-actions">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sample Program 4 -->
                <div class="program-card-premium" data-aos="fade-up" data-aos-delay="300">
                    <div class="program-image">
                        <img src="https://via.placeholder.com/400x250?text=Smart+Agriculture" alt="Smart Agriculture">
                        <div class="program-overlay">
                            <span class="badge-featured">Featured</span>
                            <span class="badge-difficulty difficulty-beginner">Beginner</span>
                        </div>
                    </div>
                    <div class="program-content">
                        <div class="program-category">Agriculture</div>
                        <h3 class="program-title">
                            <a href="#">Smart Agriculture & IoT</a>
                        </h3>
                        <p class="program-description">
                            Modern farming techniques using IoT sensors, precision agriculture, and sustainable farming practices...
                        </p>
                        <div class="program-meta">
                            <span class="meta-item">
                                <i class="fas fa-clock text-primary"></i>
                                6 weeks
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-users text-primary"></i>
                                950+ enrolled
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-star text-warning"></i>
                                4.6
                            </span>
                        </div>
                        <div class="program-footer">
                            <div class="program-price">
                                <span class="price-free">Free</span>
                            </div>
                            <div class="program-actions">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sample Program 5 -->
                <div class="program-card-premium" data-aos="fade-up" data-aos-delay="400">
                    <div class="program-image">
                        <img src="https://via.placeholder.com/400x250?text=Mobile+Development" alt="Mobile Development">
                        <div class="program-overlay">
                            <span class="badge-difficulty difficulty-advanced">Advanced</span>
                        </div>
                    </div>
                    <div class="program-content">
                        <div class="program-category">Technology</div>
                        <h3 class="program-title">
                            <a href="#">Mobile App Development</a>
                        </h3>
                        <p class="program-description">
                            Build native iOS and Android apps using React Native, Flutter, or native development frameworks...
                        </p>
                        <div class="program-meta">
                            <span class="meta-item">
                                <i class="fas fa-clock text-primary"></i>
                                14 weeks
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-users text-primary"></i>
                                1,400+ enrolled
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-star text-warning"></i>
                                4.9
                            </span>
                        </div>
                        <div class="program-footer">
                            <div class="program-price">
                                <span class="price">KSh 30,000</span>
                            </div>
                            <div class="program-actions">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sample Program 6 -->
                <div class="program-card-premium" data-aos="fade-up" data-aos-delay="500">
                    <div class="program-image">
                        <img src="https://via.placeholder.com/400x250?text=Healthcare+Management" alt="Healthcare Management">
                        <div class="program-overlay">
                            <span class="badge-difficulty difficulty-intermediate">Intermediate</span>
                        </div>
                    </div>
                    <div class="program-content">
                        <div class="program-category">Healthcare</div>
                        <h3 class="program-title">
                            <a href="#">Healthcare Management</a>
                        </h3>
                        <p class="program-description">
                            Learn healthcare administration, patient care systems, medical technology, and healthcare policy...
                        </p>
                        <div class="program-meta">
                            <span class="meta-item">
                                <i class="fas fa-clock text-primary"></i>
                                10 weeks
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-users text-primary"></i>
                                750+ enrolled
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-star text-warning"></i>
                                4.8
                            </span>
                        </div>
                        <div class="program-footer">
                            <div class="program-price">
                                <span class="price">KSh 20,000</span>
                            </div>
                            <div class="program-actions">
                                <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
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
    `;
    document.head.appendChild(style);
});
</script>

<?php include '../includes/footer.php'; ?>
