<?php

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/Blog.php';

class BlogController extends BaseController
{
    private $blogModel;

    public function __construct()
    {
        parent::__construct();
        $this->blogModel = new Blog($this->db);
    }

    /**
     * Display blog posts listing page
     */
    public function index()
    {
        // Check permissions
        if (!$this->hasPermission('blog') && !$this->hasPermission('*')) {
            $this->redirect(BASE_URL . '/admin/', 'Access denied. You do not have permission to manage blog posts.');
        }

        // Handle actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }

        // Get filter parameters
        $filters = $this->getFilters();
        
        // Get blog posts data
        $data = [
            'posts' => $this->blogModel->getAll($filters['limit'], $filters['offset'], $filters['category'], $filters['status'], $filters['search']),
            'totalPosts' => $this->blogModel->getTotalCount($filters['category'], $filters['status'], $filters['search']),
            'categories' => $this->blogModel->getCategories(),
            'currentPage' => $filters['page'],
            'totalPages' => ceil($this->blogModel->getTotalCount($filters['category'], $filters['status'], $filters['search']) / $filters['limit']),
            'filters' => $filters,
            'pageTitle' => 'Blog Management',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Blog']
            ]
        ];

        $this->render('blog/index', $data);
    }

    /**
     * Handle blog post actions (publish, unpublish, feature, unfeature)
     */
    private function handleActions()
    {
        if (!isset($_POST['action'], $_POST['id'])) {
            return;
        }

        $action = $_POST['action'];
        $postId = (int)$_POST['id'];

        try {
            switch ($action) {
                case 'publish':
                    $this->blogModel->publish($postId);
                    $this->setFlashMessage('success', 'Post published successfully.');
                    break;
                    
                case 'unpublish':
                    $this->blogModel->unpublish($postId);
                    $this->setFlashMessage('success', 'Post unpublished successfully.');
                    break;
                    
                case 'feature':
                    $this->blogModel->setFeatured($postId, true);
                    $this->setFlashMessage('success', 'Post set as featured.');
                    break;
                    
                case 'unfeature':
                    $this->blogModel->setFeatured($postId, false);
                    $this->setFlashMessage('success', 'Post removed from featured.');
                    break;
                    
                default:
                    $this->setFlashMessage('error', 'Invalid action.');
                    break;
            }
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error performing action: ' . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/blog.php');
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
        $status = trim($_GET['status'] ?? '');
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
     * Check if user has permission for blog management
     */
    private function checkPermission($permission)
    {
        return $this->hasPermission($permission) || $this->hasPermission('*');
    }
}
