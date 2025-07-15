<?php
$pageTitle = "Events & News - Skills for Africa";
$pageDescription = "Upcoming events and news from Skills for Africa";
$activePage = "events";

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Get events count
$totalEvents = $db->query("SELECT COUNT(*) FROM events")->fetchColumn();
$totalPages = ceil($totalEvents / $limit);

// Get events
$events = $db->query("
    SELECT * FROM events 
    ORDER BY event_date DESC
    LIMIT $limit OFFSET $offset
")->fetchAll();
?>

<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8">
                <h1 class="fw-bold mb-3">Events & News</h1>
                <p class="lead">Stay updated with our latest workshops, info sessions, and announcements.</p>
            </div>
        </div>

        <?php if (empty($events)): ?>
        <div class="text-center py-5">
            <div class="py-5">
                <i class="fas fa-calendar-alt fa-4x text-muted mb-4"></i>
                <h3 class="h4">No events scheduled</h3>
                <p class="text-muted">Check back later for upcoming events.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($events as $event): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-img-top overflow-hidden" style="height: 180px;">
                        <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($event['image_path']); ?>"
                             alt="<?php echo htmlspecialchars($event['title']); ?>"
                             class="img-fluid w-100 h-100 object-fit-cover">
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <?php if (strtotime($event['event_date']) >= time()): ?>
                            <span class="badge bg-primary">Upcoming</span>
                            <?php endif; ?>
                        </div>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($event['short_description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                            </span>
                            <span class="text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($event['location']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <div class="d-grid">
                            <a href="<?php echo BASE_URL; ?>/client/public/event.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-5">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/client/public/events.php?page=<?php echo $page - 1; ?>">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/client/public/events.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>

                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/client/public/events.php?page=<?php echo $page + 1; ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>