<?php
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../../shared/Core/Media.php';

class MediaController extends BaseController {
    private $media;

    public function __construct() {
        parent::__construct();
        $this->media = new Media($this->db);
        $this->setPageTitle('Media Library');
        $this->setBreadcrumbs([
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Media Library']
        ]);
    }

    public function index() {
        // CRITICAL SECURITY CHECK - Check permissions
        if (!$this->hasPermission('media') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to manage media.');
            header('Location: index.php');
            exit;
        }

        // Handle bulk delete
        if (($_POST['action'] ?? '') === 'bulk_delete' && !empty($_POST['selected_items'])) {
            $this->handleBulkDelete();
        }

        // Handle search and filtering
        $search = $_GET['search'] ?? '';
        $filter = $_GET['filter'] ?? 'all';
        $sort = $_GET['sort'] ?? 'newest';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20; // Items per page
        $offset = ($page - 1) * $limit;

        try {
            // Get filtered and paginated results
            if (!empty($search)) {
                $mediaItems = $this->media->search($search, $filter, $sort, $limit, $offset);
                $totalItems = $this->media->getSearchCount($search, $filter);
            } else {
                $mediaItems = $this->media->getFiltered($filter, $sort, $limit, $offset);
                $totalItems = $this->media->getFilteredCount($filter);
            }

            $totalPages = ceil($totalItems / $limit);

            $this->render('media/index', [
                'mediaItems' => $mediaItems,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'search' => $search,
                'filter' => $filter,
                'sort' => $sort,
                'limit' => $limit,
                'media' => $this->media
            ]);

        } catch (Exception $e) {
            error_log("Media fetch error: " . $e->getMessage());
            $this->addError('Error loading media files.');
            $this->render('media/index', [
                'mediaItems' => [],
                'totalItems' => 0,
                'totalPages' => 0,
                'currentPage' => 1,
                'search' => $search,
                'filter' => $filter,
                'sort' => $sort,
                'limit' => $limit,
                'media' => $this->media
            ]);
        }
    }

    private function handleBulkDelete() {
        try {
            $deletedCount = 0;
            foreach ($_POST['selected_items'] as $id) {
                if ($this->media->delete($id)) {
                    $deletedCount++;
                }
            }
            $this->setSuccess("Successfully deleted {$deletedCount} media file(s).");
        } catch (Exception $e) {
            $this->addError('Error deleting media files: ' . $e->getMessage());
        }
        
        $this->redirect($_SERVER['REQUEST_URI']);
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/media.php');
        }

        // Handle file upload logic here
        // This would typically be handled by an upload action or API endpoint
        $this->setSuccess('File uploaded successfully.');
        $this->redirect(BASE_URL . '/admin/media.php');
    }

    public function delete() {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            $this->redirect(BASE_URL . '/admin/media.php', 'Invalid media ID.');
        }

        try {
            if ($this->media->delete($id)) {
                $this->setSuccess('Media file deleted successfully.');
            } else {
                $this->addError('Failed to delete media file.');
            }
        } catch (Exception $e) {
            $this->addError('Error deleting media file: ' . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/media.php');
    }
}
?>
