<?php
/**
 * Events Page - Streamlined
 * Displays event listing or detailed event view with registration handling
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Initialize variables to prevent undefined variable errors
$slug = filter_input(INPUT_GET, 'slug', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$isEventDetail = !empty($slug);
$registrationMessage = '';
$registrationSuccess = false;

// Variables for event listing
$eventType = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$totalEvents = 0;
$totalPages = 1;
$events = [];

// Event types for filter dropdown
$eventTypes = [
    'workshop' => 'Workshop',
    'seminar' => 'Seminar', 
    'training' => 'Training',
    'conference' => 'Conference',
    'networking' => 'Networking',
    'info_session' => 'Info Session'
];

// Handle CSRF token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Main Entry Point
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isEventDetail) {
    handleRegistration($slug);
    $event = fetchEventDetails($slug);
} elseif ($isEventDetail) {
    $event = fetchEventDetails($slug);
    if (!$event) {
        header('HTTP/1.0 404 Not Found');
        include __DIR__ . '/../includes/header.php';
        echo '<div class="container py-5 text-center"><h1>Event Not Found</h1><p>The event you\'re looking for doesn\'t exist.</p></div>';
        include __DIR__ . '/../includes/footer.php';
        exit;
    }
} else {
    // Fetch events for listing
    $result = fetchEvents($eventType, $search, $page, $limit);
    $events = $result['events'];
    $totalEvents = $result['total'];
    $totalPages = ceil($totalEvents / $limit);
}

/**
 * Handle registration logic.
 */
function handleRegistration($slug) {
    global $db, $registrationMessage, $registrationSuccess;
    try {
        validateCsrfToken();
        $data = validateRegistrationForm();
        $event = fetchEventDetails($slug);
        validateEventForRegistration($event, $data['email']);
        saveRegistration($event, $data);
        $registrationSuccess = true;
        $registrationMessage = 'Registration successful! You will receive a confirmation email shortly.';
    } catch (Exception $e) {
        $registrationMessage = $e->getMessage();
        error_log("Registration error: " . $e->getMessage());
    }
}

/**
 * Fetch detailed event information.
 *
 * @param string $slug Event slug
 * @return array|null Event details or null if not found
 */
function fetchEventDetails($slug) {
    global $db;
    try {
        $eventStmt = $db->prepare(
            "SELECT e.*, u.username as created_by_name, CASE " .
            "WHEN e.event_date > NOW() THEN 'upcoming' " .
            "WHEN e.event_date <= NOW() AND e.end_date >= NOW() THEN 'ongoing' " .
            "ELSE 'past' END as event_status " .
            "FROM events e " .
            "LEFT JOIN users u ON e.created_by = u.id " .
            "WHERE e.slug = ? AND e.is_active = 1 AND e.deleted_at IS NULL"
        );
        $eventStmt->execute([$slug]);
        return $eventStmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Fetch event details error: " . $e->getMessage());
        return null;
    }
}


/**
 * Fetch events with filtering and pagination
 */
