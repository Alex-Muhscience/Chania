<?php
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/TeamMember.php';

class TeamMembersController extends BaseController {
    private $teamMemberManager;

    public function __construct() {
        parent::__construct();
        $this->teamMemberManager = new TeamMember($this->db);
        $this->setPageTitle('Team Members Management');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Team Members Management']
        ]);
    }

    public function index() {
        // CRITICAL SECURITY CHECK - Check permissions
        if (!$this->hasPermission('team_members') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to manage team members.');
            header('Location: index.php');
            exit;
        }

        // Handle bulk actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['bulk_action'])) {
            $this->handleBulkActions();
        }

        // Get filters and pagination
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => isset($_GET['status']) && $_GET['status'] !== '' ? intval($_GET['status']) : ''
        ];
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        try {
            // Get team members with filters
            $teamMembers = $this->teamMemberManager->getAll($perPage, $offset, $filters['search'], $filters['status']);
            $totalMembers = $this->teamMemberManager->getTotalCount($filters['search'], $filters['status']);
            $totalPages = ceil($totalMembers / $perPage);

            // Get statistics
            $stats = $this->teamMemberManager->getStats();

            $this->render('team_members/index', [
                'team_members' => $teamMembers,
                'stats' => $stats,
                'filters' => $filters,
                'pagination' => [
                    'page' => $page,
                    'totalPages' => $totalPages,
                    'totalMembers' => $totalMembers,
                    'perPage' => $perPage
                ]
            ]);

        } catch (Exception $e) {
            error_log("Team members fetch error: " . $e->getMessage());
            $this->addError('Error loading team members.');
            $this->render('team_members/index', [
                'team_members' => [],
                'stats' => ['total' => 0, 'active' => 0, 'inactive' => 0, 'with_photos' => 0],
                'filters' => $filters,
                'pagination' => [
                    'page' => 1,
                    'totalPages' => 0,
                    'totalMembers' => 0,
                    'perPage' => $perPage
                ]
            ]);
        }
    }

    public function add() {
        // Check permissions
        if (!$this->hasPermission('team_members') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to add team members.');
            header('Location: index.php');
            exit;
        }

        $this->setPageTitle('Add New Team Member');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Team Members Management', 'url' => BASE_URL . '/admin/team_members.php'],
            ['title' => 'Add New Team Member']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleTeamMemberCreation();
        }

        $this->render('team_members/add', []);
    }

    public function edit() {
        // Check permissions
        if (!$this->hasPermission('team_members') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to edit team members.');
            header('Location: index.php');
            exit;
        }

        $memberId = intval($_GET['id'] ?? 0);
        if (!$memberId) {
            $this->redirect(BASE_URL . '/admin/team_members.php', 'Invalid team member ID.');
        }

        $this->setPageTitle('Edit Team Member');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Team Members Management', 'url' => BASE_URL . '/admin/team_members.php'],
            ['title' => 'Edit Team Member']
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleTeamMemberUpdate($memberId);
        }

        try {
            $member = $this->teamMemberManager->getById($memberId);
            if (!$member) {
                $this->redirect(BASE_URL . '/admin/team_members.php', 'Team member not found.');
            }

            $this->render('team_members/edit', [
                'member' => $member
            ]);

        } catch (Exception $e) {
            $this->redirect(BASE_URL . '/admin/team_members.php', 'Error loading team member: ' . $e->getMessage());
        }
    }

    public function delete() {
        // Check permissions
        if (!$this->hasPermission('team_members') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to delete team members.');
            header('Location: index.php');
            exit;
        }

        $memberId = intval($_GET['id'] ?? 0);
        if (!$memberId) {
            $this->redirect(BASE_URL . '/admin/team_members.php', 'Invalid team member ID.');
        }

        try {
            if ($this->teamMemberManager->delete($memberId)) {
                $this->logAdminAction('DELETE_TEAM_MEMBER', 'team_members', $memberId);
                $this->setSuccess('Team member deleted successfully.');
            } else {
                $this->addError('Failed to delete team member.');
            }
        } catch (Exception $e) {
            $this->addError('Error deleting team member: ' . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/team_members.php');
    }

    public function toggleStatus() {
        // Check permissions
        if (!$this->hasPermission('team_members') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied.');
            header('Location: index.php');
            exit;
        }

        $memberId = intval($_GET['id'] ?? 0);
        if (!$memberId) {
            $this->redirect(BASE_URL . '/admin/team_members.php', 'Invalid team member ID.');
        }

        try {
            if ($this->teamMemberManager->toggleStatus($memberId)) {
                $this->logAdminAction('TOGGLE_TEAM_MEMBER_STATUS', 'team_members', $memberId);
                $this->setSuccess('Team member status updated.');
            } else {
                $this->addError('Failed to update team member status.');
            }
        } catch (Exception $e) {
            $this->addError('Error updating team member: ' . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/team_members.php');
    }

    private function handleBulkActions() {
        $action = $_POST['bulk_action'] ?? '';
        $selectedIds = $_POST['selected_members'] ?? [];

        if (empty($selectedIds)) {
            $this->addError('No team members selected.');
            return;
        }

        try {
            switch ($action) {
                case 'delete':
                    if ($this->teamMemberManager->bulkDelete($selectedIds)) {
                        $this->logAdminAction('BULK_DELETE_TEAM_MEMBERS', 'team_members', null, ['ids' => $selectedIds]);
                        $this->setSuccess(count($selectedIds) . ' team member(s) deleted successfully.');
                    } else {
                        $this->addError('Failed to delete selected team members.');
                    }
                    break;
                default:
                    $this->addError('Invalid bulk action.');
            }
        } catch (Exception $e) {
            $this->addError('Error performing bulk action: ' . $e->getMessage());
        }
    }

    private function handleTeamMemberCreation() {
        $requiredFields = ['name', 'position'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        // Validate email format if provided
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        // Validate social links JSON if provided
        if (!empty($_POST['socialLinks']) && !$this->teamMemberManager->validateSocialLinks($_POST['socialLinks'])) {
            $errors[] = 'Social links must be in valid JSON format with valid URLs.';
        }

        if (empty($errors)) {
            try {
                $memberData = $this->sanitizeInput($_POST);
                
                // Handle image upload
                $imagePath = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->handleImageUpload($_FILES['image'], 'team');
                    if ($uploadResult['success']) {
                        $imagePath = $uploadResult['path'];
                    } else {
                        $this->addError($uploadResult['error']);
                        return;
                    }
                }

                $memberData['imagePath'] = $imagePath;
                $memberData['status'] = isset($memberData['isActive']) && $memberData['isActive'] ? 'active' : 'inactive';

                $result = $this->teamMemberManager->create($memberData);
                
                if ($result) {
                    $this->logAdminAction('CREATE_TEAM_MEMBER', 'team_members', $result);
                    $this->redirect(BASE_URL . '/admin/team_members.php', 'Team member created successfully.');
                } else {
                    $this->addError('Failed to create team member.');
                }
            } catch (Exception $e) {
                $this->addError('Error creating team member: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    private function handleTeamMemberUpdate($memberId) {
        $requiredFields = ['name', 'position'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        // Validate email format if provided
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        // Validate social links JSON if provided
        if (!empty($_POST['socialLinks']) && !$this->teamMemberManager->validateSocialLinks($_POST['socialLinks'])) {
            $errors[] = 'Social links must be in valid JSON format with valid URLs.';
        }

        if (empty($errors)) {
            try {
                $memberData = $this->sanitizeInput($_POST);
                
                // Handle image upload and removal
                $currentMember = $this->teamMemberManager->getById($memberId);
                $imagePath = $currentMember['image_path'];
                
                // Handle image removal
                if (isset($memberData['removeImage']) && $memberData['removeImage']) {
                    if ($imagePath && file_exists(__DIR__ . '/../../uploads/' . $imagePath)) {
                        unlink(__DIR__ . '/../../uploads/' . $imagePath);
                    }
                    $imagePath = null;
                }
                
                // Handle new image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->handleImageUpload($_FILES['image'], 'team');
                    if ($uploadResult['success']) {
                        $imagePath = $uploadResult['path'];
                        // Delete old image if exists
                        if ($currentMember['image_path'] && file_exists(__DIR__ . '/../../uploads/' . $currentMember['image_path'])) {
                            unlink(__DIR__ . '/../../uploads/' . $currentMember['image_path']);
                        }
                    } else {
                        $this->addError($uploadResult['error']);
                        return;
                    }
                }

                $memberData['imagePath'] = $imagePath;
                $memberData['status'] = isset($memberData['isActive']) && $memberData['isActive'] ? 'active' : 'inactive';

                $result = $this->teamMemberManager->update($memberId, $memberData);
                
                if ($result) {
                    $this->logAdminAction('UPDATE_TEAM_MEMBER', 'team_members', $memberId);
                    $this->redirect(BASE_URL . '/admin/team_members.php', 'Team member updated successfully.');
                } else {
                    $this->addError('Failed to update team member.');
                }
            } catch (Exception $e) {
                $this->addError('Error updating team member: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    private function handleImageUpload($file, $subfolder = '') {
        $uploadDir = __DIR__ . '/../../uploads/' . ($subfolder ? $subfolder . '/' : '');
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.'];
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'error' => 'File is too large. Maximum size is 5MB.'];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'path' => ($subfolder ? $subfolder . '/' : '') . $filename];
        } else {
            return ['success' => false, 'error' => 'Failed to upload file.'];
        }
    }
}
?>
