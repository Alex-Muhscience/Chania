<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$pageTitle = "Search Results";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'Search Results']
];

$query = trim($_GET['q'] ?? '');
$results = [];
$totalResults = 0;

if (!empty($query)) {
    try {
        $db = (new Database())->connect();
        $searchTerm = '%' . $query . '%';
        
        // Search in users
        $stmt = $db->prepare("
            SELECT 'user' as type, id, username as title, email as subtitle, created_at
            FROM users 
            WHERE (username LIKE ? OR email LIKE ?) AND is_active = 1
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$searchTerm, $searchTerm]);
        $userResults = $stmt->fetchAll();
        
        // Search in applications
        $stmt = $db->prepare("
            SELECT 'application' as type, a.id, CONCAT(a.first_name, ' ', a.last_name) as title, 
                   CONCAT('Program: ', p.title) as subtitle, a.submitted_at as created_at
            FROM applications a
            JOIN programs p ON a.program_id = p.id
            WHERE a.first_name LIKE ? OR a.last_name LIKE ? OR p.title LIKE ?
            ORDER BY a.submitted_at DESC
            LIMIT 10
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $applicationResults = $stmt->fetchAll();
        
        // Search in contacts
        $stmt = $db->prepare("
            SELECT 'contact' as type, id, name as title, subject as subtitle, submitted_at as created_at
            FROM contacts 
            WHERE name LIKE ? OR subject LIKE ? OR message LIKE ?
            ORDER BY submitted_at DESC
            LIMIT 10
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $contactResults = $stmt->fetchAll();
        
        // Search in events
        $stmt = $db->prepare("
            SELECT 'event' as type, id, title, 
                   CONCAT('Date: ', DATE_FORMAT(event_date, '%M %d, %Y')) as subtitle, created_at
            FROM events 
            WHERE (title LIKE ? OR description LIKE ?)
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$searchTerm, $searchTerm]);
        $eventResults = $stmt->fetchAll();
        
        // Search in programs
        $stmt = $db->prepare("
            SELECT 'program' as type, id, title, 
                   CONCAT('Duration: ', duration) as subtitle, created_at
            FROM programs 
            WHERE (title LIKE ? OR description LIKE ?)
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$searchTerm, $searchTerm]);
        $programResults = $stmt->fetchAll();
        
        // Combine all results
        $results = array_merge($userResults, $applicationResults, $contactResults, $eventResults, $programResults);
        $totalResults = count($results);
        
        // Sort by created_at DESC
        usort($results, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
    } catch (PDOException $e) {
        error_log("Search error: " . $e->getMessage());
        $error = "An error occurred while searching. Please try again.";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-search mr-2"></i>Search Results
            <?php if (!empty($query)): ?>
                for "<?= htmlspecialchars($query) ?>"
            <?php endif; ?>
        </h6>
    </div>
    <div class="card-body">
        <!-- Search Form -->
        <form method="GET" action="<?= BASE_URL ?>/admin/public/search.php" class="mb-4">
            <div class="input-group">
                <input type="text" name="q" class="form-control" 
                       placeholder="Search users, applications, events, programs..." 
                       value="<?= htmlspecialchars($query) ?>" autofocus>
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </form>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php elseif (!empty($query)): ?>
            <div class="mb-3">
                <span class="text-muted">Found <?= $totalResults ?> results</span>
            </div>

            <?php if (empty($results)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No results found</h5>
                    <p class="text-muted">Try adjusting your search terms or check for typos.</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($results as $result): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <?php
                                            $iconClass = '';
                                            $badgeClass = '';
                                            $linkUrl = '';
                                            
                                            switch ($result['type']) {
                                                case 'user':
                                                    $iconClass = 'fa-user';
                                                    $badgeClass = 'badge-primary';
                                                    $linkUrl = BASE_URL . '/admin/public/users.php?action=view&id=' . $result['id'];
                                                    break;
                                                case 'application':
                                                    $iconClass = 'fa-file-alt';
                                                    $badgeClass = 'badge-success';
                                                    $linkUrl = BASE_URL . '/admin/public/applications.php?action=view&id=' . $result['id'];
                                                    break;
                                                case 'contact':
                                                    $iconClass = 'fa-envelope';
                                                    $badgeClass = 'badge-info';
                                                    $linkUrl = BASE_URL . '/admin/public/contacts.php?action=view&id=' . $result['id'];
                                                    break;
                                                case 'event':
                                                    $iconClass = 'fa-calendar-alt';
                                                    $badgeClass = 'badge-warning';
                                                    $linkUrl = BASE_URL . '/admin/public/events.php?action=view&id=' . $result['id'];
                                                    break;
                                                case 'program':
                                                    $iconClass = 'fa-graduation-cap';
                                                    $badgeClass = 'badge-secondary';
                                                    $linkUrl = BASE_URL . '/admin/public/programs.php?action=view&id=' . $result['id'];
                                                    break;
                                            }
                                            ?>
                                            <div class="icon-circle bg-light">
                                                <i class="fas <?= $iconClass ?> text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="<?= $linkUrl ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($result['title']) ?>
                                                </a>
                                            </h6>
                                            <p class="text-muted small mb-1"><?= htmlspecialchars($result['subtitle'] ?? '') ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge <?= $badgeClass ?>">
                                                    <?= ucfirst($result['type']) ?>
                                                </span>
                                                <small class="text-muted">
                                                    <?= Utilities::timeAgo($result['created_at']) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Enter a search term</h5>
                <p class="text-muted">Search for users, applications, events, programs, and more.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
