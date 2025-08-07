<?php

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/ImpactBlog.php';

class ImpactBlogController extends BaseController
{
    private $impactBlogModel;

    public function __construct()
    {
        parent::__construct();
        $this->impactBlogModel = new ImpactBlog($this->db);
    }

    /**
     * Display impact blogs listing page
     */
    public function index()
    {
        // Check permissions
        if (!$this->hasPermission('impact_blogs') && !$this->hasPermission('*')) {
            $this->redirect(BASE_URL . '/admin/', 'Access denied. You do not have permission to manage impact blogs.');
        }

        // Handle actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }

        // Get filter parameters
        $filters = $this->getFilters();
        
        // Get impact blogs data
        $data = [
            'impactBlogs' => $this->impactBlogModel->getAll($filters['limit'], $filters['offset'], $filters['category'], $filters['status'], $filters['search']),
            'totalBlogs' => $this->impactBlogModel->getTotalCount($filters['category'], $filters['status'], $filters['search']),
            'categories' => $this->impactBlogModel->getCategories(),
            'stats' => $this->impactBlogModel->getStats(),
            'currentPage' => $filters['page'],
            'totalPages' => ceil($this->impactBlogModel->getTotalCount($filters['category'], $filters['status'], $filters['search']) / $filters['limit']),
            'filters' => $filters,
            'pageTitle' => 'Impact Blogs Management',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Impact Blogs']
            ]
        ];

