<?php
require_once '../includes/config.php';

$page_title = 'Training Fields';
$page_description = 'Explore our diverse training fields including Technology, Business, Agriculture, and Healthcare. Quick, intensive short courses designed to fit your schedule.';
$page_keywords = 'training fields, short courses, online training, intensive courses, technology courses, business courses, agricultural courses, healthcare courses';

// Get categories with program counts
$categories_query = "
    SELECT 
        category,
        COUNT(*) as program_count,
        MIN(CASE WHEN image_url IS NOT NULL AND image_url != '' THEN image_url END) as sample_image
    FROM programs 
    WHERE is_active = 1 AND deleted_at IS NULL AND category IS NOT NULL
    GROUP BY category
    ORDER BY program_count DESC
";

try {
    $categories = $db->query($categories_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

// Default categories if database is empty
$default_categories = [
    [
        'category' => 'technology',
        'program_count' => 12,
        'sample_image' => null
    ],
    [
        'category' => 'business',
        'program_count' => 8,
        'sample_image' => null
    ],
    [
        'category' => 'agriculture',
        'program_count' => 6,
        'sample_image' => null
    ],
    [
        'category' => 'healthcare',
        'program_count' => 5,
        'sample_image' => null
    ]
];

if (empty($categories)) {
    $categories = $default_categories;
}

// Category descriptions and icons
$category_info = [
    'technology' => [
        'title' => 'Technology',
        'description' => 'Master cutting-edge technologies in just days with our intensive courses. Learn web development, mobile apps, data science, AI, and cybersecurity through hands-on practice.',
        'icon' => 'fas fa-laptop-code',
        'color' => 'primary',
        'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
    ],
    'business' => [
        'title' => 'Business',
        'description' => 'Fast-track your business skills with intensive courses in digital marketing, project management, entrepreneurship, and leadership. Practical knowledge you can apply immediately.',
        'icon' => 'fas fa-chart-line',
        'color' => 'success',
        'gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'
    ],
    'agriculture' => [
        'title' => 'Agriculture',
        'description' => 'Transform your farming practices with short, intensive courses in modern agriculture, smart farming with IoT, crop management, and agribusiness strategies.',
        'icon' => 'fas fa-seedling',
        'color' => 'warning',
        'gradient' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'
    ],
    'healthcare' => [
        'title' => 'Healthcare',
        'description' => 'Advance your healthcare career quickly with focused courses in medical technology, healthcare management, patient care systems, and health informatics.',
        'icon' => 'fas fa-heartbeat',
        'color' => 'danger',
        'gradient' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
    ]
];

include '../includes/header.php';
?>

<!-- Page Header -->
<section class="page-header bg-gradient-primary position-relative overflow-hidden">
    <div class="header-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    <div class="container position-relative">
        <div class="row align-items-center py-5">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Training Fields</li>
                    </ol>
                </nav>
                <h1 class="display-4 text-white fw-bold mb-3">Training Fields</h1>
                <p class="lead text-white-75 mb-0">Explore our diverse range of intensive short courses designed to match your career goals. Learn new skills in just 2-5 days with our online training programs.</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo array_sum(array_column($categories, 'program_count')); ?>+</div>
                        <div class="stat-label">Total Programs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Training Fields Overview -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">Choose Your Field of Interest</h2>
                <p class="section-subtitle text-muted">
                    Our intensive short courses are organized into four main fields, each designed to provide practical skills you can apply immediately. Complete in days, not months.
                </p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($categories as $index => $category): ?>
                <?php 
                $cat_key = strtolower($category['category']);
                $info = $category_info[$cat_key] ?? [
                    'title' => ucfirst($category['category']),
                    'description' => 'Comprehensive training programs in ' . $category['category'],
                    'icon' => 'fas fa-bookmark',
                    'color' => 'primary',
                    'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                ];
                ?>
                <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <div class="training-field-card h-100">
                        <div class="field-header" style="background: <?php echo $info['gradient']; ?>">
                            <div class="field-icon">
                                <i class="<?php echo $info['icon']; ?>"></i>
                            </div>
                            <div class="field-info">
                                <h3 class="field-title"><?php echo $info['title']; ?></h3>
                                <div class="field-stats">
                                    <span class="program-count"><?php echo $category['program_count']; ?> Short Courses</span>
                                </div>
                            </div>
                        </div>
                        <div class="field-body">
                            <p class="field-description"><?php echo $info['description']; ?></p>
                            
                            <div class="field-highlights">
                                <h6 class="mb-3">What You'll Learn:</h6>
                                <ul class="highlight-list">
                                    <?php if ($cat_key === 'technology'): ?>
                                        <li>Web & Mobile Development</li>
                                        <li>Data Science & Analytics</li>
                                        <li>Artificial Intelligence</li>
                                        <li>Cybersecurity</li>
                                    <?php elseif ($cat_key === 'business'): ?>
                                        <li>Digital Marketing</li>
                                        <li>Project Management</li>
                                        <li>Entrepreneurship</li>
                                        <li>Financial Management</li>
                                    <?php elseif ($cat_key === 'agriculture'): ?>
                                        <li>Smart Farming Techniques</li>
                                        <li>Sustainable Agriculture</li>
                                        <li>Crop Management</li>
                                        <li>Agribusiness</li>
                                    <?php elseif ($cat_key === 'healthcare'): ?>
                                        <li>Healthcare Management</li>
                                        <li>Medical Technology</li>
                                        <li>Patient Care Systems</li>
                                        <li>Health Informatics</li>
                                    <?php else: ?>
                                        <li>Industry-relevant skills</li>
                                        <li>Practical applications</li>
                                        <li>Professional development</li>
                                        <li>Career advancement</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="field-footer">
                            <a href="<?php echo BASE_URL; ?>programs.php?category=<?php echo $category['category']; ?>" 
                               class="btn btn-<?php echo $info['color']; ?> w-100">
                                <i class="fas fa-arrow-right me-2"></i>Explore <?php echo $info['title']; ?> Courses
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Our Training Fields -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">Why Our Short Courses Stand Out</h2>
                <p class="section-subtitle text-muted">
                    Each course is carefully designed for maximum impact in minimum time. Learn practical skills that you can apply immediately.
                </p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-industry"></i>
                    </div>
                    <h5 class="feature-title">Industry-Aligned</h5>
                    <p class="feature-description">Curriculum developed with industry partners to ensure relevance and employability.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h5 class="feature-title">Quick Results</h5>
                    <p class="feature-description">Complete courses in 2-5 days and start applying new skills immediately.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="feature-title">Expert Instructors</h5>
                    <p class="feature-description">Learn from experienced professionals and industry experts.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h5 class="feature-title">Online Flexibility</h5>
                    <p class="feature-description">100% online courses that fit your schedule. Learn at your own pace.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Career Pathways -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">Career Pathways</h2>
                <p class="section-subtitle text-muted">
                    Each training field opens up multiple career opportunities and advancement paths.
                </p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="pathway-card">
                    <div class="pathway-header">
                        <i class="fas fa-rocket text-primary"></i>
                        <h5>Entry Level Positions</h5>
                    </div>
                    <p>Start your career with foundational roles that provide growth opportunities and skill development.</p>
                    <ul class="pathway-list">
                        <li>Junior Developer / Analyst</li>
                        <li>Marketing Assistant</li>
                        <li>Agricultural Technician</li>
                        <li>Healthcare Support Staff</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="pathway-card">
                    <div class="pathway-header">
                        <i class="fas fa-trophy text-warning"></i>
                        <h5>Advanced Positions</h5>
                    </div>
                    <p>Advance to senior roles with specialized skills and leadership responsibilities.</p>
                    <ul class="pathway-list">
                        <li>Senior Developer / Data Scientist</li>
                        <li>Business Manager / Consultant</li>
                        <li>Agricultural Specialist</li>
                        <li>Healthcare Administrator</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up">
                <h2 class="h3 mb-3">Ready to Learn Something New?</h2>
                <p class="mb-4">
                    Choose your training field and master new skills in just days. Quick, practical, and designed for busy professionals.
                </p>
                <div class="cta-actions">
                    <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-search me-2"></i>Browse All Programs
                    </a>
                    <a href="<?php echo BASE_URL; ?>apply.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Apply Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.training-field-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.training-field-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

.field-header {
    padding: 2rem;
    color: white;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.field-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
}

.field-icon {
    font-size: 3rem;
    margin-right: 1.5rem;
    position: relative;
    z-index: 2;
}

.field-info {
    position: relative;
    z-index: 2;
}

.field-title {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.program-count {
    background: rgba(255,255,255,0.2);
    padding: 0.25rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.field-body {
    padding: 2rem;
}

.field-description {
    color: #6c757d;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.field-highlights h6 {
    color: #495057;
    font-weight: 600;
}

.highlight-list {
    list-style: none;
    padding: 0;
}

.highlight-list li {
    padding: 0.5rem 0;
    position: relative;
    padding-left: 1.5rem;
    color: #6c757d;
}

.highlight-list li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #28a745;
    font-weight: bold;
}

.field-footer {
    padding: 0 2rem 2rem 2rem;
}

.feature-card {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    height: 100%;
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-icon {
    color: #007bff;
    font-size: 2.5rem;
}

.feature-title {
    color: #495057;
    margin-bottom: 1rem;
}

.feature-description {
    color: #6c757d;
    margin: 0;
}

.pathway-card {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    border-left: 4px solid #007bff;
    height: 100%;
}

.pathway-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.pathway-header i {
    font-size: 1.5rem;
    margin-right: 1rem;
}

.pathway-header h5 {
    margin: 0;
    color: #495057;
}

.pathway-list {
    list-style: none;
    padding: 0;
}

.pathway-list li {
    padding: 0.5rem 0;
    color: #6c757d;
    position: relative;
    padding-left: 1.5rem;
}

.pathway-list li::before {
    content: '→';
    position: absolute;
    left: 0;
    color: #007bff;
    font-weight: bold;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>

<?php include '../includes/footer.php'; ?>
