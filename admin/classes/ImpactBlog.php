<?php

require_once __DIR__ . '/../../shared/Core/Database.php';

class ImpactBlog {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all impact blogs with filters
     */
    public function getAll($limit, $offset, $category = '', $status = '', $search = '') {
        $query = "SELECT * FROM impact_blogs WHERE deleted_at IS NULL";
        $params = [];

        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }

        if ($status === 'active') {
            $query .= " AND is_active = 1";
        } elseif ($status === 'inactive') {
            $query .= " AND is_active = 0";
        }

        if ($search) {
            $query .= " AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $query .= " ORDER BY sort_order ASC, created_at DESC LIMIT ?, ?";
        $params[] = (int)$offset;
        $params[] = (int)$limit;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count with filters
     */
    public function getTotalCount($category = '', $status = '', $search = '') {
        $query = "SELECT COUNT(*) FROM impact_blogs WHERE deleted_at IS NULL";
        $params = [];

        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }

        if ($status === 'active') {
            $query .= " AND is_active = 1";
        } elseif ($status === 'inactive') {
            $query .= " AND is_active = 0";
        }

        if ($search) {
            $query .= " AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Get impact blog by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT * FROM impact_blogs 
            WHERE id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get impact blog by slug
     */
    public function getBySlug($slug) {
        $stmt = $this->db->prepare("
            SELECT * FROM impact_blogs 
            WHERE slug = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new impact blog
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO impact_blogs (
                title, slug, excerpt, content, category, featured_image, 
                video_url, video_embed_code, stats_data, tags, author_name, 
                is_active, sort_order, published_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['excerpt'],
            $data['content'],
            $data['category'],
            $data['featured_image'] ?? null,
            $data['video_url'] ?? null,
            $data['video_embed_code'] ?? null,
            $data['stats_data'] ?? null,
            $data['tags'] ?? null,
            $data['author_name'] ?? null,
            $data['is_active'] ? 1 : 0,
            $data['sort_order'] ?? 0,
            $data['is_active'] ? date('Y-m-d H:i:s') : null
        ]);

        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Update impact blog
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE impact_blogs SET 
                title = ?, slug = ?, excerpt = ?, content = ?, category = ?, 
                featured_image = ?, video_url = ?, video_embed_code = ?, 
                stats_data = ?, tags = ?, author_name = ?, is_active = ?, 
                sort_order = ?, published_at = ?, updated_at = NOW()
            WHERE id = ? AND deleted_at IS NULL
        ");
        
        return $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['excerpt'],
            $data['content'],
            $data['category'],
            $data['featured_image'] ?? null,
            $data['video_url'] ?? null,
            $data['video_embed_code'] ?? null,
            $data['stats_data'] ?? null,
            $data['tags'] ?? null,
            $data['author_name'] ?? null,
            $data['is_active'] ? 1 : 0,
            $data['sort_order'] ?? 0,
            $data['is_active'] ? date('Y-m-d H:i:s') : null,
            $id
        ]);
    }

    /**
     * Soft delete impact blog
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE impact_blogs SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Hard delete impact blog
     */
    public function hardDelete($id) {
        $stmt = $this->db->prepare("DELETE FROM impact_blogs WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all categories
     */
    public function getCategories() {
        $stmt = $this->db->query("
            SELECT DISTINCT category 
            FROM impact_blogs 
            WHERE category IS NOT NULL AND category != '' AND deleted_at IS NULL 
            ORDER BY category ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Activate impact blog
     */
    public function activate($id) {
        $stmt = $this->db->prepare("
            UPDATE impact_blogs 
            SET is_active = 1, published_at = NOW(), updated_at = NOW() 
            WHERE id = ? AND deleted_at IS NULL
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Deactivate impact blog
     */
    public function deactivate($id) {
        $stmt = $this->db->prepare("
            UPDATE impact_blogs 
            SET is_active = 0, updated_at = NOW() 
            WHERE id = ? AND deleted_at IS NULL
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Update sort order
     */
    public function updateSortOrder($id, $sortOrder) {
        $stmt = $this->db->prepare("
            UPDATE impact_blogs 
            SET sort_order = ?, updated_at = NOW() 
            WHERE id = ? AND deleted_at IS NULL
        ");
        return $stmt->execute([$sortOrder, $id]);
    }

    /**
     * Generate unique slug
     */
    public function generateSlug($title, $excludeId = null) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private function slugExists($slug, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM impact_blogs 
                WHERE slug = ? AND id != ? AND deleted_at IS NULL
            ");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM impact_blogs 
                WHERE slug = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$slug]);
        }
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get published impact blogs for frontend
     */
    public function getPublished($limit = 10, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT * FROM impact_blogs 
            WHERE is_active = 1 AND deleted_at IS NULL 
            ORDER BY sort_order ASC, created_at DESC 
            LIMIT ?, ?
        ");
        $stmt->execute([(int)$offset, (int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get stats for dashboard
     */
    public function getStats() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
                SUM(view_count) as total_views
            FROM impact_blogs 
            WHERE deleted_at IS NULL
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update view count
     */
    public function incrementViewCount($id) {
        $stmt = $this->db->prepare("
            UPDATE impact_blogs 
            SET view_count = view_count + 1 
            WHERE id = ? AND deleted_at IS NULL
        ");
        return $stmt->execute([$id]);
    }
}
?>
