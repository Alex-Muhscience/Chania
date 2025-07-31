<?php
require_once '../includes/config.php';

// Get program ID from URL
$program_id = $_GET['id'] ?? null;

if (!$program_id) {
    header('Location: ' . BASE_URL . 'programs.php');
    exit;
}

// Fetch program details from database
try {
    $stmt = $db->prepare("SELECT * FROM programs WHERE id = ? AND is_active = 1 AND deleted_at IS NULL");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$program) {
        header('Location: ' . BASE_URL . 'programs.php');
        exit;
    }
} catch (Exception $e) {
    // If database error, show sample program
    $program = [
        'id' => 1,
        'title' => 'Full Stack Web Development',
        'description' => 'Master modern web development with HTML, CSS, JavaScript, React, Node.js, and database management. This comprehensive course covers both front-end and back-end development.',
        'category' => 'Technology',
        'duration' => '12 weeks',
        'fee_amount' => 25000,
        'image_url' => null
    ];
}

$page_title = $program['title'] . ' - Program Details';
$page_description = truncateText($program['description'] ?? 'Comprehensive training program', 160);

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
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>programs.php" class="text-white-50">Programs</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page"><?php echo htmlspecialchars($program['title']); ?></li>
                    </ol>
                </nav>
                <h1 class="display-4 text-white fw-bold mb-3"><?php echo htmlspecialchars($program['title']); ?></h1>
                <p class="lead text-white-75 mb-0"><?php echo ucfirst($program['category'] ?? 'General'); ?> Program</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="program-hero-image">
                    <img src="<?php echo !empty($program['image_url']) ? ASSETS_URL . 'images/programs/' . $program['image_url'] : 'https://via.placeholder.com/300x200?text=' . urlencode($program['title']); ?>" 
                         alt="<?php echo htmlspecialchars($program['title']); ?>" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Program Details -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Program Description -->
                <div class="program-section mb-5">
                    <h2 class="h3 mb-3">Program Overview</h2>
                    <p class="lead"><?php echo nl2br(htmlspecialchars($program['description'] ?? 'Comprehensive training program designed to enhance your skills.')); ?></p>
                </div>

                <!-- Schedule -->
                <div class="program-section mb-5">
                    <h2 class="h3 mb-3">Schedule & Sessions</h2>
                    <div class="schedule-container">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="h5">Online Sessions</h4>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-calendar text-primary me-2"></i>Session 1: March 1 - March 21, 2024</li>
                                    <li class="mb-2"><i class="fas fa-calendar text-primary me-2"></i>Session 2: June 1 - June 21, 2024</li>
                                    <li class="mb-2"><i class="fas fa-calendar text-primary me-2"></i>Session 3: September 1 - September 21, 2024</li>
                                    <li class="mb-2"><i class="fas fa-calendar text-primary me-2"></i>Session 4: December 1 - December 21, 2024</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h4 class="h5">Physical Location Sessions</h4>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-map-marker-alt text-success me-2"></i>Session 1: April 15 - May 5, 2024 (Nairobi)</li>
                                    <li class="mb-2"><i class="fas fa-map-marker-alt text-success me-2"></i>Session 2: July 15 - August 5, 2024 (Mombasa)</li>
                                    <li class="mb-2"><i class="fas fa-map-marker-alt text-success me-2"></i>Session 3: October 15 - November 5, 2024 (Kisumu)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Curriculum -->
                <div class="program-section mb-5">
                    <h2 class="h3 mb-3">Curriculum Overview</h2>
                    <div class="curriculum-modules">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="h6 mb-3">Core Modules</h4>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Module 1: Fundamentals</li>
                                    <li class="list-group-item">Module 2: Practical Applications</li>
                                    <li class="list-group-item">Module 3: Advanced Techniques</li>
                                    <li class="list-group-item">Module 4: Project Work</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h4 class="h6 mb-3">Supplementary</h4>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Industry Best Practices</li>
                                    <li class="list-group-item">Case Studies</li>
                                    <li class="list-group-item">Certification Preparation</li>
                                    <li class="list-group-item">Career Guidance</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Program Info Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="h5 mb-0">Program Information</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <strong>Duration:</strong><br>
                                <span class="text-muted"><?php echo $program['duration'] ?? '12 weeks'; ?></span>
                            </li>
                            <li class="mb-3">
                                <strong>Category:</strong><br>
                                <span class="badge bg-primary"><?php echo ucfirst($program['category'] ?? 'General'); ?></span>
                            </li>
                            <li class="mb-3">
                                <strong>Mode:</strong><br>
                                <span class="text-muted">Online & Physical Location</span>
                            </li>
                            <li class="mb-3">
                                <strong>Fee:</strong><br>
                                <?php if (empty($program['fee_amount']) || $program['fee_amount'] == 0): ?>
                                    <span class="h4 text-success mb-0">Free</span>
                                <?php else: ?>
                                    <span class="h4 text-primary mb-0">KSh <?php echo number_format($program['fee_amount']); ?></span>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Application Form -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="h5 mb-0">Apply Now</h3>
                    </div>
                    <div class="card-body">
                        <form action="submit_application.php" method="post">
                            <input type="hidden" name="program_id" value="<?php echo $program['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mode" class="form-label">Preferred Mode</label>
                                <select id="mode" name="mode" class="form-select" required>
                                    <option value="">Choose Mode...</option>
                                    <option value="online">Online</option>
                                    <option value="onsite">Physical Location</option>
                                    <option value="both">Both (Hybrid)</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Additional Information</label>
                                <textarea id="message" name="message" class="form-control" rows="3" placeholder="Tell us about your background and expectations..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i>Submit Application
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php';
?>
