<?php

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

class ContactsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display contacts listing page
     */
    public function index()
    {
        // Check permissions - contacts don't need specific permission, admin users can manage
        if (!$this->hasPermission('*') && !$this->hasPermission('contacts')) {
            $this->redirect(BASE_URL . '/admin/', 'Access denied.');
        }

        // Handle actions
        if (isset($_GET['action'], $_GET['id'])) {
            $this->handleActions();
        }

        // Get contacts data
        try {
            $stmt = $this->db->query("SELECT * FROM contacts WHERE deleted_at IS NULL ORDER BY submitted_at DESC");
            $contacts = $stmt->fetchAll();
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error loading contacts. Please try again later.');
            error_log("Contacts loading error: " . $e->getMessage());
            $contacts = [];
        }

        $data = [
            'contacts' => $contacts,
            'pageTitle' => 'Contact Management',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Contacts']
            ]
        ];

        $this->render('contacts/index', $data);
    }

    /**
     * Handle contact actions (mark_read, delete)
     */
    private function handleActions()
    {
        $contactId = (int)$_GET['id'];
        $action = $_GET['action'];

        try {
            switch ($action) {
                case 'mark_read':
                    $stmt = $this->db->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?");
                    $stmt->execute([$contactId]);
                    $this->setFlashMessage('success', 'Contact marked as read.');
                    break;
                    
                case 'delete':
                    $stmt = $this->db->prepare("UPDATE contacts SET deleted_at = NOW() WHERE id = ?");
                    $stmt->execute([$contactId]);
                    $this->setFlashMessage('success', 'Contact deleted successfully.');
                    break;
                    
                default:
                    $this->setFlashMessage('error', 'Invalid action.');
                    break;
            }
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error performing action: ' . $e->getMessage());
            error_log("Contact action error: " . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/contacts.php');
    }
}
