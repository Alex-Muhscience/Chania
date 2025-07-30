<?php

class Program {
    private $conn;
    private $table = 'programs';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all programs with filtering, sorting, and pagination
    public function getAll($limit, $offset, $search = '', $sortBy = 'created_at', $sortOrder = 'DESC', $filter = null) {
        $query = "SELECT * FROM " . $this->table;
        $conditions = [];
        $params = [];

        // Add search conditions
        if ($search) {
            $conditions[] = "(title LIKE :search OR description LIKE :search OR short_description LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // Add filter conditions
        if ($filter) {
            switch ($filter) {
                case 'active':
                    $conditions[] = "is_active = 1";
                    break;
                case 'inactive':
                    $conditions[] = "is_active = 0";
                    break;
                case 'free':
                    $conditions[] = "(fee IS NULL OR fee = 0)";
                    break;
                case 'paid':
                    $conditions[] = "fee > 0";
                    break;
                case 'featured':
                    $conditions[] = "is_featured = 1";
                    break;
            }
        }

        // Add WHERE clause if conditions exist
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        // Add ORDER BY and LIMIT
        $query .= " ORDER BY $sortBy $sortOrder LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get total count of programs
    public function getTotalCount($search = '', $filter = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table;
        $conditions = [];
        $params = [];

        if ($search) {
            $conditions[] = "(title LIKE :search OR description LIKE :search OR short_description LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if ($filter) {
            switch ($filter) {
                case 'active':
                    $conditions[] = "is_active = 1";
                    break;
                case 'inactive':
                    $conditions[] = "is_active = 0";
                    break;
                case 'free':
                    $conditions[] = "(fee IS NULL OR fee = 0)";
                    break;
                case 'paid':
                    $conditions[] = "fee > 0";
                    break;
                case 'featured':
                    $conditions[] = "is_featured = 1";
                    break;
            }
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Get a single program by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create a new program
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (title, slug, description, short_description, category, duration, 
                   difficulty_level, fee, max_participants, start_date, end_date, 
                   image_path, instructor_name, location, is_featured, is_active, 
                   is_published, is_online, created_at, updated_at)
                  VALUES 
                  (:title, :slug, :description, :short_description, :category, :duration,
                   :difficulty_level, :fee, :max_participants, :start_date, :end_date,
                   :image_path, :instructor_name, :location, :is_featured, :is_active,
                   :is_published, :is_online, NOW(), NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':slug', $data['slug']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':short_description', $data['short_description']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':duration', $data['duration']);
        $stmt->bindParam(':difficulty_level', $data['difficulty_level']);
        $stmt->bindParam(':fee', $data['fee']);
        $stmt->bindParam(':max_participants', $data['max_participants']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':image_path', $data['image_path']);
        $stmt->bindParam(':instructor_name', $data['instructor_name']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':is_featured', $data['is_featured']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':is_published', $data['is_published']);
        $stmt->bindParam(':is_online', $data['is_online']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Update a program
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET title = :title, slug = :slug, description = :description, 
                      short_description = :short_description, category = :category,
                      duration = :duration, difficulty_level = :difficulty_level,
                      fee = :fee, max_participants = :max_participants,
                      start_date = :start_date, end_date = :end_date,
                      image_path = :image_path, instructor_name = :instructor_name,
                      location = :location, is_featured = :is_featured,
                      is_active = :is_active, is_published = :is_published,
                      is_online = :is_online, updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':slug', $data['slug']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':short_description', $data['short_description']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':duration', $data['duration']);
        $stmt->bindParam(':difficulty_level', $data['difficulty_level']);
        $stmt->bindParam(':fee', $data['fee']);
        $stmt->bindParam(':max_participants', $data['max_participants']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':image_path', $data['image_path']);
        $stmt->bindParam(':instructor_name', $data['instructor_name']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':is_featured', $data['is_featured']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':is_published', $data['is_published']);
        $stmt->bindParam(':is_online', $data['is_online']);

        return $stmt->execute();
    }

    // Delete a program
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Toggle program status
    public function toggleStatus($id) {
        $query = "UPDATE " . $this->table . " SET is_active = NOT is_active WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Toggle featured status
    public function toggleFeatured($id) {
        $query = "UPDATE " . $this->table . " SET is_featured = NOT is_featured WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Get program statistics
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured,
                    SUM(CASE WHEN fee > 0 THEN 1 ELSE 0 END) as paid,
                    AVG(fee) as avg_fee
                  FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get featured programs
    public function getFeatured($limit = 5) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE is_featured = 1 AND is_active = 1 
                  ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get upcoming programs
    public function getUpcoming($limit = 5) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE is_active = 1 AND start_date > NOW() 
                  ORDER BY start_date ASC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Generate slug for SEO-friendly URLs
    public function generateSlug($title, $id = null) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        
        // Check if slug exists
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE slug = :slug";
        if ($id) {
            $query .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        if ($id) {
            $stmt->bindParam(':id', $id);
        }
        $stmt->execute();
        
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }
        
        return $slug;
    }

    // Get enrollment count for a program
    public function getEnrollmentCount($id) {
        $query = "SELECT COUNT(*) FROM applications WHERE program_id = :id AND status = 'approved'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Check if program has capacity
    public function hasCapacity($id) {
        $program = $this->getById($id);
        if (!$program || !$program['max_participants']) {
            return true; // No capacity limit
        }
        
        $enrollments = $this->getEnrollmentCount($id);
        return $enrollments < $program['max_participants'];
    }
}
?>
