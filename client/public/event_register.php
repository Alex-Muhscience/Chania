<?php
require_once '../includes/config.php';

// Fetch event details
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

if (!$event_id) {
    header('Location: events.php');
    exit;
}

// Fetch the event info
$stmt = $db->prepare("
    SELECT e.*, 
           (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = 'confirmed') as current_registrations
    FROM events e 
    WHERE e.id = ? AND e.event_date >= CURDATE() AND e.deleted_at IS NULL
");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header('Location: events.php');
    exit;
}

$page_title = 'Register for ' . $event['title'];
$page_description = 'Register for ' . $event['title'] . ' - ' . substr($event['description'], 0, 150);

include '../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto" data-aos="fade-up">
                <div class="text-center">
                    <h2 class="display-5">Register for Event</h2>
                    <p class="text-muted">Secure your spot for this exciting event</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h3 class="h5 mb-0">
                            <i class="fas fa-ticket-alt me-2"></i>Registration Form
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <div id="alertContainer"></div>
                        
                        <form id="eventRegistrationForm">
                            <input type="hidden" name="event_id" value="<?= $event_id ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           placeholder="+254 700 000 000" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="organization" class="form-label">Organization/Company</label>
                                    <input type="text" class="form-control" id="organization" name="organization" 
                                           placeholder="Optional">
                                </div>
                                
                                <div class="col-12">
                                    <label for="special_requirements" class="form-label">Special Requirements</label>
                                    <textarea class="form-control" id="special_requirements" name="special_requirements" 
                                              rows="3" placeholder="Any dietary restrictions, accessibility needs, etc. (Optional)"></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" target="_blank">terms and conditions</a> and 
                                            <a href="#" target="_blank">privacy policy</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                                        <i class="fas fa-ticket-alt me-2"></i>Register Now
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h3 class="h5 mb-0">
                            <i class="fas fa-info-circle me-2"></i>Event Details
                        </h3>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                        <p class="card-text text-muted small">
                            <?= htmlspecialchars(substr($event['description'], 0, 150)) ?>...
                        </p>
                        
                        <hr>
                        
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                <strong>Date:</strong> <?= date('F j, Y', strtotime($event['event_date'])) ?>
                            </li>
                            <?php if (!empty($event['event_time'])): ?>
                            <li class="mb-2">
                                <i class="fas fa-clock text-primary me-2"></i>
                                <strong>Time:</strong> <?= date('g:i A', strtotime($event['event_time'])) ?>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($event['location'])): ?>
                            <li class="mb-2">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <strong>Location:</strong> <?= htmlspecialchars($event['location']) ?>
                            </li>
                            <?php endif; ?>
                            <li class="mb-2">
                                <i class="fas fa-tag text-primary me-2"></i>
                                <strong>Type:</strong> <?= ucfirst($event['event_type'] ?? 'Event') ?>
                            </li>
                            <?php if (!empty($event['category'])): ?>
                            <li class="mb-2">
                                <i class="fas fa-folder text-primary me-2"></i>
                                <strong>Category:</strong> <?= ucfirst($event['category']) ?>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($event['max_participants'])): ?>
                            <li class="mb-2">
                                <i class="fas fa-users text-primary me-2"></i>
                                <strong>Capacity:</strong> 
                                <?= ($event['max_participants'] - $event['current_registrations']) ?> 
                                seats remaining (<?= $event['current_registrations'] ?>/<?= $event['max_participants'] ?>)
                            </li>
                            <?php endif; ?>
                        </ul>
                        
                        <?php if (isset($event['max_participants']) && $event['max_participants'] && $event['current_registrations'] >= $event['max_participants']): ?>
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This event is fully booked!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card shadow-sm mt-4">
                    <div class="card-body text-center">
                        <h6 class="card-title">Need Help?</h6>
                        <p class="card-text small text-muted">
                            Contact our support team if you have any questions
                        </p>
                        <a href="contact.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<script>
document.getElementById('eventRegistrationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const alertContainer = document.getElementById('alertContainer');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Registering...';
    
    // Clear previous alerts
    alertContainer.innerHTML = '';
    
    // Get form data
    const formData = new FormData(this);
    
    // Send AJAX request
    fetch('/chania/api/events/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alertContainer.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Success!</strong> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.getElementById('eventRegistrationForm').reset();
            
            // Redirect after 3 seconds
            setTimeout(() => {
                window.location.href = 'events.php';
            }, 3000);
        } else {
            let errorHtml = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error!</strong> ${data.message}
            `;
            
            if (data.errors && data.errors.length > 0) {
                errorHtml += '<ul class="mb-0 mt-2">';
                data.errors.forEach(error => {
                    errorHtml += `<li>${error}</li>`;
                });
                errorHtml += '</ul>';
            }
            
            errorHtml += `
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            alertContainer.innerHTML = errorHtml;
        }
        
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-ticket-alt me-2"></i>Register Now';
    })
    .catch(error => {
        console.error('Error:', error);
        alertContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error!</strong> Something went wrong. Please try again later.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-ticket-alt me-2"></i>Register Now';
    });
});
</script>