function fetchEvents($eventType = '', $search = '', $page = 1, $limit = 12) {
    global $db;
    try {
        $offset = ($page - 1) * $limit;
        
        // Build WHERE clause
        $whereConditions = ["e.is_active = 1", "e.deleted_at IS NULL"];
        $params = [];
        
        if ($eventType) {
            $whereConditions[] = "e.event_type = ?";
            $params[] = $eventType;
        }
        
        if ($search) {
            $whereConditions[] = "(e.title LIKE ? OR e.description LIKE ? OR e.short_description LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Count total events
        $countStmt = $db->prepare("SELECT COUNT(*) FROM events e $whereClause");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Fetch events
        $offsetInt = (int)$offset;
        $limitInt = (int)$limit;
        
        $sql = "SELECT e.*, u.username as created_by_name,
                       CASE 
                           WHEN e.event_date > NOW() THEN 'upcoming'
                           WHEN e.event_date <= NOW() AND e.end_date >= NOW() THEN 'ongoing'
                           ELSE 'past'
                       END as event_status,
                       COALESCE(attendee_count.count, 0) as current_attendees
                FROM events e 
                LEFT JOIN users u ON e.created_by = u.id
                LEFT JOIN (
                    SELECT event_id, COUNT(*) as count 
                    FROM event_registrations 
                    WHERE status = 'confirmed' 
                    GROUP BY event_id
                ) attendee_count ON e.id = attendee_count.event_id
                $whereClause
                ORDER BY e.is_featured DESC, e.event_date ASC
                LIMIT $limitInt OFFSET $offsetInt";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['events' => $events, 'total' => $total];
    } catch (Exception $e) {
        error_log("Fetch events error: " . $e->getMessage());
        return ['events' => [], 'total' => 0];
    }
}

/**
 * Validate and retrieve registration form data.
 *
 * @return array Validated form data
 * @throws Exception on validation error
 */
function validateRegistrationForm() {
    $fullName = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $nameParts = explode(' ', $fullName, 2);
    
    $data = [
        'firstName' => $nameParts[0] ?? '',
        'lastName' => $nameParts[1] ?? '',
        'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
        'phone' => trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS)),
        'organization' => trim(filter_input(INPUT_POST, 'organization', FILTER_SANITIZE_FULL_SPECIAL_CHARS)),
        'dietaryReqs' => trim(filter_input(INPUT_POST, 'dietary_requirements', FILTER_SANITIZE_FULL_SPECIAL_CHARS)),
        'specialNeeds' => trim(filter_input(INPUT_POST, 'special_needs', FILTER_SANITIZE_FULL_SPECIAL_CHARS))
    ];
    
    if (!$fullName || strlen($fullName) < 2) throw new Exception('Please enter your full name (minimum 2 characters)');
    if (!$data['firstName']) throw new Exception('Please enter your first name');
    if (!$data['email']) throw new Exception('Please enter a valid email address');
    if (!$data['phone'] || strlen($data['phone']) < 10) throw new Exception('Please enter a valid phone number');
    return $data;
}

/**
 * Validate CSRF token.
 */
function validateCsrfToken() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        throw new Exception('Invalid security token');
    }
}

/**
 * Check event suitability for registration.
 */
function validateEventForRegistration($event, $email) {
    global $db;
    
    if (!$event) {
        throw new Exception('Event not found');
    }
    
    // Check if event is in the past
    if (strtotime($event['event_date']) < time()) {
        throw new Exception('This event has already occurred');
    }
    
    // Check registration deadline
    if ($event['registration_deadline'] && strtotime($event['registration_deadline']) < time()) {
        throw new Exception('Registration deadline has passed');
    }
    
    // Check if already registered
    $stmt = $db->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND email = ? AND status != 'cancelled'");
    $stmt->execute([$event['id'], $email]);
    if ($stmt->fetch()) {
        throw new Exception('You are already registered for this event');
    }
    
    // Check capacity
    if ($event['max_attendees']) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM event_registrations WHERE event_id = ? AND status = 'confirmed'");
        $stmt->execute([$event['id']]);
        $currentCount = $stmt->fetchColumn();
        
        if ($currentCount >= $event['max_attendees']) {
            throw new Exception('This event is full');
        }
    }
}

/**
 * Save registration into the database.
 */
function saveRegistration($event, $data) {
    global $db;
    
    $stmt = $db->prepare(
        "INSERT INTO event_registrations (event_id, first_name, last_name, email, phone, organization, dietary_requirements, accessibility_needs, registration_date, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'registered')"
    );
    
    $stmt->execute([
        $event['id'],
        $data['firstName'],
        $data['lastName'],
        $data['email'],
        $data['phone'],
        $data['organization'],
        $data['dietaryReqs'],
        $data['specialNeeds']
    ]);
    
    // Get the registration ID
    $registrationId = $db->lastInsertId();
    
    // Log client activity for admin notifications
    require_once __DIR__ . '/../includes/ClientActivityLogger.php';
    $activityLogger = new ClientActivityLogger();
    
    // Log to client activities table
    $activityLogger->logEventRegistration(
        $registrationId,
        $data['email'],
        $event['title']
    );
    
    // Also log to admin_logs for immediate notification
    $activityLogger->logToAdminLogs(
        'CLIENT_EVENT_REGISTRATION',
        'event_registration',
        $registrationId,
        "New event registration for '{$event['title']}'",
        $data['email']
    );
}



