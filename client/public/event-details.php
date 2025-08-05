<?php
require_once '../includes/config.php';

// Event ID from GET; handle it safely
$event_id = $_GET['id'] ?? null;

if (!$event_id) {
    header('Location: events.php');
    exit;
}

try {
    // Fetch event details with registration count
    $stmt_event = $db->prepare("
        SELECT e.*, 
               DATEDIFF(e.event_date, NOW()) as days_until,
               CASE 
                   WHEN e.event_date >= NOW() THEN 0 
                   ELSE 1 
               END as is_past,
               (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = 'confirmed') as current_registrations
        FROM events e 
        WHERE e.id = ? AND e.deleted_at IS NULL
    ");
    $stmt_event->execute([$event_id]);
    $event = $stmt_event->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        header('Location: events.php');
        exit;
    }
    
    // Set page meta data
    $page_title = htmlspecialchars($event['title']);
    $page_description = htmlspecialchars(substr($event['description'], 0, 150)) . '...';
    $page_keywords = 'event, ' . strtolower($event['event_type'] ?? 'workshop') . ', ' . strtolower($event['category'] ?? 'training') . ', skills development';
    
} catch (Exception $e) {
    header('Location: events.php');
    exit;
}

include '../includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="header-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>
    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-4" data-aos="fade-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>events.php">Events</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Event Details</li>
                    </ol>
                </nav>
                <h1 class="text-white mb-4" data-aos="fade-up"><?php echo htmlspecialchars($event['title']); ?></h1>
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <span class="badge event-type-badge">
                        <i class="fas fa-tag me-1"></i><?php echo ucfirst($event['event_type'] ?? 'Event'); ?>
                    </span>
                    <?php if (!empty($event['category'])): ?>
                        <span class="badge category-badge">
                            <i class="fas fa-folder me-1"></i><?php echo ucfirst($event['category']); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($event['days_until'] !== null): ?>
                        <?php if ($event['days_until'] <= 0): ?>
                            <span class="badge bg-secondary">
                                <i class="fas fa-history me-1"></i>Past Event
                            </span>
                        <?php elseif ($event['days_until'] <= 7): ?>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-clock me-1"></i><?php echo $event['days_until']; ?> day<?php echo $event['days_until'] > 1 ? 's' : ''; ?> left
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Event Details -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Event Image -->
                <div class="mb-5" data-aos="fade-up">
                    <div class="position-relative overflow-hidden rounded-3 shadow-lg">
                        <img src="<?php echo !empty($event['image_url']) ? ASSETS_URL . 'images/events/' . $event['image_url'] : 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop'; ?>" 
                             alt="<?php echo htmlspecialchars($event['title']); ?>" 
                             class="img-fluid w-100 event-hero-image">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-primary px-3 py-2 rounded-pill">
                                <?php echo ucfirst($event['event_type'] ?? 'Event'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Event Description -->
                <div class="mb-5" data-aos="fade-up" data-aos-delay="100">
                    <h2 class="h3 mb-4">
                        <i class="fas fa-info-circle text-primary me-2"></i>Event Overview
                    </h2>
                    <div class="event-description">
                        <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                    </div>
                </div>

                <!-- Event Schedule -->
                <?php if (!empty($event['event_date'])): ?>
                <div class="mb-5" data-aos="fade-up" data-aos-delay="200">
                    <h2 class="h3 mb-4">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>Schedule
                    </h2>
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-play-circle text-success me-3 fs-5"></i>
                                        <div>
                                            <strong>Start Date & Time</strong><br>
                                            <span class="text-muted">
                                                <?php echo date('F j, Y', strtotime($event['event_date'])); ?>
                                                <?php if (!empty($event['event_time'])): ?>
                                                    at <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($event['end_date'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-stop-circle text-danger me-3 fs-5"></i>
                                        <div>
                                            <strong>End Date & Time</strong><br>
                                            <span class="text-muted">
                                                <?php echo date('F j, Y', strtotime($event['end_date'])); ?>
                                                <?php if (!empty($event['end_time'])): ?>
                                                    at <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($event['location'])): ?>
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-map-marker-alt text-primary me-3 fs-5"></i>
                                        <div>
                                            <strong>Location</strong><br>
                                            <span class="text-muted"><?php echo htmlspecialchars($event['location']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Additional Details -->
                <?php if (!empty($event['requirements']) || !empty($event['what_to_bring'])): ?>
                <div class="mb-5" data-aos="fade-up" data-aos-delay="300">
                    <h2 class="h3 mb-4">
                        <i class="fas fa-list-check text-primary me-2"></i>Additional Information
                    </h2>
                    <div class="row g-4">
                        <?php if (!empty($event['requirements'])): ?>
                        <div class="col-md-6">
                            <div class="card border-0 h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">
                                        <i class="fas fa-clipboard-check me-2"></i>Requirements
                                    </h5>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($event['requirements'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($event['what_to_bring'])): ?>
                        <div class="col-md-6">
                            <div class="card border-0 h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">
                                        <i class="fas fa-briefcase me-2"></i>What to Bring
                                    </h5>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($event['what_to_bring'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Event Info Card -->
                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Event Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?>
                            </li>
                            <?php if (!empty($event['event_time'])): ?>
                            <li class="mb-3">
                                <i class="fas fa-clock text-primary me-2"></i>
                                <strong>Time:</strong> <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($event['location'])): ?>
                            <li class="mb-3">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                            </li>
                            <?php endif; ?>
                            <li class="mb-3">
                                <i class="fas fa-tag text-primary me-2"></i>
                                <strong>Type:</strong> <?php echo ucfirst($event['event_type'] ?? 'Event'); ?>
                            </li>
                            <?php if (!empty($event['category'])): ?>
                            <li class="mb-3">
                                <i class="fas fa-folder text-primary me-2"></i>
                                <strong>Category:</strong> <?php echo ucfirst($event['category']); ?>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($event['max_participants'])): ?>
                            <li class="mb-3">
                                <i class="fas fa-users text-primary me-2"></i>
                                <strong>Capacity:</strong> 
                                <?php 
                                $remaining = $event['max_participants'] - $event['current_registrations'];
                                echo $remaining; ?> seats remaining 
                                (<?php echo $event['current_registrations']; ?>/<?php echo $event['max_participants']; ?>)
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($event['price'])): ?>
                            <li class="mb-3">
                                <i class="fas fa-dollar-sign text-primary me-2"></i>
                                <strong>Price:</strong> 
                                <?php echo $event['price'] == 0 ? 'Free' : '$' . number_format($event['price'], 2); ?>
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

                <!-- Registration Card -->
                <?php if (!$event['is_past']): ?>
                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-ticket-alt me-2"></i>Register Now
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <p class="card-text mb-4">
                            Secure your spot for this exciting event. Registration is quick and easy!
                        </p>
                        <a href="<?php echo BASE_URL; ?>event_register.php?event_id=<?php echo $event['id']; ?>" 
                           class="btn btn-success btn-lg w-100">
                            <i class="fas fa-ticket-alt me-2"></i>Register for Event
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Share Card -->
                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-share-alt me-2"></i>Share This Event
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-primary btn-sm flex-fill" onclick="shareOnFacebook()">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="btn btn-outline-info btn-sm flex-fill" onclick="shareOnTwitter()">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-outline-success btn-sm flex-fill" onclick="shareOnWhatsApp()">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="#" class="btn btn-outline-secondary btn-sm flex-fill" onclick="copyEventLink()">
                                <i class="fas fa-link"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card border-0 shadow-sm" data-aos="fade-up" data-aos-delay="400">
                    <div class="card-body text-center">
                        <h6 class="card-title">Need Help?</h6>
                        <p class="card-text small text-muted">
                            Contact our support team if you have any questions about this event.
                        </p>
                        <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<style>
.event-hero-image {
    height: 400px;
    object-fit: cover;
}

.event-type-badge {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    font-size: 0.8rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 50px;
}

.category-badge {
    background-color: rgba(var(--primary-color-rgb), 0.1);
    color: var(--primary-color);
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
}

.event-description {
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--dark-color);
}

.card-header {
    border-bottom: none;
}

.list-unstyled li {
    display: flex;
    align-items: flex-start;
}

.list-unstyled li i {
    margin-top: 0.25rem;
    width: 20px;
}

@media (max-width: 768px) {
    .event-hero-image {
        height: 250px;
    }
    
    .d-flex.gap-3 {
        flex-direction: column;
        gap: 1rem !important;
    }
    
    .badge {
        align-self: flex-start;
    }
}
</style>

<script>
// Share functions
function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?php echo addslashes($event['title']); ?>');
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?php echo addslashes($event['title']); ?>');
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
}

function shareOnWhatsApp() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?php echo addslashes($event['title']); ?>');
    window.open(`https://wa.me/?text=${title} ${url}`, '_blank');
}

function copyEventLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        // Show success message
        const btn = event.target.closest('a');
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalContent;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    });
}
</script>
