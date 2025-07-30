<?php
class File {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll($filters = [], $page = 1, $limit = 20) {
        $conditions = ["deleted_at IS NULL"];
        $params = [];
        
        if (!empty($filters['search'])) {
            $conditions[] = "(original_name LIKE ? OR stored_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['file_type'])) {
            $conditions[] = "file_type = ?";
            $params[] = $filters['file_type'];
        }
        
        if (!empty($filters['entity_type'])) {
            $conditions[] = "entity_type = ?";
            $params[] = $filters['entity_type'];
        }
        
        $whereClause = "WHERE " . implode(" AND ", $conditions);
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("
            SELECT f.*, u.username as uploaded_by_name
            FROM file_uploads f
            LEFT JOIN users u ON f.uploaded_by = u.id
            $whereClause
            ORDER BY f.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([...$params, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getTotalCount($filters = []) {
        $conditions = ["deleted_at IS NULL"];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(original_name LIKE ? OR stored_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['file_type'])) {
            $conditions[] = "file_type = ?";
            $params[] = $filters['file_type'];
        }
        
        if (!empty($filters['entity_type'])) {
            $conditions[] = "entity_type = ?";
            $params[] = $filters['entity_type'];
        }

        $whereClause = "WHERE " . implode(" AND ", $conditions);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM file_uploads $whereClause");
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getFileTypeCounts() {
        $stmt = $this->db->query("
            SELECT file_type, COUNT(*) as count
            FROM file_uploads
            WHERE deleted_at IS NULL
            GROUP BY file_type
        ");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function delete($id) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT * FROM file_uploads WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $file = $stmt->fetch();

            if ($file) {
                $stmt = $this->db->prepare("UPDATE file_uploads SET deleted_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);

                if (file_exists($file['file_path'])) {
                    unlink($file['file_path']);
                }
                $this->db->commit();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("File deletion error: " . $e->getMessage());
            return false;
        }
    }
}
?>
