<?php
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

class ApplicationsController extends BaseController {
    public function __construct() {
        parent::__construct();
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
            header('Location: index.php');
            exit;
        }

        // Handle POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }

        // Get filters and pagination
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $whereClause = '';
            $params = [];
            $conditions = [];

            if ($search) {
                $conditions[] = "(CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if ($status) {
                $conditions[] = "status = ?";
                $params[] = $status;
            }

            if (!empty($conditions)) {
                $whereClause = "WHERE " . implode(" AND ", $conditions);
            }

            // Get total count
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM applications $whereClause");
            $countStmt->execute($params);
            $totalApplications = $countStmt->fetchColumn();

            // Get applications
            $stmt = $this->db->prepare("SELECT a.*, p.title as program_title, CONCAT(a.first_name, ' ', a.last_name) as full_name FROM applications a LEFT JOIN programs p ON a.program_id = p.id $whereClause ORDER BY a.submitted_at DESC LIMIT $limit OFFSET $offset");
            $stmt->execute($params);
            $applications = $stmt->fetchAll();

            $totalPages = ceil($totalApplications / $limit);

            $this->renderView(__DIR__ . '/../views/applications/index.php', [
                'applications' => $applications,
                'search' => $search,
                'status' => $status,
                'pagination' => [
                    'page' => $page,
                    'totalPages' => $totalPages,
                    'totalApplications' => $totalApplications,
                    'limit' => $limit
                ]
            ]);

        } catch (PDOException $e) {
            error_log("Applications fetch error: " . $e->getMessage());
            $this->addError('Error loading applications.');
            $this->renderView(__DIR__ . '/../views/applications/index.php', [
                'applications' => [],
                'search' => $search,
                'status' => $status,
                'pagination' => [
                    'page' => 1,
                    'totalPages' => 0,
                    'totalApplications' => 0,
                    'limit' => $limit
                ]
            ]);
        }
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
}
?>
