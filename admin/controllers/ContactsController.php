<?php

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

class ContactsController extends BaseController
{
    private $apiBaseUrl;
    
    public function __construct()
    {
        parent::__construct();
        $this->apiBaseUrl = BASE_URL . '/api/v1';
    }

    /**
     * Display contacts listing page
     */
    public function index()
    {
        // Check permissions - contacts don't need specific permission, admin users can manage
        if (!$this->hasPermission('*') && !$this->hasPermission('contacts')) {
            $this->redirect(BASE_URL . '/admin/', 'Access denied.');
        }

        // Handle AJAX actions via API
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $this->handleApiActions();
            return;
        }

        // Handle legacy GET actions
        if (isset($_GET['action'], $_GET['id'])) {
            $this->handleActions();
        }

        // Get filter parameters
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        
        // Get contacts data via API
        $contacts = $this->fetchContactsFromApi($page, $limit, $status, $search);

        $data = [
            'contacts' => $contacts['data'] ?? [],
            'pagination' => $contacts['pagination'] ?? [],
            'pageTitle' => 'Contact Management',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Contacts']
            ],
            'filters' => [
                'page' => $page,
                'status' => $status,
                'search' => $search
            ]
        ];

        $this->render('contacts/index', $data);
    }

    /**
     * Handle contact actions (mark_read, delete)
     */
    private function handleActions()
    {
        $contactId = (int)$_GET['id'];
        $action = $_GET['action'];

        try {
            switch ($action) {
                case 'mark_read':
                    $stmt = $this->db->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?");
                    $stmt->execute([$contactId]);
                    $this->setFlashMessage('success', 'Contact marked as read.');
                    break;
                    
                case 'delete':
                    $stmt = $this->db->prepare("UPDATE contacts SET deleted_at = NOW() WHERE id = ?");
                    $stmt->execute([$contactId]);
                    $this->setFlashMessage('success', 'Contact deleted successfully.');
                    break;
                    
                default:
                    $this->setFlashMessage('error', 'Invalid action.');
                    break;
            }
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error performing action: ' . $e->getMessage());
            error_log("Contact action error: " . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/contacts.php');
    }
    
    /**
     * Handle API-based actions (AJAX)
     */
    private function handleApiActions()
    {
        header('Content-Type: application/json');
        
        $action = $_POST['action'] ?? '';
        $contactId = $_POST['id'] ?? '';
        
        if (!$contactId) {
            echo json_encode(['success' => false, 'message' => 'Contact ID is required']);
            return;
        }
        
        try {
            switch ($action) {
                case 'mark_read':
                    $result = $this->callApi('PUT', "/contacts/{$contactId}", ['is_read' => 1]);
                    break;
                    
                case 'update_status':
                    $status = $_POST['status'] ?? '';
                    $result = $this->callApi('PUT', "/contacts/{$contactId}", ['status' => $status]);
                    break;
                    
                case 'delete':
                    $result = $this->callApi('DELETE', "/contacts/{$contactId}");
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
     * Fetch contacts from API
     */
    private function fetchContactsFromApi($page = 1, $limit = 20, $status = '', $search = '')
    {
        $params = [
            'page' => $page,
            'limit' => $limit
        ];
        
        if ($status) $params['status'] = $status;
        if ($search) $params['search'] = $search;
        
        $queryString = http_build_query($params);
        
        try {
            $response = $this->callApi('GET', "/contacts?{$queryString}");
            
            // Check if response is valid and has data
            if (isset($response['success']) && $response['success']) {
                return $response;
            } elseif (isset($response['data'])) {
                // If no 'success' key but has 'data', assume it's valid
                return $response;
            } else {
                // If API is not responding properly, fallback to direct database query
                return $this->fallbackToDirectQuery($page, $limit, $status, $search);
            }
        } catch (Exception $e) {
            error_log("API fetch error: " . $e->getMessage());
            
            // Fallback to direct database query if API fails
            return $this->fallbackToDirectQuery($page, $limit, $status, $search);
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
    
    /**
     * Fallback to direct database query when API fails
     */
    private function fallbackToDirectQuery($page = 1, $limit = 20, $status = '', $search = '')
    {
        try {
            $whereConditions = ['deleted_at IS NULL'];
            $queryParams = [];
            
            if ($status) {
                $whereConditions[] = 'status = ?';
                $queryParams[] = $status;
            }
            
            if ($search) {
                $whereConditions[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ?)';
                $searchTerm = "%{$search}%";
                $queryParams[] = $searchTerm;
                $queryParams[] = $searchTerm;
                $queryParams[] = $searchTerm;
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM contacts {$whereClause}";
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->execute($queryParams);
            $totalContacts = $countStmt->fetchColumn();
            
            // Get contacts
            $offset = ($page - 1) * $limit;
            $query = "SELECT id, name, email, phone, subject, message, category, status, priority, submitted_at, is_read 
                     FROM contacts 
                     {$whereClause} 
                     ORDER BY submitted_at DESC 
                     LIMIT {$limit} OFFSET {$offset}";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($queryParams);
            $contacts = $stmt->fetchAll();
            
            // Calculate pagination
            $totalPages = ceil($totalContacts / $limit);
            
            return [
                'data' => $contacts,
                'pagination' => [
                    'current_page' => (int)$page,
                    'total_pages' => $totalPages,
                    'total_items' => (int)$totalContacts,
                    'per_page' => (int)$limit,
                    'has_previous' => $page > 1,
                    'has_next' => $page < $totalPages
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Fallback database query error: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error loading contacts from database.');
            
            return [
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'total_pages' => 0,
                    'total_items' => 0,
                    'per_page' => $limit,
                    'has_previous' => false,
                    'has_next' => false
                ]
            ];
        }
    }
}
