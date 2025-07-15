<?php
$pageTitle = "Home - Skills for Africa";
$pageDescription = "Empowering African youth with skills for the digital economy";
$activePage = "home";

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

// Fetch featured programs
$featuredPrograms = $db->query("SELECT * FROM programs WHERE is_featured = TRUE LIMIT 3")->fetchAll();

// Fetch upcoming events
$upcomingEvents = $db->query("SELECT * FROM events WHERE event_date >= NOW() ORDER BY event_date ASC LIMIT 2")->fetchAll();

// Fetch featured testimonials
$testimonials = $db->query("SELECT * FROM testimonials WHERE is_featured = TRUE")->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Empowering African Youth for the Digital Economy</h1>
                <p class="lead mb-4">We provide world-class training programs to equip young Africans with in-demand digital skills.</p>
                <div class="d-flex gap-3">
                    <a href="<?php echo BASE_URL; ?>/client/public/apply.php" class="btn btn-light btn-lg px-4">Apply Now</a>
                    <a href="<?php echo BASE_URL; ?>/client/public/programs.php" class="btn btn-outline-light btn-lg px-4">Our Programs</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?php echo BASE_URL; ?>/client/public/assets/images/hero-image.jpg" alt="Students learning" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">Our Mission</h2>
                <p class="lead">To bridge the digital skills gap in Africa by providing accessible, high-quality training programs that prepare youth for the jobs of tomorrow.</p>
                <p>We partner with industry leaders to design a curriculum that meets current market demands, ensuring our graduates are job-ready.</p>
                <a href="<?php echo BASE_URL; ?>/client/public/about.php" class="btn btn-outline-primary mt-3">Learn More About Us</a>
            </div>
            <div class="col-lg-6">
                <div class="ratio ratio-16x9">
                    <iframe src="https://www.youtube.com/embed/example" title="About Skills for Africa" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Programs -->
<section class="bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Featured Programs</h2>
            <p class="lead text-muted">Explore our most popular training programs</p>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredPrograms as $program): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($program['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($program['title']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($program['title']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($program['short_description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($program['category']); ?></span>
                            <span class="text-muted small"><?php echo htmlspecialchars($program['duration']); ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="<?php echo BASE_URL . '/program.php?id=' . $program['id']; ?>" class="btn btn-sm btn-outline-primary stretched-link">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>/client/public/programs.php" class="btn btn-primary">View All Programs</a>
        </div>
    </div>
</section>

<!-- Upcoming Events -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Upcoming Events</h2>
            <p class="lead text-muted">Join our workshops, info sessions, and networking events</p>
        </div>

        <div class="row g-4">
            <?php foreach ($upcomingEvents as $event): ?>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="row g-0 h-100">
                        <div class="col-md-5">
                            <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($event['image_path']); ?>" class="img-fluid rounded-start h-100 object-fit-cover" alt="<?php echo htmlspecialchars($event['title']); ?>">
                        </div>
                        <div class="col-md-7">
                            <div class="card-body h-100 d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                <div class="mb-2">
                                    <span class="badge bg-primary"><?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                                    <span class="text-muted small ms-2"><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                                <p class="card-text flex-grow-1"><?php echo htmlspecialchars($event['short_description']); ?></p>
                                <a href="<?php echo BASE_URL . '/event.php?id=' . $event['id']; ?>" class="btn btn-sm btn-outline-primary align-self-start">More Info</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>/client/public/events.php" class="btn btn-primary">View All Events</a>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Success Stories</h2>
            <p class="lead text-muted">What our graduates say about us</p>
        </div>

        <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                <div class="carousel-item<?php echo $index === 0 ? ' active' : ''; ?>">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="testimonial-card text-center p-4 p-lg-5">
                                <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($testimonial['image_path']); ?>" class="rounded-circle mb-3" width="80" height="80" alt="<?php echo htmlspecialchars($testimonial['author_name']); ?>">
                                <p class="lead fst-italic mb-4">"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                                <h5 class="mb-1"><?php echo htmlspecialchars($testimonial['author_name']); ?></h5>
                                <p class="text-muted small"><?php echo htmlspecialchars($testimonial['author_title']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-secondary text-white">
    <div class="container text-center py-4">
        <h2 class="fw-bold mb-3">Ready to Transform Your Future?</h2>
        <p class="lead mb-4">Join thousands of African youth who have acquired digital skills through our programs.</p>
        <a href="<?php echo BASE_URL; ?>/client/public/apply.php" class="btn btn-light btn-lg px-4 me-2">Apply Now</a>
        <a href="<?php echo BASE_URL; ?>/client/public/contact.php" class="btn btn-outline-light btn-lg px-4">Contact Us</a>
    </div>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>