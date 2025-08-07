<?php
require_once '../includes/config.php';
require_once '../../shared/Core/CurrencyConverter.php';

// Fetch program details with enhanced fields
$program_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$program_query = "SELECT * FROM programs WHERE id = :id AND is_active = 1 AND deleted_at IS NULL";
$stmt = $db->prepare($program_query);
$stmt->execute(['id' => $program_id]);
$program = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$program) {
    header('Location: programs.php');
    exit;
}

// Fetch program schedules
$schedules_query = "SELECT * FROM program_schedules WHERE program_id = :program_id AND is_active = 1 ORDER BY start_date ASC";
$schedule_stmt = $db->prepare($schedules_query);
$schedule_stmt->execute(['program_id' => $program_id]);
$schedules = $schedule_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch enhanced program information from program_info table
$program_info_query = "SELECT * FROM program_info WHERE program_id = :program_id";
$info_stmt = $db->prepare($program_info_query);
$info_stmt->execute(['program_id' => $program_id]);
$program_info = $info_stmt->fetch(PDO::FETCH_ASSOC);

// Merge program_info data with program data for easier access in the template
if ($program_info) {
    $program = array_merge($program, $program_info);
}

$page_title = htmlspecialchars($program['title']);
$page_description = 'Learn more about ' . htmlspecialchars($program['title']) . ' - ' . substr(htmlspecialchars($program['description']), 0, 150) . '...';
$page_keywords = 'short course, ' . strtolower($program['category']) . ', ' . strtolower($program['title']) . ', online learning, certification';

// Generate year-round schedule for short courses
    function renderAvailableSchedules($schedules) {
        foreach ($schedules as $schedule) {
            $modeIndicator = $schedule['delivery_mode'] === 'online' ? 'info' : ($schedule['delivery_mode'] === 'physical' ? 'success' : 'warning');
            echo "<div class='schedule-item'>";
            echo "<strong>{$schedule['title']}</strong><br>";
            echo "<span class='badge bg-$modeIndicator'>{$schedule['delivery_mode']}</span><br>";
            echo "Start: " . date('M j, Y g:i A', strtotime($schedule['start_date'])) . "<br>";
            echo "End: " . date('M j, Y g:i A', strtotime($schedule['end_date'])) . "<br>";
            echo "Location: " . htmlspecialchars($schedule['location']) . "<br>";
            echo "Fee: " . ($schedule['online_fee'] ? 'Online: ' . $schedule['currency'] . ' ' . number_format($schedule['online_fee'], 2) : '') .
                          ($schedule['physical_fee'] ? ', Physical: ' . $schedule['currency'] . ' ' . number_format($schedule['physical_fee'], 2) : '') . "<br>";
            echo "<div class='apply-button'>";
            echo "<button class='btn btn-primary' onclick='handleApplyHover(event, " . json_encode($schedule['id']) . ")'>Apply</button>";
            echo "</div>";
            echo "</div>";
        }
    }

// JavaScript function will be defined in the HTML section below

    function generateYearSchedule($category = 'general') {
    $months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    
    $schedule = [];
    foreach ($months as $month) {
        $schedule[] = [
            'month' => $month,
            'online_dates' => [
                'Week 1: ' . $month . ' 6-8 (3 Days)',
                'Week 3: ' . $month . ' 20-22 (3 Days)'
            ],
            'physical_locations' => [
                'Nairobi Campus' => $month . ' 13-15 (3 Days)',
                'Mombasa Campus' => $month . ' 27-29 (3 Days)'
            ]
        ];
    }
    return $schedule;
}

// Course fees structure
function getCourseFees($category = 'general') {
    $fees = [
        'online' => [
            'amount' => 'KES 4,500',
            'description' => 'Includes all course materials, online resources, and digital certificate',
            'payment_options' => ['Full Payment', 'Installments (2 parts)']
        ],
        'physical' => [
            'amount' => 'KES 6,500',
            'description' => 'Includes course materials, lunch, certificates, and hands-on lab access',
            'payment_options' => ['Full Payment', 'Installments (2 parts)']
        ]
    ];
    return $fees;
}

// Course curriculum (sample - should be from database)
function getCourseCurriculum($program_id) {
    // This would typically come from a database
    return [
        'Day 1: Foundation & Setup' => [
            'Introduction to the field',
            'Setting up development environment',
            'Basic concepts and terminology',
            'Hands-on exercise 1'
        ],
        'Day 2: Core Skills Development' => [
            'Advanced concepts',
            'Practical applications',
            'Industry best practices',
            'Project work begins'
        ],
        'Day 3: Implementation & Certification' => [
            'Project completion',
            'Real-world case studies',
            'Assessment and evaluation',
            'Certificate award ceremony'
        ]
    ];
}

$schedule = generateYearSchedule($program['category']);
$fees = getCourseFees($program['category']);
$curriculum = getCourseCurriculum($program_id);

include '../includes/header.php';
?>

<!-- Modern Hero Section -->
<section class="modern-hero py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); position: relative; overflow: hidden;">
    <div class="hero-overlay"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8" data-aos="fade-right">
                <div class="hero-content">
                    <div class="program-category mb-3">
                        <span class="category-badge">
                            <i class="fas fa-graduation-cap me-2"></i><?php echo ucwords(htmlspecialchars($program['category'])); ?>
                        </span>
                    </div>
                    <h1 class="display-4 fw-bold text-white mb-4" style="line-height: 1.2;">
                        <?php echo htmlspecialchars($program['title']); ?>
                    </h1>
                    <p class="lead text-white-50 mb-4" style="font-size: 1.25rem; max-width: 600px;">
                        <?php echo htmlspecialchars($program['description']); ?>
                    </p>
                    <div class="program-highlights d-flex flex-wrap gap-3 mb-4">
                        <div class="highlight-item">
                            <i class="fas fa-clock text-warning"></i>
                            <span class="ms-2"><?php echo htmlspecialchars($program['duration']); ?></span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-certificate text-success"></i>
                            <span class="ms-2">Certificate Included</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-users text-info"></i>
                            <span class="ms-2">Expert Instructors</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-left" data-aos-delay="200">
                <div class="hero-card">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4 text-center">
                            <div class="price-display mb-3">
                                <?php if ($program['fee'] && $program['fee'] > 0): 
                                    $kshAmount = CurrencyConverter::usdToKsh($program['fee']);
                                    $formattedKsh = CurrencyConverter::formatKsh($kshAmount);
                                    $formattedUsd = CurrencyConverter::formatUsd($program['fee']);
                                ?>
                                    <div class="price-amount text-primary fw-bold" style="font-size: 2rem;">
                                        <?php echo $formattedKsh; ?>
                                    </div>
                                    <div class="price-currency text-muted"><?php echo $formattedUsd; ?> equivalent</div>
                                <?php else: ?>
                                    <div class="price-amount text-success fw-bold" style="font-size: 2rem;">FREE</div>
                                    <div class="price-currency text-muted">No Cost</div>
                                <?php endif; ?>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="#schedules" class="btn btn-primary btn-lg smooth-scroll">
                                    <i class="fas fa-calendar-alt me-2"></i>View Schedules
                                </a>
                                <a href="#details" class="btn btn-outline-primary smooth-scroll">
                                    <i class="fas fa-info-circle me-2"></i>Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
