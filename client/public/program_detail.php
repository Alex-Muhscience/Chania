<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Get program ID from URL
$program_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($program_id <= 0) {
    header('Location: ' . BASE_URL . '/client/public/programs.php');
    exit;
}

try {
    // Get program details with category information
    $sql = "SELECT p.*, 
                   pc.name as category_name, 
                   pc.color as category_color, 
                   pc.icon as category_icon,
                   pc.description as category_description
            FROM programs p 
            LEFT JOIN program_categories pc ON (
                (p.category = pc.name) OR 
                (FIND_IN_SET(pc.name, p.category) > 0)
            ) AND pc.is_active = 1 AND pc.deleted_at IS NULL
            WHERE p.id = ? AND p.is_active = 1 AND p.deleted_at IS NULL
            LIMIT 1";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$program_id]);
    $program = $stmt->fetch();
    
    if (!$program) {
        header('Location: ' . BASE_URL . '/programs.php');
        exit;
    }
    
    // Get program sessions/dates if available
    $session_sql = "SELECT * FROM program_sessions 
                    WHERE program_id = ? AND is_active = 1 AND deleted_at IS NULL 
                    AND start_date >= CURDATE() 
                    ORDER BY start_date ASC";
    $session_stmt = $db->prepare($session_sql);
    $session_stmt->execute([$program_id]);
    $sessions = $session_stmt->fetchAll();
    
    // Get related programs from the same category
    $related_sql = "SELECT p.*, pc.name as category_name, pc.color as category_color, pc.icon as category_icon
                    FROM programs p 
                    LEFT JOIN program_categories pc ON (
                        (p.category = pc.name) OR 
                        (FIND_IN_SET(pc.name, p.category) > 0)
                    ) AND pc.is_active = 1 AND pc.deleted_at IS NULL
                    WHERE p.id != ? AND p.is_active = 1 AND p.deleted_at IS NULL
                    AND (
                        p.category = ? OR 
                        FIND_IN_SET(?, p.category) > 0 OR
                        p.difficulty_level = ?
                    )
                    ORDER BY p.is_featured DESC, p.created_at DESC 
                    LIMIT 4";
    $related_stmt = $db->prepare($related_sql);
    $related_stmt->execute([$program_id, $program['category'], $program['category'], $program['difficulty_level']]);
    $related_programs = $related_stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Program detail page error: " . $e->getMessage());
    header('Location: ' . BASE_URL . '/programs.php');
    exit;
}

