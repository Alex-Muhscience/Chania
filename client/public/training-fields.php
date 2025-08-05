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

<!-- Modern Page Header -->
<section class="page-header bg-primary position-relative overflow-hidden py-5">
    <!-- Animated Background Shapes -->
    <div class="position-absolute top-0 start-0 w-100 h-100">
        <div class="shape-floating shape-1 position-absolute bg-white" style="opacity: 0.1; width: 120px; height: 120px; border-radius: 50%; top: 10%; left: 5%; animation: float 6s ease-in-out infinite;"></div>
        <div class="shape-floating shape-2 position-absolute bg-white" style="opacity: 0.08; width: 100px; height: 100px; border-radius: 50%; top: 60%; right: 10%; animation: float 8s ease-in-out infinite reverse;"></div>
        <div class="shape-floating shape-3 position-absolute bg-white" style="opacity: 0.05; width: 80px; height: 80px; border-radius: 50%; top: 30%; right: 30%; animation: float 10s ease-in-out infinite;"></div>
    </div>
    
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-up">
                <!-- Enhanced Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?php echo BASE_URL; ?>" class="text-white-50 text-decoration-none">
                                <i class="fas fa-home me-2"></i>Home
                            </a>
                        </li>
                        <li class="breadcrumb-item active text-white" aria-current="page">
                            <i class="fas fa-graduation-cap me-2"></i>Training Fields
                        </li>
                    </ol>
                </nav>
                
                <!-- Enhanced Title Section -->
                <h1 class="display-4 text-white fw-bold mb-4">
                    Training Fields
                </h1>
                <p class="lead text-white-75 mb-4">
                    Choose from our specialized training fields and master new skills with intensive, practical courses designed for quick results.
                </p>
            </div>
            
            <div class="col-lg-4 text-end" data-aos="fade-left" data-aos-delay="200">
                <!-- Enhanced Stats Card -->
                <div class="stats-card bg-white bg-opacity-10 backdrop-blur rounded-3 p-4 text-white">
                    <div class="text-center">
                        <div class="display-5 fw-bold text-warning"><?php echo array_sum(array_column($categories, 'program_count')); ?>+</div>
                        <div class="small">Training Programs</div>
                        <hr class="my-3 border-white-50">
                        <div class="small opacity-75"><?php echo count($categories); ?> Specialized Fields</div>
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
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4">
            <?php foreach ($categories as $index => $category): ?>
                <?php 
                $cat_key = strtolower($category['category']);
                $info = $category_info[$cat_key] ?? [
                    'title' => ucfirst($category['category']),
                    'description' => 'Comprehensive training programs in ' . $category['category'],
                    'icon' => 'fas fa-bookmark',
                    'color' => 'primary'
                ];
                ?>
                <div class="col" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <div class="card h-100 shadow-sm border-0 training-field-card">
                        <div class="card-body p-4 text-center">
                            <div class="training-icon mb-3">
                                <div class="icon-wrapper bg-<?php echo $info['color']; ?> bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="<?php echo $info['icon']; ?> fs-1 text-<?php echo $info['color']; ?>"></i>
                                </div>
                            </div>
                            
                            <h5 class="card-title mb-2"><?php echo $info['title']; ?></h5>
                            <span class="badge bg-<?php echo $info['color']; ?> bg-opacity-10 text-<?php echo $info['color']; ?> mb-3">
                                <?php echo $category['program_count']; ?> Courses
                            </span>
                            
                            <p class="card-text text-muted small mb-3"><?php echo substr($info['description'], 0, 100) . '...'; ?></p>
                            
                            <div class="training-highlights mb-4">
                                <ul class="list-unstyled small text-start">
                                    <?php if ($cat_key === 'technology'): ?>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Web Development</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Data Science</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Cybersecurity</li>
                                    <?php elseif ($cat_key === 'business'): ?>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Digital Marketing</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Project Management</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Entrepreneurship</li>
                                    <?php elseif ($cat_key === 'agriculture'): ?>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Smart Farming</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Sustainable Practices</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Agribusiness</li>
                                    <?php elseif ($cat_key === 'healthcare'): ?>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Healthcare Management</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Medical Technology</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Health Informatics</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-transparent px-4 pb-4 pt-0">
                            <a href="<?php echo BASE_URL; ?>programs.php?category=<?php echo $category['category']; ?>" 
                               class="btn btn-<?php echo $info['color']; ?> btn-sm w-100">
                                <i class="fas fa-arrow-right me-2"></i>View Courses
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
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <div class="col" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-industry text-primary" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="card-title">Industry-Aligned</h5>
                        <p class="card-text text-muted">Curriculum developed with industry partners to ensure relevance and employability.</p>
                    </div>
                </div>
            </div>
            <div class="col" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-bolt text-warning" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="card-title">Quick Results</h5>
                        <p class="card-text text-muted">Complete courses in 2-14 weeks and start applying new skills immediately.</p>
                    </div>
                </div>
            </div>
            <div class="col" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-users text-success" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="card-title">Expert Instructors</h5>
                        <p class="card-text text-muted">Learn from experienced professionals and industry experts.</p>
                    </div>
                </div>
            </div>
            <div class="col" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-laptop text-info" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="card-title">Online Flexibility</h5>
                        <p class="card-text text-muted">100% online courses that fit your schedule. Learn at your own pace.</p>
                    </div>
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
        
        <div class="row row-cols-1 row-cols-lg-2 g-4">
            <div class="col" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-start border-primary border-4 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-rocket text-primary fs-3 me-3"></i>
                            <h5 class="card-title mb-0">Entry Level Positions</h5>
                        </div>
                        <p class="card-text text-muted">Start your career with foundational roles that provide growth opportunities and skill development.</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-arrow-right text-primary me-2"></i>Junior Developer / Analyst</li>
                            <li class="mb-2"><i class="fas fa-arrow-right text-primary me-2"></i>Marketing Assistant</li>
                            <li class="mb-2"><i class="fas fa-arrow-right text-primary me-2"></i>Agricultural Technician</li>
                            <li class="mb-2"><i class="fas fa-arrow-right text-primary me-2"></i>Healthcare Support Staff</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-start border-warning border-4 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-trophy text-warning fs-3 me-3"></i>
                            <h5 class="card-title mb-0">Advanced Positions</h5>
                        </div>
                        <p class="card-text text-muted">Advance to senior roles with specialized skills and leadership responsibilities.</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-arrow-right text-warning me-2"></i>Senior Developer / Data Scientist</li>
                            <li class="mb-2"><i class="fas fa-arrow-right text-warning me-2"></i>Business Manager / Consultant</li>
                            <li class="mb-2"><i class="fas fa-arrow-right text-warning me-2"></i>Agricultural Specialist</li>
                            <li class="mb-2"><i class="fas fa-arrow-right text-warning me-2"></i>Healthcare Administrator</li>
                        </ul>
                    </div>
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
                <h2 class="h3 mb-3">Ready to Transform Your Career?</h2>
                <p class="mb-4 fs-5">
                    Choose your training field and master new skills with our intensive programs. Quick, practical, and designed for busy professionals.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="<?php echo BASE_URL; ?>programs.php" class="btn btn-light btn-lg px-4">
                        <i class="fas fa-search me-2"></i>Browse All Courses
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-phone me-2"></i>Get In Touch
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Floating animation keyframes
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .training-field-card {
            transition: all 0.3s ease;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.08);
        }
        
        .training-field-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
            border-color: rgba(218, 37, 37, 0.2);
        }
        
        .icon-wrapper {
            transition: all 0.3s ease;
        }
        
        .training-field-card:hover .icon-wrapper {
            transform: scale(1.1);
        }
        
        .breadcrumb .breadcrumb-item + .breadcrumb-item::before {
            content: '/';
            color: rgba(255,255,255,0.5);
        }
        
        .backdrop-blur {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    `;
    document.head.appendChild(style);
});
</script>

<?php include '../includes/footer.php'; ?>
