<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../../shared/Core/AdminLogger.php';

abstract class BaseController {
    protected $db;
    protected $pageTitle = '';
    protected $breadcrumbs = [];
    protected $errors = [];
    protected $success = '';
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check authentication BEFORE any output
        $this->checkAuthentication();
        
        // Initialize database connection
        $this->db = (new Database())->connect();
        
        // Handle flash messages
        $this->handleFlashMessages();
    }
    
    private function checkAuthentication() {
        // Use Utilities::requireLogin() instead of custom logic
        Utilities::requireLogin();
    }
    
    protected function handleFlashMessages() {
        if (isset($_SESSION['success'])) {
            $this->success = $_SESSION['success'];
            unset($_SESSION['success']);
        }
        
        if (isset($_SESSION['error'])) {
            $this->errors[] = $_SESSION['error'];
            unset($_SESSION['error']);
        }
        
        if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])) {
            $this->errors = array_merge($this->errors, $_SESSION['errors']);
            unset($_SESSION['errors']);
        }
    }
    
    protected function setPageTitle($title) {
        $this->pageTitle = $title;
    }
    
    protected function setBreadcrumbs($breadcrumbs) {
        $this->breadcrumbs = $breadcrumbs;
    }
    
    protected function addError($error) {
        $this->errors[] = $error;
    }
    
    protected function setSuccess($message) {
        $this->success = $message;
    }
    
    protected function redirect($url, $flash = null) {
        if ($flash) {
            if (is_array($flash)) {
                $_SESSION['errors'] = $flash;
            } else {
                $_SESSION['success'] = $flash;
            }
        }
        Utilities::redirect($url);
    }
    
    protected function validateRequired($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }
        return $errors;
    }
    
    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    protected function getPaginationData($page, $totalItems, $limit = null) {
        $limit = $limit ?? ITEMS_PER_PAGE;
        $totalPages = ceil($totalItems / $limit);
        $offset = ($page - 1) * $limit;
        
        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems
        ];
    }
    
    protected function renderView($viewFile, $data = []) {
        // Extract data for the view
        extract($data);
        
        // Make controller properties available to the view
        $pageTitle = $this->pageTitle;
        $breadcrumbs = $this->breadcrumbs;
        $errors = $this->errors;
        $success = $this->success;
        
        // Include header
        require_once __DIR__ . '/../includes/header.php';
        
        // Include the view file
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            throw new Exception("View file not found: $viewFile");
        }
        
        // Include footer
        require_once __DIR__ . '/../includes/footer.php';
    }
    
    /**
     * Set flash message
     */
    protected function setFlashMessage($type, $message)
    {
        $_SESSION[$type] = $message;
    }

    /**
     * Check if user has permission
     */
    protected function hasPermission($permission)
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check if user has admin permission (full access)
        if (isset($_SESSION['permissions']) && in_array('*', $_SESSION['permissions'])) {
            return true;
        }
        
        // Check specific permission
        return isset($_SESSION['permissions']) && in_array($permission, $_SESSION['permissions']);
    }

    /**
     * Render view with data
     */
    protected function render($viewName, $data = [])
    {
        // Extract data for the view
        extract($data);
        
        // Make controller properties available to the view
        $pageTitle = $data['pageTitle'] ?? $this->pageTitle;
        $breadcrumbs = $data['breadcrumbs'] ?? $this->breadcrumbs;
        $errors = $this->errors;
        $success = $this->success;
        
        // Include header
        require_once __DIR__ . '/../includes/header.php';
        
        // Include the view file
        $viewFile = __DIR__ . '/../views/' . $viewName . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            throw new Exception("View file not found: $viewFile");
        }
        
        // Include footer
        require_once __DIR__ . '/../includes/footer.php';
    }

    /**
     * Log admin activity
     */
    protected function logAdminAction($action, $entityType = null, $entityId = null, $details = null)
    {
        AdminLogger::log($action, $entityType, $entityId, $details);
    }

    abstract public function index();
}
?>
