<?php
require_once '../includes/config.php';

// Fetch program details
$program_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$program_id) {
    header('Location: programs.php');
    exit;
}

try {
    // Get program basic info
    $program_query = "SELECT * FROM programs WHERE id = :id AND is_active = 1 AND deleted_at IS NULL";
    $stmt = $db->prepare($program_query);
    $stmt->execute(['id' => $program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$program) {
        header('Location: programs.php');
        exit;
    }

    // Get program detailed info
    $info_query = "SELECT * FROM program_info WHERE program_id = :program_id";
    $stmt = $db->prepare($info_query);
    $stmt->execute(['program_id' => $program_id]);
    $program_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get program schedules
    $schedules_query = "SELECT * FROM program_schedules 
                       WHERE program_id = :program_id 
                       AND is_active = 1 
                       AND is_open_for_registration = 1 
                       AND deleted_at IS NULL 
                       AND start_date > NOW() 
                       ORDER BY start_date ASC";
    $stmt = $db->prepare($schedules_query);
    $stmt->execute(['program_id' => $program_id]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get program curriculum
    $curriculum_query = "SELECT * FROM program_curriculum 
                        WHERE program_id = :program_id 
                        AND is_active = 1 
                        ORDER BY module_order ASC";
    $stmt = $db->prepare($curriculum_query);
    $stmt->execute(['program_id' => $program_id]);
    $curriculum = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching program details: " . $e->getMessage());
    header('Location: programs.php');
    exit;
}

$page_title = htmlspecialchars($program['title']);
$page_description = 'Learn more about ' . htmlspecialchars($program['title']) . ' - ' . substr(htmlspecialchars($program['description']), 0, 150) . '...';
$page_keywords = 'short course, ' . strtolower($program['title']) . ', online learning, certification';

include '../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <!-- Program Header -->
        <div class="row mb-5">
            <div class="col-lg-12" data-aos="fade-up">
                <div class="program-detail-header text-center">
                    <h1 class="program-title mb-3"><?= htmlspecialchars($program['title']) ?></h1>
                    <p class="program-description lead"><?= htmlspecialchars($program['description']) ?></p>
                    <div class="program-meta">
                        <span class="badge bg-primary me-2">Duration: <?= htmlspecialchars($program['duration']) ?></span>
                        <?php if ($program['fee'] > 0): ?>
                            <span class="badge bg-success">Starting from: $<?= number_format($program['fee'], 2) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Program Details -->
            <div class="col-lg-8">
                <!-- Program Introduction -->
                <?php if ($program_info && $program_info['introduction']): ?>
                    <div class="mb-5" data-aos="fade-up" data-aos-delay="100">
                        <h3>About This Program</h3>
                        <div class="program-intro">
                            <?= nl2br(htmlspecialchars($program_info['introduction'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Learning Objectives -->
                <?php if ($program_info && $program_info['objectives']): ?>
                    <div class="mb-5" data-aos="fade-up" data-aos-delay="200">
                        <h3>Learning Objectives</h3>
                        <div class="objectives">
                            <?= nl2br(htmlspecialchars($program_info['objectives'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Course Content -->
                <?php if (!empty($curriculum)): ?>
                    <div class="mb-5" data-aos="fade-up" data-aos-delay="300">
                        <h3>Course Modules</h3>
                        <div class="curriculum-modules">
                            <?php foreach ($curriculum as $module): ?>
                                <div class="module-item mb-4 p-4 border rounded">
                                    <h5 class="module-title"><?= htmlspecialchars($module['module_title']) ?></h5>
                                    <?php if ($module['duration_hours']): ?>
                                        <span class="badge bg-secondary mb-2"><?= $module['duration_hours'] ?> hours</span>
                                    <?php endif; ?>
                                    <?php if ($module['module_description']): ?>
                                        <p class="module-description"><?= nl2br(htmlspecialchars($module['module_description'])) ?></p>
                                    <?php endif; ?>
                                    <?php if ($module['learning_objectives']): ?>
                                        <div class="module-objectives">
                                            <strong>Learning Objectives:</strong>
                                            <div class="mt-2"><?= nl2br(htmlspecialchars($module['learning_objectives'])) ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php elseif ($program_info && $program_info['course_content']): ?>
                    <div class="mb-5" data-aos="fade-up" data-aos-delay="300">
                        <h3>Course Content</h3>
                        <div class="course-content">
                            <?= nl2br(htmlspecialchars($program_info['course_content'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Target Audience & Prerequisites -->
                <div class="row mb-5">
                    <?php if ($program_info && $program_info['target_audience']): ?>
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="400">
                            <h4>Who Should Attend</h4>
                            <div class="target-audience">
                                <?= nl2br(htmlspecialchars($program_info['target_audience'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($program_info && $program_info['prerequisites']): ?>
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="500">
                            <h4>Prerequisites</h4>
                            <div class="prerequisites">
                                <?= nl2br(htmlspecialchars($program_info['prerequisites'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- General Notes -->
                <?php if ($program_info && $program_info['general_notes']): ?>
                    <div class="mb-5" data-aos="fade-up" data-aos-delay="600">
                        <h4>Important Notes</h4>
                        <div class="alert alert-info">
                            <?= nl2br(htmlspecialchars($program_info['general_notes'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Schedules & Application -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 20px;">
                    <div class="card">
                        <div class="card-header">
                            <h4>Available Schedules</h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($schedules)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No upcoming schedules available.</p>
                                    <p class="text-muted">Please check back later or contact us for more information.</p>
                                </div>
                            <?php else: ?>
                                <div class="schedules-list">
                                    <?php foreach ($schedules as $index => $schedule): ?>
                                        <div class="schedule-item mb-4 p-3 border rounded" data-schedule-id="<?= $schedule['id'] ?>">
                                            <h6 class="schedule-title"><?= htmlspecialchars($schedule['title']) ?></h6>
                                            
                                            <!-- Delivery Mode Badge - Show both options available -->
                                            <span class="badge bg-primary me-1">
                                                <i class="fas fa-laptop"></i> Online Available
                                            </span>
                                            <span class="badge bg-success">
                                                <i class="fas fa-building"></i> Physical Available
                                            </span>
                                            
                                            <!-- Schedule Details -->
                                            <div class="schedule-details mt-2">
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-calendar"></i> 
                                                    <?= date('M j, Y', strtotime($schedule['start_date'])) ?> - 
                                                    <?= date('M j, Y', strtotime($schedule['end_date'])) ?>
                                                </small>
                                                
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-clock"></i> 
                                                    <?= date('g:i A', strtotime($schedule['start_time'])) ?> - 
                                                    <?= date('g:i A', strtotime($schedule['end_time'])) ?>
                                                </small>
                                                
                                                <?php if ($schedule['location']): ?>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-map-marker-alt"></i> 
                                                        <?= htmlspecialchars($schedule['location']) ?>
                                                    </small>
                                                <?php endif; ?>
                                                
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-globe"></i> 
                                                    <?= htmlspecialchars($schedule['timezone']) ?>
                                                </small>
                                            </div>
                                            
                                            <!-- Fees -->
                                            <div class="fees mt-3">
                                                <?php if ($schedule['online_fee'] > 0): ?>
                                                    <div class="fee-option">
                                                        <strong>Online:</strong> <?= $schedule['currency'] ?> <?= number_format($schedule['online_fee'], 2) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($schedule['physical_fee'] > 0): ?>
                                                    <div class="fee-option">
                                                        <strong>Physical:</strong> <?= $schedule['currency'] ?> <?= number_format($schedule['physical_fee'], 2) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($schedule['online_fee'] == 0 && $schedule['physical_fee'] == 0): ?>
                                                    <div class="fee-option">
                                                        <strong class="text-success">Free</strong>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Apply Button with Hover -->
                                            <div class="apply-section mt-3">
                                                <div class="btn-group w-100" role="group">
                                                    <button type="button" class="btn btn-primary apply-btn" 
                                                            onclick="showDeliveryOptions(<?= $schedule['id'] ?>, '<?= $schedule['delivery_mode'] ?>')">
                                                        Apply Now
                                                    </button>
                                                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" 
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                        <span class="visually-hidden">Toggle Dropdown</span>
                                                    </button>
                                                    <ul class="dropdown-menu w-100">
                                                        <!-- Both modes available for all schedules -->
                                                        <?php if ($schedule['online_fee'] > 0): ?>
                                                            <li><a class="dropdown-item" href="apply.php?schedule_id=<?= $schedule['id'] ?>&mode=online">
                                                                <i class="fas fa-laptop"></i> Apply for Online (<?= $schedule['currency'] ?> <?= number_format($schedule['online_fee'], 2) ?>)
                                                            </a></li>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($schedule['physical_fee'] > 0): ?>
                                                            <li><a class="dropdown-item" href="apply.php?schedule_id=<?= $schedule['id'] ?>&mode=physical">
                                                                <i class="fas fa-building"></i> Apply for Physical (<?= $schedule['currency'] ?> <?= number_format($schedule['physical_fee'], 2) ?>)
                                                            </a></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                            
                                            <!-- Registration Deadline -->
                                            <?php if ($schedule['registration_deadline']): ?>
                                                <div class="deadline mt-2">
                                                    <small class="text-warning">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Registration closes: <?= date('M j, Y', strtotime($schedule['registration_deadline'])) ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Capacity -->
                                            <?php if ($schedule['max_participants']): ?>
                                                <div class="capacity mt-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-users"></i>
                                                        Max participants: <?= $schedule['max_participants'] ?>
                                                        (<?= $schedule['current_participants'] ?> registered)
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Certification Details -->
                    <?php if ($program_info && $program_info['certification_details']): ?>
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5>Certification</h5>
                            </div>
                            <div class="card-body">
                                <?= nl2br(htmlspecialchars($program_info['certification_details'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function showDeliveryOptions(scheduleId, deliveryMode) {
    // Show dropdown with both options by default
    // The dropdown will automatically show when clicked due to Bootstrap behavior
    // No need to redirect immediately - let users choose their preferred mode
}

// Auto-scroll to schedules if no schedules are visible
document.addEventListener('DOMContentLoaded', function() {
    const schedules = document.querySelectorAll('.schedule-item');
    if (schedules.length === 0) {
        console.log('No schedules available');
    }
});
</script>

<style>
.schedule-item {
    transition: all 0.3s ease;
}

.schedule-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.apply-btn:hover + .dropdown-toggle {
    background-color: #0056b3;
    border-color: #004085;
}

.fee-option {
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.deadline {
    border-top: 1px solid #dee2e6;
    padding-top: 0.5rem;
}

.capacity {
    font-size: 0.8rem;
}

.sticky-top {
    z-index: 1020;
}
</style>

<?php include '../includes/footer.php'; ?>
