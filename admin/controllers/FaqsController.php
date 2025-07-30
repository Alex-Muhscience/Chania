<?php

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/Faq.php';

class FaqsController extends BaseController
{
    private $faqModel;

    public function __construct()
    {
        parent::__construct();
        $this->faqModel = new Faq($this->db);
    }

    /**
     * Display FAQs listing page
     */
    public function index()
    {
        // Check permissions
        if (!$this->hasPermission('faqs') && !$this->hasPermission('*')) {
            $this->redirect(BASE_URL . '/admin/', 'Access denied. You do not have permission to manage FAQs.');
        }

        // Handle actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }

        // Get filter parameters
        $filters = $this->getFilters();
        
        // Get FAQs data
        $data = [
            'faqs' => $this->faqModel->getAll($filters['limit'], $filters['offset'], $filters['category'], $filters['status'], $filters['search']),
            'totalFaqs' => $this->faqModel->getTotalCount($filters['category'], $filters['status'], $filters['search']),
            'categories' => $this->faqModel->getCategories(),
            'currentPage' => $filters['page'],
            'totalPages' => ceil($this->faqModel->getTotalCount($filters['category'], $filters['status'], $filters['search']) / $filters['limit']),
            'filters' => $filters,
            'pageTitle' => 'FAQ Management',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'FAQs']
            ]
        ];

        $this->render('faqs/index', $data);
    }

    /**
     * Handle FAQ actions (activate/deactivate)
     */
    private function handleActions()
    {
        if (!isset($_POST['action'], $_POST['id'])) {
            return;
        }

        $action = $_POST['action'];
        $faqId = (int)$_POST['id'];

        try {
            switch ($action) {
                case 'activate':
                    $this->faqModel->activate($faqId);
                    $this->setFlashMessage('success', 'FAQ activated successfully.');
                    break;
                    
                case 'deactivate':
                    $this->faqModel->deactivate($faqId);
                    $this->setFlashMessage('success', 'FAQ deactivated successfully.');
                    break;
                    
                default:
                    $this->setFlashMessage('error', 'Invalid action.');
                    break;
            }
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error performing action: ' . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/faqs.php');
    }

    /**
     * Get and validate filter parameters
     */
    private function getFilters()
    {
        $limit = 20;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        $category = trim($_GET['category'] ?? '');
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null;
        $search = trim($_GET['search'] ?? '');

        return [
            'limit' => $limit,
            'page' => $page,
            'offset' => $offset,
            'category' => $category,
            'status' => $status,
            'search' => $search
        ];
    }

    /**
     * Check if user has permission for FAQ management
     */
    private function checkPermission($permission)
    {
        return $this->hasPermission($permission) || $this->hasPermission('*');
    }
}