        $this->render('impact_blogs/index', $data);
    }

    /**
     * Display add new impact blog page
     */
    public function add()
    {
        // Check permissions
        if (!$this->hasPermission('impact_blogs') && !$this->hasPermission('*')) {
            $this->redirect(BASE_URL . '/admin/public/impact_blogs.php', 'Access denied. You do not have permission to add impact blogs.');
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleAdd();
        }

        $data = [
            'pageTitle' => 'Add New Impact Blog',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Impact Blogs', 'url' => 'impact_blogs.php'],
                ['title' => 'Add New']
            ],
            'categories' => $this->getAvailableCategories(),
            'currentUser' => $_SESSION['username'] ?? 'Admin'
        ];

        $this->render('impact_blogs/add', $data);
    }

    /**
     * Display edit impact blog page
     */
    public function edit($id = null)
    {
        // Check permissions
        if (!$this->hasPermission('impact_blogs') && !$this->hasPermission('*')) {
            $this->redirect(BASE_URL . '/admin/public/impact_blogs.php', 'Access denied. You do not have permission to edit impact blogs.');
        }

        $id = $id ?? (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->redirect(BASE_URL . '/admin/public/impact_blogs.php', 'Invalid impact blog ID.');
        }

        $impactBlog = $this->impactBlogModel->getById($id);
        if (!$impactBlog) {
            $this->redirect(BASE_URL . '/admin/public/impact_blogs.php', 'Impact blog not found.');
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($id);
        }

        $data = [
            'impactBlog' => $impactBlog,
            'pageTitle' => 'Edit Impact Blog - ' . htmlspecialchars($impactBlog['title']),
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Impact Blogs', 'url' => 'impact_blogs.php'],
                ['title' => 'Edit - ' . htmlspecialchars($impactBlog['title'])]
            ],
            'categories' => $this->getAvailableCategories(),
            'currentUser' => $_SESSION['username'] ?? 'Admin'
        ];

        $this->render('impact_blogs/edit', $data);
    }

    /**
     * Handle impact blog actions (activate, deactivate, delete)
     */
    private function handleActions()
    {
        if (!isset($_POST['action'], $_POST['id'])) {
            return;
        }

        $action = $_POST['action'];
        $blogId = (int)$_POST['id'];

        try {
            switch ($action) {
                case 'activate':
                    $this->impactBlogModel->activate($blogId);
                    $this->setFlashMessage('success', 'Impact blog activated successfully.');
                    break;
                    
                case 'deactivate':
                    $this->impactBlogModel->deactivate($blogId);
                    $this->setFlashMessage('success', 'Impact blog deactivated successfully.');
                    break;
                    
                case 'delete':
                    $this->impactBlogModel->delete($blogId);
                    $this->setFlashMessage('success', 'Impact blog deleted successfully.');
                    break;
                    
                case 'update_sort_order':
                    if (isset($_POST['sort_order'])) {
                        $this->impactBlogModel->updateSortOrder($blogId, (int)$_POST['sort_order']);
                        echo json_encode(['success' => true]);
                        exit;
                    }
                    break;
                    
                default:
                    $this->setFlashMessage('error', 'Invalid action.');
                    break;
            }
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error performing action: ' . $e->getMessage());
        }

        if ($action !== 'update_sort_order') {
            $this->redirect(BASE_URL . '/admin/public/impact_blogs.php');
        }
    }

    /**
     * Handle adding new impact blog
     */
    private function handleAdd()
    {
        try {
            $data = $this->validateAndSanitizeData();
            
            // Generate slug
            $data['slug'] = $this->impactBlogModel->generateSlug($data['title']);
            
            $newId = $this->impactBlogModel->create($data);
            
            if ($newId) {
                $this->setFlashMessage('success', 'Impact blog created successfully.');
                $this->redirect(BASE_URL . '/admin/public/impact_blogs.php');
            } else {
                throw new Exception('Failed to create impact blog.');
            }
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error creating impact blog: ' . $e->getMessage());
        }
    }

    /**
     * Handle editing impact blog
     */
    private function handleEdit($id)
    {
        try {
            $data = $this->validateAndSanitizeData();
            
            // Generate slug if title changed, otherwise use existing slug
            $currentBlog = $this->impactBlogModel->getById($id);
            if ($currentBlog['title'] !== $data['title']) {
                $data['slug'] = $this->impactBlogModel->generateSlug($data['title'], $id);
            } else {
                $data['slug'] = $currentBlog['slug']; // Use existing slug
            }
            
            if ($this->impactBlogModel->update($id, $data)) {
                $this->setFlashMessage('success', 'Impact blog updated successfully.');
                $this->redirect(BASE_URL . '/admin/public/impact_blogs.php');
            } else {
                throw new Exception('Failed to update impact blog.');
            }
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error updating impact blog: ' . $e->getMessage());
        }
    }

    /**
     * Validate and sanitize form data
     */
    private function validateAndSanitizeData()
    {
        $data = [];
        
        // Required fields
        if (empty($_POST['title'])) {
            throw new Exception('Title is required.');
        }
        $data['title'] = trim($_POST['title']);
        
        if (empty($_POST['category'])) {
            throw new Exception('Category is required.');
        }
        $data['category'] = trim($_POST['category']);
        
        if (empty($_POST['excerpt'])) {
            throw new Exception('Excerpt is required.');
        }
        $data['excerpt'] = trim($_POST['excerpt']);
        
        if (empty($_POST['content'])) {
            throw new Exception('Content is required.');
        }
        $data['content'] = $_POST['content']; // Keep HTML formatting
        
        // Optional fields
        $data['featured_image'] = trim($_POST['featured_image'] ?? '');
        $data['video_url'] = trim($_POST['video_url'] ?? '');
        $data['video_embed_code'] = trim($_POST['video_embed_code'] ?? '');
        $data['author_name'] = trim($_POST['author_name'] ?? '');
        $data['is_active'] = isset($_POST['is_active']) && $_POST['is_active'] === '1';
        $data['sort_order'] = (int)($_POST['sort_order'] ?? 0);
        
        // Handle stats data (JSON)
        $statsData = [];
        if (!empty($_POST['stats_labels']) && !empty($_POST['stats_values'])) {
            $labels = array_filter(array_map('trim', $_POST['stats_labels']));
            $values = array_filter(array_map('trim', $_POST['stats_values']));
            
            if (count($labels) === count($values)) {
                $statsData = array_combine($labels, $values);
            }
        }
        $data['stats_data'] = !empty($statsData) ? json_encode($statsData) : null;
        
        // Handle tags (JSON)
        $tags = [];
        if (!empty($_POST['tags'])) {
            $tags = array_filter(array_map('trim', explode(',', $_POST['tags'])));
        }
        $data['tags'] = !empty($tags) ? json_encode($tags) : null;
        
        return $data;
    }

    /**
     * Get filter parameters
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
     * Get available categories
     */
    private function getAvailableCategories()
    {
        return [
            'agriculture' => 'Agriculture',
            'technology' => 'Technology',
            'business' => 'Business',
            'healthcare' => 'Healthcare',
            'education' => 'Education',
            'environment' => 'Environment'
        ];
    }
}
?>
