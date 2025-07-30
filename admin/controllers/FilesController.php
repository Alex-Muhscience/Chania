<?php
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/File.php';

class FilesController extends BaseController {
    private $fileManager;

    public function __construct() {
        parent::__construct();
        $this->fileManager = new File($this->db);
    }

    public function index() {
        // CRITICAL SECURITY CHECK - Check permissions
        if (!$this->hasPermission('files') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to manage files.');
            header('Location: index.php');
            exit;
        }

        $this->setPageTitle('File Management');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'File Management']
        ]);

        $filters = [
            'search' => $_GET['search'] ?? '',
            'file_type' => $_GET['file_type'] ?? '',
            'entity_type' => $_GET['entity_type'] ?? ''
        ];
        $page = max(1, intval($_GET['page'] ?? 1));

        try {
            $files = $this->fileManager->getAll($filters, $page, ITEMS_PER_PAGE);
            $totalItems = $this->fileManager->getTotalCount($filters);
            $pagination = $this->getPaginationData($page, $totalItems, ITEMS_PER_PAGE);
            $fileTypeCounts = $this->fileManager->getFileTypeCounts();

            $this->renderView(__DIR__ . '/../views/files/index.php', [
                'files' => $files,
                'pagination' => $pagination,
                'fileTypeCounts' => $fileTypeCounts,
                'filters' => $filters
            ]);

        } catch (Exception $e) {
            $this->addError('Error loading files: ' . $e->getMessage());
            $this->renderView(__DIR__ . '/../views/files/index.php', [
                'files' => [],
                'pagination' => $this->getPaginationData(1, 0),
                'fileTypeCounts' => [],
                'filters' => $filters
            ]);
        }
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/files.php');
        }

        $uploadedFile = $_FILES['file'] ?? null;
        if ($uploadedFile && $uploadedFile['error'] === UPLOAD_ERR_OK) {
            $result = Utilities::uploadFile($uploadedFile, $_POST['entity_type'] ?? null, $_POST['entity_id'] ?? null);
            if ($result['success']) {
                $this->redirect(BASE_URL . '/admin/files.php', 'File uploaded successfully.');
            } else {
                $this->redirect(BASE_URL . '/admin/files.php', ['error' => $result['error']]);
            }
        } else {
            $this->redirect(BASE_URL . '/admin/files.php', ['error' => 'Please select a file to upload.']);
        }
    }

    public function delete() {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            $this->redirect(BASE_URL . '/admin/files.php', ['error' => 'Invalid file ID.']);
        }

        if ($this->fileManager->delete($id)) {
            Utilities::logActivity($_SESSION['user_id'], 'DELETE_FILE', 'file_uploads', $id, $_SERVER['REMOTE_ADDR']);
            $this->redirect(BASE_URL . '/admin/files.php', 'File deleted successfully.');
        } else {
            $this->redirect(BASE_URL . '/admin/files.php', ['error' => 'Failed to delete file.']);
        }
    }
}
?>
