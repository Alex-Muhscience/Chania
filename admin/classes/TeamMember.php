<?php
require_once __DIR__ . '/../../shared/Core/Database.php';

class TeamMember {
    private $db;
    private $table = 'team_members';

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Get all team members with pagination and filters
     */
    public function getAll($limit = 20, $offset = 0, $search = '', $status = '', $orderBy = 'created_at', $orderDir = 'DESC') {
        $conditions = ["deleted_at IS NULL"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(name LIKE ? OR position LIKE ? OR bio LIKE ? OR email LIKE ?)";
            $searchParam = "%$search%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }

        if ($status !== '') {
            $conditions[] = "status = ?";
            $params[] = $status === '1' ? 'active' : 'inactive';
        }

        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} ORDER BY {$orderBy} {$orderDir} LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count for pagination
     */
    public function getTotalCount($search = '', $status = '') {
        $conditions = ["deleted_at IS NULL"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(name LIKE ? OR position LIKE ? OR bio LIKE ? OR email LIKE ?)";
            $searchParam = "%$search%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }

        if ($status !== '') {
            $conditions[] = "status = ?";
            $params[] = $status === '1' ? 'active' : 'inactive';
        }

        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$whereClause}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Get team member by ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new team member
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, position, bio, email, phone, social_links, image_path, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            $data['name'],
            $data['position'] ?? null,
            $data['bio'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['socialLinks'] ?? null,
            $data['imagePath'] ?? null,
            $data['status'] ?? 'active'
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update team member
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET 
                name = ?, position = ?, bio = ?, email = ?, phone = ?, 
                social_links = ?, image_path = ?, status = ?, updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['position'] ?? null,
            $data['bio'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['socialLinks'] ?? null,
            $data['imagePath'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);
    }

    /**
     * Soft delete team member
     */
    public function delete($id) {
        // Get member info for image cleanup
        $member = $this->getById($id);
        
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([$id]);
        
        // Delete associated image file if exists
        if ($success && $member && !empty($member['image_path'])) {
            $imagePath = __DIR__ . '/../../uploads/' . $member['image_path'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        return $success;
    }

    /**
     * Bulk delete team members
     */
    public function bulkDelete($ids) {
        if (empty($ids)) return false;

        // Get all members for image cleanup
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "SELECT image_path FROM {$this->table} WHERE id IN ($placeholders) AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Soft delete all members
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute($ids);

        // Delete associated image files
        if ($success) {
            foreach ($members as $member) {
                if (!empty($member['image_path'])) {
                    $imagePath = __DIR__ . '/../../uploads/' . $member['image_path'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }
        }

        return $success;
    }

    /**
     * Toggle member status (active/inactive)
     */
    public function toggleStatus($id) {
        $sql = "UPDATE {$this->table} SET 
                status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END,
                updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Get statistics
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN image_path IS NOT NULL THEN 1 ELSE 0 END) as with_photos
                FROM {$this->table} WHERE deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total' => intval($result['total']),
            'active' => intval($result['active']),
            'inactive' => intval($result['inactive']),
            'with_photos' => intval($result['with_photos'])
        ];
    }

    /**
     * Get active team members for public display
     */
    public function getActiveMembers($limit = null) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'active' AND deleted_at IS NULL 
                ORDER BY created_at ASC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validate social links JSON format
     */
    public function validateSocialLinks($socialLinksJson) {
        if (empty($socialLinksJson)) return true;
        
        $decoded = json_decode($socialLinksJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        // Validate URL format for each social link
        if (is_array($decoded)) {
            foreach ($decoded as $platform => $url) {
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Format social links for display
     */
    public function formatSocialLinks($socialLinksJson) {
        if (empty($socialLinksJson)) return [];
        
        $decoded = json_decode($socialLinksJson, true);
        return is_array($decoded) ? $decoded : [];
    }
}
?>
