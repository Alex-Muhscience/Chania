<?php
require_once __DIR__ . '/../classes/BaseController.php';

class EventsController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->setPageTitle('Events Management');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Events Management']
        ]);
    }

    public function index() {
        // CRITICAL SECURITY CHECK - Check permissions
        if (!$this->hasPermission('events') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to manage events.');
            header('Location: index.php');
            exit;
        }

        // Handle POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }

        // Get filters and pagination
        $search = $_GET['search'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $whereClause = '';
            $params = [];

            if ($search) {
                $whereClause = "WHERE title LIKE ? OR description LIKE ?";
                $searchTerm = "%$search%";
                $params = [$searchTerm, $searchTerm];
            }

            // Get total count
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM events $whereClause");
            $countStmt->execute($params);
            $totalEvents = $countStmt->fetchColumn();

            // Get events
            $stmt = $this->db->prepare("SELECT * FROM events $whereClause ORDER BY event_date DESC LIMIT $limit OFFSET $offset");
            $stmt->execute($params);
            $events = $stmt->fetchAll();

            $totalPages = ceil($totalEvents / $limit);

            $this->renderView(__DIR__ . '/../views/events/index.php', [
                'events' => $events,
                'search' => $search,
                'pagination' => [
                    'page' => $page,
                    'totalPages' => $totalPages,
                    'totalEvents' => $totalEvents,
                    'limit' => $limit
                ]
            ]);

        } catch (PDOException $e) {
            error_log("Events fetch error: " . $e->getMessage());
            $this->addError('Error loading events.');
            $this->renderView(__DIR__ . '/../views/events/index.php', [
                'events' => [],
                'search' => $search,
                'pagination' => [
                    'page' => 1,
                    'totalPages' => 0,
                    'totalEvents' => 0,
                    'limit' => $limit
                ]
            ]);
        }
    }

    private function handleActions() {
        $action = $_POST['action'] ?? '';
        $eventId = $_POST['event_id'] ?? '';

        try {
            switch ($action) {
                case 'delete':
                    if ($eventId) {
                        $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
                        $stmt->execute([$eventId]);
                        $this->setSuccess("Event deleted successfully.");
                    }
                    break;

                case 'toggle_status':
                    if ($eventId) {
                        $stmt = $this->db->prepare("UPDATE events SET is_active = NOT is_active WHERE id = ?");
                        $stmt->execute([$eventId]);
                        $this->setSuccess("Event status updated successfully.");
                    }
                    break;
            }

            $this->redirect($_SERVER['PHP_SELF']);

        } catch (PDOException $e) {
            error_log("Event management error: " . $e->getMessage());
            $this->addError("An error occurred while processing your request.");
        }
    }

    public function add() {
        // Check permissions
        if (!$this->hasPermission('events') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to add events.');
            header('Location: index.php');
            exit;
        }

        $this->setPageTitle('Add New Event');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Events Management', 'url' => BASE_URL . '/admin/events.php'],
            ['title' => 'Add New Event']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEventCreation();
        }

        $this->renderView(__DIR__ . '/../views/events/add.php', []);
    }

    public function edit() {
        // Check permissions
        if (!$this->hasPermission('events') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to edit events.');
            header('Location: index.php');
            exit;
        }

        $eventId = intval($_GET['id'] ?? 0);
        if (!$eventId) {
            $this->redirect(BASE_URL . '/admin/events.php', 'Invalid event ID.');
        }

        $this->setPageTitle('Edit Event');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Events Management', 'url' => BASE_URL . '/admin/events.php'],
            ['title' => 'Edit Event']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEventUpdate($eventId);
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch();
            
            if (!$event) {
                $this->redirect(BASE_URL . '/admin/events.php', 'Event not found.');
            }

            $this->renderView(__DIR__ . '/../views/events/edit.php', [
                'event' => $event
            ]);

        } catch (Exception $e) {
            $this->redirect(BASE_URL . '/admin/events.php', 'Error loading event: ' . $e->getMessage());
        }
    }

    private function handleEventCreation() {
        $requiredFields = ['title', 'description', 'event_date', 'location'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        if (empty($errors)) {
            try {
                $eventData = $this->sanitizeInput($_POST);
                $stmt = $this->db->prepare("
                    INSERT INTO events (title, description, event_date, location, is_active, created_at) 
                    VALUES (?, ?, ?, ?, 1, NOW())
                ");
                $result = $stmt->execute([
                    $eventData['title'],
                    $eventData['description'],
                    $eventData['event_date'],
                    $eventData['location']
                ]);
                
                if ($result) {
                    $this->redirect(BASE_URL . '/admin/events.php', 'Event created successfully.');
                } else {
                    $this->addError('Failed to create event.');
                }
            } catch (Exception $e) {
                $this->addError('Error creating event: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    private function handleEventUpdate($eventId) {
        $requiredFields = ['title', 'description', 'event_date', 'location'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        if (empty($errors)) {
            try {
                $eventData = $this->sanitizeInput($_POST);
                $stmt = $this->db->prepare("
                    UPDATE events 
                    SET title = ?, description = ?, event_date = ?, location = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $result = $stmt->execute([
                    $eventData['title'],
                    $eventData['description'],
                    $eventData['event_date'],
                    $eventData['location'],
                    $eventId
                ]);
                
                if ($result) {
                    $this->redirect(BASE_URL . '/admin/events.php', 'Event updated successfully.');
                } else {
                    $this->addError('Failed to update event.');
                }
            } catch (Exception $e) {
                $this->addError('Error updating event: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }
}
?>
