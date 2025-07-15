<?php
class ProgramModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllPrograms($limit = null, $offset = null, $category = null) {
        $sql = "SELECT * FROM programs";
        $params = [];

        if ($category) {
            $sql .= " WHERE category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY title";

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;

            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = $offset;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getFeaturedPrograms($limit = 3) {
        $stmt = $this->db->prepare("
            SELECT * FROM programs 
            WHERE is_featured = TRUE 
            ORDER BY title 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getProgramById($id) {
        $stmt = $this->db->prepare("SELECT * FROM programs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getProgramCategories() {
        $stmt = $this->db->query("SELECT DISTINCT category FROM programs ORDER BY category");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getRelatedPrograms($currentId, $category, $limit = 3) {
        $stmt = $this->db->prepare("
            SELECT * FROM programs 
            WHERE category = ? AND id != ?
            ORDER BY RAND()
            LIMIT ?
        ");
        $stmt->execute([$category, $currentId, $limit]);
        return $stmt->fetchAll();
    }

    public function countPrograms($category = null) {
        $sql = "SELECT COUNT(*) FROM programs";
        $params = [];

        if ($category) {
            $sql .= " WHERE category = ?";
            $params[] = $category;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
?>