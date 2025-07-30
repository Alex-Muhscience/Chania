<?php

class Testimonial {
    private $conn;
    private $table = 'testimonials';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all testimonials with filtering and pagination
    public function getAll($limit, $offset, $search, $programId, $featured) {
        $query = "SELECT t.*, p.title as program_title
                  FROM " . $this->table . " t
                  LEFT JOIN programs p ON t.program_id = p.id";
        
        $conditions = ["t.deleted_at IS NULL"];
        $params = [];

        if ($search) {
            $conditions[] = "(t.name LIKE :search OR t.position LIKE :search OR t.testimonial LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if ($programId) {
            $conditions[] = "t.program_id = :programId";
            $params[':programId'] = $programId;
        }

        if ($featured !== '') {
            $conditions[] = "t.is_featured = :featured";
            $params[':featured'] = $featured;
        }

        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $query .= " ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get total count of testimonials
    public function getTotalCount($search, $programId, $featured) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " t";
        
        $conditions = ["t.deleted_at IS NULL"];
        $params = [];

        if ($search) {
            $conditions[] = "(t.name LIKE :search OR t.position LIKE :search OR t.testimonial LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if ($programId) {
            $conditions[] = "t.program_id = :programId";
            $params[':programId'] = $programId;
        }

        if ($featured !== '') {
            $conditions[] = "t.is_featured = :featured";
            $params[':featured'] = $featured;
        }

        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Get a single testimonial by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create a new testimonial
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                    (name, position, company, testimonial, rating, program_id, is_featured, is_approved, image_path, created_at, updated_at)
                  VALUES 
                    (:name, :position, :company, :testimonial, :rating, :program_id, :is_featured, :is_approved, :image_path, NOW(), NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $data['authorName']);
        $stmt->bindParam(':position', $data['authorTitle']);
        $stmt->bindParam(':company', $data['authorCompany']);
        $stmt->bindParam(':testimonial', $data['content']);
        $stmt->bindParam(':rating', $data['rating']);
        $stmt->bindParam(':program_id', $data['programId']);
        $stmt->bindParam(':is_featured', $data['isFeatured']);
        $stmt->bindParam(':is_approved', $data['isActive']);
        $stmt->bindParam(':image_path', $data['imagePath']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Update a testimonial
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                    SET name = :name, position = :position, company = :company, testimonial = :testimonial, 
                        rating = :rating, program_id = :program_id, is_featured = :is_featured, 
                        is_approved = :is_approved, image_path = :image_path, updated_at = NOW()
                    WHERE id = :id AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['authorName']);
        $stmt->bindParam(':position', $data['authorTitle']);
        $stmt->bindParam(':company', $data['authorCompany']);
        $stmt->bindParam(':testimonial', $data['content']);
        $stmt->bindParam(':rating', $data['rating']);
        $stmt->bindParam(':program_id', $data['programId']);
        $stmt->bindParam(':is_featured', $data['isFeatured']);
        $stmt->bindParam(':is_approved', $data['isActive']);
        $stmt->bindParam(':image_path', $data['imagePath']);

        return $stmt->execute();
    }

    // Delete a testimonial
    public function delete($id) {
        $query = "UPDATE " . $this->table . " SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Bulk delete testimonials
    public function bulkDelete($ids) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $query = "UPDATE " . $this->table . " SET deleted_at = NOW() WHERE id IN ($placeholders)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($ids);
    }

    // Toggle featured status
    public function toggleFeatured($id) {
        $query = "UPDATE " . $this->table . " SET is_featured = NOT is_featured WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    // Toggle active status
    public function toggleActive($id) {
        $query = "UPDATE " . $this->table . " SET is_approved = NOT is_approved WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Get programs for filter dropdown
    public function getPrograms() {
        $query = "SELECT id, title FROM programs WHERE is_active = TRUE AND deleted_at IS NULL ORDER BY title ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get statistics
    public function getStats() {
        $query = "SELECT COUNT(*) as total, 
                         SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured, 
                         SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved, 
                         AVG(rating) as avg_rating 
                  FROM " . $this->table . " WHERE deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
