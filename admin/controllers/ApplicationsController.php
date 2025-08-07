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

        // Handle AJAX actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'assign_cohort') {
                $this->handleAssignCohort();
            } elseif ($_POST['action'] === 'bulk_assign') {
                $this->handleBulkAssign();
            } else {
                $this->handleDirectActions();
            }
            return;
        }

        // Handle legacy POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }

        // Get filters and pagination
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $cohort = $_GET['cohort'] ?? '';
        $program = $_GET['program_id'] ?? '';
        $delivery_mode = $_GET['delivery_mode'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = $_GET['limit'] ?? 10;
        $sort = $_GET['sort'] ?? 'submitted_at';
        $order = $_GET['order'] ?? 'DESC';

        // Get applications data directly from database
        $applications = $this->fetchApplicationsFromDatabase($page, $limit, $status, $search, $cohort, $program, $delivery_mode, $sort, $order);
        
        // Get filter options
        $programs = $this->getPrograms();
        $schedules = $this->getSchedules();

        $this->render('applications/index', [
            'applications' => $applications['data'] ?? [],
            'search' => $search,
            'status' => $status,
            'cohort' => $cohort,
            'program' => $program,
            'delivery_mode' => $delivery_mode,
            'sort' => $sort,
            'order' => $order,
            'programs' => $programs,
            'schedules' => $schedules,
            'pagination' => $applications['pagination'] ?? [
                'page' => 1,
                'totalPages' => 0,
                'totalApplications' => 0,
                'limit' => $limit
            ],
            'filters' => [
                'page' => $page,
                'status' => $status,
                'search' => $search,
                'cohort' => $cohort,
                'program_id' => $program,
                'delivery_mode' => $delivery_mode
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
     * Handle cohort assignment
     */
    private function handleAssignCohort()
    {
        header('Content-Type: application/json');
        
        $applicationId = $_POST['application_id'] ?? '';
        $scheduleId = $_POST['schedule_id'] ?? '';
        $deliveryMode = $_POST['preferred_delivery_mode'] ?? 'online';
        
        if (!$applicationId || !$scheduleId) {
            echo json_encode(['success' => false, 'message' => 'Application ID and Schedule ID are required']);
            return;
        }
        
        try {
            $result = $this->callApi('PUT', "/applications/{$applicationId}", [
                'schedule_id' => $scheduleId,
                'preferred_delivery_mode' => $deliveryMode
            ]);
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            error_log("Cohort assignment error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error occurred']);
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
            if (isset($response['status']) && $response['status'] === 'success' && isset($response['data'])) {
                // The API returns data nested under 'data', so we need to extract it properly
                return [
                    'data' => $response['data']['data'] ?? [],
                    'pagination' => [
                        'page' => $response['data']['pagination']['page'] ?? $page,
                        'totalPages' => $response['data']['pagination']['pages'] ?? 1,
                        'totalApplications' => $response['data']['pagination']['total'] ?? 0,
                        'limit' => $response['data']['pagination']['limit'] ?? $limit
                    ]
                ];
            } elseif (isset($response['data'])) {
                // If no 'status' key but has 'data', assume it's valid
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
     * Fetch applications directly from database with comprehensive filtering
     */
    private function fetchApplicationsFromDatabase($page = 1, $limit = 10, $status = '', $search = '', $cohort = '', $program = '', $delivery_mode = '', $sort = 'submitted_at', $order = 'DESC')
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
        
        if ($cohort) {
            $whereClauses[] = 's.id = ?';
            $parameters[] = $cohort;
        }
        
        if ($program) {
            $whereClauses[] = 'a.program_id = ?';
            $parameters[] = $program;
        }
        
        if ($delivery_mode) {
            $whereClauses[] = 'a.preferred_delivery_mode = ?';
            $parameters[] = $delivery_mode;
        }
        
        $where = '';
        if (!empty($whereClauses)) {
            $where = 'WHERE ' . implode(' AND ', $whereClauses);
        }

        // Validate sort field to prevent SQL injection
        $validSortFields = ['submitted_at', 'first_name', 'last_name', 'email', 'status', 'priority', 'program_title'];
        if (!in_array($sort, $validSortFields)) {
            $sort = 'submitted_at';
        }
        
        $validOrders = ['ASC', 'DESC'];
        if (!in_array(strtoupper($order), $validOrders)) {
            $order = 'DESC';
        }

        $countQuery = "SELECT COUNT(*) as totalApplications FROM applications a LEFT JOIN programs p ON a.program_id = p.id LEFT JOIN program_schedules s ON a.schedule_id = s.id {$where}";
        $stmt = $this->db->prepare($countQuery);
        $stmt->execute($parameters);
        $totalApplications = $stmt->fetchColumn();

        $query = "
            SELECT 
                a.id, a.application_number, a.first_name, a.last_name, a.email, a.phone,
                a.status, a.priority, a.submitted_at, a.reviewed_at, a.payment_status,
                a.preferred_delivery_mode, a.schedule_id, a.program_id,
                p.title as program_title, p.category as program_category,
                s.title as schedule_title, s.start_date as schedule_start_date, s.end_date as schedule_end_date,
                CONCAT(a.first_name, ' ', a.last_name) as full_name
            FROM applications a
            LEFT JOIN programs p ON a.program_id = p.id
            LEFT JOIN program_schedules s ON a.schedule_id = s.id
            {$where} 
            ORDER BY {$sort} {$order}
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
    
    /**
     * Handle direct database actions (non-API)
     */
    private function handleDirectActions()
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
                    $stmt = $this->db->prepare("UPDATE applications SET status = ?, reviewed_at = NOW() WHERE id = ?");
                    $success = $stmt->execute([$status, $applicationId]);
                    
                    if ($success) {
                        echo json_encode(['success' => true, 'message' => 'Application status updated successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update application status']);
                    }
                    break;
                    
                case 'delete':
                    $stmt = $this->db->prepare("DELETE FROM applications WHERE id = ?");
                    $success = $stmt->execute([$applicationId]);
                    
                    if ($success) {
                        echo json_encode(['success' => true, 'message' => 'Application deleted successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to delete application']);
                    }
                    break;
                    
                case 'bulk_approve':
                    $ids = $_POST['ids'] ?? [];
                    if (!is_array($ids) || empty($ids)) {
                        echo json_encode(['success' => false, 'message' => 'No applications selected']);
                        return;
                    }
                    
                    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                    $stmt = $this->db->prepare("UPDATE applications SET status = 'approved', reviewed_at = NOW() WHERE id IN ({$placeholders})");
                    $success = $stmt->execute($ids);
                    
                    if ($success) {
                        echo json_encode(['success' => true, 'message' => count($ids) . ' applications approved successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to approve applications']);
                    }
                    break;
                    
                case 'bulk_reject':
                    $ids = $_POST['ids'] ?? [];
                    if (!is_array($ids) || empty($ids)) {
                        echo json_encode(['success' => false, 'message' => 'No applications selected']);
                        return;
                    }
                    
                    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                    $stmt = $this->db->prepare("UPDATE applications SET status = 'rejected', reviewed_at = NOW() WHERE id IN ({$placeholders})");
                    $success = $stmt->execute($ids);
                    
                    if ($success) {
                        echo json_encode(['success' => true, 'message' => count($ids) . ' applications rejected successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to reject applications']);
                    }
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    return;
            }
            
        } catch (Exception $e) {
            error_log("Direct action error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error occurred: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Handle bulk cohort assignment
     */
    private function handleBulkAssign()
    {
        header('Content-Type: application/json');
        
        $ids = $_POST['ids'] ?? [];
        $scheduleId = $_POST['schedule_id'] ?? '';
        $deliveryMode = $_POST['preferred_delivery_mode'] ?? 'online';
        
        if (!is_array($ids) || empty($ids) || !$scheduleId) {
            echo json_encode(['success' => false, 'message' => 'Application IDs and Schedule ID are required']);
            return;
        }
        
        try {
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $params = array_merge($ids, [$scheduleId, $deliveryMode]);
            
            $stmt = $this->db->prepare("
                UPDATE applications 
                SET schedule_id = ?, preferred_delivery_mode = ? 
                WHERE id IN ({$placeholders})
            ");
            
            $stmt->execute(array_merge([$scheduleId, $deliveryMode], $ids));
            
            echo json_encode([
                'success' => true, 
                'message' => count($ids) . ' applications assigned to cohort successfully'
            ]);
            
        } catch (Exception $e) {
            error_log("Bulk assignment error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error occurred']);
        }
    }
    
    /**
     * Generic API call helper (kept for backward compatibility)
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
                a.preferred_delivery_mode, a.schedule_id, a.program_id,
                p.title as program_title, p.category as program_category,
                s.title as schedule_title, s.start_date as schedule_start_date, s.end_date as schedule_end_date,
                CONCAT(a.first_name, ' ', a.last_name) as full_name
            FROM applications a
            LEFT JOIN programs p ON a.program_id = p.id
            LEFT JOIN program_schedules s ON a.schedule_id = s.id
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
    
    private function getPrograms() {
        try {
            $stmt = $this->db->prepare("SELECT id, title FROM programs WHERE is_active = 1 ORDER BY title");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get programs error: " . $e->getMessage());
            return [];
        }
    }

    private function getSchedules() {
        try {
            $stmt = $this->db->prepare("
                SELECT s.id, s.title, s.start_date, s.end_date, s.program_id, p.title as program_title
                FROM program_schedules s
                LEFT JOIN programs p ON s.program_id = p.id
                WHERE s.is_active = 1
                ORDER BY s.start_date ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get schedules error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Export applications to CSV
     */
    public function exportCSV() {
        // Check permissions
        if (!$this->hasPermission('applications') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to export applications.');
            $this->redirect('/admin/public/index.php');
            exit;
        }
        
        // Get all applications without pagination
        $applications = $this->fetchApplicationsFromDatabase(1, PHP_INT_MAX);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="applications_export_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // CSV headers
        $headers = [
            'Application ID',
            'Application Number',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Program',
            'Schedule',
            'Delivery Mode',
            'Status',
            'Priority',
            'Payment Status',
            'Submitted At',
            'Reviewed At'
        ];
        
        fputcsv($output, $headers);
        
        // CSV data rows
        foreach ($applications['data'] as $app) {
            $row = [
                $app['id'],
                $app['application_number'],
                $app['first_name'],
                $app['last_name'],
                $app['email'],
                $app['phone'],
                $app['program_title'] ?? '',
                $app['schedule_title'] ?? '',
                $app['preferred_delivery_mode'],
                $app['status'],
                $app['priority'],
                $app['payment_status'],
                $app['submitted_at'],
                $app['reviewed_at'] ?? ''
            ];
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export applications to PDF
     */
    public function exportPDF() {
        // Check permissions
        if (!$this->hasPermission('applications') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to export applications.');
            $this->redirect('/admin/public/index.php');
            exit;
        }
        
        // Get all applications without pagination
        $applications = $this->fetchApplicationsFromDatabase(1, PHP_INT_MAX);
        
        // Generate HTML for PDF
        $html = $this->generatePDFHTML($applications['data']);
        
        // For now, output HTML directly - in production you'd use a PDF library like TCPDF or mPDF
        header('Content-Type: text/html');
        echo $html;
        exit;
    }
    
    /**
     * Generate HTML for PDF export
     */
    private function generatePDFHTML($applications) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Applications Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .meta { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Applications Export Report</h1>
        <div class="meta">Generated on: ' . date('Y-m-d H:i:s') . '</div>
        <div class="meta">Total Applications: ' . count($applications) . '</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Application Number</th>
                <th>Name</th>
                <th>Email</th>
                <th>Program</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($applications as $app) {
            $html .= '<tr>
                <td>' . htmlspecialchars($app['id']) . '</td>
                <td>' . htmlspecialchars($app['application_number']) . '</td>
                <td>' . htmlspecialchars($app['full_name']) . '</td>
                <td>' . htmlspecialchars($app['email']) . '</td>
                <td>' . htmlspecialchars($app['program_title'] ?? '') . '</td>
                <td>' . htmlspecialchars($app['status']) . '</td>
                <td>' . htmlspecialchars($app['submitted_at']) . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
    </table>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * View individual application details
     */
    public function view($id) {
        // Check permissions
        if (!$this->hasPermission('applications') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to view applications.');
            $this->redirect('/admin/public/index.php');
            exit;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    a.*, 
                    p.title as program_title, 
                    p.category as program_category,
                    s.title as schedule_title, 
                    s.start_date as schedule_start_date, 
                    s.end_date as schedule_end_date,
                    CONCAT(a.first_name, ' ', a.last_name) as full_name
                FROM applications a
                LEFT JOIN programs p ON a.program_id = p.id
                LEFT JOIN program_schedules s ON a.schedule_id = s.id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            $application = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$application) {
                $this->setFlashMessage('error', 'Application not found.');
                $this->redirect('/admin/controllers/ApplicationsController.php');
                exit;
            }
            
            $this->render('applications/view', [
                'application' => $application
            ]);
            
        } catch (Exception $e) {
            error_log("Application view error: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error loading application details.');
            $this->redirect('/admin/controllers/ApplicationsController.php');
        }
    }
}
?>
