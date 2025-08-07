<?php
require_once '../includes/config.php';

$page_title = 'Events  Workshops';
$page_description = 'Join our upcoming events, workshops, webinars, and training sessions. Stay updated with the latest skills development opportunities at Chania Skills for Africa.';
$page_keywords = 'events, workshops, webinars, training, seminars, networking, skills development, Africa';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 9; // Reduced for better mobile experience
$offset = ($page - 1) * $per_page;

// Filtering
$category_filter = $_GET['category'] ?? '';
$type_filter = $_GET['type'] ?? '';
$search_query = $_GET['search'] ?? '';
$view_mode = $_GET['view'] ?? 'grid';

// Build the query
$where_conditions = ["e.deleted_at IS NULL"];
$params = [];

// Add is_active condition with fallback
try {
    $test_query = "SELECT is_active FROM events LIMIT 1";
    $db->query($test_query);
    $where_conditions[] = "e.is_active = 1";
} catch (PDOException $e) {
    // Column doesn't exist, skip this condition
}

// Only show future events (events that haven't expired) - main feature
// Allow showing past events only if explicitly requested
$show_past = $_GET['show_past'] ?? false;
if (!$show_past) {
    $where_conditions[] = "e.event_date >= NOW()";
}

if (!empty($category_filter)) {
    $where_conditions[] = "e.category = ?";
    $params[] = $category_filter;
}

if (!empty($type_filter)) {
    $where_conditions[] = "e.event_type = ?";
    $params[] = $type_filter;
}

