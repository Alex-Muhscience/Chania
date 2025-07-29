<?php
$pageTitle = "Program Details - Skills for Africa";
$activePage = "programs";

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "/client/public/programs.php");
    exit;
}

$programId = (int)$_GET['id'];
$program = getProgramById($db, $programId);

if (!$program) {
    header("Location: " . BASE_URL . "/client/public/programs.php");
    exit;
}

$pageTitle = $program['title'] . " - Skills for Africa";
$pageDescription = $program['short_description'];

// Get related programs
$relatedPrograms = getRelatedPrograms($db, $programId, $program['category']);
?>

<style>
:root {
    --primary-color: #D8C99B;
    --secondary-color: #273E47;
    --accent-color: #D8973C;
    --text-dark: #1a1a1a;
    --text-light: #6b7280;
    --bg-light: #fafafa;
    --border-light: #e5e7eb;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Premium Breadcrumb */
.premium-breadcrumb {
    background: linear-gradient(135deg, var(--primary-color) 0%, #e8dab7 100%);
    padding: 2rem 0;
    position: relative;
    overflow: hidden;
}

.premium-breadcrumb::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.5" fill="%23000" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.premium-breadcrumb .container {
    position: relative;
    z-index: 2;
}

.premium-breadcrumb .breadcrumb {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 50px;
    padding: 0.75rem 1.5rem;
    margin: 0;
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.premium-breadcrumb .breadcrumb-item a {
    color: var(--secondary-color);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.premium-breadcrumb .breadcrumb-item a:hover {
    color: var(--accent-color);
    transform: translateY(-1px);
}

.premium-breadcrumb .breadcrumb-item.active {
    color: var(--secondary-color);
    font-weight: 600;
}

/* Program Header */
.program-header {
    padding: 3rem 0;
    background: linear-gradient(135deg, #ffffff 0%, var(--bg-light) 100%);
}

.program-badge {
    background: linear-gradient(135deg, var(--accent-color) 0%, #e6a854 100%);
    color: white;
    padding: 0.5rem 1.25rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: var(--shadow-md);
    display: inline-block;
    margin-bottom: 1rem;
}

.program-title {
    font-size: 3rem;
    font-weight: 800;
    color: var(--secondary-color);
    line-height: 1.1;
    margin-bottom: 1.5rem;
    background: linear-gradient(135deg, var(--secondary-color) 0%, #3a5761 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.program-meta {
    display: flex;
    align-items: center;
    gap: 2rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.program-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-light);
    font-weight: 500;
    background: rgba(255, 255, 255, 0.8);
    padding: 0.75rem 1.25rem;
    border-radius: 50px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-light);
    transition: all 0.3s ease;
}

.program-meta-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.program-meta-item i {
    color: var(--accent-color);
    font-size: 1.1rem;
}

.featured-badge {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    box-shadow: var(--shadow-md);
}

/* Premium Image Container */
.premium-image-container {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-xl);
    margin-bottom: 3rem;
    background: linear-gradient(135deg, var(--primary-color) 0%, #e8dab7 100%);
    padding: 1rem;
}

.premium-image-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(216, 201, 155, 0.1) 0%, rgba(216, 151, 60, 0.1) 100%);
    z-index: 1;
}

.premium-image-container img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 15px;
    position: relative;
    z-index: 2;
    transition: transform 0.5s ease;
}

.premium-image-container:hover img {
    transform: scale(1.02);
}

/* Content Sections */
.content-section {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-light);
    position: relative;
    overflow: hidden;
}

.content-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color) 0%, var(--accent-color) 100%);
}

.content-section h3 {
    color: var(--secondary-color);
    font-weight: 700;
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    position: relative;
    padding-left: 1rem;
}

.content-section h3::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 24px;
    background: linear-gradient(135deg, var(--accent-color) 0%, var(--primary-color) 100%);
    border-radius: 2px;
}

.program-content {
    line-height: 1.8;
    color: var(--text-dark);
    font-size: 1.1rem;
}

/* Premium Sidebar */
.premium-sidebar-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-xl);
    border: 1px solid var(--border-light);
    position: sticky;
    top: 2rem;
}

