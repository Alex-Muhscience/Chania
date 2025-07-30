<?php
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../../shared/Core/User.php';
require_once __DIR__ . '/../../shared/Core/Role.php';

class UsersController extends BaseController {
    private $userModel;
    private $roleModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User($this->db);
        $this->roleModel = new Role($this->db);
        $this->setPageTitle('User Management');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'User Management']
        ]);
    }

    public function index() {
        // Check permissions
        if (!$this->userModel->hasPermission($_SESSION['user_id'], 'users') && !$this->userModel->hasPermission($_SESSION['user_id'], '*')) {
            $this->redirect(BASE_URL . '/admin/', 'You do not have permission to access user management.');
        }

        // Handle POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }

        // Get filters and pagination
        $filters = [
            'search' => $_GET['search'] ?? '',
            'role' => isset($_GET['role']) && $_GET['role'] !== '' ? (int)$_GET['role'] : null,
            'status' => isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        try {
            // Get users with filters
            $users = $this->userModel->getAll($perPage, $offset, $filters['search'], $filters['role'], $filters['status']);
            $totalUsers = $this->userModel->getTotalCount($filters['search'], $filters['role'], $filters['status']);
            $totalPages = ceil($totalUsers / $perPage);

            // Get roles for filter dropdown
            $roles = $this->roleModel->getAll();

            // Get statistics
            $stats = $this->userModel->getStatistics();

            $this->renderView(__DIR__ . '/../views/users/index.php', [
                'users' => $users,
                'roles' => $roles,
                'stats' => $stats,
                'filters' => $filters,
                'pagination' => [
                    'page' => $page,
                    'totalPages' => $totalPages,
                    'totalUsers' => $totalUsers,
                    'perPage' => $perPage
                ]
            ]);

        } catch (Exception $e) {
            error_log("Users fetch error: " . $e->getMessage());
            $this->addError('Error loading users.');
            $this->renderView(__DIR__ . '/../views/users/index.php', [
                'users' => [],
                'roles' => [],
                'stats' => ['total' => 0, 'active' => 0, 'this_month' => 0],
                'filters' => $filters,
                'pagination' => [
                    'page' => 1,
                    'totalPages' => 0,
                    'totalUsers' => 0,
                    'perPage' => $perPage
                ]
            ]);
        }
    }

    private function handleActions() {
        try {
            if (isset($_POST['activate']) && isset($_POST['id'])) {
                if ($this->userModel->activate($_POST['id'])) {
                    $this->setSuccess("User activated successfully.");
                } else {
                    $this->addError("Failed to activate user.");
                }
            } elseif (isset($_POST['deactivate']) && isset($_POST['id'])) {
                $this->userModel->deactivate($_POST['id']);
                $this->setSuccess("User deactivated successfully.");
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }
    }

    public function add() {
        // Handle user addition logic
        $this->setPageTitle('Add New User');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'User Management', 'url' => BASE_URL . '/admin/users.php'],
            ['title' => 'Add New User']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle form submission
            $this->handleUserCreation();
        }

        $roles = $this->roleModel->getAll();
        $this->renderView(__DIR__ . '/../views/users/add.php', ['roles' => $roles]);
    }

    public function edit() {
        $userId = intval($_GET['id'] ?? 0);
        if (!$userId) {
            $this->redirect(BASE_URL . '/admin/users.php', 'Invalid user ID.');
        }

        $this->setPageTitle('Edit User');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'User Management', 'url' => BASE_URL . '/admin/users.php'],
            ['title' => 'Edit User']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUserUpdate($userId);
        }

        try {
            $user = $this->userModel->getById($userId);
            $roles = $this->roleModel->getAll();
            
            if (!$user) {
                $this->redirect(BASE_URL . '/admin/users.php', 'User not found.');
            }

            $this->renderView(__DIR__ . '/../views/users/edit.php', [
                'user' => $user,
                'roles' => $roles
            ]);

        } catch (Exception $e) {
            $this->redirect(BASE_URL . '/admin/users.php', 'Error loading user: ' . $e->getMessage());
        }
    }

    private function handleUserCreation() {
        $requiredFields = ['username', 'email', 'password', 'first_name', 'last_name', 'role_id'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        if (empty($errors)) {
            try {
                $userData = $this->sanitizeInput($_POST);
                $result = $this->userModel->create($userData);
                
                if ($result) {
                    $this->redirect(BASE_URL . '/admin/users.php', 'User created successfully.');
                } else {
                    $this->addError('Failed to create user.');
                }
            } catch (Exception $e) {
                $this->addError('Error creating user: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    private function handleUserUpdate($userId) {
        $requiredFields = ['username', 'email', 'first_name', 'last_name', 'role_id'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        if (empty($errors)) {
            try {
                $userData = $this->sanitizeInput($_POST);
                $result = $this->userModel->update($userId, $userData);
                
                if ($result) {
                    $this->redirect(BASE_URL . '/admin/users.php', 'User updated successfully.');
                } else {
                    $this->addError('Failed to update user.');
                }
            } catch (Exception $e) {
                $this->addError('Error updating user: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }
}
?>
