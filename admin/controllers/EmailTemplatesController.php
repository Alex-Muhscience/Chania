<?php

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../../shared/Core/EmailTemplate.php';

class EmailTemplatesController extends BaseController
{
    private $emailTemplateModel;

    public function __construct()
    {
        parent::__construct();
        $this->emailTemplateModel = new EmailTemplate($this->db);
    }

    /**
     * Display email templates listing page
     */
    public function index()
    {
        // Check permissions
        if (!$this->hasPermission('templates') && !$this->hasPermission('*')) {
            $this->redirect(BASE_URL . '/admin/', "You don't have permission to access this resource.");
        }

        // Handle actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }

        // Get all templates
        $templates = $this->emailTemplateModel->getAll();

        $data = [
            'templates' => $templates,
            'pageTitle' => 'Email Templates',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Email Templates']
            ]
        ];

        $this->render('email_templates/index', $data);
    }

    /**
     * Handle email template actions (create_defaults, activate, deactivate)
     */
    private function handleActions()
    {
        try {
            if (isset($_POST['create_defaults'])) {
                $created = $this->emailTemplateModel->createDefaultTemplates();
                $this->setFlashMessage('success', "Created {$created} default email templates.");
                
            } elseif (isset($_POST['activate'], $_POST['id'])) {
                if ($this->emailTemplateModel->activate($_POST['id'])) {
                    $this->setFlashMessage('success', 'Template activated successfully.');
                } else {
                    $this->setFlashMessage('error', 'Failed to activate template.');
                }
                
            } elseif (isset($_POST['deactivate'], $_POST['id'])) {
                if ($this->emailTemplateModel->deactivate($_POST['id'])) {
                    $this->setFlashMessage('success', 'Template deactivated successfully.');
                } else {
                    $this->setFlashMessage('error', 'Failed to deactivate template.');
                }
            }
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error performing action: ' . $e->getMessage());
            error_log("Email template action error: " . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/email_templates.php');
    }

    /**
     * Check if user has permission for email templates management
     */
    private function checkPermission($permission)
    {
        return $this->hasPermission($permission) || $this->hasPermission('*');
    }
}