/**
 * Format event date for display
 */
function formatEventDate($eventDate, $endDate = null) {
    $startDate = new DateTime($eventDate);
    $formatted = $startDate->format('M j, Y');

    if ($endDate && $endDate !== $eventDate) {
        $endDateTime = new DateTime($endDate);
        if ($startDate->format('Y-m-d') === $endDateTime->format('Y-m-d')) {
            $formatted .= ' - ' . $endDateTime->format('g:i A');
        } else {
            $formatted .= ' - ' . $endDateTime->format('M j, Y');
        }
    }

    return $formatted;
}

/**
 * Get event status badge class
 */
function getEventStatusBadge($status) {
    switch ($status) {
        case 'upcoming': return 'bg-success';
        case 'ongoing': return 'bg-warning';
        case 'past': return 'bg-secondary';
        default: return 'bg-primary';
    }
}

// Include appropriate header
if ($isEventDetail && isset($event) && $event) {
    $pageTitle = htmlspecialchars($event['title']) . " - Skills for Africa";
    $pageDescription = htmlspecialchars($event['short_description'] ?? '');
} else {
    $pageTitle = "Events - Skills for Africa";
    $pageDescription = "Stay updated with our latest workshops, info sessions, and announcements.";
}
require_once __DIR__ . '/../includes/header.php';

// Render event detail or event list
if ($isEventDetail): ?>
<!-- Event Detail View -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($event['title']); ?></h1>
                <?php if ($event['image_path']): ?>
                <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($event['image_path']); ?>" 
                     alt="<?php echo htmlspecialchars($event['title']); ?>" class="img-fluid mb-4">
                <?php endif; ?>
                <p class="lead">By <?php echo htmlspecialchars($event['created_by_name'] ?? 'Unknown'); ?></p>
                <p><strong>When:</strong> <?php echo formatEventDate($event['event_date'], $event['end_date']); ?></p>
                <p><strong>Where:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                <?php if ($event['registration_fee'] > 0): ?>
                <p><strong>Fee:</strong> KES <?php echo number_format($event['registration_fee'], 2); ?></p>
                <?php endif; ?>
                <p class="mb-5"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            </div>
            <div class="col-lg-4">
                <?php if ($registrationSuccess): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($registrationMessage); ?>
                    </div>
                <?php elseif ($registrationMessage): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($registrationMessage); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($event['event_status'] === 'upcoming' && (!$event['registration_deadline'] || strtotime($event['registration_deadline']) > time())): ?>
                <div class="card">
                    <div class="card-header">Register for this Event</div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" name="full_name" id="full_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="text" name="phone" id="phone" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="organization" class="form-label">Organization</label>
                                <input type="text" name="organization" id="organization" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="dietary_requirements" class="form-label">Dietary Requirements</label>
                                <input type="text" name="dietary_requirements" id="dietary_requirements" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="special_needs" class="form-label">Accessibility Needs</label>
                                <input type="text" name="special_needs" id="special_needs" class="form-control">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Register Now</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-body text-center">
                        <h5>Registration Closed</h5>
                        <p class="text-muted mb-0">
                            <?php if ($event['event_status'] === 'past'): ?>
                                This event has already occurred.
                            <?php else: ?>
                                Registration deadline has passed.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php else: ?>
