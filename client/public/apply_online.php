<?php
require_once '../includes/config.php';

// Fetch program details
$program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;
$schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;

if (!$program_id || !$schedule_id) {
    header('Location: programs.php');
    exit;
}

// Fetch the program and schedule info
$stmt = $db->prepare("SELECT p.title, s.title as schedule_title, s.start_date, s.end_date, s.online_fee 
                      FROM programs p JOIN program_schedules s ON p.id = s.program_id 
                      WHERE p.id = ? AND s.id = ? AND p.is_active = 1 AND s.is_active = 1 AND s.deleted_at IS NULL");
$stmt->execute([$program_id, $schedule_id]);
$program_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$program_data) {
    header('Location: programs.php');
    exit;
}

include '../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto" data-aos="fade-up">
                <div class="program-detail-header">
                    <h2 class="display-4">Apply for <?= htmlspecialchars($program_data['schedule_title']) ?></h2>
                    <p>Part of the <?= htmlspecialchars($program_data['title']) ?> program.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="h5 mb-0">Application Form - Online</h3>
                    </div>
                    <div class="card-body">
                        <form id="applicationForm" method="POST" action="application_submit.php">
                            <input type="hidden" name="program_id" value="<?= $program_id ?>">
                            <input type="hidden" name="schedule_id" value="<?= $schedule_id ?>">

                            <div class="mb-3">
                                <label for="fullName" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullName" name="fullName" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>

                            <div class="mb-3">
                                <label for="reason" class="form-label">Why do you want to join this program?</label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Submit Application</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="h5 mb-0">Schedule Details</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><strong>Program:</strong> <?= htmlspecialchars($program_data['title']) ?></li>
                            <li><strong>Schedule:</strong> <?= htmlspecialchars($program_data['schedule_title']) ?></li>
                            <li><strong>Start Date:</strong> <?= htmlspecialchars(date('M d, Y', strtotime($program_data['start_date']))) ?></li>
                            <li><strong>End Date:</strong> <?= htmlspecialchars(date('M d, Y', strtotime($program_data['end_date']))) ?></li>
                            <li><strong>Fee:</strong> KES <?= number_format($program_data['online_fee'], 2) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>
