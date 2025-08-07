<?php
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../../shared/Core/Page.php';

class PagesController extends BaseController {
    private $pageManager;

    public function __construct() {
        parent::__construct();
        $this->pageManager = new Page($this->db);
        $this->setPageTitle('Pages Management');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Pages Management']
        ]);
    }

    public function index() {
        // CRITICAL SECURITY CHECK - Check permissions
        if (!$this->hasPermission('pages') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to manage pages.');
            header('Location: index.php');
            exit;
        }

        try {
            $pages = $this->pageManager->getAll();

            $this->render('pages/index', [
                'pages' => $pages,
                'pageManager' => $this->pageManager
            ]);

        } catch (Exception $e) {
            error_log("Pages fetch error: " . $e->getMessage());
            $this->addError('Error loading pages.');
            $this->render('pages/index', [
                'pages' => [],
                'pageManager' => $this->pageManager
            ]);
        }
    }

    public function add() {
        // Check permissions
        if (!$this->hasPermission('pages') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to add pages.');
            header('Location: index.php');
            exit;
        }

        $this->setPageTitle('Create New Page');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Pages Management', 'url' => BASE_URL . '/admin/pages.php'],
            ['title' => 'Create New Page']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePageCreation();
        }

        $templates = $this->pageManager->getTemplates();
        $this->render('pages/add', [
            'templates' => $templates
        ]);
    }

    public function edit() {
        // Check permissions
        if (!$this->hasPermission('pages') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to edit pages.');
            header('Location: index.php');
            exit;
        }

        $pageId = intval($_GET['id'] ?? 0);
        if (!$pageId) {
            $this->redirect(BASE_URL . '/admin/pages.php', 'Invalid page ID.');
        }

        $this->setPageTitle('Edit Page');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Pages Management', 'url' => BASE_URL . '/admin/pages.php'],
            ['title' => 'Edit Page']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePageUpdate($pageId);
        }

        try {
            $page = $this->pageManager->getById($pageId);
            if (!$page) {
                $this->redirect(BASE_URL . '/admin/pages.php', 'Page not found.');
            }

            $templates = $this->pageManager->getTemplates();
            $this->render('pages/edit', [
                'page' => $page,
                'templates' => $templates
            ]);

        } catch (Exception $e) {
            $this->redirect(BASE_URL . '/admin/pages.php', 'Error loading page: ' . $e->getMessage());
        }
    }

    public function delete() {
        // Check permissions
        if (!$this->hasPermission('pages') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to delete pages.');
            header('Location: index.php');
            exit;
        }

        $pageId = intval($_POST['id'] ?? 0);
        if (!$pageId) {
            $this->redirect(BASE_URL . '/admin/pages.php', 'Invalid page ID.');
        }

        try {
            if ($this->pageManager->delete($pageId)) {
                $this->setSuccess('Page deleted successfully.');
            } else {
                $this->addError('Failed to delete page.');
            }
        } catch (Exception $e) {
            $this->addError('Error deleting page: ' . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/pages.php');
    }

    private function handlePageCreation() {
        $requiredFields = ['title', 'slug', 'content'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        if (empty($errors)) {
            try {
                $pageData = $this->sanitizeInput($_POST);
                $result = $this->pageManager->create([
                    'title' => $pageData['title'],
                    'slug' => $pageData['slug'],
                    'content' => $pageData['content'],
                    'template' => $pageData['template'] ?? 'default',
                    'meta_title' => $pageData['meta_title'] ?? '',
                    'meta_description' => $pageData['meta_description'] ?? '',
                    'is_published' => isset($pageData['is_published']) ? 1 : 0
                ]);
                
                if ($result) {
                    $this->redirect(BASE_URL . '/admin/pages.php', 'Page created successfully.');
                } else {
                    $this->addError('Failed to create page.');
                }
            } catch (Exception $e) {
                $this->addError('Error creating page: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    private function handlePageUpdate($pageId) {
        $requiredFields = ['title', 'slug', 'content'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        if (empty($errors)) {
            try {
                $pageData = $this->sanitizeInput($_POST);
                $result = $this->pageManager->update($pageId, [
                    'title' => $pageData['title'],
                    'slug' => $pageData['slug'],
                    'content' => $pageData['content'],
                    'template' => $pageData['template'] ?? 'default',
                    'meta_title' => $pageData['meta_title'] ?? '',
                    'meta_description' => $pageData['meta_description'] ?? '',
                    'is_published' => isset($pageData['is_published']) ? 1 : 0
                ]);
                
                if ($result) {
                    $this->redirect(BASE_URL . '/admin/pages.php', 'Page updated successfully.');
                } else {
                    $this->addError('Failed to update page.');
                }
            } catch (Exception $e) {
                $this->addError('Error updating page: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }
}
?>