</section>

<!-- Program Content Section -->
<section class="py-5 bg-light" id="details">
    <div class="container">
        <div class="program-content-wrapper">
            <!-- Program Description -->
            <div class="content-card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle me-2"></i>Course Description</h3>
                </div>
                <div class="card-body">
                    <p class="lead"><?php echo nl2br(htmlspecialchars($program['description'])); ?></p>
                </div>
            </div>

            <!-- Course Schedule Section -->
            <?php if (!empty($schedules)): ?>
            <div class="content-card mb-4" id="schedules">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-alt me-2"></i>Available Schedules</h3>
                </div>
                <div class="card-body">
                    <div class="skills-africa-table">
                        <table class="course-schedule-table">
                            <thead>
                                <tr>
                                    <th>Schedule</th>
                                    <th>Dates</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                    <th>Location</th>
                                    <th>Currency</th>
                                    <th>Apply</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule): ?>
                                    <tr class="schedule-table-row">
                                        <td class="schedule-column">
                                            <div class="schedule-title">
                                                <?php echo htmlspecialchars($schedule['title'] ?? 'Program Schedule'); ?>
                                            </div>
                                            <div class="delivery-mode">
                                                <?php 
                                                $mode = $schedule['delivery_mode'] ?? 'physical';
                                                $modeClass = $mode === 'online' ? 'online-badge' : 'physical-badge';
                                                $modeIcon = $mode === 'online' ? 'fas fa-laptop' : 'fas fa-map-marker-alt';
                                                echo "<span class='mode-badge $modeClass'><i class='$modeIcon'></i> " . ucfirst($mode) . "</span>";
                                                ?>
                                            </div>
                                        </td>
                                        <td class="dates-column">
                                            <div class="date-info">
                                                <div class="start-date"><?php echo date('d M Y', strtotime($schedule['start_date'])); ?></div>
                                                <div class="date-separator">to</div>
                                                <div class="end-date"><?php echo date('d M Y', strtotime($schedule['end_date'])); ?></div>
                                            </div>
                                        </td>
                                        <td class="time-column">
                                            <div class="time-info">
                                                <?php 
                                                $start_time = $schedule['start_time'] ?? '09:00';
                                                $end_time = $schedule['end_time'] ?? '17:00';
                                                echo date('g:i A', strtotime($start_time)) . ' -<br>' . date('g:i A', strtotime($end_time));
                                                ?>
                                            </div>
                                        </td>
                                        <td class="duration-column">
                                            <div class="duration-info">
                                                <?php 
                                                $start = new DateTime($schedule['start_date']);
                                                $end = new DateTime($schedule['end_date']);
                                                $interval = $start->diff($end);
                                                $days = $interval->days + 1; // Include start day
                                                echo $days . ' Day' . ($days > 1 ? 's' : '');
                                                ?>
                                            </div>
                                        </td>
                                        <td class="location-column">
                                            <?php echo htmlspecialchars($schedule['location']); ?>
                                        </td>
                                        <td class="currency-column" id="currency-<?php echo $schedule['id']; ?>">
                                            <div class="currency-selector-wrapper">
                                                <select class="currency-selector" onchange="updateSchedulePricing(<?php echo $schedule['id']; ?>, this.value)">
                                                    <option value="USD">USD ($)</option>
                                                    <option value="KSH">KSH (KSh)</option>
                                                </select>
                                                <div class="pricing-display" id="pricing-display-<?php echo $schedule['id']; ?>">
                                                    <?php if (!empty($schedule['online_fee']) && $schedule['online_fee'] > 0): 
                                                        // Convert USD fees to KSH
                                                        $onlineKsh = CurrencyConverter::usdToKsh($schedule['online_fee']);
                                                        $onlineFormattedKsh = CurrencyConverter::formatKsh($onlineKsh);
                                                        $onlineFormattedUsd = CurrencyConverter::formatUsd($schedule['online_fee']);
                                                    ?>
                                                        <div class="price-item online-price">
                                                            <span class="mode-label">Online:</span>
                                                            <span class="price-value">
                                                                <span class="usd-price"><?php echo $onlineFormattedUsd; ?></span>
                                                                <span class="ksh-price" style="display: none;"><?php echo $onlineFormattedKsh; ?></span>
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($schedule['physical_fee']) && $schedule['physical_fee'] > 0): 
                                                        // Convert USD fees to KSH
                                                        $physicalKsh = CurrencyConverter::usdToKsh($schedule['physical_fee']);
                                                        $physicalFormattedKsh = CurrencyConverter::formatKsh($physicalKsh);
                                                        $physicalFormattedUsd = CurrencyConverter::formatUsd($schedule['physical_fee']);
                                                    ?>
                                                        <div class="price-item physical-price">
                                                            <span class="mode-label">Physical:</span>
                                                            <span class="price-value">
                                                                <span class="usd-price"><?php echo $physicalFormattedUsd; ?></span>
                                                                <span class="ksh-price" style="display: none;"><?php echo $physicalFormattedKsh; ?></span>
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (empty($schedule['online_fee']) && empty($schedule['physical_fee'])): ?>
                                                        <div class="price-item free-price">
                                                            <span class="mode-label">FREE</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="apply-column">
                                            <div class="apply-hover-container" onmouseleave="hideApplyOptions(<?php echo $schedule['id']; ?>)">
                                                <button class="apply-trigger-btn" 
                                                        onmouseenter="showApplyOptions(<?php echo $schedule['id']; ?>)"
                                                        onclick="showApplyOptions(<?php echo $schedule['id']; ?>)">
                                                    <i class="fas fa-hand-pointer me-1"></i>Apply
                                                </button>
                                                <div class="apply-options-dropdown" id="apply-options-<?php echo $schedule['id']; ?>" style="display: none;">
                                                    <?php if (!empty($schedule['online_fee']) || (!empty($schedule['online_fee']) && $schedule['online_fee'] == 0)): ?>
                                                        <button class="apply-option online-option" 
                                                                onclick="applyForScheduleMode(<?php echo $schedule['id']; ?>, '<?php echo addslashes($schedule['title'] ?? 'Schedule'); ?>', 'online')">
                                                            <i class="fas fa-laptop me-2"></i>Apply Online
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if (!empty($schedule['physical_fee']) || (!empty($schedule['physical_fee']) && $schedule['physical_fee'] == 0)): ?>
                                                        <button class="apply-option physical-option" 
                                                                onclick="applyForScheduleMode(<?php echo $schedule['id']; ?>, '<?php echo addslashes($schedule['title'] ?? 'Schedule'); ?>', 'physical')">
                                                            <i class="fas fa-map-marker-alt me-2"></i>Apply Physical
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="content-card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-times me-2"></i>Course Schedule</h3>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5>No Active Schedules</h5>
                        <p class="text-muted">Please contact us for upcoming schedules</p>
                        <a href="mailto:info@chaniacollege.com" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-1"></i> Contact Us
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

                    <!-- Learning Objectives -->
                    <?php if (!empty($program['objectives'])): ?>
                    <div class="content-card mb-4">
                        <div class="card-header">
                            <h3><i class="fas fa-bullseye me-2"></i>Learning Objectives</h3>
                        </div>
                        <div class="card-body">
                            <div class="objectives-list">
                                <?php 
                                $objectives = explode("\n", $program['objectives']);
                                foreach($objectives as $objective):
                                    if(trim($objective)):
                                ?>
                                <div class="objective-item mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><?php echo htmlspecialchars(trim($objective)); ?></span>
                                </div>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Two Column Layout for Additional Info -->
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <?php if (!empty($program['target_audience'])): ?>
                            <div class="content-card mb-4">
                                <div class="card-header">
                                    <h4><i class="fas fa-users me-2"></i>Target Audience</h4>
                                </div>
                                <div class="card-body">
                                    <p><?php echo nl2br(htmlspecialchars($program['target_audience'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($program['benefits'])): ?>
                            <div class="content-card mb-4">
                                <div class="card-header">
                                    <h4><i class="fas fa-star me-2"></i>Course Benefits</h4>
                                </div>
                                <div class="card-body">
                                    <p><?php echo nl2br(htmlspecialchars($program['benefits'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($program['materials_included'])): ?>
                            <div class="content-card mb-4">
                                <div class="card-header">
                                    <h4><i class="fas fa-box me-2"></i>Materials Included</h4>
                                </div>
                                <div class="card-body">
                                    <p><?php echo nl2br(htmlspecialchars($program['materials_included'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <?php if (!empty($program['prerequisites'])): ?>
                            <div class="content-card mb-4">
                                <div class="card-header">
                                    <h4><i class="fas fa-list-check me-2"></i>Prerequisites</h4>
                                </div>
                                <div class="card-body">
                                    <p><?php echo nl2br(htmlspecialchars($program['prerequisites'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($program['career_outcomes'])): ?>
                            <div class="content-card mb-4">
                                <div class="card-header">
                                    <h4><i class="fas fa-briefcase me-2"></i>Career Outcomes</h4>
                                </div>
                                <div class="card-body">
                                    <p><?php echo nl2br(htmlspecialchars($program['career_outcomes'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($program['certification_details'])): ?>
                            <div class="content-card mb-4">
                                <div class="card-header">
                                    <h4><i class="fas fa-certificate me-2"></i>Certification Details</h4>
                                </div>
                                <div class="card-body">
                                    <p><?php echo nl2br(htmlspecialchars($program['certification_details'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <?php if (!empty($program['general_notes'])): ?>
                    <div class="content-card mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-info-circle me-2"></i>Important Notes</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <?php echo nl2br(htmlspecialchars($program['general_notes'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Contact Information -->
                    <div class="content-card">
                        <div class="card-header">
                            <h4><i class="fas fa-phone me-2"></i>Contact Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="contact-item mb-2">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        <span>info@chaniacollege.com</span>
                                    </div>
                                    <div class="contact-item mb-2">
                                        <i class="fas fa-phone text-primary me-2"></i>
                                        <span>+254-700-000-000</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="contact-item mb-2">
                                        <i class="fas fa-globe text-primary me-2"></i>
                                        <span>www.chaniacollege.com</span>
                                    </div>
                                    <div class="contact-item mb-2">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                        <span>Chania Technical Training Institute, Kenya</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</section>

<style>
/* Modern Hero Styles */
.modern-hero {
    position: relative;
    min-height: 70vh;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.1);
    z-index: 1;
}

.min-vh-50 {
    min-height: 50vh;
}

.category-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 25px;
    color: white;
    font-size: 0.9rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.category-badge:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.program-highlights {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 1.5rem;
}

.highlight-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    font-size: 0.9rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.highlight-item:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.hero-card {
    position: relative;
    z-index: 3;
}

.hero-card .card {
    border-radius: 16px;
    backdrop-filter: blur(20px);
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.price-display {
    text-align: center;
    padding: 1rem 0;
}

.price-amount {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
}

.price-currency {
    font-size: 0.9rem;
    margin-top: 0.25rem;
}

.hero-shapes {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    z-index: 1;
}

.shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: float 6s ease-in-out infinite;
}

.shape-1 {
    width: 80px;
    height: 80px;
    top: 10%;
    right: 10%;
    animation-delay: 0s;
}

.shape-2 {
    width: 60px;
    height: 60px;
    bottom: 20%;
    left: 10%;
    animation-delay: 2s;
}

.shape-3 {
    width: 40px;
    height: 40px;
    top: 60%;
    right: 30%;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.smooth-scroll {
    scroll-behavior: smooth;
    text-decoration: none;
}

.smooth-scroll:hover {
    text-decoration: none;
}

/* Responsive Hero */
@media (max-width: 768px) {
    .modern-hero {
        min-height: 60vh;
        text-align: center;
    }
    
    .program-highlights {
        justify-content: center;
    }
    
    .highlight-item {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
    }
    
    .category-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
    
    .price-amount {
        font-size: 2rem;
    }
}

/* Clean Content Card Styles */
.program-content-wrapper {
    max-width: 100%;
}

.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    overflow: hidden;
    transition: all 0.3s ease;
}

.content-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.content-card .card-header {
    background: linear-gradient(135deg, #DA2525 0%, #B31E1E 100%);
    color: white;
    padding: 1.2rem 1.5rem;
    border-bottom: none;
    margin-bottom: 0;
}

.content-card .card-header h3,
.content-card .card-header h4 {
    color: white;
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.content-card .card-header i {
    opacity: 0.9;
}

.content-card .card-body {
    padding: 1.5rem;
    background: white;
}

.content-card .card-body p {
    color: #495057;
    line-height: 1.6;
    margin-bottom: 0;
}

.content-card .card-body .lead {
    font-size: 1.1rem;
    font-weight: 400;
    color: #343a40;
}

/* Objectives List */
.objectives-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.objective-item {
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
    padding: 0.8rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #28a745;
    transition: all 0.2s ease;
}

.objective-item:hover {
    background: #e8f5e8;
    transform: translateX(5px);
}

.objective-item i {
    margin-top: 0.2rem;
    flex-shrink: 0;
    font-size: 1rem;
}

.objective-item span {
    color: #495057;
    line-height: 1.5;
}

/* Contact Items */
.contact-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.5rem 0;
    font-size: 0.95rem;
}

.contact-item i {
    width: 16px;
    text-align: center;
    flex-shrink: 0;
}

.objectives-list {
    display: grid;
    gap: 0.8rem;
}

.objective-item {
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
    padding: 0.5rem 0;
}

.objective-item i {
    margin-top: 0.2rem;
    flex-shrink: 0;
}

/* Schedule Table Styles */
.schedule-table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    width: 100%;
}

.schedule-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.schedule-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.schedule-table th {
    padding: 1rem 0.8rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: none;
}

.schedule-table td {
    padding: 1.2rem 0.8rem;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: top;
}

.schedule-row:hover {
    background: #f8f9fa;
    transition: background-color 0.2s ease;
}

.schedule-row:last-child td {
    border-bottom: none;
}

/* Column Specific Styles - Larger widths for better readability */
.schedule-col { width: 25%; min-width: 180px; }
.dates-col { width: 22%; min-width: 170px; }
.location-col { width: 18%; min-width: 150px; }
.pricing-col { width: 22%; min-width: 180px; }
.apply-col { width: 13%; min-width: 120px; }

/* Schedule Name Cell */
.schedule-title-cell strong {
    color: #333;
    font-size: 1rem;
    display: block;
    margin-bottom: 0.3rem;
}

.timezone-info {
    color: #666;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.timezone-info i {
    color: #667eea;
}

/* Date Range Cell */
.date-range {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.start-date, .end-date {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
}

.start-date i, .end-date i {
    color: #667eea;
    width: 12px;
}

.date-separator {
    text-align: center;
    color: #999;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Time Cell */
.time-range {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.2rem;
}

.time-range i {
    color: #667eea;
    margin-bottom: 0.2rem;
}

.time-separator {
    color: #999;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Duration Cell */
.duration-cell {
    text-align: center;
}

.duration-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
}

.duration-info i {
    color: #667eea;
    font-size: 0.9rem;
}

.duration-value {
    font-size: 1.2rem;
    font-weight: 700;
    color: #333;
}

.duration-text {
    font-size: 0.75rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Location Cell */
.location-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.location-info i {
    color: #667eea;
    width: 12px;
    flex-shrink: 0;
}

/* Currency Cell */
.currency-cell {
    padding: 0.8rem 0.5rem;
}

.currency-selector {
    position: relative;
}

.currency-select {
    width: 100%;
    padding: 0.5rem 0.3rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.75rem;
    background: white;
    cursor: pointer;
    transition: border-color 0.3s ease;
}

.currency-select:hover {
    border-color: #667eea;
}

.currency-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
}

.currency-icon {
    position: absolute;
    top: 50%;
    right: 0.5rem;
    transform: translateY(-50%);
    color: #667eea;
    font-size: 0.7rem;
    pointer-events: none;
}

/* Mode Badge */
.mode-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.mode-badge i {
    font-size: 0.8rem;
}

.mode-badge.mode-online {
    background: #e3f2fd;
    color: #1976d2;
    border: 1px solid #bbdefb;
}

.mode-badge.mode-physical {
    background: #e8f5e8;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}


/* Pricing Cell */
.pricing-info {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.price-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.3rem 0.6rem;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.price-label {
    font-size: 0.75rem;
    color: #666;
    font-weight: 500;
}

.price-value {
    font-size: 0.85rem;
    font-weight: 600;
    color: #333;
}

.online-price {
    border-left: 3px solid #1976d2;
}

.physical-price {
    border-left: 3px solid #2e7d32;
}

.free-price {
    background: #e8f5e8;
    border: 1px solid #2e7d32;
    justify-content: center;
    gap: 0.4rem;
}

.free-price i {
    color: #2e7d32;
}

/* Apply Hover Container */
.apply-hover-container {
    position: relative;
    display: inline-block;
    width: 100%;
}

/* Course Schedule Sidebar Layout Fix */
.course-schedule-sidebar {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.schedule-table-wrapper {
    flex: 1;
    overflow-y: auto;
    overflow-x: auto;
}

/* Ensure table content is fully visible */
.schedule-table {
    min-width: 800px;
}

/* Wrapper for horizontal scrolling */
.table-scroll-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.apply-hover-trigger {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 0.6rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    text-decoration: none;
    white-space: nowrap;
    justify-content: center;
    width: 100%;
    text-align: center;
}

.apply-hover-trigger:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.apply-hover-trigger i {
    font-size: 0.75rem;
}

.apply-buttons-group {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 10;
    background: white;
    border-radius: 8px;
    padding: 0.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #e9ecef;
}

.apply-hover-container:hover .apply-buttons-group {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.apply-hover-container:hover .apply-hover-trigger {
    opacity: 0.3;
}

.btn-apply-online,
.btn-apply-physical {
    background: transparent;
    border: 1px solid #ddd;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    justify-content: center;
    white-space: nowrap;
}

.btn-apply-online {
    color: #1976d2;
    border-color: #1976d2;
}

.btn-apply-online:hover {
    background: #1976d2;
    color: white;
    transform: translateY(-1px);
}

.btn-apply-physical {
    color: #2e7d32;
    border-color: #2e7d32;
}

.btn-apply-physical:hover {
    background: #2e7d32;
    color: white;
    transform: translateY(-1px);
}

.btn-apply-online i,
.btn-apply-physical i {
    font-size: 0.7rem;
}

/* Table Legend */
.table-legend {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.table-legend h6 {
    color: #333;
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 1rem;
}

.legend-items {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.legend-desc {
    color: #666;
    font-size: 0.85rem;
}

/* No Schedules Table State */
.no-schedules-table {
    background: white;
    border-radius: 12px;
    padding: 3rem;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.no-schedules-content {
    color: #666;
}

.no-schedules-content i {
    font-size: 3rem;
    color: #ddd;
    margin-bottom: 1rem;
}

.no-schedules-content h5 {
    color: #333;
    margin: 1rem 0 0.5rem 0;
}

.contact-prompt {
    margin-top: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 0.9rem;
    color: #555;
}

.contact-prompt i {
    color: #667eea;
    font-size: 0.9rem;
    margin-right: 0.3rem;
}

/* Apply cell center alignment */
.apply-cell {
    text-align: center;
}

.apply-cell .apply-buttons {
    justify-content: center;
}

/* Program Info Wrapper */
.program-info-wrapper {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

/* Responsive Table */
@media (max-width: 1400px) {
    .schedules-table {
        font-size: 0.8rem;
    }
    
    .schedules-table th,
    .schedules-table td {
        padding: 0.8rem 0.6rem;
    }
}

@media (max-width: 1200px) {
    .schedule-table-wrapper {
        position: relative;
        top: 0;
        max-height: none;
        margin-top: 2rem;
    }
    
    .schedules-table {
        font-size: 0.75rem;
        min-width: 700px;
    }
    
    .schedules-table th,
    .schedules-table td {
        padding: 0.7rem 0.5rem;
    }
}

@media (max-width: 992px) {
    .schedules-table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .schedules-table {
        min-width: 650px;
    }
}

@media (max-width: 768px) {
    .schedules-table {
        min-width: 600px;
        font-size: 0.7rem;
    }
    
    .schedules-table th,
    .schedules-table td {
        padding: 0.6rem 0.4rem;
    }
    
    .brochure-page {
        padding: 1.5rem 1rem;
    }
    
    .btn-apply-compact {
        width: 24px;
        height: 24px;
    }
    
    .btn-apply-compact i {
        font-size: 0.7rem;
    }
}

/* Contact Section */
.contact-section {
    background: linear-gradient(135deg, #DA2525 0%, #B31E1E 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-top: 2rem;
}

.contact-section h4 {
    color: white;
    margin-bottom: 1.5rem;
}

.contact-details {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.contact-item i {
    width: 16px;
    opacity: 0.8;
}

/* Print Styles */
@media print {
    .brochure-wrapper {
        background: none;
        padding: 0;
        box-shadow: none;
    }
    
    .brochure-container {
        box-shadow: none;
        max-width: none;
    }
    
    .brochure-page {
        padding: 1.5rem;
        min-height: auto;
    }
    
    .btn-apply {
        display: none;
    }
    
    body * {
        visibility: hidden;
    }
    
    #brochure-content,
    #brochure-content * {
        visibility: visible;
    }
    
    #brochure-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}

/* Schedule Table Wrapper */
.schedule-table-wrapper {
    position: sticky;
    top: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
    max-height: calc(100vh - 40px);
    display: flex;
    flex-direction: column;
}

.schedule-table-header {
    background: linear-gradient(135deg, #DA2525 0%, #B31E1E 100%);
    color: white;
    padding: 1.2rem;
    border-radius: 12px 12px 0 0;
    text-align: center;
}

.schedule-table-header h4 {
    margin: 0 0 0.25rem 0;
    font-size: 1.2rem;
    font-weight: 600;
}

.schedule-table-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.85rem;
}

/* Currency Toggle */
.currency-toggle-wrapper {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.currency-toggle-label {
    font-size: 0.8rem;
    font-weight: 500;
    color: #666;
    margin-right: 0.5rem;
}

.currency-toggle-buttons {
    display: flex;
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
}

.currency-btn {
    background: white;
    border: none;
    padding: 0.3rem 0.7rem;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.currency-btn.active {
    background: #DA2525;
    color: white;
}

.currency-btn:not(.active):hover {
    background: #f1f3f4;
}

/* Course Schedule Sidebar */
.course-schedule-sidebar {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
    position: sticky;
    top: 20px;
    max-height: calc(100vh - 40px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    background: linear-gradient(135deg, #DA2525 0%, #B31E1E 100%);
    color: white;
    padding: 1.2rem;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar-header h4 {
    color: white;
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.download-pdf-btn {
    background: #27ae60;
    border: none;
    color: white;
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.download-pdf-btn:hover {
    background: #2ecc71;
    transform: translateY(-1px);
}

.schedule-table-wrapper {
    overflow: hidden;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.schedule-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-size: 0.85rem;
}

.schedule-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.schedule-table th {
    padding: 1rem 0.8rem;
    text-align: left;
    font-weight: 600;
    color: white;
    border-bottom: none;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.schedule-table td {
    padding: 1.2rem 0.8rem;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: top;
    color: #333;
    font-size: 0.85rem;
}

.schedule-row:hover {
    background: #f8f9fa;
    transition: background-color 0.2s ease;
}

.schedule-row:last-child td {
    border-bottom: none;
}

.dates-cell {
    font-weight: 500;
    line-height: 1.3;
    color: #333;
}

.fees-cell {
    font-weight: 600;
    color: #f39c12;
}

.location-cell {
    color: #333;
}

/* Apply Dropdown */
.apply-dropdown {
    position: relative;
}

.btn-register {
    background: #f39c12;
    border: none;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    transition: all 0.2s ease;
}

.btn-register:hover {
    background: #e67e22;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.apply-options {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #34495e;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 100;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    margin-top: 0.25rem;
}

.apply-options.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.apply-option {
    display: block;
    width: 100%;
    padding: 0.6rem 1rem;
    background: transparent;
    border: none;
    color: white;
    text-align: center;
    font-size: 0.8rem;
    cursor: pointer;
    transition: background 0.2s ease;
    border-radius: 0;
}

.apply-option:first-child {
    border-radius: 4px 4px 0 0;
}

.apply-option:last-child {
    border-radius: 0 0 4px 4px;
}

.apply-option:only-child {
    border-radius: 4px;
}

.apply-option:hover {
    background: rgba(52, 152, 219, 0.2);
}

.physical-class {
    border-bottom: 1px solid #4a5f7a;
}

.physical-class:hover {
    background: #27ae60;
}

.online-class:hover {
    background: #e74c3c;
}

.no-schedules {
    padding: 2rem;
    text-align: center;
    color: #bdc3c7;
}

.no-schedules h6 {
    color: #ecf0f1;
    margin-bottom: 1rem;
}

.no-schedules i {
    color: #7f8c8d;
    margin-bottom: 1rem;
}

/* Column Width Distribution - Optimized for Apply Button */
.course-schedule-table th:nth-child(1), /* Schedule */
.course-schedule-table td:nth-child(1) {
    width: 20%;
    min-width: 120px;
}

.course-schedule-table th:nth-child(2), /* Dates */
.course-schedule-table td:nth-child(2) {
    width: 15%;
    min-width: 100px;
}

.course-schedule-table th:nth-child(3), /* Time */
.course-schedule-table td:nth-child(3) {
    width: 12%;
    min-width: 90px;
}

.course-schedule-table th:nth-child(4), /* Duration */
.course-schedule-table td:nth-child(4) {
    width: 10%;
    min-width: 80px;
}

.course-schedule-table th:nth-child(5), /* Location */
.course-schedule-table td:nth-child(5) {
    width: 18%;
    min-width: 110px;
}

.course-schedule-table th:nth-child(6), /* Currency */
.course-schedule-table td:nth-child(6) {
    width: 15%;
    min-width: 120px;
}

.course-schedule-table th:nth-child(7), /* Apply */
.course-schedule-table td:nth-child(7) {
    width: 10%;
    min-width: 100px;
    text-align: center;
}

/* Cell Specific Styles */
.schedule-name-cell .schedule-name {
    font-weight: 600;
    color: #333;
    font-size: 0.85rem;
}

.schedule-name-cell .schedule-duration {
    font-size: 0.75rem;
    color: #666;
}

.dates-cell .date-info {
    font-size: 0.75rem;
    line-height: 1.4;
}

.location-cell {
    font-size: 0.8rem;
}

.pricing-cell .price-item {
    font-size: 0.75rem;
    margin-bottom: 0.25rem;
}
.pricing-cell .price-item:last-child {
    margin-bottom: 0;
}

.pricing-cell .mode-label {
    font-weight: 500;
    margin-right: 0.25rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.pricing-cell .price-value {
    font-weight: 600;
}

.pricing-cell .online .mode-label {
    color: #1976d2;
}

.pricing-cell .physical .mode-label {
    color: #2e7d32;
}

.apply-cell .apply-buttons {
    display: flex;
    gap: 0.25rem;
}

.btn-apply-compact {
    border: 1px solid;
    border-radius: 5px;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.btn-apply-compact i {
    font-size: 0.8rem;
}

.btn-online {
    color: #1976d2;
    background: white;
    border-color: #1976d2;
}

.btn-online:hover {
    background: #1976d2;
    color: white;
}

.btn-physical {
    color: #2e7d32;
    background: white;
    border-color: #2e7d32;
}

.btn-physical:hover {
    background: #2e7d32;
    color: white;
}

.quick-actions {
    padding: 0.75rem 1rem;
    text-align: center;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}

.no-schedules-table {
    padding: 2rem 1rem;
    text-align: center;
}

/* Skills for Africa Table Layout */
.skills-africa-table {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background-color: #0b2239; /* Dark blue background */
    color: white;
}

/* Currency Selector Column */
.currency-column {
    text-align: center;
    width: 120px;
    min-width: 120px;
}

.currency-selector-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.8rem;
}

.currency-selector {
    background: #1e3a5f;
    border: 1px solid #34495e;
    color: white;
    padding: 0.4rem 0.6rem;
    border-radius: 4px;
    font-size: 0.8rem;
    cursor: pointer;
    min-width: 90px;
    transition: all 0.3s ease;
}

.currency-selector:hover {
    background: #2c4d6d;
    border-color: #5a6c7d;
}

.currency-selector:focus {
    outline: none;
    border-color: #f39c12;
    box-shadow: 0 0 0 2px rgba(243, 156, 18, 0.3);
}

.pricing-display {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    align-items: center;
}

.price-item {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 4px;
    padding: 0.3rem 0.6rem;
    font-size: 0.8rem;
    min-width: 90px;
    text-align: center;
}

.online-price {
    border-left: 3px solid #3498db;
}

.physical-price {
    border-left: 3px solid #27ae60;
}

.free-price {
    background: rgba(39, 174, 96, 0.2);
    border-color: #27ae60;
    color: #2ecc71;
    font-weight: 600;
}

.mode-label {
    display: block;
    font-size: 0.75rem;
    color: #ffffff;
    margin-bottom: 0.2rem;
    opacity: 0.9;
}

.price-value {
    font-weight: 600;
    font-size: 0.9rem;
    color: #ffffff !important;
}

/* Bright color for amount text */
.usd-price, .ksh-price {
    color: #ffeb3b !important; /* Bright yellow for high visibility */
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

/* Apply Hover Container */
.apply-hover-container {
    position: relative;
    display: inline-block;
    width: 100%;
    height: 60px; /* Fixed height for consistency */
}

.apply-trigger-btn {
    background: #f39c12;
    color: white;
    border: none;
    padding: 4px 8px;
    border-radius: 3px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.7rem;
    width: 100%;
    max-width: 80px; /* Limit width */
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.2rem;
    position: absolute;
    top: 5px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 5;
    height: 24px; /* Fixed height */
}

.apply-trigger-btn:hover {
    background: #e67e22;
    transform: translateX(-50%) translateY(-1px);
}

.apply-options-dropdown {
    position: absolute;
    top: 32px; /* Position below the apply button */
    left: 50%;
    transform: translateX(-50%);
    background: #2c3e50;
    border-radius: 3px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 1000;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateX(-50%) translateY(-5px);
    transition: all 0.2s ease;
    min-width: 100px;
    max-width: 120px;
}

.apply-options-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}

.apply-option {
    width: 100%;
    background: transparent;
    border: none;
    color: white;
    padding: 0.3rem 0.5rem;
    font-size: 0.65rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    justify-content: flex-start;
    text-align: left;
    white-space: nowrap;
}

.apply-option:hover {
    background: rgba(52, 152, 219, 0.2);
}

.online-option:hover {
    background: #3498db;
}

.physical-option:hover {
    background: #27ae60;
}

.apply-option:not(:last-child) {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.apply-option i {
    font-size: 0.55rem;
    width: 10px;
    flex-shrink: 0;
}

.course-schedule-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem; /* Increased base font size for better readability */
}

.course-schedule-table th,
.course-schedule-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #2c3e50;
    font-size: 0.9rem; /* Increased consistent font size */
}

.course-schedule-table th {
    background-color: #0b2239;
    font-weight: 600;
    font-size: 0.85rem; /* Increased header font size */
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.schedule-table-row:last-child td {
    border-bottom: none;
}

.dates-column,
.time-column,
.duration-column,
.location-column,
.currency-column,
.apply-column {
    vertical-align: middle;
    font-size: 0.9rem;
}

/* Schedule column content */
.schedule-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: white;
    line-height: 1.3;
}

.delivery-mode .mode-badge {
    font-size: 0.7rem;
    font-weight: 500;
}

/* Date info styling */
.date-info {
    font-size: 0.9rem;
    line-height: 1.4;
    color: white;
}

.date-separator {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
}

/* Time info styling */
.time-info {
    font-size: 0.9rem;
    line-height: 1.3;
    color: white;
    text-align: center;
}

/* Duration info styling */
.duration-info {
    font-size: 0.9rem;
    font-weight: 600;
    color: white;
    text-align: center;
}

/* Location styling */
.location-column {
    font-size: 0.9rem;
    color: white;
    line-height: 1.3;
}

.register-btn {
    background-color: #f39c12; /* Yellow-orange button */
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

.register-btn:hover {
    background-color: #e67e22; /* Darker orange on hover */
}

/* Responsive Design */
@media (max-width: 1200px) {
    .course-schedule-sidebar {
        position: relative;
        top: 0;
        max-height: none;
        margin-top: 2rem;
    }
    
    .schedule-cards-container {
        max-height: none;
    }
}

@media (max-width: 768px) {
    .brochure-page {
        padding: 1.5rem;
    }
    
    .program-title {
        font-size: 2rem;
    }
    
    .program-highlights {
        gap: 1rem;
    }
    
    .highlight-item {
        font-size: 0.9rem;
    }
    
    .contact-details {
        grid-template-columns: 1fr;
    }
    
    .apply-buttons-row {
        flex-direction: column;
    }
    
    .btn-apply {
        flex: none;
        width: 100%;
    }
    
    .program-info-wrapper {
        padding: 1rem;
    }
    
    .schedule-card-header,
    .schedule-card-body,
    .schedule-card-footer {
        padding: 0.8rem;
    }
    
    .schedule-info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .info-item {
        width: 100%;
    }
    
    .schedule-cards-container {
        gap: 0.75rem;
    }
}
</style>

<script>
function applyForSchedule(scheduleId, scheduleTitle) {
    // Show application modal instead of redirecting
    showApplicationModal(scheduleId, scheduleTitle);
}

function showApplicationModal(scheduleId, scheduleTitle) {
    // Set the selected schedule in the modal
    document.getElementById('modal-schedule-title').textContent = scheduleTitle;
    document.getElementById('selected-schedule-id').value = scheduleId;
    document.getElementById('selected-program-id').value = <?php echo $program_id; ?>;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('applicationModal'));
    modal.show();
}

function submitApplication() {
    const form = document.getElementById('applicationForm');
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = document.getElementById('submitApplicationBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    submitBtn.disabled = true;
    
    // Submit the form via AJAX
    fetch('application_submit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            document.getElementById('applicationModal').querySelector('.modal-body').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-success">Application Submitted!</h4>
                    <p class="text-muted">Thank you for your application. We will contact you soon with further details.</p>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            `;
        } else {
            throw new Error(data.message || 'Submission failed');
        }
    })
    .catch(error => {
        // Show error message
        alert('Error submitting application: ' + error.message);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Function to update pricing based on selected currency
function updatePricing(scheduleId, currency) {
    const pricingCell = document.getElementById('pricing-' + scheduleId);
    const priceItems = pricingCell.querySelectorAll('.price-item');
    
    priceItems.forEach(item => {
        const kshValues = item.querySelectorAll('.currency-ksh');
        const usdValues = item.querySelectorAll('.currency-usd');
        
        if (currency === 'KSH') {
            kshValues.forEach(val => val.style.display = 'inline');
            usdValues.forEach(val => val.style.display = 'none');
        } else {
            kshValues.forEach(val => val.style.display = 'none');
            usdValues.forEach(val => val.style.display = 'inline');
        }
    });
}

// Function to handle mode-specific applications
function applyForScheduleMode(scheduleId, scheduleTitle, mode) {
    // Pre-select the mode in the modal
    showApplicationModal(scheduleId, scheduleTitle);
    
    // Set the delivery mode after a short delay to ensure modal is loaded
    setTimeout(() => {
        const deliveryModeSelect = document.getElementById('delivery_mode');
        if (deliveryModeSelect) {
            deliveryModeSelect.value = mode;
        }
    }, 100);
}

// Function to switch global currency display
function switchGlobalCurrency(currency) {
    // Update currency toggle buttons
    document.querySelectorAll('.currency-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.currency === currency) {
            btn.classList.add('active');
        }
    });

    // Update pricing in all schedule cards (works for both table and card layouts)
    document.querySelectorAll('.schedule-card, .schedule-row').forEach(item => {
        const kshValues = item.querySelectorAll('.currency-ksh');
        const usdValues = item.querySelectorAll('.currency-usd');

        if (currency === 'KSH') {
            kshValues.forEach(val => val.style.display = 'inline');
            usdValues.forEach(val => val.style.display = 'none');
        } else {
            kshValues.forEach(val => val.style.display = 'none');
            usdValues.forEach(val => val.style.display = 'inline');
        }
    });
}

// Function to toggle apply dropdown options
function toggleApplyOptions(scheduleId) {
    // Hide all other open dropdowns
    document.querySelectorAll('.apply-options.show').forEach(dropdown => {
        if (dropdown.id !== `apply-options-${scheduleId}`) {
            dropdown.classList.remove('show');
        }
    });
    
    // Toggle the clicked dropdown
    const dropdown = document.getElementById(`apply-options-${scheduleId}`);
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.apply-dropdown')) {
        document.querySelectorAll('.apply-options.show').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
});

// Function to update schedule pricing based on currency selection
function updateSchedulePricing(scheduleId, currency) {
    const pricingDisplay = document.getElementById(`pricing-display-${scheduleId}`);
    if (!pricingDisplay) return;
    
    const usdPrices = pricingDisplay.querySelectorAll('.usd-price');
    const kshPrices = pricingDisplay.querySelectorAll('.ksh-price');
    
    if (currency === 'USD') {
        usdPrices.forEach(price => price.style.display = 'inline');
        kshPrices.forEach(price => price.style.display = 'none');
    } else {
        usdPrices.forEach(price => price.style.display = 'none');
        kshPrices.forEach(price => price.style.display = 'inline');
    }
}

// Function to show apply options on hover/click
function showApplyOptions(scheduleId) {
    // Hide all other dropdowns first
    document.querySelectorAll('.apply-options-dropdown').forEach(dropdown => {
        if (dropdown.id !== `apply-options-${scheduleId}`) {
            dropdown.classList.remove('show');
            dropdown.style.display = 'none';
        }
    });
    
    // Show the current dropdown
    const dropdown = document.getElementById(`apply-options-${scheduleId}`);
    if (dropdown) {
        dropdown.style.display = 'block';
        // Use setTimeout to allow display change to take effect first
        setTimeout(() => {
            dropdown.classList.add('show');
        }, 10);
    }
}

// Function to hide apply options
function hideApplyOptions(scheduleId) {
    const dropdown = document.getElementById(`apply-options-${scheduleId}`);
    if (dropdown) {
        dropdown.classList.remove('show');
        // Hide after animation completes
        setTimeout(() => {
            if (!dropdown.classList.contains('show')) {
                dropdown.style.display = 'none';
            }
        }, 300);
    }
}

// Close all dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.apply-hover-container')) {
        document.querySelectorAll('.apply-options-dropdown.show').forEach(dropdown => {
            dropdown.classList.remove('show');
            setTimeout(() => {
                if (!dropdown.classList.contains('show')) {
                    dropdown.style.display = 'none';
                }
            }, 300);
        });
    }
});

function downloadBrochure() {
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    
    // Get the brochure content
    const brochureContent = document.getElementById('brochure-content').innerHTML;
    
    // Create the print document
    const printDocument = `
        <!DOCTYPE html>
        <html>
        <head>
            <title><?php echo htmlspecialchars($program['title']); ?> - Course Brochure</title>
            <style>
                ${document.querySelector('style').innerHTML}
                
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    margin: 0;
                    padding: 0;
                    background: white;
                }
                
                .brochure-wrapper {
                    background: none !important;
                    padding: 0 !important;
                    box-shadow: none !important;
                }
                
                .brochure-container {
                    box-shadow: none !important;
                    max-width: none !important;
                }
                
                .btn-apply {
                    display: none !important;
                }
                
                @page {
                    size: A4;
                    margin: 0.5in;
                }
            </style>
        </head>
        <body>
            ${brochureContent}
        </body>
        </html>
    `;
    
    // Write the content and trigger print
    printWindow.document.write(printDocument);
    printWindow.document.close();
    
    // Wait for content to load then print
    printWindow.onload = function() {
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    };
}
</script>

<!-- Application Modal -->
<div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicationModalLabel">
                    <i class="fas fa-paper-plane me-2"></i>Apply for Program
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    Applying for: <strong id="modal-schedule-title"></strong>
                </div>
                
                <form id="applicationForm">
                    <input type="hidden" id="selected-program-id" name="program_id" value="">
                    <input type="hidden" id="selected-schedule-id" name="schedule_id" value="">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="delivery_mode" class="form-label">Preferred Mode *</label>
                            <select class="form-select" id="delivery_mode" name="delivery_mode" required>
                                <option value="">Choose Mode...</option>
                                <option value="online">Online</option>
                                <option value="physical">Physical Location</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="education_level" class="form-label">Education Level</label>
                            <select class="form-select" id="education_level" name="education_level">
                                <option value="">Select Level...</option>
                                <option value="high_school">High School</option>
                                <option value="diploma">Diploma</option>
                                <option value="degree">Bachelor's Degree</option>
                                <option value="masters">Master's Degree</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="current_occupation" class="form-label">Current Occupation</label>
                        <input type="text" class="form-control" id="current_occupation" name="current_occupation" placeholder="e.g., Student, Engineer, Manager">
                    </div>
                    
                    <div class="mb-3">
                        <label for="motivation" class="form-label">Why are you interested in this program?</label>
                        <textarea class="form-control" id="motivation" name="motivation" rows="3" placeholder="Tell us about your goals and expectations..."></textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms_agreed" name="terms_agreed" required>
                        <label class="form-check-label" for="terms_agreed">
                            I agree to the <a href="#" target="_blank">Terms and Conditions</a> and <a href="#" target="_blank">Privacy Policy</a> *
                        </label>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter">
                        <label class="form-check-label" for="newsletter">
                            Subscribe to our newsletter for updates and new programs
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="submitApplicationBtn" onclick="submitApplication()">
                    <i class="fas fa-paper-plane me-1"></i>Submit Application
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

