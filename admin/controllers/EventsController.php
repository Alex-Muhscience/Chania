<?php
require_once __DIR__ . '/../classes/BaseController.php';

class EventsController extends BaseController {
    private $apiBaseUrl;
    
    public function __construct() {
        parent::__construct();
        $this->apiBaseUrl = BASE_URL . '/api/v1';
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

        // Handle AJAX actions via API
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $this->handleApiActions();
            return;
        }

        // Handle legacy POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }

        // Get filters and pagination
        $search = $_GET['search'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = $_GET['limit'] ?? 10;

        // Get events data via API
        $events = $this->fetchEventsFromApi($page, $limit, $search);

        $this->render('events/index', [
            'events' => $events['data']['data'] ?? [],
            'search' => $search,
            'pagination' => [
                'page' => $events['data']['pagination']['page'] ?? 1,
                'totalPages' => $events['data']['pagination']['pages'] ?? 0,
                'totalEvents' => $events['data']['pagination']['total'] ?? 0,
                'limit' => $events['data']['pagination']['limit'] ?? $limit
            ],
            'filters' => [
                'page' => $page,
                'search' => $search
            ]
        ]);
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

    /**
     * Handle API-based actions (AJAX)
     */
    private function handleApiActions()
    {
        header('Content-Type: application/json');

        $action = $_POST['action'] ?? '';
        $eventId = $_POST['id'] ?? $_POST['event_id'] ?? '';

        if (!$eventId) {
            echo json_encode(['success' => false, 'message' => 'Event ID is required']);
            return;
        }

        try {
            switch ($action) {
                case 'update_status':
                    $isActive = $_POST['is_active'] ?? '';
                    $result = $this->callApi('PUT', "/events/{$eventId}", ['is_active' => $isActive]);
                    break;

                case 'delete':
                    $result = $this->callApi('DELETE', "/events/{$eventId}");
                    break;

                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    return;
            }

            echo json_encode($result);

        } catch (Exception $e) {
            error_log("API action error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error occurred']);
        }
    }

    /**
     * Fetch events from API
     */
    private function fetchEventsFromApi($page = 1, $limit = 10, $search = '')
    {
        $params = [
            'page' => $page,
            'limit' => $limit
        ];

        if ($search) $params['search'] = $search;

        $queryString = http_build_query($params + ['admin' => true]);
        
        try {
            $response = $this->callApi('GET', "/events?{$queryString}");
            
            // Check if response is valid and has data
            if (isset($response['success']) && $response['success']) {
                return $response;
            } elseif (isset($response['data'])) {
                // If no 'success' key but has 'data', assume it's valid
                return $response;
            } else {
                // If API is not responding properly, fallback to direct database query
                return $this->fallbackToDirectQuery($page, $limit, $search);
            }
        } catch (Exception $e) {
            error_log("API fetch error: " . $e->getMessage());
            
            // Fallback to direct database query if API fails
            return $this->fallbackToDirectQuery($page, $limit, $search);
        }
    }

    /**
     * Generic API call helper
     */
    private function callApi($method, $endpoint, $data = null)
    {
        $url = $this->apiBaseUrl . $endpoint;

        $options = [
            'http' => [
                'method' => $method,
                'header' => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                'ignore_errors' => true
            ]
        ];

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options['http']['content'] = json_encode($data);
        }

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            throw new Exception("Failed to call API endpoint: {$endpoint}");
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response from API");
        }

        return $decodedResponse;
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

    /**
     * Fallback to direct database query when API fails
     */
    private function fallbackToDirectQuery($page = 1, $limit = 10, $search = '')
    {
        $offset = ($page - 1) * $limit;
        
        $whereClauses = [];
        $parameters = [];

        if ($search) {
            $whereClauses[] = '(title LIKE ? OR description LIKE ? OR location LIKE ?)';
            $parameters[] = "%{$search}%";
            $parameters[] = "%{$search}%";
            $parameters[] = "%{$search}%";
        }
        
        $where = '';
        if (!empty($whereClauses)) {
            $where = 'WHERE ' . implode(' AND ', $whereClauses);
        }

        $countQuery = "SELECT COUNT(*) as totalEvents FROM events {$where}";
        $stmt = $this->db->prepare($countQuery);
        $stmt->execute($parameters);
        $totalEvents = $stmt->fetchColumn();

        $query = "SELECT * FROM events {$where} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->db->prepare($query);
        $stmt->execute($parameters);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $events,
            'pagination' => [
                'page' => $page,
                'totalPages' => ceil($totalEvents / $limit),
                'totalEvents' => $totalEvents,
                'limit' => $limit
            ]
        ];
    }

}
?>
