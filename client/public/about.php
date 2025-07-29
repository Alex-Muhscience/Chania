<?php
$pageTitle = "About Us - Skills for Africa";
$pageDescription = "Learn about our mission, vision, and leadership team at Skills for Africa";
$activePage = "about";

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

// Fetch the leadership team from the database
$leadershipTeam = $db->query("SELECT * FROM team_members WHERE is_active = 1 AND deleted_at IS NULL ORDER BY display_order, name")->fetchAll();

// Fetch partners from the database
require_once __DIR__ . '/../includes/functions.php';
$partners = getPartners($db);
?>

<!-- Main About Content -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h1 class="display-5 fw-bold mb-3">About Skills for Africa</h1>
                <p class="lead">Empowering African youth with skills for the digital economy</p>
            </div>
        </div>

        <div class="row g-5">
            <div class="col-lg-6">
                <div class="pe-lg-5">
                    <h2 class="fw-bold mb-4">Our Mission</h2>
                    <p class="mb-4">To bridge the digital skills gap in Africa by providing accessible, high-quality training programs that prepare youth for the jobs of tomorrow. We partner with industry leaders to design curriculum that meets current market demands, ensuring our graduates are job-ready.</p>

                    <h2 class="fw-bold mb-4">Our Vision</h2>
                    <p class="mb-4">We envision an Africa where every young person has access to the digital skills needed to thrive in the 21st century economy, driving innovation and economic growth across the continent.</p>

                    <div class="bg-secondary p-4 rounded-3">
                        <h3 class="h5 fw-bold mb-3">Our Impact</h3>
                        <div class="row g-4">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="display-6 fw-bold text-primary">5,000+</h4>
                                    <p class="mb-0 small">Graduates Trained</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="display-6 fw-bold text-primary">85%</h4>
                                    <p class="mb-0 small">Employment Rate</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="display-6 fw-bold text-primary">12</h4>
                                    <p class="mb-0 small">Countries Reached</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="display-6 fw-bold text-primary">50+</h4>
                                    <p class="mb-0 small">Industry Partners</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <img src="<?php echo BASE_URL; ?>/client/public/assets/images/about-image.jpg" alt="Our Team" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Partners Info Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-12 text-center mb-4">
                <h2 class="fw-bold">Our Partners</h2>
                <p class="lead">We collaborate with leading organizations to deliver world-class training programs</p>
            </div>
        </div>
    </div>
</section>

<!-- Partners Carousel Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div id="partnersCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $chunkedPartners = array_chunk($partners, 6); // Show 6 partners per slide
                foreach ($chunkedPartners as $index => $partnerGroup): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="row g-3 justify-content-center">
                        <?php foreach ($partnerGroup as $partner): ?>
                        <div class="col-4 col-sm-2">
                            <div class="bg-white p-3 rounded-3 shadow-sm h-100 d-flex align-items-center justify-content-center">
                                <?php if (!empty($partner['website_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($partner['website_url']); ?>" target="_blank">
                                        <img src="<?php echo BASE_URL . '/uploads/partners/' . htmlspecialchars($partner['logo_path']); ?>"
                                             alt="<?php echo htmlspecialchars($partner['name']); ?>"
                                             class="img-fluid" style="max-height: 50px;">
                                    </a>
                                <?php else: ?>
                                    <img src="<?php echo BASE_URL . '/uploads/partners/' . htmlspecialchars($partner['logo_path']); ?>"
                                         alt="<?php echo htmlspecialchars($partner['name']); ?>"
                                         class="img-fluid" style="max-height: 50px;">
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#partnersCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#partnersCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>

<!-- Leadership Team -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <h2 class="fw-bold">Our Leadership Team</h2>
                <p class="lead text-muted">Meet the people driving our mission forward</p>
            </div>
        </div>
        <div class="row g-4 justify-content-center">
            <?php foreach ($leadershipTeam as $member): ?>
            <div class="col-md-6 col-lg-3"> <!-- Changed from col-4 to col-3 for more compact layout -->
                <div class="card h-100 border-0 shadow-sm">
                    <?php if (!empty($member['image_path'])): ?>
                        <img src="<?php echo BASE_URL . '/uploads/team/' . htmlspecialchars($member['image_path']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($member['name']); ?>">
                    <?php else: ?>
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body text-center">
                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($member['name']); ?></h5>
                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($member['position']); ?></p>
                        <p class="card-text small"><?php echo htmlspecialchars($member['bio']); ?></p>
                        <div class="social-links">
                            <?php if (!empty($member['linkedin_url'])): ?>
                                <a href="<?php echo htmlspecialchars($member['linkedin_url']); ?>" target="_blank" class="text-decoration-none me-2">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($member['twitter_url'])): ?>
                                <a href="<?php echo htmlspecialchars($member['twitter_url']); ?>" target="_blank" class="text-decoration-none me-2">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>