.sidebar-header {
    background: linear-gradient(135deg, var(--secondary-color) 0%, #3a5761 100%);
    color: white;
    padding: 2rem;
    text-align: center;
}

.sidebar-header h3 {
    font-weight: 700;
    font-size: 1.25rem;
    margin: 0;
}

.program-details-list {
    padding: 0;
    margin: 0;
}

.program-details-list .list-group-item {
    border: none;
    border-bottom: 1px solid var(--border-light);
    padding: 1.25rem 2rem;
    background: transparent;
    transition: all 0.3s ease;
}

.program-details-list .list-group-item:hover {
    background: linear-gradient(135deg, rgba(216, 201, 155, 0.1) 0%, rgba(216, 151, 60, 0.1) 100%);
    transform: translateX(5px);
}

.program-details-list .list-group-item:last-child {
    border-bottom: none;
}

.detail-label {
    color: var(--text-light);
    font-weight: 500;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    color: var(--secondary-color);
    font-weight: 600;
    font-size: 1rem;
}

/* Premium Buttons */
.premium-btn {
    background: linear-gradient(135deg, var(--accent-color) 0%, #e6a854 100%);
    border: none;
    padding: 1rem 2.5rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: white;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-md);
    position: relative;
    overflow: hidden;
}

.premium-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.premium-btn:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-xl);
    color: white;
    text-decoration: none;
}

.premium-btn:hover::before {
    left: 100%;
}

.premium-btn-large {
    padding: 1.25rem 3rem;
    font-size: 1.2rem;
}

.sidebar-apply-btn {
    margin: 2rem;
    width: calc(100% - 4rem);
}

/* Related Programs Section */
.related-section {
    background: linear-gradient(135deg, var(--bg-light) 0%, #ffffff 100%);
    padding: 4rem 0;
    position: relative;
}

.related-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="%23D8C99B" opacity="0.3"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
    opacity: 0.5;
}

.related-section .container {
    position: relative;
    z-index: 2;
}

.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--secondary-color);
    margin-bottom: 1rem;
    background: linear-gradient(135deg, var(--secondary-color) 0%, var(--accent-color) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.section-subtitle {
    font-size: 1.2rem;
    color: var(--text-light);
    font-weight: 400;
}

/* Premium Program Cards */
.premium-program-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-light);
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
}

.premium-program-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color) 0%, var(--accent-color) 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.premium-program-card:hover::before {
    transform: scaleX(1);
}

.premium-program-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-xl);
}

.card-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
}

.card-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.premium-program-card:hover .card-image-container img {
    transform: scale(1.1);
}

.card-body-premium {
    padding: 1.5rem;
}

.card-title-premium {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--secondary-color);
    margin-bottom: 0.75rem;
    line-height: 1.3;
}

.card-description {
    color: var(--text-light);
    line-height: 1.6;
    margin-bottom: 1rem;
    font-size: 0.95rem;
}

