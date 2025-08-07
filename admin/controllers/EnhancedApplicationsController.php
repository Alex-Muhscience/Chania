<?php
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

class EnhancedApplicationsController extends BaseController {
    private $apiBaseUrl;
    
    public function __construct() {
        parent::__construct();
        $this->apiBaseUrl = BASE_URL . '/api/v1';
        $this->setPageTitle('Applications Management Dashboard');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Enhanced Applications Management']
        ]);
    }

    public function dashboard() {
        // CRITICAL SECURITY CHECK - Check permissions
        if (!$this->hasPermission('applications') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to manage applications.');
            $this->redirect('/admin/public/index.php');
            exit;
        }

        // Get dashboard data
        $stats = $this->getDashboardStats();
        $chartData = $this->getChartData();
        $recentApplications = $this->getRecentApplications();
        $cohortData = $this->getCohortData();

        $this->render('applications/dashboard', [
            'stats' => $stats,
            'chartData' => $chartData,
            'recentApplications' => $recentApplications,
            'cohortData' => $cohortData
        ]);
    }

    public function index() {
        // CRITICAL SECURITY CHECK - Check permissions
        if (!$this->hasPermission('applications') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to manage applications.');
            $this->redirect('/admin/public/index.php');
            exit;
        }

        // Debug logging
        error_log("Enhanced Applications Controller - Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
        error_log("Enhanced Applications Controller - POST data: " . json_encode($_POST));
        
        // Handle AJAX actions via API
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            error_log("POST request detected with action: " . ($_POST['action'] ?? 'NONE'));
            if ($_POST['action'] === 'assign_cohort') {
                $this->handleAssignCohort();
            } elseif ($_POST['action'] === 'bulk_assign') {
                $this->handleBulkAssign();
            } elseif ($_POST['action'] === 'bulk_approve') {
                $this->handleBulkApprove();
            } elseif ($_POST['action'] === 'bulk_reject') {
                $this->handleBulkReject();
            } else {
                $this->handleApiActions();
            }
            return;
        }

        // Get filters and pagination
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $cohort = $_GET['cohort'] ?? '';
        $program = $_GET['program_id'] ?? '';
        $delivery_mode = $_GET['delivery_mode'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = $_GET['limit'] ?? 20;
        $sort = $_GET['sort'] ?? 'submitted_at';
        $order = $_GET['order'] ?? 'DESC';

        // Get applications data
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

    public function export() {
        // CRITICAL SECURITY CHECK - Check permissions
        if (!$this->hasPermission('applications') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to export applications.');
            $this->redirect('/admin/public/index.php');
            exit;
        }

        $format = $_GET['format'] ?? 'csv';
        $filters = $this->getExportFilters();
        
        $applications = $this->getApplicationsForExport($filters);

        if ($format === 'csv') {
            $this->exportToCSV($applications);
        } elseif ($format === 'pdf') {
            $this->exportToPDF($applications);
        } else {
            $this->setFlashMessage('error', 'Invalid export format.');
            $this->redirect('/admin/public/applications.php');
        }
    }

    private function getDashboardStats() {
        try {
            $stats = [];

            // Total applications
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM applications");
            $stmt->execute();
            $stats['total_applications'] = $stmt->fetchColumn();

            // Applications by status
            $stmt = $this->db->prepare("
                SELECT status, COUNT(*) as count 
                FROM applications 
                GROUP BY status
            ");
            $stmt->execute();
            $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($statusCounts as $status) {
                $stats['status_' . $status['status']] = $status['count'];
            }

            // Applications this month
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM applications 
                WHERE YEAR(submitted_at) = YEAR(CURDATE()) 
                AND MONTH(submitted_at) = MONTH(CURDATE())
            ");
            $stmt->execute();
            $stats['applications_this_month'] = $stmt->fetchColumn();

            // Applications with assigned cohorts
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM applications WHERE schedule_id IS NOT NULL");
            $stmt->execute();
            $stats['applications_with_cohorts'] = $stmt->fetchColumn();

            // Popular programs (top 5)
            $stmt = $this->db->prepare("
                SELECT p.title, COUNT(a.id) as application_count
                FROM programs p
                LEFT JOIN applications a ON p.id = a.program_id
                WHERE p.is_active = 1
                GROUP BY p.id, p.title
                ORDER BY application_count DESC
                LIMIT 5
            ");
            $stmt->execute();
            $stats['popular_programs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $stats;

        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            return [];
        }
    }

    private function getChartData() {
        try {
            $chartData = [];

            // Applications over time (last 12 months)
            $stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(submitted_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM applications 
                WHERE submitted_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(submitted_at, '%Y-%m')
                ORDER BY month
            ");
            $stmt->execute();
            $chartData['applications_over_time'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Status distribution
            $stmt = $this->db->prepare("
                SELECT status, COUNT(*) as count 
                FROM applications 
                GROUP BY status
            ");
            $stmt->execute();
            $chartData['status_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Program popularity
            $stmt = $this->db->prepare("
                SELECT p.title as program, COUNT(a.id) as count
                FROM programs p
                LEFT JOIN applications a ON p.id = a.program_id
                WHERE p.is_active = 1
                GROUP BY p.id, p.title
                ORDER BY count DESC
                LIMIT 10
            ");
            $stmt->execute();
            $chartData['program_popularity'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Delivery mode preferences
            $stmt = $this->db->prepare("
                SELECT preferred_delivery_mode, COUNT(*) as count 
                FROM applications 
                GROUP BY preferred_delivery_mode
            ");
            $stmt->execute();
            $chartData['delivery_mode_preferences'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $chartData;

        } catch (Exception $e) {
            error_log("Chart data error: " . $e->getMessage());
            return [];
        }
    }

    private function getRecentApplications($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    a.id, a.first_name, a.last_name, a.email, a.status, a.submitted_at,
                    p.title as program_title,
                    s.title as schedule_title,
                    CONCAT(a.first_name, ' ', a.last_name) as full_name
                FROM applications a
                LEFT JOIN programs p ON a.program_id = p.id
                LEFT JOIN program_schedules s ON a.schedule_id = s.id
                ORDER BY a.submitted_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Recent applications error: " . $e->getMessage());
            return [];
        }
    }

    private function getCohortData() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    s.id, s.title, s.start_date, s.end_date, s.max_participants,
                    p.title as program_title,
                    COUNT(a.id) as current_participants,
                    (s.max_participants - COUNT(a.id)) as available_spots
                FROM program_schedules s
                LEFT JOIN programs p ON s.program_id = p.id
                LEFT JOIN applications a ON s.id = a.schedule_id AND a.status = 'approved'
                WHERE s.is_active = 1 AND s.start_date >= CURDATE()
                GROUP BY s.id, s.title, s.start_date, s.end_date, s.max_participants, p.title
                ORDER BY s.start_date ASC
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Cohort data error: " . $e->getMessage());
            return [];
        }
    }

    private function fetchApplicationsFromDatabase($page = 1, $limit = 20, $status = '', $search = '', $cohort = '', $program = '', $delivery_mode = '', $sort = 'submitted_at', $order = 'DESC') {
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
            $whereClauses[] = 'a.schedule_id = ?';
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

        // Get total count
        $countQuery = "SELECT COUNT(*) as totalApplications FROM applications a LEFT JOIN programs p ON a.program_id = p.id {$where}";
        $stmt = $this->db->prepare($countQuery);
        $stmt->execute($parameters);
        $totalApplications = $stmt->fetchColumn();

        // Validate sort column
        $allowedSorts = ['id', 'first_name', 'last_name', 'email', 'status', 'submitted_at', 'program_title', 'schedule_title'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'submitted_at';
        }

        // Validate order
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        // Get applications
        $query = "
            SELECT 
                a.id, a.application_number, a.first_name, a.last_name, a.email, a.phone,
                a.status, a.priority, a.submitted_at, a.reviewed_at, a.payment_status,
                a.preferred_delivery_mode, a.schedule_id, a.program_id,
                p.title as program_title, p.category as program_category,
                s.title as schedule_title, s.start_date as schedule_start_date, s.end_date as schedule_end_date,
                s.online_fee, s.physical_fee, s.max_participants,
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

    private function handleAssignCohort() {
        header('Content-Type: application/json');
        
        // Debug logging
        error_log("handleAssignCohort called with POST data: " . json_encode($_POST));
        
        $applicationId = $_POST['application_id'] ?? '';
        $scheduleId = $_POST['schedule_id'] ?? '';
        $deliveryMode = $_POST['preferred_delivery_mode'] ?? 'online';
        
        if (!$applicationId || !$scheduleId) {
            echo json_encode(['success' => false, 'message' => 'Application ID and Schedule ID are required']);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE applications 
                SET schedule_id = ?, preferred_delivery_mode = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $result = $stmt->execute([$scheduleId, $deliveryMode, $applicationId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Application assigned to cohort successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to assign cohort']);
            }
            
        } catch (Exception $e) {
            error_log("Cohort assignment error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error occurred']);
        }
    }

    private function handleBulkAssign() {
        header('Content-Type: application/json');
        
        $applicationIds = $_POST['ids'] ?? $_POST['application_ids'] ?? [];
        $scheduleId = $_POST['schedule_id'] ?? '';
        $deliveryMode = $_POST['preferred_delivery_mode'] ?? 'online';
        
        if (empty($applicationIds) || !$scheduleId) {
            echo json_encode(['success' => false, 'message' => 'Application IDs and Schedule ID are required']);
            return;
        }
        
        try {
            $placeholders = str_repeat('?,', count($applicationIds) - 1) . '?';
            $stmt = $this->db->prepare("
                UPDATE applications 
                SET schedule_id = ?, preferred_delivery_mode = ?, updated_at = NOW() 
                WHERE id IN ($placeholders)
            ");
            $params = array_merge([$scheduleId, $deliveryMode], $applicationIds);
            $result = $stmt->execute($params);
            
            if ($result) {
                // Log the admin action
                $this->logAdminAction('bulk_assign_cohort', 'applications', null, ['count' => count($applicationIds), 'ids' => $applicationIds, 'schedule_id' => $scheduleId]);
                
                echo json_encode(['success' => true, 'message' => count($applicationIds) . ' applications assigned to cohort successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to assign cohorts']);
            }
            
        } catch (Exception $e) {
            error_log("Bulk cohort assignment error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error occurred: ' . $e->getMessage()]);
        }
    }

    private function handleBulkApprove() {
        header('Content-Type: application/json');
        
        $applicationIds = $_POST['ids'] ?? [];
        
        if (empty($applicationIds) || !is_array($applicationIds)) {
            echo json_encode(['success' => false, 'message' => 'Application IDs are required']);
            return;
        }
        
        try {
            // Get current user ID for reviewed_by field - must not be null due to trigger
            $reviewedBy = $_SESSION['user_id'] ?? null;
            
            if (!$reviewedBy) {
                echo json_encode(['success' => false, 'message' => 'User session invalid. Please log in again.']);
                return;
            }
            
            $placeholders = str_repeat('?,', count($applicationIds) - 1) . '?';
            $stmt = $this->db->prepare("
                UPDATE applications 
                SET status = 'approved', reviewed_by = ?, reviewed_at = NOW(), updated_at = NOW() 
                WHERE id IN ($placeholders)
            ");
            
            // Merge reviewed_by with application IDs
            $params = array_merge([$reviewedBy], $applicationIds);
            $result = $stmt->execute($params);
            
            if ($result) {
                // Log the admin action
                $this->logAdminAction('bulk_approve', 'applications', null, ['count' => count($applicationIds), 'ids' => $applicationIds]);
                
                echo json_encode(['success' => true, 'message' => count($applicationIds) . ' applications approved successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to approve applications']);
            }
            
        } catch (Exception $e) {
            error_log("Bulk approve error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error occurred: ' . $e->getMessage()]);
        }
    }

    private function handleBulkReject() {
        header('Content-Type: application/json');
        
        $applicationIds = $_POST['ids'] ?? [];
        
        if (empty($applicationIds) || !is_array($applicationIds)) {
            echo json_encode(['success' => false, 'message' => 'Application IDs are required']);
            return;
        }
        
        try {
            // Get current user ID for reviewed_by field - must not be null due to trigger
            $reviewedBy = $_SESSION['user_id'] ?? null;
            
            if (!$reviewedBy) {
                echo json_encode(['success' => false, 'message' => 'User session invalid. Please log in again.']);
                return;
            }
            
            $placeholders = str_repeat('?,', count($applicationIds) - 1) . '?';
            $stmt = $this->db->prepare("
                UPDATE applications 
                SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), updated_at = NOW() 
                WHERE id IN ($placeholders)
            ");
            
            // Merge reviewed_by with application IDs
            $params = array_merge([$reviewedBy], $applicationIds);
            $result = $stmt->execute($params);
            
            if ($result) {
                // Log the admin action
                $this->logAdminAction('bulk_reject', 'applications', null, ['count' => count($applicationIds), 'ids' => $applicationIds]);
                
                echo json_encode(['success' => true, 'message' => count($applicationIds) . ' applications rejected successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to reject applications']);
            }
            
        } catch (Exception $e) {
            error_log("Bulk reject error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error occurred: ' . $e->getMessage()]);
        }
    }

    private function handleApiActions() {
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
                    $stmt = $this->db->prepare("UPDATE applications SET status = ?, updated_at = NOW() WHERE id = ?");
                    $result = $stmt->execute([$status, $applicationId]);
                    break;
                    
                case 'delete':
                    $stmt = $this->db->prepare("DELETE FROM applications WHERE id = ?");
                    $result = $stmt->execute([$applicationId]);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    return;
            }
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Action completed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to complete action']);
            }
            
        } catch (Exception $e) {
            error_log("API action error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error occurred']);
        }
    }

    private function getExportFilters() {
        return [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'cohort' => $_GET['cohort'] ?? '',
            'program' => $_GET['program_id'] ?? '',
            'delivery_mode' => $_GET['delivery_mode'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];
    }

    private function getApplicationsForExport($filters) {
        $whereClauses = [];
        $parameters = [];
        
        if ($filters['status']) {
            $whereClauses[] = 'a.status = ?';
            $parameters[] = $filters['status'];
        }

        if ($filters['search']) {
            $whereClauses[] = '(a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ?)';
            $parameters[] = "%{$filters['search']}%";
            $parameters[] = "%{$filters['search']}%";
            $parameters[] = "%{$filters['search']}%";
        }

        if ($filters['cohort']) {
            $whereClauses[] = 'a.schedule_id = ?';
            $parameters[] = $filters['cohort'];
        }

        if ($filters['program']) {
            $whereClauses[] = 'a.program_id = ?';
            $parameters[] = $filters['program'];
        }

        if ($filters['delivery_mode']) {
            $whereClauses[] = 'a.preferred_delivery_mode = ?';
            $parameters[] = $filters['delivery_mode'];
        }

        if ($filters['start_date']) {
            $whereClauses[] = 'a.submitted_at >= ?';
            $parameters[] = $filters['start_date'];
        }

        if ($filters['end_date']) {
            $whereClauses[] = 'a.submitted_at <= ?';
            $parameters[] = $filters['end_date'] . ' 23:59:59';
        }
        
        $where = '';
        if (!empty($whereClauses)) {
            $where = 'WHERE ' . implode(' AND ', $whereClauses);
        }

        $query = "
            SELECT 
                a.*, p.title as program_title, p.category as program_category,
                s.title as schedule_title, s.start_date as schedule_start_date, 
                s.end_date as schedule_end_date, s.online_fee, s.physical_fee,
                CONCAT(a.first_name, ' ', a.last_name) as full_name
            FROM applications a
            LEFT JOIN programs p ON a.program_id = p.id
            LEFT JOIN program_schedules s ON a.schedule_id = s.id
            {$where}
            ORDER BY a.submitted_at DESC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($parameters);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function exportToCSV($applications) {
        $filename = 'applications_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        
        $output = fopen('php://output', 'w');
        
        // CSV Header
        fputcsv($output, [
            'ID', 'Application Number', 'Full Name', 'Email', 'Phone', 'Status',
            'Program', 'Program Category', 'Schedule/Cohort', 'Schedule Start', 'Schedule End',
            'Delivery Mode', 'Online Fee', 'Physical Fee', 'Priority', 'Payment Status',
            'Submitted Date', 'Reviewed Date', 'Last Updated'
        ]);
        
        // CSV Data
        foreach ($applications as $app) {
            fputcsv($output, [
                $app['id'],
                $app['application_number'] ?? '',
                $app['full_name'],
                $app['email'],
                $app['phone'] ?? '',
                ucfirst($app['status']),
                $app['program_title'] ?? '',
                $app['program_category'] ?? '',
                $app['schedule_title'] ?? 'Not Assigned',
                $app['schedule_start_date'] ?? '',
                $app['schedule_end_date'] ?? '',
                ucfirst($app['preferred_delivery_mode'] ?? 'online'),
                $app['online_fee'] ?? '',
                $app['physical_fee'] ?? '',
                $app['priority'] ?? 'normal',
                ucfirst($app['payment_status'] ?? 'pending'),
                $app['submitted_at'] ?? '',
                $app['reviewed_at'] ?? '',
                $app['updated_at'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }

    private function exportToPDF($applications) {
        require_once __DIR__ . '/../../vendor/autoload.php'; // Assuming TCPDF is installed
        
        // For now, create a simple HTML-based PDF export
        $filename = 'applications_export_' . date('Y-m-d_H-i-s') . '.pdf';
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Applications Export</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .header { text-align: center; margin-bottom: 20px; }
                .summary { margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Applications Export Report</h1>
                <p>Generated on: <?= date('Y-m-d H:i:s') ?></p>
                <p>Total Applications: <?= count($applications) ?></p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Program</th>
                        <th>Cohort</th>
                        <th>Status</th>
                        <th>Delivery Mode</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= $app['id'] ?></td>
                        <td><?= htmlspecialchars($app['full_name']) ?></td>
                        <td><?= htmlspecialchars($app['email']) ?></td>
                        <td><?= htmlspecialchars($app['program_title'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($app['schedule_title'] ?? 'Not Assigned') ?></td>
                        <td><?= ucfirst($app['status']) ?></td>
                        <td><?= ucfirst($app['preferred_delivery_mode'] ?? 'online') ?></td>
                        <td><?= date('Y-m-d', strtotime($app['submitted_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
        
        $html = ob_get_clean();
        
        // For a production environment, use TCPDF or similar library
        // For now, we'll return the HTML content as PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo $html;
        exit;
    }
}
?>
