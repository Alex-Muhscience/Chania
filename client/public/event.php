<?php
$pageTitle = "Event Details - Skills for Africa";
$activePage = "events";

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "/events.php");
    exit;
}

$eventId = (int)$_GET['id'];
$event = getEventById($db, $eventId);

if (!$event) {
    header("Location: " . BASE_URL . "/events.php");
    exit;
}

$pageTitle = $event['title'] . " - Skills for Africa";
$pageDescription = $event['short_description'];
?>

<section class="py-5">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/events.php">Events</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($event['title']); ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <div class="col-lg-8">
                <div class="mb-4">
                    <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($event['title']); ?></h1>

                    <div class="d-flex flex-wrap gap-3 align-items-center mb-4">
                        <div class="d-flex align-items-center text-muted">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <span><?php echo date('l, F j, Y', strtotime($event['event_date'])); ?></span>
                        </div>
                        <div class="d-flex align-items-center text-muted">
                            <i class="fas fa-clock me-2"></i>
                            <span><?php echo date('g:i a', strtotime($event['event_date'])); ?></span>
                        </div>
                        <div class="d-flex align-items-center text-muted">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <span><?php echo htmlspecialchars($event['location']); ?></span>
                        </div>
                    </div>

                    <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($event['image_path']); ?>"
                         alt="<?php echo htmlspecialchars($event['title']); ?>"
                         class="img-fluid rounded-3 mb-4 w-100" style="max-height: 400px; object-fit: cover;">

                    <div class="mb-5">
                        <h3 class="h4 fw-bold mb-3">Event Details</h3>
                        <div class="event-content">
                            <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h3 class="h5 fw-bold mb-4">Event Information</h3>

                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-alt text-primary me-3"></i>
                                    <div>
                                        <h6 class="mb-0 small text-muted">Date</h6>
                                        <p class="mb-0"><?php echo date('F j, Y', strtotime($event['event_date'])); ?></p>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock text-primary me-3"></i>
                                    <div>
                                        <h6 class="mb-0 small text-muted">Time</h6>
                                        <p class="mb-0"><?php echo date('g:i a', strtotime($event['event_date'])); ?></p>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt text-primary me-3"></i>
                                    <div>
                                        <h6 class="mb-0 small text-muted">Location</h6>
                                        <p class="mb-0"><?php echo htmlspecialchars($event['location']); ?></p>
                                    </div>
                                </div>
                            </li>
                            <?php if (strtotime($event['event_date']) >= time()): ?>
                            <li class="list-group-item">
                                <div class="d-grid">
                                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">
                                        Register Now
                                    </a>
                                </div>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Registration Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Register for <?php echo htmlspecialchars($event['title']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="eventRegistrationForm">
                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo Utilities::generateCsrfToken(); ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>

                    <div class="mb-3">
                        <label for="organization" class="form-label">Organization (Optional)</label>
                        <input type="text" class="form-control" id="organization" name="organization">
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter" checked>
                        <label class="form-check-label" for="newsletter">Subscribe to our newsletter</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="eventRegistrationForm" class="btn btn-primary">Register</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('eventRegistrationForm');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        const formData = new FormData(form);

        fetch('<?php echo BASE_URL; ?>/client/src/Services/register_event.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                modal.hide();

                // Show toast notification
                const toast = new bootstrap.Toast(document.getElementById('registrationToast'));
                toast.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
});
</script>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="registrationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Your registration was successful! We've sent a confirmation to your email.
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>