$pageTitle = $program['title'] . " - Skills for Africa";
$pageDescription = $program['short_description'] ?: "Learn " . $program['title'] . " with Skills for Africa training program";
$activePage = "programs";

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Program Hero Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/client/public/programs.php">Programs</a></li>
                        <?php if ($program['category_name']): ?>
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/client/public/programs.php?category=<?= urlencode($program['category_name']) ?>">
                                <?= htmlspecialchars($program['category_name']) ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($program['title']) ?></li>
                    </ol>
                </nav>

                <!-- Program Header -->
                <div class="d-flex align-items-center mb-3">
                    <?php if ($program['category_icon']): ?>
                    <div class="me-3" style="width: 60px; height: 60px; background: <?= htmlspecialchars($program['category_color'] ?? '#007bff') ?>; 
                                          border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="<?= htmlspecialchars($program['category_icon']) ?> fa-2x text-white"></i>
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <h1 class="display-5 fw-bold mb-2"><?= htmlspecialchars($program['title']) ?></h1>
                        <?php if ($program['category_name']): ?>
                        <span class="badge fs-6 mb-2" style="background-color: <?= htmlspecialchars($program['category_color'] ?? '#007bff') ?>;">
                            <i class="<?= htmlspecialchars($program['category_icon']) ?> me-1"></i>
                            <?= htmlspecialchars($program['category_name']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <p class="lead text-muted mb-4"><?= htmlspecialchars($program['short_description']) ?></p>

                <!-- Program Meta Information -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock text-primary me-2"></i>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($program['duration']) ?></div>
                                <small class="text-muted">Duration</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-signal text-primary me-2"></i>
                            <div>
                                <?php
                                $difficulty_colors = [
                                    'beginner' => 'success',
                                    'intermediate' => 'warning', 
                                    'advanced' => 'danger'
                                ];
                                $color = $difficulty_colors[$program['difficulty_level']] ?? 'primary';
                                ?>
                                <div class="fw-bold text-<?= $color ?>"><?= ucfirst($program['difficulty_level']) ?></div>
                                <small class="text-muted">Level</small>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($program['fee'])): ?>
                    <div class="col-sm-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-tag text-primary me-2"></i>
                            <div>
                                <div class="fw-bold">$<?= number_format($program['fee'] ?? 0, 0) ?></div>
                                <small class="text-muted">Price</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($program['certification_available']): ?>
                    <div class="col-sm-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-certificate text-primary me-2"></i>
                            <div>
                                <div class="fw-bold text-success">Yes</div>
                                <small class="text-muted">Certificate</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($program['is_featured']): ?>
                <div class="mb-3">
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-star me-1"></i> Featured Program
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-4 text-center">
                        <h5 class="card-title mb-3">Ready to Get Started?</h5>
                        <div class="d-grid gap-2">
                            <a href="#application-form" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i> Apply Now
                            </a>
                            <?php if (!empty($program['brochure_path'])): ?>
                            <a href="<?= BASE_URL ?>/<?= htmlspecialchars($program['brochure_path']) ?>" 
                               class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-download me-2"></i> Download Brochure
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($sessions)): ?>
                        <div class="mt-4">
                            <h6 class="text-muted mb-2">Upcoming Sessions:</h6>
                            <?php foreach (array_slice($sessions, 0, 3) as $session): ?>
                            <div class="text-start border-bottom pb-2 mb-2">
                                <div class="fw-bold"><?= date('M j, Y', strtotime($session['start_date'])) ?></div>
                                <small class="text-muted">
                                    <?php if ($session['end_date']): ?>
                                        - <?= date('M j, Y', strtotime($session['end_date'])) ?>
                                    <?php endif; ?>
                                    <?php if ($session['location']): ?>
                                        • <?= htmlspecialchars($session['location']) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Program Details Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Program Description -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-3">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            About This Program
                        </h3>
                        <div class="card-text">
                            <?= nl2br(htmlspecialchars($program['description'])) ?>
                        </div>
                    </div>
                </div>

                <!-- Benefits -->
                <?php if (!empty($program['benefits'])): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-3">
                            <i class="fas fa-bullseye text-primary me-2"></i>
                            Program Benefits
                        </h3>
                        <div class="card-text">
                            <?= nl2br(htmlspecialchars($program['benefits'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Curriculum -->
                <?php if (!empty($program['curriculum'])): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-3">
                            <i class="fas fa-list text-primary me-2"></i>
                            Course Curriculum
                        </h3>
                        <div class="card-text">
                            <?= nl2br(htmlspecialchars($program['curriculum'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Prerequisites -->
                <?php if (!empty($program['prerequisites'])): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-3">
                            <i class="fas fa-list-check text-primary me-2"></i>
                            Prerequisites
                        </h3>
                        <div class="card-text">
                            <?= nl2br(htmlspecialchars($program['prerequisites'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Course Schedule -->
                <?php if (!empty($sessions)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            Course Schedule & Registration
                        </h3>
                        
                        <div class="row g-4">
                            <?php foreach ($sessions as $session): ?>
                            <div class="col-md-6">
                                <div class="card border h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <?= date('F j, Y', strtotime($session['start_date'])) ?>
                                                </h5>
                                                <?php if (!empty($session['end_date'])): ?>
                                                <small class="text-muted">to <?= date('F j, Y', strtotime($session['end_date'])) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($session['is_open_for_registration'])): ?>
                                                <span class="badge bg-success">Open</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Closed</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="session-details">
                                            <!-- Location -->
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                <span class="fw-medium">
                                                    <?= htmlspecialchars($session['location'] ?? ($program['is_online'] ? 'Online Training' : 'TBD')) ?>
                                                </span>
                                                <?php if ($program['is_online']): ?>
                                                <span class="badge bg-info ms-2">Online</span>
                                                <?php else: ?>
                                                <span class="badge bg-warning text-dark ms-2">Physical</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Fee -->
                                            <?php if (!empty($session['fee']) || !empty($program['fee'])): ?>
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-dollar-sign text-muted me-2"></i>
                                                <span class="fw-bold text-primary">
                                                    $<?= number_format($session['fee'] ?? $program['fee'], 0) ?>
                                                </span>
                                                <span class="text-muted ms-1">per person</span>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- Duration -->
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-clock text-muted me-2"></i>
                                                <span><?= htmlspecialchars($program['duration']) ?> <?= htmlspecialchars($program['duration_type']) ?></span>
                                            </div>
                                            
                                            <!-- Availability -->
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fas fa-users text-muted me-2"></i>
                                                <?php if (!empty($session['max_participants'])): ?>
                                                    <?php 
                                                    $enrolled = $session['current_participants'] ?? 0;
                                                    $available = $session['max_participants'] - $enrolled;
                                                    ?>
                                                    <span class="<?= $available > 5 ? 'text-success' : ($available > 0 ? 'text-warning' : 'text-danger') ?>">
                                                        <?= $available ?> seats available (<?= $enrolled ?>/<?= $session['max_participants'] ?> enrolled)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-success">Unlimited seats</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Register Button -->
                                            <?php if (!empty($session['is_open_for_registration'])): ?>
                                                <?php if (empty($session['max_participants']) || ($session['current_participants'] ?? 0) < $session['max_participants']): ?>
                                                    <a href="#application-form" class="btn btn-success btn-sm w-100">
                                                        <i class="fas fa-user-plus me-1"></i> Register for this Session
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                                                        <i class="fas fa-times me-1"></i> Session Full
                                                    </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                                                    <i class="fas fa-lock me-1"></i> Registration Closed
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (!empty($program['registration_deadline'])): ?>
                        <div class="alert alert-info mt-4" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Registration Deadline:</strong> <?= date('F j, Y', strtotime($program['registration_deadline'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <!-- Default Program Schedule -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-3">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            Program Schedule
                        </h3>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                <i class="fas fa-play-circle text-success me-2"></i>
                                    <div>
                                        <div class="fw-bold">Start Date</div>
                                        <small class="text-muted"><?= $program['start_date'] ? date('F j, Y', strtotime($program['start_date'])) : 'TBD' ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($program['end_date'])): ?>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-stop-circle text-danger me-2"></i>
                                    <div>
                                        <div class="fw-bold">End Date</div>
                                        <small class="text-muted"><?= date('F j, Y', strtotime($program['end_date'])) ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    <div>
                                        <div class="fw-bold">Location</div>
                                        <small class="text-muted"><?= htmlspecialchars($program['location'] ?? ($program['is_online'] ? 'Online Training' : 'TBD')) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($program['registration_deadline'])): ?>
                        <div class="alert alert-info mt-4" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Registration Deadline:</strong> <?= date('F j, Y', strtotime($program['registration_deadline'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <!-- Program Application Form -->
                <div class="card shadow-sm sticky-top" style="top: 100px;" id="application-form">
                    <div class="card-body">
                        <h4 class="card-title mb-3">
                            <i class="fas fa-paper-plane text-primary me-2"></i>
                            Apply for This Program
                        </h4>
                        
                        <form id="programApplicationForm" class="needs-validation" novalidate>
                            <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                            <input type="hidden" name="program_title" value="<?= htmlspecialchars($program['title']) ?>">
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                                <div class="invalid-feedback">Please provide your full name.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Please provide a valid email address.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                                <div class="invalid-feedback">Please provide your phone number.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="country" class="form-label">Country *</label>
                                <select class="form-control" id="country" name="country" required>
                                    <option value="">Select your country</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Tanzania">Tanzania</option>
                                    <option value="Rwanda">Rwanda</option>
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="Other">Other</option>
                                </select>
                                <div class="invalid-feedback">Please select your country.</div>
                            </div>
                            
                            <?php if (!empty($sessions)): ?>
                            <div class="mb-3">
                                <label for="preferred_session" class="form-label">Preferred Session</label>
                                <select class="form-control" id="preferred_session" name="preferred_session">
                                    <option value="">No preference</option>
                                    <?php foreach ($sessions as $session): ?>
                                    <?php if ($session['is_open_for_registration']): ?>
                                    <option value="<?= $session['id'] ?>">
                                        <?= date('M j, Y', strtotime($session['start_date'])) ?>
                                        <?= $session['location'] ? ' - ' . htmlspecialchars($session['location']) : '' ?>
                                    </option>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="experience_level" class="form-label">Your Experience Level</label>
                                <select class="form-control" id="experience_level" name="experience_level">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="motivation" class="form-label">Why are you interested in this program?</label>
                                <textarea class="form-control" id="motivation" name="motivation" rows="3" 
                                          placeholder="Tell us about your goals and motivation..."></textarea>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms_accepted" name="terms_accepted" required>
                                <label class="form-check-label" for="terms_accepted">
                                    I agree to the <a href="<?= BASE_URL ?>/client/public/terms.php" target="_blank">Terms & Conditions</a> *
                                </label>
                                <div class="invalid-feedback">You must agree to the terms and conditions.</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Submit Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Programs Section -->
<?php if (!empty($related_programs)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Related Programs</h2>
        <div class="row g-4">
            <?php foreach ($related_programs as $related): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <?php if ($related['category_icon']): ?>
                            <div class="me-2" style="width: 40px; height: 40px; background: <?= htmlspecialchars($related['category_color'] ?? '#007bff') ?>; 
                                                  border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="<?= htmlspecialchars($related['category_icon']) ?> text-white"></i>
                            </div>
                            <?php endif; ?>
                            <?php if ($related['category_name']): ?>
                            <span class="badge" style="background-color: <?= htmlspecialchars($related['category_color'] ?? '#007bff') ?>;">
                                <?= htmlspecialchars($related['category_name']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <h5 class="card-title"><?= htmlspecialchars($related['title']) ?></h5>
                        <p class="card-text text-muted small">
                            <?= htmlspecialchars(substr($related['short_description'], 0, 100)) ?>...
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i> <?= htmlspecialchars($related['duration']) ?>
                            </small>
                            <span class="badge bg-<?= $difficulty_colors[$related['difficulty_level']] ?? 'primary' ?>">
                                <?= ucfirst($related['difficulty_level']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <a href="<?= BASE_URL ?>/client/public/program_detail.php?id=<?= $related['id'] ?>"
                           class="btn btn-outline-primary btn-sm w-100">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- JavaScript for form handling -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('programApplicationForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (form.checkValidity()) {
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;
            
            // Collect form data
            const formData = new FormData(form);
            
            // Submit via AJAX (you'll need to create the handler)
            fetch('<?= BASE_URL ?>/client/api/submit_application.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show a success message
                    form.innerHTML = `
                        <div class="text-center p-4">
                            <div class="text-success mb-3">
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                            <h5 class="text-success">Application Submitted Successfully!</h5>
                            <p class="text-muted">Thank you for your interest. We will contact you within 2-3 business days.</p>
                            <a href="<?= BASE_URL ?>/client/public/programs.php" class="btn btn-primary">Browse More Programs</a>
                        </div>
                    `;
                } else {
                    throw new Error(data.message || 'Submission failed');
                }
            })
            .catch(error => {
                // Show error message
                alert('Error submitting application: ' + error.message);
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }
        
        form.classList.add('was-validated');
    });
});
</script>

<style>
/* Main Layout & Typography */
:root {
    --primary-color: #1E90FF;
    --secondary-color: #FF9800;
    --success-color: #28a745;
    --warning-color: #FF9800;
    --danger-color: #dc3545;
    --info-color: #1E90FF;
    --light-color: #F9FAFB;
    --dark-color: #212529;
    --border-radius: 12px;
    --box-shadow: 0 0.125rem 0.25rem rgba(30, 144, 255, 0.075);
    --box-shadow-lg: 0 0.5rem 1rem rgba(30, 144, 255, 0.15);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.6;
    color: var(--dark-color);
}

/* Enhanced Cards */
.card {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    overflow: hidden;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: var(--box-shadow-lg);
}

.card-body {
    padding: 2rem;
}

.card-title {
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--dark-color);
}

/* Program Header Enhancements */
.program-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    position: relative;
    overflow: hidden;
}

.program-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.1);
    z-index: 1;
}

.program-hero .container {
    position: relative;
    z-index: 2;
}

.program-hero .breadcrumb {
    background: none;
    padding: 0;
    margin: 0;
}

.program-hero .breadcrumb a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: var(--transition);
}

.program-hero .breadcrumb a:hover {
    color: white;
}

.program-hero .breadcrumb-item.active {
    color: white;
}

.category-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: var(--transition);
}

.category-icon:hover {
    transform: scale(1.05);
}

/* Meta Information Cards */
.meta-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    border-left: 4px solid var(--primary-color);
}

.meta-card:hover {
    box-shadow: var(--box-shadow-lg);
    transform: translateY(-2px);
}

.meta-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    color: white;
    margin-right: 1rem;
}

/* Enhanced Badges */
.badge {
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    letter-spacing: 0.025em;
}

.badge.featured {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(240, 147, 251, 0.4);
}

/* Session Cards */
.session-card {
    border: 2px solid #e9ecef;
    border-radius: var(--border-radius);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.session-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.15);
}

.session-card.available {
    border-color: var(--success-color);
}

.session-card.full {
    border-color: var(--danger-color);
    opacity: 0.7;
}

/* Enhanced Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: var(--transition);
    text-transform: none;
    letter-spacing: 0.025em;
    border: none;
    position: relative;
    overflow: hidden;
}

.btn:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn:hover:before {
    left: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
}

.btn-success {
    background: linear-gradient(135deg, var(--success-color), #1e7e34);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.btn-outline-primary {
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    background: transparent;
}

.btn-outline-primary:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
}

/* Sticky Sidebar */
.sticky-sidebar {
    position: sticky;
    top: 120px;
    z-index: 10;
}

@media (max-width: 991.98px) {
    .sticky-sidebar {
        position: static;
        top: auto;
    }
}

/* Form Enhancements */
.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: var(--transition);
    font-size: 1rem;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
}

.form-label {
    font-weight: 500;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.form-check-input {
    border: 2px solid #dee2e6;
    border-radius: 4px;
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

/* Alert Enhancements */
.alert {
    border: none;
    border-radius: var(--border-radius);
    padding: 1.25rem;
    box-shadow: var(--box-shadow);
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1, #bee5eb);
    color: #0c5460;
}

/* Progress Indicators */
.availability-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 0.5rem;
}

.availability-indicator.available {
    background-color: var(--success-color);
    box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.3);
}

.availability-indicator.limited {
    background-color: var(--warning-color);
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.3);
}

.availability-indicator.full {
    background-color: var(--danger-color);
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.3);
}

/* Loading States */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.loading {
    animation: pulse 1.5s ease-in-out infinite;
}

/* Responsive Improvements */
@media (max-width: 768px) {
    .card-body {
        padding: 1.5rem;
    }
    
    .program-hero {
        padding: 2rem 0;
    }
    
    .meta-card {
        margin-bottom: 1rem;
    }
    
    .btn {
        padding: 0.625rem 1.25rem;
    }
}

/* Accessibility Improvements */
.btn:focus, .form-control:focus, .form-select:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* Smooth Scrolling */
html {
    scroll-behavior: smooth;
}

/* Section Dividers */
.section-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, #dee2e6, transparent);
    margin: 3rem 0;
}
</style>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