<!-- Event List View -->
<section class="py-5">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-5">
            <div class="col-lg-8">
                <h1 class="fw-bold mb-3">Events & News</h1>
                <p class="lead">Stay updated with our latest workshops, info sessions, and announcements.</p>
            </div>
            <div class="col-lg-4">
                <!-- Search and Filter Form -->
                <form method="get" class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" name="search" class="form-control" placeholder="Search events..."
                                   value="<?php echo htmlspecialchars($search ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <select name="type" class="form-select">
                                <option value="">All Event Types</option>
                                <?php foreach ($eventTypes as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $eventType === $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="<?php echo BASE_URL; ?>/client/public/events.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Events Grid -->
        <?php if (empty($events)): ?>
        <div class="text-center py-5">
            <div class="py-5">
                <i class="fas fa-calendar-alt fa-4x text-muted mb-4"></i>
                <h3 class="h4">No events found</h3>
                <p class="text-muted">
                    <?php if ($search || $eventType): ?>
                        Try adjusting your search criteria or <a href="<?php echo BASE_URL; ?>/client/public/events.php">view all events</a>.
                    <?php else: ?>
                        Check back later for upcoming events.
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($events as $event): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0">
                    <!-- Event Image -->
                    <div class="card-img-top overflow-hidden position-relative" style="height: 200px;">
                        <?php if ($event['image_path']): ?>
                        <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($event['image_path']); ?>"
                             alt="<?php echo htmlspecialchars($event['title']); ?>"
                             class="img-fluid w-100 h-100 object-fit-cover">
                        <?php else: ?>
                        <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-calendar-alt fa-3x text-muted"></i>
                        </div>
                        <?php endif; ?>

                        <!-- Event Status Badge -->
                        <span class="position-absolute top-0 end-0 m-2 badge <?php echo getEventStatusBadge($event['event_status']); ?>">
                            <?php echo ucfirst($event['event_status']); ?>
                        </span>

                        <!-- Featured Badge -->
                        <?php if ($event['is_featured']): ?>
                        <span class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark">
                            <i class="fas fa-star"></i> Featured
                        </span>
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <!-- Event Type -->
                        <div class="mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <?php echo ucfirst($event['event_type']); ?>
                            </span>
                            <?php if ($event['is_virtual']): ?>
                            <span class="badge bg-info bg-opacity-10 text-info">
                                <i class="fas fa-video"></i> Virtual
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Event Title -->
                        <h5 class="card-title mb-2"><?php echo htmlspecialchars($event['title']); ?></h5>

                        <!-- Event Description -->
                        <p class="card-text text-muted small mb-3">
                            <?php echo htmlspecialchars($event['short_description']); ?>
                        </p>

                        <!-- Event Details -->
                        <div class="small text-muted">
                            <div class="mb-1">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo formatEventDate($event['event_date'], $event['end_date']); ?>
                            </div>
                            <div class="mb-1">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($event['location']); ?>
                            </div>
                            <?php if ($event['max_attendees']): ?>
                            <div class="mb-1">
                                <i class="fas fa-users me-1"></i>
                                <?php echo $event['current_attendees']; ?> / <?php echo $event['max_attendees']; ?> attendees
                            </div>
                            <?php endif; ?>
                            <?php if ($event['registration_fee'] > 0): ?>
                            <div class="mb-1">
                                <i class="fas fa-money-bill-wave me-1"></i>
                                KES <?php echo number_format($event['registration_fee'], 2); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-0 pt-0">
                        <div class="d-grid">
                            <a href="<?php echo BASE_URL; ?>/client/public/event.php?slug=<?php echo urlencode($event['slug']); ?>"
                               class="btn btn-outline-primary">
                                <i class="fas fa-info-circle me-1"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-5" aria-label="Events pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/client/public/events.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                </li>

                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);

                if ($startPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/client/public/events.php?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a>
                </li>
                <?php if ($startPage > 2): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/client/public/events.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/client/public/events.php?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>"><?php echo $totalPages; ?></a>
                </li>
                <?php endif; ?>

                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/client/public/events.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Results Summary -->
        <div class="text-center mt-3">
            <small class="text-muted">
                Showing <?php echo min(($page - 1) * $limit + 1, $totalEvents); ?> to
                <?php echo min($page * $limit, $totalEvents); ?> of <?php echo $totalEvents; ?> events
            </small>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>


<?php endif; // End event detail/list conditional ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
