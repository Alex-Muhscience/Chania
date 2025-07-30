<?php

class Page {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAll($published_only = false) {
        $sql = "SELECT * FROM pages";
        if ($published_only) {
            $sql .= " WHERE is_published = 1";
        }
        $sql .= " ORDER BY title ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getBySlug($slug) {
        $stmt = $this->db->prepare("SELECT * FROM pages WHERE slug = ? AND is_published = 1");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO pages (title, slug, content, meta_title, meta_description, is_published, template) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['title'],
            $this->generateSlug($data['title']),
            $data['content'],
            $data['meta_title'] ?? '',
            $data['meta_description'] ?? '',
            $data['is_published'] ?? 1,
            $data['template'] ?? 'default'
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE pages 
            SET title = ?, slug = ?, content = ?, meta_title = ?, meta_description = ?, is_published = ?, template = ?
            WHERE id = ?
        ");
        
        $slug = !empty($data['slug']) ? $this->sanitizeSlug($data['slug']) : $this->generateSlug($data['title']);
        
        return $stmt->execute([
            $data['title'],
            $slug,
            $data['content'],
            $data['meta_title'] ?? '',
            $data['meta_description'] ?? '',
            $data['is_published'] ?? 1,
            $data['template'] ?? 'default',
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM pages WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function search($term) {
        $stmt = $this->db->prepare("
            SELECT * FROM pages 
            WHERE title LIKE ? OR content LIKE ? 
            ORDER BY title ASC
        ");
        $searchTerm = '%' . $term . '%';
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    private function generateSlug($title) {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Check if slug exists and make it unique
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    private function sanitizeSlug($slug) {
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }

    private function slugExists($slug, $excludeId = null) {
        $sql = "SELECT id FROM pages WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function getTemplates() {
        return [
            'default' => 'Default Template',
            'about' => 'About Page Template',
            'services' => 'Services Template',
            'contact' => 'Contact Template',
            'landing' => 'Landing Page Template'
        ];
    }

    public function publish($id) {
        $stmt = $this->db->prepare("UPDATE pages SET is_published = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function unpublish($id) {
        $stmt = $this->db->prepare("UPDATE pages SET is_published = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

?>
