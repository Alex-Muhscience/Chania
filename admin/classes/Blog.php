<?php
require_once __DIR__ . '/../../shared/Core/Database.php';

class Blog {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll($limit, $offset, $category, $status, $search) {
        $query = "SELECT bp.*, u.username as author_username FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE 1=1";
        $params = [];

        if ($category) {
            $query .= " AND bp.category = ?";
            $params[] = $category;
        }

        if ($status) {
            $query .= " AND bp.status = ?";
            $params[] = $status;
        }

        if ($search) {
            $query .= " AND (bp.title LIKE ? OR bp.body LIKE ? OR bp.excerpt LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $query .= " ORDER BY bp.created_at DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount($category, $status, $search) {
        $query = "SELECT COUNT(*) FROM blog_posts WHERE 1=1";
        $params = [];

        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }

        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        if ($search) {
            $query .= " AND (title LIKE ? OR body LIKE ? OR excerpt LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBySlug($slug) {
        $stmt = $this->db->prepare("SELECT bp.*, u.username as author_username FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE bp.slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO blog_posts (title, slug, body, category, excerpt, image, is_featured, status, published_at, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['title'], 
            $data['slug'], 
            $data['body'], 
            $data['category'], 
            $data['excerpt'], 
            $data['image'], 
            $data['is_featured'], 
            $data['status'], 
            $data['published_at'], 
            $data['author_id']
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE blog_posts SET title = ?, slug = ?, body = ?, category = ?, excerpt = ?, image = ?, is_featured = ?, status = ?, published_at = ? WHERE id = ?");
        return $stmt->execute([
            $data['title'], 
            $data['slug'], 
            $data['body'], 
            $data['category'], 
            $data['excerpt'], 
            $data['image'], 
            $data['is_featured'], 
            $data['status'], 
            $data['published_at'], 
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM blog_posts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getCategories() {
        $stmt = $this->db->query("SELECT DISTINCT category FROM blog_posts ORDER BY category ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function publish($id) {
        $stmt = $this->db->prepare("UPDATE blog_posts SET status = 'published', published_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function unpublish($id) {
        $stmt = $this->db->prepare("UPDATE blog_posts SET status = 'draft', published_at = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function setFeatured($id, $featured = true) {
        $stmt = $this->db->prepare("UPDATE blog_posts SET is_featured = ? WHERE id = ?");
        return $stmt->execute([$featured ? 1 : 0, $id]);
    }

    public function generateSlug($title, $excludeId = null) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $originalSlug = $slug;
        $counter = 1;

        // Check if slug exists
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists($slug, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        return $stmt->fetchColumn() > 0;
    }

    public function getPublishedPosts($limit = 10, $offset = 0) {
        $stmt = $this->db->prepare("SELECT bp.*, u.username as author_username FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE bp.status = 'published' ORDER BY bp.published_at DESC LIMIT ?, ?");
        $stmt->execute([$offset, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFeaturedPosts($limit = 3) {
        $stmt = $this->db->prepare("SELECT bp.*, u.username as author_username FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE bp.status = 'published' AND bp.is_featured = 1 ORDER BY bp.published_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
