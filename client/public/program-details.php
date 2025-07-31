<?php
require_once '../includes/config.php';

// Fetch program details
$program_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$program_query = "SELECT * FROM programs WHERE id = :id AND is_active = 1 AND deleted_at IS NULL";
$stmt = $db->prepare($program_query);
$stmt->execute(['id' => $program_id]);
$program = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$program) {
    header('Location: programs.php');
    exit;
}

$page_title = htmlspecialchars($program['title']);
$page_description = 'Learn more about ' . htmlspecialchars($program['title']) . ' - ' . substr(htmlspecialchars($program['description']), 0, 150) . '...';
$page_keywords = 'short course, ' . strtolower($program['category']) . ', ' . strtolower($program['title']) . ', online learning, certification';

// Generate year-round schedule for short courses
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
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto" data-aos="fade-up">
                <div class="program-detail-header">
                    <h1 class="program-title"> <?php echo htmlspecialchars($program['title']); ?> </h1>
                    <p class="program-description"> <?php echo htmlspecialchars($program['description']); ?> </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-4" data-aos="fade-up" data-aos-delay="100">
                <h5>Course Schedule</h5>
                <ul>
                    <?php foreach ($schedule as $location => $time): ?>
                        <li><strong><?php echo $location; ?>:</strong> <?php echo $time; ?></li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?php echo BASE_URL; ?>schedule.pdf" class="btn btn-outline-primary">Download Schedule PDF</a>
            </div>
            <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <h5>Fees</h5>
                <p><?php echo $fees; ?></p>
            </div>
            <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <h5>Apply Now</h5>
                <form action="apply.php" method="POST">
                    <input type="hidden" name="program_id" value="<?php echo $program_id; ?>">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="class_type" id="online" value="online" checked>
                        <label class="form-check-label" for="online">
                            Online Class
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="class_type" id="physical" value="physical">
                        <label class="form-check-label" for="physical">
                            Physical Class
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Apply</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

