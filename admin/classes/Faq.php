<?php
require_once __DIR__ . '/../../shared/Core/Database.php';

class Faq {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll($limit, $offset, $category, $status, $search) {
        $query = "SELECT f.*, u.username as created_by_username FROM faqs f LEFT JOIN users u ON f.created_by = u.id WHERE 1=1";
        $params = [];

        if ($category) {
            $query .= " AND f.category = ?";
            $params[] = $category;
        }

        if ($status !== null) {
            $query .= " AND f.is_active = ?";
            $params[] = $status;
        }

        if ($search) {
            $query .= " AND (f.question LIKE ? OR f.answer LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $query .= " ORDER BY f.display_order ASC, f.created_at DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount($category, $status, $search) {
        $query = "SELECT COUNT(*) FROM faqs WHERE 1=1";
        $params = [];

        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }

        if ($status !== null) {
            $query .= " AND is_active = ?";
            $params[] = $status;
        }

        if ($search) {
            $query .= " AND (question LIKE ? OR answer LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM faqs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO faqs (question, answer, category, is_active, display_order, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$data['question'], $data['answer'], $data['category'], $data['is_active'], $data['display_order'], $data['created_by']]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE faqs SET question = ?, answer = ?, category = ?, is_active = ?, display_order = ? WHERE id = ?");
        return $stmt->execute([$data['question'], $data['answer'], $data['category'], $data['is_active'], $data['display_order'], $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM faqs WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getCategories() {
        $stmt = $this->db->query("SELECT DISTINCT category FROM faqs ORDER BY category ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function activate($id) {
        $stmt = $this->db->prepare("UPDATE faqs SET is_active = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deactivate($id) {
        $stmt = $this->db->prepare("UPDATE faqs SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
