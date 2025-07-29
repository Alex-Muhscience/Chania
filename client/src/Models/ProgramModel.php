<?php
class ProgramModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getDb() {
        return $this->db;
    }

    public function getAllPrograms($limit = null, $offset = null, $category = null) {
        $sql = "SELECT p.*, 
                       COALESCE(pc.name, p.category) as category_name, 
                       pc.color as category_color, 
                       pc.icon as category_icon,
                       pc.category_id as category_id_actual
                FROM programs p 
                LEFT JOIN program_categories pc ON (
                    (p.category = pc.name) OR 
                    (FIND_IN_SET(pc.name, p.category) > 0)
                )
                WHERE p.is_active = 1 AND p.deleted_at IS NULL";
        $params = [];

        if ($category) {
            // Handle both category ID and name filtering
            if (is_numeric($category)) {
                $sql .= " AND pc.category_id = ?";
                $params[] = $category;
            } else {
                $sql .= " AND (p.category = ? OR pc.name = ?)";
                $params[] = $category;
                $params[] = $category;
            }
        }

        $sql .= " ORDER BY p.is_featured DESC, p.created_at DESC";

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
            SELECT p.*, 
                   COALESCE(pc.name, p.category) as category_name, 
                   pc.color as category_color, 
                   pc.icon as category_icon,
                   pc.category_id as category_id_actual
            FROM programs p 
            LEFT JOIN program_categories pc ON (
                (p.category = pc.name) OR 
                (FIND_IN_SET(pc.name, p.category) > 0)
            )
            WHERE p.is_featured = 1 AND p.is_active = 1 AND p.deleted_at IS NULL
            ORDER BY p.title 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getProgramById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   COALESCE(pc.name, p.category) as category_name, 
                   pc.color as category_color, 
                   pc.icon as category_icon,
                   pc.category_id as category_id_actual
            FROM programs p 
            LEFT JOIN program_categories pc ON (
                (p.category = pc.name) OR 
                (FIND_IN_SET(pc.name, p.category) > 0)
            )
            WHERE p.id = ? AND p.is_active = 1 AND p.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getProgramCategories() {
        $stmt = $this->db->query("SELECT category_id as id, name, slug, color, icon FROM program_categories WHERE is_active = 1 AND deleted_at IS NULL ORDER BY sort_order, name");
        return $stmt->fetchAll();
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
        $sql = "SELECT COUNT(DISTINCT p.id) 
                FROM programs p 
                LEFT JOIN program_categories pc ON (
                    (p.category = pc.name) OR 
                    (FIND_IN_SET(pc.name, p.category) > 0)
                )
                WHERE p.is_active = 1 AND p.deleted_at IS NULL";
        $params = [];

        if ($category) {
            // Handle both category ID and name filtering
            if (is_numeric($category)) {
                $sql .= " AND pc.category_id = ?";
                $params[] = $category;
            } else {
                $sql .= " AND (p.category = ? OR pc.name = ?)";
                $params[] = $category;
                $params[] = $category;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
?>