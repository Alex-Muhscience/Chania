<?php
require_once __DIR__ . '/../Models/ProgramModel.php';

class ProgramService {
    private $model;

    public function __construct($db) {
        $this->model = new ProgramModel($db);
    }

    /**
     * Get paginated list of programs with optional category filter
     *
     * @param int $page Current page number
     * @param int $perPage Number of items per page
     * @param string|null $category Filter by category (optional)
     * @return array Array containing programs and pagination info
     */
    public function getPaginatedPrograms($page = 1, $perPage = 6, $category = null) {
        // Validate inputs
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);

        // Get programs from model
        $offset = ($page - 1) * $perPage;
        $programs = $this->model->getAllPrograms($perPage, $offset, $category);

        // Get total count for pagination
        $total = $this->model->countPrograms($category);
        $totalPages = ceil($total / $perPage);

        return [
            'programs' => $programs,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $total,
                'per_page' => $perPage,
                'has_previous' => $page > 1,
                'has_next' => $page < $totalPages
            ]
        ];
    }

    /**
     * Get featured programs
     *
     * @param int $limit Number of programs to return
     * @return array Array of featured programs
     */
    public function getFeaturedPrograms($limit = 3) {
        $limit = max(1, (int)$limit);
        return $this->model->getFeaturedPrograms($limit);
    }

    /**
     * Get program details by ID
     *
     * @param int $id Program ID
     * @return array Program details
     * @throws Exception If program not found
     */
    public function getProgramDetails($id) {
        $id = (int)$id;
        $program = $this->model->getProgramById($id);

        if (!$program) {
            throw new Exception('Program not found');
        }

        return $program;
    }

    /**
     * Get all available program categories
     *
     * @return array List of categories
     */
    public function getProgramCategories() {
        return $this->model->getProgramCategories();
    }

    /**
     * Get related programs (same category, excluding current program)
     *
     * @param int $currentId Current program ID to exclude
     * @param string $category Category to match
     * @param int $limit Number of programs to return
     * @return array Array of related programs
     */
    public function getRelatedPrograms($currentId, $category, $limit = 3) {
        $currentId = (int)$currentId;
        $limit = max(1, (int)$limit);

        return $this->model->getRelatedPrograms($currentId, $category, $limit);
    }

    /**
     * Search programs by title or description
     *
     * @param string $query Search query
     * @param int $limit Maximum results to return
     * @return array Matching programs
     */
    public function searchPrograms($query, $limit = 10) {
        $query = trim($query);
        $limit = max(1, (int)$limit);

        if (empty($query)) {
            return [];
        }

        $stmt = $this->model->getDb()->prepare("
            SELECT * FROM programs 
            WHERE title LIKE :query OR description LIKE :query
            ORDER BY title
            LIMIT :limit
        ");

        $searchQuery = "%$query%";
        $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get programs by IDs
     *
     * @param array $ids Array of program IDs
     * @return array Programs matching the IDs
     */
    public function getProgramsByIds(array $ids) {
        if (empty($ids)) {
            return [];
        }

        // Sanitize IDs
        $ids = array_map('intval', $ids);
        $ids = array_unique($ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $this->model->getDb()->prepare("
            SELECT * FROM programs 
            WHERE id IN ($placeholders)
            ORDER BY FIELD(id, $placeholders)
        ");

        // Bind parameters twice for FIELD() ordering
        $params = array_merge($ids, $ids);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get all programs with minimal data (for select dropdowns)
     *
     * @return array Array of programs with id, title, and category
     */
    public function getAllProgramsMinimal() {
        $stmt = $this->model->getDb()->query("
            SELECT id, title, category 
            FROM programs 
            ORDER BY title
        ");
        return $stmt->fetchAll();
    }

    /**
     * Check if a program exists
     *
     * @param int $id Program ID
     * @return bool True if exists, false otherwise
     */
    public function programExists($id) {
        $id = (int)$id;
        $stmt = $this->model->getDb()->prepare("
            SELECT COUNT(*) FROM programs WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }
}
?>