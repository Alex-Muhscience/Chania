<?php
require_once '../includes/config.php';

$page_title = 'Short Courses';
$page_description = 'Browse our intensive short courses in technology, business, agriculture, and healthcare. Learn new skills in just 2-5 days with our online courses.';
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

<!-- Page Header -->
<section class="page-header bg-primary position-relative overflow-hidden">
    <div class="header-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>
    <div class="container position-relative">
        <div class="row align-items-center py-5">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Short Courses</li>
                    </ol>
                </nav>
                <h1 class="display-4 text-white fw-bold mb-3">Intensive Short Courses</h1>
                <p class="lead text-white-75 mb-0">Master new skills in just days with our intensive online short courses. Quick, practical, and designed to fit your busy schedule.</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($programs) ?: '50+'; ?></div>
                        <div class="stat-label">Short Courses Available</div>
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
                        <label class="form-label fw-medium">Search Short Courses</label>
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
                        All Short Courses
                    <?php endif ?>
                </h2>
                <p class="text-muted mb-0">
                    <?php echo count($programs); ?> short courses found
                    <?php if (!empty($search)): ?>
                        for "<strong><?php echo htmlspecialchars($search); ?></strong>"
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="view-options">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary active" data-view="grid">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Programs Grid -->
        <?php if (!empty($programs)): ?>
            <div class="programs-grid" id="programs-container">
                <?php foreach ($programs as $program): ?>
                    <div class="program-card-premium" data-aos="fade-up">
                        <div class="program-image">
                            <img src="<?php echo !empty($program['image_url']) ? ASSETS_URL . 'images/programs/' . $program['image_url'] : 'https://via.placeholder.com/400x250?text=' . urlencode($program['title']); ?>" 
                                 alt="<?php echo htmlspecialchars($program['title']); ?>">
                        </div>
                        <div class="program-content">
                            <div class="program-category">
                                <?php echo ucfirst($program['category'] ?? 'General'); ?>
                            </div>
                            <h3 class="program-title">
                    <a href="<?php echo BASE_URL; ?>program.php?id=<?php echo $program['id']; ?>">
                        <?php echo htmlspecialchars($program['title']); ?>
                    </a>
                            </h3>
                            <div class="program-footer">
                                <div class="program-price">
                                    <?php if (empty($program['fee_amount']) || $program['fee_amount'] == 0): ?>
                                        <span class="price-free">Free</span>
                                    <?php else: ?>
                                        <span class="price">KSh <?php echo number_format($program['fee_amount']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="program-actions">
                    <a href="<?php echo BASE_URL; ?>program.php?id=<?php echo $program['id']; ?>" 
                       class="btn btn-primary btn-sm-minimal">
                        View
                    </a>
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
                    We couldn't find any short courses matching your criteria. Try adjusting your filters or search terms.
                    <?php else: ?>
                        No short courses are currently available. Please check back later.
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
            <div class="programs-grid">
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
                    <a href="<?php echo BASE_URL; ?>apply.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Apply Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