.card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.card-category {
    background: linear-gradient(135deg, var(--primary-color) 0%, #e8dab7 100%);
    color: var(--secondary-color);
    padding: 0.4rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-duration {
    color: var(--text-light);
    font-size: 0.9rem;
    font-weight: 500;
}

.card-footer-premium {
    padding: 0 1.5rem 1.5rem;
}

.card-btn {
    background: linear-gradient(135deg, var(--secondary-color) 0%, #3a5761 100%);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    transition: all 0.3s ease;
    width: 100%;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.card-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    color: white;
    text-decoration: none;
    background: linear-gradient(135deg, var(--accent-color) 0%, #e6a854 100%);
}

/* Responsive Design */
@media (max-width: 768px) {
    .program-title {
        font-size: 2rem;
    }

    .program-meta {
        flex-direction: column;
        gap: 1rem;
    }

    .premium-image-container img {
        height: 250px;
    }

    .content-section {
        padding: 1.5rem;
    }

    .premium-sidebar-card {
        position: static;
        margin-top: 2rem;
    }

    .section-title {
        font-size: 2rem;
    }
}

@media (max-width: 576px) {
    .premium-breadcrumb {
        padding: 1rem 0;
    }

    .program-header {
        padding: 2rem 0;
    }

    .premium-btn {
        padding: 0.875rem 2rem;
        font-size: 1rem;
    }

    .premium-btn-large {
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
    }
}
</style>

<!-- Premium Breadcrumb Section -->
<section class="premium-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo BASE_URL; ?>/client/public/programs.php">
                        <i class="fas fa-graduation-cap me-2"></i>Programs
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($program['title']); ?>
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Program Header Section -->
<section class="program-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="program-badge"><?php echo htmlspecialchars($program['category']); ?></span>
                <h1 class="program-title"><?php echo htmlspecialchars($program['title']); ?></h1>
                <div class="program-meta">
                    <div class="program-meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo htmlspecialchars($program['duration']); ?></span>
                    </div>
                    <div class="program-meta-item">
                        <i class="fas fa-users"></i>
                        <span>Online & In-Person</span>
                    </div>
                    <div class="program-meta-item">
                        <i class="fas fa-certificate"></i>
                        <span>Certified Program</span>
                    </div>
                    <?php if ($program['is_featured']): ?>
                    <div class="featured-badge">
                        <i class="fas fa-star me-2"></i>Featured Program
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <a href="<?php echo BASE_URL; ?>/client/public/apply.php?program=<?php echo $programId; ?>"
                   class="premium-btn premium-btn-large">
                    <i class="fas fa-paper-plane me-2"></i>Apply Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Main Content Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Program Image -->
                <div class="premium-image-container">
                    <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($program['image_path']); ?>"
                         alt="<?php echo htmlspecialchars($program['title']); ?>">
                </div>

                <!-- About Section -->
                <div class="content-section">
                    <h3><i class="fas fa-info-circle me-3 text-primary"></i>About This Program</h3>
                    <div class="program-content">
                        <?php echo nl2br(htmlspecialchars($program['description'])); ?>
                    </div>
                </div>

                <!-- Requirements Section -->
                <?php if (!empty($program['requirements'])): ?>
                <div class="content-section">
                    <h3><i class="fas fa-list-check me-3 text-warning"></i>Requirements</h3>
                    <div class="program-content">
                        <?php echo nl2br(htmlspecialchars($program['requirements'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Benefits Section -->
                <?php if (!empty($program['benefits'])): ?>
                <div class="content-section">
                    <h3><i class="fas fa-lightbulb me-3 text-success"></i>What You'll Learn</h3>
                    <div class="program-content">
                        <?php echo nl2br(htmlspecialchars($program['benefits'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- CTA Section -->
                <div class="text-center mt-5">
                    <div class="content-section">
                        <h3 class="mb-4">Ready to Transform Your Future?</h3>
                        <p class="lead text-muted mb-4">Join thousands of successful graduates who have launched their careers through our programs.</p>
                        <a href="<?php echo BASE_URL; ?>/client/public/apply.php?program=<?php echo $programId; ?>"
                           class="premium-btn premium-btn-large">
                            <i class="fas fa-rocket me-2"></i>Start Your Journey Today
                        </a>
                    </div>
                </div>
            </div>

            <!-- Premium Sidebar -->
            <div class="col-lg-4">
                <div class="premium-sidebar-card">
                    <div class="sidebar-header">
                        <h3><i class="fas fa-info-circle me-2"></i>Program Details</h3>
                    </div>

                    <ul class="program-details-list list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="detail-label">
                                <i class="fas fa-tag me-2"></i>Category
                            </span>
                            <span class="detail-value"><?php echo htmlspecialchars($program['category']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="detail-label">
                                <i class="fas fa-clock me-2"></i>Duration
                            </span>
                            <span class="detail-value"><?php echo htmlspecialchars($program['duration']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="detail-label">
                                <i class="fas fa-laptop me-2"></i>Format
                            </span>
                            <span class="detail-value">Online & In-Person</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="detail-label">
                                <i class="fas fa-certificate me-2"></i>Certification
                            </span>
                            <span class="detail-value">
                                <i class="fas fa-check-circle text-success me-1"></i>Yes
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="detail-label">
                                <i class="fas fa-language me-2"></i>Language
                            </span>
                            <span class="detail-value">English</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="detail-label">
                                <i class="fas fa-headset me-2"></i>Support
                            </span>
                            <span class="detail-value">24/7 Available</span>
                        </li>
                    </ul>

                    <div class="sidebar-apply-btn">
                        <a href="<?= BASE_URL ?>/client/public/apply.php?program=<?php echo $programId; ?>"
                           class="premium-btn w-100">
                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Programs Section -->
<?php if (!empty($relatedPrograms)): ?>
<section class="related-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Related Programs</h2>
            <p class="section-subtitle">Expand your skills with these complementary programs</p>
        </div>

        <div class="row g-4">
            <?php foreach ($relatedPrograms as $relatedProgram): ?>
            <div class="col-md-6 col-lg-4">
                <div class="premium-program-card">
                    <div class="card-image-container">
                        <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($relatedProgram['image_path']); ?>"
                             alt="<?php echo htmlspecialchars($relatedProgram['title']); ?>">
                    </div>
                    <div class="card-body-premium">
                        <h5 class="card-title-premium"><?php echo htmlspecialchars($relatedProgram['title']); ?></h5>
                        <p class="card-description"><?php echo htmlspecialchars($relatedProgram['short_description']); ?></p>
                        <div class="card-meta">
                            <span class="card-category"><?php echo htmlspecialchars($relatedProgram['category']); ?></span>
                            <span class="card-duration">
                                <i class="fas fa-clock me-1"></i><?php echo htmlspecialchars($relatedProgram['duration']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-footer-premium">
                        <a href="<?php echo BASE_URL; ?>/client/public/program.php?id=<?php echo $relatedProgram['id']; ?>"
                           class="card-btn">
                            <i class="fas fa-arrow-right me-2"></i>View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>