if (!empty($search_query)) {
    $where_conditions[] = "(e.title LIKE ? OR e.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$where_clause = implode(' AND ', $where_conditions);

// Fetch events
try {
    // Get total count for pagination
    $count_query = "SELECT COUNT(*) FROM events e WHERE $where_clause";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->execute($params);
    $total_events = $count_stmt->fetchColumn();
    
    // Get events with better ordering
    $events_query = "
        SELECT e.*, 
               CASE 
                   WHEN e.event_date >= NOW() THEN 0 
                   ELSE 1 
               END as is_past,
               DATEDIFF(e.event_date, NOW()) as days_until
        FROM events e 
        WHERE $where_clause 
        ORDER BY is_past ASC, e.event_date ASC, e.created_at DESC 
        LIMIT $per_page OFFSET $offset
    ";
    $events_stmt = $db->prepare($events_query);
    $events_stmt->execute($params);
    $events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $events = [];
    $total_events = 0;
}

// Calculate pagination
$total_pages = ceil($total_events / $per_page);

// Fetch featured/upcoming events for hero section
try {
    $featured_query = "
        SELECT *, DATEDIFF(event_date, NOW()) as days_until
        FROM events 
        WHERE event_date >= NOW() AND deleted_at IS NULL AND is_active = 1
        ORDER BY event_date ASC 
        LIMIT 3
    ";
    $featured_events = $db->query($featured_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $featured_events = [];
}

// Helper function to render event cards consistently
function renderEventCard($event, $featured = false, $view_mode = 'grid') {
    $days_until = $event['days_until'] ?? null;
    $is_past = isset($event['is_past']) ? $event['is_past'] : (strtotime($event['event_date']) < time());
    
    $card_class = $view_mode === 'list' ? 'event-card-list' : 'h-100';
    $col_class = $view_mode === 'list' ? 'col-12' : 'col-lg-4 col-md-6';
    
    if ($view_mode === 'list') {
        return renderEventListItem($event, $days_until, $is_past);
    }
    
    ob_start();
    ?>
    <div class="<?php echo $col_class; ?>" data-aos="fade-up">
        <div class="event-card card border-0 shadow-sm <?php echo $card_class; ?> hover-lift">
            <div class="position-relative overflow-hidden">
                <img src="<?php echo !empty($event['image_url']) ? ASSETS_URL . 'images/events/' . $event['image_url'] : 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=400&h=250&fit=crop'; ?>" 
                     alt="<?php echo htmlspecialchars($event['title']); ?>" 
                     class="card-img-top event-image" 
                     loading="lazy">
                
                <!-- Event Type Badge -->
                <div class="position-absolute top-0 end-0 m-3">
                    <span class="badge event-type-badge">
                        <?php echo ucfirst($event['event_type'] ?? 'Event'); ?>
                    </span>
                </div>
                
                <!-- Timing Badge -->
                <?php if ($days_until !== null): ?>
                    <div class="position-absolute top-0 start-0 m-3">
                        <?php if ($days_until <= 0): ?>
                            <span class="badge bg-secondary">
                                <i class="fas fa-history me-1"></i>Past
                            </span>
                        <?php elseif ($days_until <= 7): ?>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-clock me-1"></i><?php echo $days_until; ?> day<?php echo $days_until > 1 ? 's' : ''; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="card-body d-flex flex-column">
                <!-- Event Meta -->
                <div class="event-meta mb-3">
                    <div class="d-flex align-items-center text-muted mb-2 small">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        <span><?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                        <?php if (!empty($event['event_time'])): ?>
                            <span class="mx-2">•</span>
                            <i class="fas fa-clock me-1 text-primary"></i>
                            <span><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($event['location'])): ?>
                        <div class="d-flex align-items-center text-muted small">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            <span><?php echo htmlspecialchars($event['location']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Category -->
                <?php if (!empty($event['category'])): ?>
                    <div class="mb-3">
                        <span class="badge category-badge"><?php echo ucfirst($event['category']); ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- Event Title & Description -->
                <h5 class="card-title event-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                <p class="card-text text-muted flex-grow-1 event-description">
                    <?php echo htmlspecialchars(strlen($event['description']) > 100 ? substr($event['description'], 0, 100) . '...' : $event['description']); ?>
                </p>
                
                <!-- Event Actions -->
                <div class="event-actions mt-auto">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <?php if (!empty($event['max_participants'])): ?>
                                <i class="fas fa-users me-1"></i>
                                <?php echo $event['max_participants']; ?> seats
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="showEventDetails(<?php echo $event['id']; ?>)">
                                <i class="fas fa-info-circle me-1"></i>Details
                            </button>
                            <?php if (!$is_past): ?>
                                <button class="btn btn-primary btn-sm" onclick="registerForEvent(<?php echo $event['id']; ?>)">
                                    <i class="fas fa-ticket-alt me-1"></i>Register
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderEventListItem($event, $days_until, $is_past) {
    ob_start();
    ?>
    <div class="col-12" data-aos="fade-up">
        <div class="event-list-item card border-0 shadow-sm hover-lift">
            <div class="row g-0">
                <div class="col-md-4">
                    <div class="position-relative overflow-hidden h-100">
                        <img src="<?php echo !empty($event['image_url']) ? ASSETS_URL . 'images/events/' . $event['image_url'] : 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=300&h=200&fit=crop'; ?>" 
                             alt="<?php echo htmlspecialchars($event['title']); ?>" 
                             class="list-event-image" 
                             loading="lazy">
                        
                        <!-- Timing Badge -->
                        <?php if ($days_until !== null): ?>
                            <div class="position-absolute top-0 start-0 m-3">
                                <?php if ($days_until <= 0): ?>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-history me-1"></i>Past
                                    </span>
                                <?php elseif ($days_until <= 7): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock me-1"></i><?php echo $days_until; ?> day<?php echo $days_until > 1 ? 's' : ''; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card-body d-flex flex-column h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="event-badges">
                                <span class="badge event-type-badge me-2">
                                    <?php echo ucfirst($event['event_type'] ?? 'Event'); ?>
                                </span>
                                <?php if (!empty($event['category'])): ?>
                                    <span class="badge category-badge"><?php echo ucfirst($event['category']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <h5 class="event-title mb-2"><?php echo htmlspecialchars($event['title']); ?></h5>
                        <p class="text-muted mb-3 flex-grow-1">
                            <?php echo htmlspecialchars(strlen($event['description']) > 150 ? substr($event['description'], 0, 150) . '...' : $event['description']); ?>
                        </p>
                        
                        <div class="event-meta-list mb-3">
                            <div class="row g-2 text-muted small">
                                <div class="col-md-6">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                    <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                    <?php if (!empty($event['event_time'])): ?>
                                        at <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($event['location'])): ?>
                                    <div class="col-md-6">
                                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                        <?php echo htmlspecialchars($event['location']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                <?php if (!empty($event['max_participants'])): ?>
                                    <i class="fas fa-users me-1"></i>
                                    <?php echo $event['max_participants']; ?> seats available
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm" onclick="showEventDetails(<?php echo $event['id']; ?>)">
                                    <i class="fas fa-info-circle me-1"></i>Details
                                </button>
                                <?php if (!$is_past): ?>
                                    <button class="btn btn-primary btn-sm" onclick="registerForEvent(<?php echo $event['id']; ?>)">
                                        <i class="fas fa-ticket-alt me-1"></i>Register
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
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
                        <li class="breadcrumb-item active" aria-current="page">Events & Workshops</li>
                    </ol>
                </nav>
                <h1 class="text-black mb-4" data-aos="fade-up">Events & Workshops</h1>
                <p class="text-black-50 fs-5 mb-0" data-aos="fade-up" data-aos-delay="200">
                    Join our community events, workshops, and training sessions designed to accelerate your skills development journey.
                </p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_events ?: '25'; ?>+</div>
                        <div class="stat-label">Upcoming Events</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Participants Monthly</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Expert Speakers</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Search and Filter Section -->
<section class="py-5 bg-light-gray">
    <div class="container">
        <div class="card border-0 shadow-sm" data-aos="fade-up">
            <div class="card-body p-4">
                <form method="GET" action="">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Events</label>
                            <div class="position-relative">
                                <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                <input type="text" class="form-control ps-5" id="search" name="search" 
                                       placeholder="Search by title or description..." 
                                       value="<?php echo htmlspecialchars($search_query); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <option value="technology" <?php echo $category_filter === 'technology' ? 'selected' : ''; ?>>Technology</option>
                                <option value="business" <?php echo $category_filter === 'business' ? 'selected' : ''; ?>>Business</option>
                                <option value="agriculture" <?php echo $category_filter === 'agriculture' ? 'selected' : ''; ?>>Agriculture</option>
                                <option value="healthcare" <?php echo $category_filter === 'healthcare' ? 'selected' : ''; ?>>Healthcare</option>
                                <option value="general" <?php echo $category_filter === 'general' ? 'selected' : ''; ?>>General</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label">Event Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="workshop" <?php echo $type_filter === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                                <option value="webinar" <?php echo $type_filter === 'webinar' ? 'selected' : ''; ?>>Webinar</option>
                                <option value="seminar" <?php echo $type_filter === 'seminar' ? 'selected' : ''; ?>>Seminar</option>
                                <option value="networking" <?php echo $type_filter === 'networking' ? 'selected' : ''; ?>>Networking</option>
                                <option value="training" <?php echo $type_filter === 'training' ? 'selected' : ''; ?>>Training</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex flex-column gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                                <div class="form-check small">
                                    <input class="form-check-input" type="checkbox" id="show_past" name="show_past" value="1" <?php echo $show_past ? 'checked' : ''; ?>>
                                    <label class="form-check-label text-muted" for="show_past">
                                        Include past events
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                
                <?php if (!empty($search_query) || !empty($category_filter) || !empty($type_filter)): ?>
                    <div class="mt-3 d-flex align-items-center gap-2">
                        <span class="text-muted">Active filters:</span>
                        <?php if (!empty($search_query)): ?>
                            <span class="badge bg-light text-dark">Search: "<?php echo htmlspecialchars($search_query); ?>"</span>
                        <?php endif; ?>
                        <?php if (!empty($category_filter)): ?>
                            <span class="badge bg-light text-dark">Category: <?php echo ucfirst($category_filter); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($type_filter)): ?>
                            <span class="badge bg-light text-dark">Type: <?php echo ucfirst($type_filter); ?></span>
                        <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>events.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Clear All
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

        <!-- Events Grid/List -->
<section class="py-5">
    <div class="container">
        <div class="row mb-4 align-items-center">
            <div class="col-lg-6">
                <h3 class="mb-0">
                    <?php if ($total_events > 0): ?>
                        <span class="text-primary"><?php echo $total_events; ?></span> Event<?php echo $total_events > 1 ? 's' : ''; ?> Found
                    <?php else: ?>
                        All Events
                    <?php endif; ?>
                </h3>
                <?php if ($total_events > 0): ?>
                    <p class="text-muted mb-0 small">
                        Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total_events); ?> of <?php echo $total_events; ?> results
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
                <div class="d-flex align-items-center justify-content-lg-end gap-3">
                    <span class="text-muted small d-none d-sm-inline">View:</span>
                    <div class="btn-group view-toggle" role="group" aria-label="View options">
                        <input type="radio" class="btn-check" name="view-mode" id="grid-view" value="grid" <?php echo $view_mode === 'grid' ? 'checked' : ''; ?>>
                        <label class="btn btn-outline-secondary" for="grid-view" title="Grid View">
                            <i class="fas fa-th"></i>
                        </label>
                        <input type="radio" class="btn-check" name="view-mode" id="list-view" value="list" <?php echo $view_mode === 'list' ? 'checked' : ''; ?>>
                        <label class="btn btn-outline-secondary" for="list-view" title="List View">
                            <i class="fas fa-list"></i>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($events)): ?>
            <div class="events-container row g-4" id="events-container">
                <?php foreach ($events as $index => $event): ?>
                    <?php echo renderEventCard($event, false, $view_mode); ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="row mt-5">
                    <div class="col-12">
                        <nav aria-label="Events pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(['search' => $search_query, 'category' => $category_filter, 'type' => $type_filter, 'show_past' => $show_past]); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query(['search' => $search_query, 'category' => $category_filter, 'type' => $type_filter, 'show_past' => $show_past]); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(['search' => $search_query, 'category' => $category_filter, 'type' => $type_filter, 'show_past' => $show_past]); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state text-center">
                <div class="empty-icon mb-4">
                    <i class="fas fa-calendar-times fa-5x text-muted"></i>
                </div>
                <h3 class="mb-3">No Events Found</h3>
                <p class="text-muted mb-4">
                    <?php if (!empty($search_query) || !empty($category_filter) || !empty($type_filter)): ?>
                        We couldn't find any events matching your search criteria. Try adjusting your filters or search terms.
                    <?php else: ?>
                        There are no events scheduled at the moment. Check back soon for upcoming workshops and training sessions.
                    <?php endif; ?>
                </p>
                <div class="empty-actions">
                    <?php if (!empty($search_query) || !empty($category_filter) || !empty($type_filter)): ?>
                        <a href="<?php echo BASE_URL; ?>events.php" class="btn btn-primary">
                            <i class="fas fa-list me-2"></i>View All Events
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-outline-primary">
                        <i class="fas fa-envelope me-2"></i>Get Notified
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Newsletter Signup -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="mb-4">Never Miss an Event</h2>
                <p class="fs-5 mb-4 opacity-90">
                    Subscribe to our newsletter and be the first to know about upcoming workshops, webinars, and training sessions.
                </p>
                <form class="row g-3 justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <input type="email" class="form-control form-control-lg" placeholder="Enter your email address" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-secondary btn-lg">
                            <i class="fas fa-bell me-2"></i>Subscribe
                        </button>
                    </div>
                </form>
                <p class="mt-3 mb-0 opacity-75 small">
                    <i class="fas fa-shield-alt me-1"></i>We respect your privacy. Unsubscribe at any time.
                </p>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<style>
.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
}

.event-image, .list-event-image {
    height: 200px;
    object-fit: cover;
    transition: all 0.3s ease;
}

.list-event-image {
    height: 100%;
    min-height: 200px;
}

.card:hover .event-image,
.card:hover .list-event-image {
    transform: scale(1.05);
}

.event-type-badge {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 50px;
}

.category-badge {
    background-color: rgba(var(--primary-color-rgb), 0.1);
    color: var(--primary-color);
    font-size: 0.7rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
}

.event-title {
    font-size: 1.1rem;
    font-weight: 600;
    line-height: 1.3;
    color: var(--dark-color);
}

.event-description {
    font-size: 0.9rem;
    line-height: 1.5;
}

.event-meta .text-muted {
    color: #6c757d !important;
}

.view-toggle .btn {
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
}

.pagination .page-link {
    border-radius: 8px;
    margin: 0 2px;
    border: 1px solid var(--gray-300);
}

.pagination .page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.empty-state {
    padding: 4rem 2rem;
}

.empty-icon i {
    opacity: 0.3;
}

@media (max-width: 768px) {
    .events-container .col-lg-4 {
        margin-bottom: 1.5rem;
    }
    
    .event-list-item .row {
        flex-direction: column;
    }
    
    .list-event-image {
        height: 200px;
        min-height: 200px;
    }
}
</style>

<script>
// View mode toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const viewToggle = document.querySelectorAll('input[name="view-mode"]');
    const eventsContainer = document.getElementById('events-container');
    
    viewToggle.forEach(toggle => {
        toggle.addEventListener('change', function() {
            if (this.checked) {
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.set('view', this.value);
                window.location.href = currentUrl.toString();
            }
        });
    });
});

// Event details function
function showEventDetails(eventId) {
    window.location.href = `<?php echo BASE_URL; ?>event-details.php?id=${eventId}`;
}

// Event registration function
function registerForEvent(eventId) {
    window.location.href = `<?php echo BASE_URL; ?>event_register.php?event_id=${eventId}`;
}

// Add loading state for buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn')) {
        const btn = e.target.closest('.btn');
        if (btn.onclick && (btn.onclick.toString().includes('showEventDetails') || btn.onclick.toString().includes('registerForEvent'))) {
            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
            
            // Re-enable after a short delay in case navigation fails
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }, 3000);
        }
    }
});
</script>
