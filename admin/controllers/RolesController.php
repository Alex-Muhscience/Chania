<?php

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/Role.php';

class RolesController extends BaseController
{
    private $roleModel;

    public function __construct()
    {
        parent::__construct();
        $this->roleModel = new Role($this->db);
    }

    /**
     * Display roles listing page
     */
    public function index()
    {
        // Check permissions
        if (!$this->hasPermission('roles') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to manage roles.');
            header('Location: index.php');
            exit;
        }

        $data = [
            'roles' => $this->getAllRoles(),
            'pageTitle' => 'Role Management',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Roles']
            ]
        ];

        $this->render('roles/index', $data);
    }

    /**
     * Get all roles with their permissions
     */
    private function getAllRoles()
    {
        $roles = $this->roleModel->getAll();
        
        // Add permissions to each role
        foreach ($roles as &$role) {
            $role['permissions'] = $this->roleModel->getPermissions($role['id']);
        }
        
        return $roles;
    }

    /**
     * Handle role creation
     */
    public function create()
    {
if (!$this->hasPermission('roles') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied.');
            header('Location: index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $permissions = $_POST['permissions'] ?? [];
            $isDefault = isset($_POST['is_default']) ? 1 : 0;

            // Validation
            if (empty($name)) {
                $this->setFlashMessage('error', 'Role name is required.');
            } else {
                try {
                    $roleId = $this->roleModel->create($name, $description, $isDefault);
                    
                    if ($roleId) {
                        // Add permissions
                        if (!empty($permissions)) {
                            $this->roleModel->setPermissions($roleId, $permissions);
                        }
                        
                        $this->setFlashMessage('success', 'Role created successfully.');
                        header('Location: roles.php');
                        exit;
                    } else {
                        $this->setFlashMessage('error', 'Failed to create role.');
                    }
                } catch (Exception $e) {
                    $this->setFlashMessage('error', 'Error creating role: ' . $e->getMessage());
                }
            }
        }

        $data = [
            'pageTitle' => 'Add Role',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Roles', 'url' => 'roles.php'],
                ['title' => 'Add Role']
            ],
            'availablePermissions' => $this->getAvailablePermissions()
        ];

        $this->render('roles/create', $data);
    }

    /**
     * Handle role editing
     */
    public function edit($id = null)
    {
if (!$this->hasPermission('roles') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied.');
            header('Location: index.php');
            exit;
        }

        $id = $id ?? ($_GET['id'] ?? null);
        
        if (!$id) {
            $this->setFlashMessage('error', 'Role ID is required.');
            header('Location: roles.php');
            exit;
        }

        $role = $this->roleModel->getById($id);
        if (!$role) {
            $this->setFlashMessage('error', 'Role not found.');
            header('Location: roles.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $permissions = $_POST['permissions'] ?? [];
            $isDefault = isset($_POST['is_default']) ? 1 : 0;

            // Validation
            if (empty($name)) {
                $this->setFlashMessage('error', 'Role name is required.');
            } else {
                try {
                    $updated = $this->roleModel->update($id, $name, $description, $isDefault);
                    
                    if ($updated) {
                        // Update permissions
                        $this->roleModel->setPermissions($id, $permissions);
                        
                        $this->setFlashMessage('success', 'Role updated successfully.');
                        header('Location: roles.php');
                        exit;
                    } else {
                        $this->setFlashMessage('error', 'Failed to update role.');
                    }
                } catch (Exception $e) {
                    $this->setFlashMessage('error', 'Error updating role: ' . $e->getMessage());
                }
            }
        }

        $data = [
            'role' => $role,
            'rolePermissions' => $this->roleModel->getPermissions($id),
            'pageTitle' => 'Edit Role: ' . htmlspecialchars($role['name']),
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Roles', 'url' => 'roles.php'],
                ['title' => 'Edit Role']
            ],
            'availablePermissions' => $this->getAvailablePermissions()
        ];

        $this->render('roles/edit', $data);
    }

    /**
     * Handle role deletion
     */
    public function delete($id = null)
    {
if (!$this->hasPermission('roles') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied.');
            header('Location: index.php');
            exit;
        }

        $id = $id ?? ($_GET['id'] ?? null);
        
        if (!$id) {
            $this->setFlashMessage('error', 'Role ID is required.');
            header('Location: roles.php');
            exit;
        }

        // Don't allow deleting Admin role (ID 1)
        if ($id == 1) {
            $this->setFlashMessage('error', 'Cannot delete the Admin role.');
            header('Location: roles.php');
            exit;
        }

        try {
            $deleted = $this->roleModel->delete($id);
            
            if ($deleted) {
                $this->setFlashMessage('success', 'Role deleted successfully.');
            } else {
                $this->setFlashMessage('error', 'Failed to delete role.');
            }
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error deleting role: ' . $e->getMessage());
        }

        header('Location: roles.php');
        exit;
    }

    /**
     * Get available permissions for role assignment
     */
    private function getAvailablePermissions()
    {
        return [
            '*' => 'All Permissions (Admin)',
            'users' => 'User Management',
            'roles' => 'Role Management',
            'programs' => 'Program Management',
            'applications' => 'Application Management',
            'events' => 'Event Management',
            'media' => 'Media Management',
            'pages' => 'Page Management',
            'files' => 'File Management',
            'templates' => 'Email Template Management',
            'settings' => 'System Settings',
            'reports' => 'Reports & Analytics'
        ];
    }

    /**
     * Check if user has permission for roles management
     */
    private function checkPermission($permission)
    {
        return $this->hasPermission($permission) || $this->hasPermission('*');
    }
}
