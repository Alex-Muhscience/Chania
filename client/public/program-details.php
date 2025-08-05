<?php
require_once '../includes/config.php';

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

<section class="py-5">
    <div class="container">
        <!-- Program Header -->
        <div class="row mb-5">
            <div class="col-lg-10 mx-auto" data-aos="fade-up">
                <div class="program-detail-header text-center">
                    <h1 class="program-title mb-3"><?php echo htmlspecialchars($program['title']); ?></h1>
                    <p class="program-description lead mb-4"><?php echo htmlspecialchars($program['description']); ?></p>
                    <div class="program-meta d-flex justify-content-center flex-wrap gap-4 mb-4">
                        <span class="badge bg-primary fs-6">Category: <?php echo htmlspecialchars($program['category']); ?></span>
                        <span class="badge bg-success fs-6">Duration: <?php echo htmlspecialchars($program['duration']); ?></span>
                        <?php if ($program['fee']): ?>
                            <span class="badge bg-info fs-6">Fee: $<?php echo number_format($program['fee'], 2); ?></span>
                        <?php else: ?>
                            <span class="badge bg-warning fs-6">Free</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Program Brochure Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Brochure Header -->
                <div class="brochure-header text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-3">Course Brochure</h2>
                    <p class="lead text-muted">Download comprehensive course information</p>
                    <button class="btn btn-primary btn-lg" onclick="downloadBrochure()">
                        <i class="fas fa-download me-2"></i>Download PDF Brochure
                    </button>
                </div>

                <!-- Brochure Content -->
                <div class="brochure-wrapper" id="brochure-content">
                    <div class="brochure-container">
                        <!-- Brochure Cover -->
                        <div class="brochure-page brochure-cover">
                            <div class="cover-header">
                                <div class="logo-section">
                                    <img src="<?php echo ASSETS_URL; ?>images/logo.png" alt="Chania College" class="brochure-logo">
                                    <h1 class="college-name">Chania Technical Training Institute</h1>
                                </div>
                            </div>
                            
                            <div class="cover-content">
                                <div class="program-title-section">
                                    <h2 class="program-title"><?php echo htmlspecialchars($program['title']); ?></h2>
                                    <div class="program-category"><?php echo ucfirst($program['category'] ?? 'General'); ?> Program</div>
                                </div>
                                
                                <div class="program-highlights">
                                    <div class="highlight-item">
                                        <i class="fas fa-clock"></i>
                                        <span>Duration: <?php echo htmlspecialchars($program['duration'] ?? 'Flexible'); ?></span>
                                    </div>
                                    <div class="highlight-item">
                                        <i class="fas fa-certificate"></i>
                                        <span>Certification Available</span>
                                    </div>
                                    <div class="highlight-item">
                                        <i class="fas fa-users"></i>
                                        <span>Expert Instructors</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="cover-footer">
                                <div class="contact-info">
                                    <p>📧 info@chaniacollege.com | 📞 +254-700-000-000</p>
                                    <p>🌐 www.chaniacollege.com</p>
                                </div>
                            </div>
                        </div>

                        <!-- Brochure Inner Pages -->
                        <div class="brochure-page brochure-content-page">
                            <div class="page-header">
                                <h3 class="page-title">Program Overview</h3>
                            </div>
                            
                            <div class="content-grid">
                                <div class="content-section">
                                    <h4><i class="fas fa-info-circle"></i> Course Description</h4>
                                    <p><?php echo nl2br(htmlspecialchars($program['description'])); ?></p>
                                </div>
                                
                                <?php if (!empty($program['objectives'])): ?>
                                <div class="content-section">
                                    <h4><i class="fas fa-bullseye"></i> Learning Objectives</h4>
                                    <div class="objectives-list">
                                        <?php 
                                        $objectives = explode("\n", $program['objectives']);
                                        foreach($objectives as $objective):
                                            if(trim($objective)):
                                        ?>
                                        <div class="objective-item">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <span><?php echo htmlspecialchars(trim($objective)); ?></span>
                                        </div>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($program['target_audience'])): ?>
                                <div class="content-section">
                                    <h4><i class="fas fa-users"></i> Target Audience</h4>
                                    <p><?php echo nl2br(htmlspecialchars($program['target_audience'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($program['prerequisites'])): ?>
                                <div class="content-section">
                                    <h4><i class="fas fa-list-check"></i> Prerequisites</h4>
                                    <p><?php echo nl2br(htmlspecialchars($program['prerequisites'])); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Schedule Information Page -->
                        <div class="brochure-page brochure-schedule-page">
                            <div class="page-header">
                                <h3 class="page-title">Available Schedules & Pricing</h3>
                            </div>
                            
                            <?php if (!empty($schedules)): ?>
                                <div class="schedule-table-container">
                                    <table class="schedule-table">
                                        <thead>
                                            <tr>
                                                <th class="schedule-col">Schedule</th>
                                                <th class="dates-col">Start - End Date</th>
                                                <th class="time-col">Time</th>
                                                <th class="location-col">Location</th>
                                                <th class="mode-col">Mode</th>
                                                <th class="pricing-col">Pricing</th>
                                                <th class="action-col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($schedules as $index => $schedule): ?>
                                            <tr class="schedule-row">
                                                <td class="schedule-name">
                                                    <div class="schedule-title-cell">
                                                        <strong><?php echo htmlspecialchars($schedule['title']); ?></strong>
                                                        <?php if (!empty($schedule['timezone'])): ?>
                                                            <div class="timezone-info">
                                                                <i class="fas fa-globe"></i> <?php echo htmlspecialchars($schedule['timezone']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="dates-cell">
                                                    <div class="date-range">
                                                        <div class="start-date">
                                                            <i class="fas fa-calendar-alt"></i>
                                                            <?php echo date('M j, Y', strtotime($schedule['start_date'])); ?>
                                                        </div>
                                                        <div class="date-separator">to</div>
                                                        <div class="end-date">
                                                            <i class="fas fa-calendar-check"></i>
                                                            <?php echo date('M j, Y', strtotime($schedule['end_date'])); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="time-cell">
                                                    <div class="time-range">
                                                        <i class="fas fa-clock"></i>
                                                        <span><?php echo date('g:i A', strtotime($schedule['start_date'])); ?></span>
                                                        <div class="time-separator">-</div>
                                                        <span><?php echo date('g:i A', strtotime($schedule['end_date'])); ?></span>
                                                    </div>
                                                </td>
                                                <td class="location-cell">
                                                    <div class="location-info">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        <span><?php echo htmlspecialchars($schedule['location']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="mode-cell">
                                                    <span class="mode-badge mode-<?php echo $schedule['delivery_mode']; ?>">
                                                        <?php 
                                                        $modeIcon = $schedule['delivery_mode'] === 'online' ? 'laptop' : 'building';
                                                        ?>
                                                        <i class="fas fa-<?php echo $modeIcon; ?>"></i>
                                                        <?php echo ucfirst($schedule['delivery_mode']); ?>
                                                    </span>
                                                </td>
                                                <td class="pricing-cell">
                                                    <div class="pricing-info">
                                                        <?php if (!empty($schedule['online_fee']) && $schedule['online_fee'] > 0): ?>
                                                            <div class="price-item online-price">
                                                                <span class="price-label">Online:</span>
                                                                <span class="price-value"><?php echo $schedule['currency']; ?> <?php echo number_format($schedule['online_fee'], 2); ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($schedule['physical_fee']) && $schedule['physical_fee'] > 0): ?>
                                                            <div class="price-item physical-price">
                                                                <span class="price-label">Physical:</span>
                                                                <span class="price-value"><?php echo $schedule['currency']; ?> <?php echo number_format($schedule['physical_fee'], 2); ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (empty($schedule['online_fee']) && empty($schedule['physical_fee'])): ?>
                                                            <div class="price-item free-price">
                                                                <i class="fas fa-gift"></i>
                                                                <span class="price-value">Free</span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="action-cell">
                                                    <button class="btn-apply-table" onclick="applyForSchedule(<?php echo $schedule['id']; ?>, '<?php echo addslashes($schedule['title']); ?>')">
                                                        <i class="fas fa-paper-plane"></i>
                                                        Apply
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Table Legend -->
                                <div class="table-legend">
                                    <h6>Legend:</h6>
                                    <div class="legend-items">
                                        <div class="legend-item">
                                            <span class="mode-badge mode-online"><i class="fas fa-laptop"></i> Online</span>
                                            <span class="legend-desc">Virtual classroom sessions</span>
                                        </div>
                                        <div class="legend-item">
                                            <span class="mode-badge mode-physical"><i class="fas fa-building"></i> Physical</span>
                                            <span class="legend-desc">In-person classes at campus</span>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="no-schedules-table">
                                    <div class="no-schedules-content">
                                        <i class="fas fa-calendar-times"></i>
                                        <h5>No Schedules Available</h5>
                                        <p>Please contact us for upcoming schedule information.</p>
                                        <div class="contact-prompt">
                                            <i class="fas fa-envelope"></i> info@chaniacollege.com
                                            <br>
                                            <i class="fas fa-phone"></i> +254-700-000-000
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Additional Information Page -->
                        <div class="brochure-page brochure-info-page">
                            <div class="page-header">
                                <h3 class="page-title">Additional Information</h3>
                            </div>
                            
                            <div class="info-grid">
                                <?php if (!empty($program['benefits'])): ?>
                                <div class="info-section">
                                    <h4><i class="fas fa-star"></i> Course Benefits</h4>
                                    <p><?php echo nl2br(htmlspecialchars($program['benefits'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($program['career_outcomes'])): ?>
                                <div class="info-section">
                                    <h4><i class="fas fa-briefcase"></i> Career Outcomes</h4>
                                    <p><?php echo nl2br(htmlspecialchars($program['career_outcomes'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($program['materials_included'])): ?>
                                <div class="info-section">
                                    <h4><i class="fas fa-box"></i> Materials Included</h4>
                                    <p><?php echo nl2br(htmlspecialchars($program['materials_included'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($program['certification_details'])): ?>
                                <div class="info-section">
                                    <h4><i class="fas fa-certificate"></i> Certification Details</h4>
                                    <p><?php echo nl2br(htmlspecialchars($program['certification_details'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($program['general_notes'])): ?>
                                <div class="info-section">
                                    <h4><i class="fas fa-info-circle"></i> Important Notes</h4>
                                    <p><?php echo nl2br(htmlspecialchars($program['general_notes'])); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Contact Information -->
                            <div class="contact-section">
                                <h4><i class="fas fa-phone"></i> Contact Information</h4>
                                <div class="contact-details">
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <span>Email: info@chaniacollege.com</span>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-phone"></i>
                                        <span>Phone: +254-700-000-000</span>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-globe"></i>
                                        <span>Website: www.chaniacollege.com</span>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Address: Chania Technical Training Institute, Kenya</span>
                                    </div>
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
/* Brochure Styles */
.brochure-wrapper {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.brochure-container {
    max-width: 100%;
    margin: 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.brochure-page {
    padding: 3rem;
    min-height: 600px;
    page-break-after: always;
}

/* Cover Page Styles */
.brochure-cover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-align: center;
    position: relative;
}

.brochure-cover::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.03"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
    pointer-events: none;
}

.cover-header {
    position: relative;
    z-index: 2;
}

.logo-section {
    margin-bottom: 2rem;
}

.brochure-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
    margin-bottom: 1rem;
    background: white;
    padding: 10px;
    border-radius: 50%;
}

.college-name {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    opacity: 0.9;
}

.cover-content {
    position: relative;
    z-index: 2;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.program-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    line-height: 1.2;
}

.program-category {
    font-size: 1.2rem;
    background: rgba(255,255,255,0.2);
    padding: 0.5rem 1.5rem;
    border-radius: 25px;
    display: inline-block;
    margin-bottom: 2rem;
}

.program-highlights {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.highlight-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
}

.highlight-item i {
    font-size: 1.2rem;
    opacity: 0.8;
}

.cover-footer {
    position: relative;
    z-index: 2;
    opacity: 0.8;
}

.contact-info p {
    margin: 0.5rem 0;
    font-size: 0.9rem;
}

/* Content Pages Styles */
.brochure-content-page,
.brochure-schedule-page,
.brochure-info-page {
    background: white;
}

.page-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid #667eea;
}

.page-title {
    color: #667eea;
    font-size: 2rem;
    font-weight: 600;
    margin: 0;
}

.content-grid,
.info-grid {
    display: grid;
    gap: 2rem;
}

.content-section,
.info-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.content-section h4,
.info-section h4 {
    color: #667eea;
    font-size: 1.3rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
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

/* Column Specific Styles - Optimized for full width */
.schedule-col { width: 20%; }
.dates-col { width: 18%; }
.time-col { width: 12%; }
.location-col { width: 16%; }
.mode-col { width: 10%; }
.pricing-col { width: 14%; }
.action-col { width: 10%; }

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

/* Action Button */
.btn-apply-table {
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
}

.btn-apply-table:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    color: white;
}

.btn-apply-table i {
    font-size: 0.75rem;
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

/* Responsive Table */
@media (max-width: 1400px) {
    .schedule-table {
        font-size: 0.85rem;
    }
    
    .schedule-table th,
    .schedule-table td {
        padding: 0.9rem 0.6rem;
    }
}

@media (max-width: 1200px) {
    .schedule-table {
        font-size: 0.8rem;
    }
    
    .schedule-table th,
    .schedule-table td {
        padding: 0.8rem 0.5rem;
    }
    
    .btn-apply-table {
        padding: 0.5rem 0.8rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 992px) {
    .schedule-table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .schedule-table {
        min-width: 900px; /* Increased for better readability */
    }
    
    .legend-items {
        flex-direction: column;
        gap: 1rem;
    }
}

@media (max-width: 768px) {
    .schedule-table {
        min-width: 800px; /* Increased for mobile */
        font-size: 0.75rem;
    }
    
    .schedule-table th,
    .schedule-table td {
        padding: 0.6rem 0.4rem;
    }
    
    .table-legend {
        padding: 1rem;
    }
    
    .legend-items {
        gap: 0.8rem;
    }
    
    .legend-item {
        gap: 0.5rem;
    }
    
    .brochure-page {
        padding: 1.5rem 1rem; /* Reduced horizontal padding on mobile */
    }
}

/* Contact Section */
.contact-section {
    background: #667eea;
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

/* Responsive Design */
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
    
    .schedules-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-details {
        grid-template-columns: 1fr;
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

