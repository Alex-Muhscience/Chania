<?php
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

class ApplicationsController extends BaseController {
    private $apiBaseUrl;
    
    public function __construct() {
        parent::__construct();
        $this->apiBaseUrl = BASE_URL . '/api/v1';
        $this->setPageTitle('Applications Management');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Applications Management']
        ]);
    }

    public function index() {
        // CRITICAL SECURITY CHECK - Check permissions
        if (!$this->hasPermission('applications') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to manage applications.');
            $this->redirect('/admin/public/index.php');
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
        $status = $_GET['status'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = $_GET['limit'] ?? 10;

        // Get applications data via API
        $applications = $this->fetchApplicationsFromApi($page, $limit, $status, $search);

        $this->renderView(__DIR__ . '/../views/applications/index.php', [
            'applications' => $applications['data'] ?? [],
            'search' => $search,
            'status' => $status,
            'pagination' => $applications['pagination'] ?? [
                'page' => 1,
                'totalPages' => 0,
                'totalApplications' => 0,
                'limit' => $limit
            ],
            'filters' => [
                'page' => $page,
                'status' => $status,
                'search' => $search
            ]
        ]);
    }

    private function handleActions() {
        $action = $_POST['action'] ?? '';
        $applicationId = $_POST['application_id'] ?? '';

        try {
            switch ($action) {
                case 'update_status':
                    if ($applicationId) {
                        $status = $_POST['status'] ?? '';
                        $stmt = $this->db->prepare("UPDATE applications SET status = ? WHERE id = ?");
                        $stmt->execute([$status, $applicationId]);
                        $this->setSuccess("Application status updated successfully.");
                    }
                    break;

                case 'delete':
                    if ($applicationId) {
                        $stmt = $this->db->prepare("DELETE FROM applications WHERE id = ?");
                        $stmt->execute([$applicationId]);
                        $this->setSuccess("Application deleted successfully.");
                    }
                    break;
            }

            $this->redirect($_SERVER['PHP_SELF']);

        } catch (PDOException $e) {
            error_log("Application management error: " . $e->getMessage());
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
        $applicationId = $_POST['id'] ?? $_POST['application_id'] ?? '';
        
        if (!$applicationId) {
            echo json_encode(['success' => false, 'message' => 'Application ID is required']);
            return;
        }
        
        try {
            switch ($action) {
                case 'update_status':
                    $status = $_POST['status'] ?? '';
                    $result = $this->callApi('PUT', "/applications/{$applicationId}", ['status' => $status]);
                    break;
                    
                case 'delete':
                    $result = $this->callApi('DELETE', "/applications/{$applicationId}");
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
     * Fetch applications from API
     */
    private function fetchApplicationsFromApi($page = 1, $limit = 10, $status = '', $search = '')
    {
        $params = [
            'page' => $page,
            'limit' => $limit
        ];
        
        if ($status) $params['status'] = $status;
        if ($search) $params['search'] = $search;
        
        $queryString = http_build_query($params);
        
        try {
            $response = $this->callApi('GET', "/applications?{$queryString}");
            
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
    private function fallbackToDirectQuery($page = 1, $limit = 10, $status = '', $search = '')
    {
        $offset = ($page - 1) * $limit;
        
        $whereClauses = [];
        $parameters = [];
        
        if ($status) {
            $whereClauses[] = 'a.status = ?';
            $parameters[] = $status;
        }

        if ($search) {
            $whereClauses[] = '(a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ? OR a.application_number LIKE ?)';
            $parameters[] = "%{$search}%";
            $parameters[] = "%{$search}%";
            $parameters[] = "%{$search}%";
            $parameters[] = "%{$search}%";
        }
        
        $where = '';
        if (!empty($whereClauses)) {
            $where = 'WHERE ' . implode(' AND ', $whereClauses);
        }

        $countQuery = "SELECT COUNT(*) as totalApplications FROM applications a LEFT JOIN programs p ON a.program_id = p.id {$where}";
        $stmt = $this->db->prepare($countQuery);
        $stmt->execute($parameters);
        $totalApplications = $stmt->fetchColumn();

        $query = "
            SELECT 
                a.id, a.application_number, a.first_name, a.last_name, a.email, a.phone,
                a.status, a.priority, a.submitted_at, a.reviewed_at, a.payment_status,
                p.title as program_title, p.category as program_category,
                CONCAT(a.first_name, ' ', a.last_name) as full_name
            FROM applications a
            LEFT JOIN programs p ON a.program_id = p.id
            {$where} 
            ORDER BY a.submitted_at DESC 
            LIMIT {$limit} OFFSET {$offset}
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute($parameters);
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $applications,
            'pagination' => [
                'page' => $page,
                'totalPages' => ceil($totalApplications / $limit),
                'totalApplications' => $totalApplications,
                'limit' => $limit
            ]
        ];
    }
}
?>
