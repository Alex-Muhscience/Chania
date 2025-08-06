<?php

class Media {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM media_library ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM media_library WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function upload($file, $uploadPath = null) {
        if (!$uploadPath) {
            $uploadPath = __DIR__ . '/../../uploads/media/';
        }

        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $allowedTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'video/mp4', 'video/avi', 'video/mov', 'video/wmv',
            'audio/mp3', 'audio/wav', 'audio/ogg'
        ];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $allowedTypes));
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('File size too large. Maximum size: ' . $this->formatFileSize($maxSize));
        }

        // Generate unique filename
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
        $filePath = $uploadPath . $fileName;
        
        // Calculate relative path from uploads directory
        $uploadsDir = __DIR__ . '/../../uploads/';
        $relativePath = '/' . str_replace($uploadsDir, 'uploads/', $uploadPath) . $fileName;
        $relativePath = str_replace('\\', '/', $relativePath); // Fix Windows path separators

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $stmt = $this->db->prepare("
                INSERT INTO media_library (original_name, file_name, file_path, file_type, mime_type, file_size, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $uploadedBy = $_SESSION['user_id'] ?? 1; // Default to user ID 1 if not logged in
            
            $stmt->execute([
                $file['name'],
                $fileName,
                $relativePath,
                $file['type'],
                $file['type'],
                $file['size'],
                $uploadedBy
            ]);

            return $this->db->lastInsertId();
        } else {
            throw new Exception('Failed to upload file');
        }
    }

    public function delete($id) {
        $media = $this->getById($id);
        if (!$media) {
            throw new Exception('Media not found');
        }

        $fullPath = __DIR__ . '/../../' . ltrim($media['file_path'], '/');
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $stmt = $this->db->prepare("DELETE FROM media_library WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getByType($type) {
        $stmt = $this->db->prepare("SELECT * FROM media_library WHERE file_type LIKE ? ORDER BY created_at DESC");
        $stmt->execute([$type . '%']);
        return $stmt->fetchAll();
    }

    public function search($term, $filter = 'all', $sort = 'newest', $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM media_library WHERE (original_name LIKE ? OR file_name LIKE ?)";
        $params = ['%' . $term . '%', '%' . $term . '%'];
        
        // Add filter conditions
        if ($filter !== 'all') {
            switch ($filter) {
                case 'images':
                    $sql .= " AND mime_type LIKE 'image/%'";
                    break;
                case 'documents':
                    $sql .= " AND (mime_type LIKE 'application/%' OR mime_type LIKE 'text/%')";
                    break;
                case 'videos':
                    $sql .= " AND mime_type LIKE 'video/%'";
                    break;
            }
        }
        
        // Add sorting
        switch ($sort) {
            case 'oldest':
                $sql .= " ORDER BY created_at ASC";
                break;
            case 'name':
                $sql .= " ORDER BY original_name ASC";
                break;
            case 'size':
                $sql .= " ORDER BY file_size DESC";
                break;
            default: // newest
                $sql .= " ORDER BY created_at DESC";
        }
        
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getSearchCount($term, $filter = 'all') {
        $sql = "SELECT COUNT(*) FROM media_library WHERE (original_name LIKE ? OR file_name LIKE ?)";
        $params = ['%' . $term . '%', '%' . $term . '%'];
        
        if ($filter !== 'all') {
            switch ($filter) {
                case 'images':
                    $sql .= " AND mime_type LIKE 'image/%'";
                    break;
                case 'documents':
                    $sql .= " AND (mime_type LIKE 'application/%' OR mime_type LIKE 'text/%')";
                    break;
                case 'videos':
                    $sql .= " AND mime_type LIKE 'video/%'";
                    break;
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    public function getFiltered($filter = 'all', $sort = 'newest', $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM media_library";
        $params = [];
        
        // Add filter conditions
        if ($filter !== 'all') {
            switch ($filter) {
                case 'images':
                    $sql .= " WHERE mime_type LIKE 'image/%'";
                    break;
                case 'documents':
                    $sql .= " WHERE (mime_type LIKE 'application/%' OR mime_type LIKE 'text/%')";
                    break;
                case 'videos':
                    $sql .= " WHERE mime_type LIKE 'video/%'";
                    break;
            }
        }
        
        // Add sorting
        switch ($sort) {
            case 'oldest':
                $sql .= " ORDER BY created_at ASC";
                break;
            case 'name':
                $sql .= " ORDER BY original_name ASC";
                break;
            case 'size':
                $sql .= " ORDER BY file_size DESC";
                break;
            default: // newest
                $sql .= " ORDER BY created_at DESC";
        }
        
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getFilteredCount($filter = 'all') {
        $sql = "SELECT COUNT(*) FROM media_library";
        
        if ($filter !== 'all') {
            switch ($filter) {
                case 'images':
                    $sql .= " WHERE mime_type LIKE 'image/%'";
                    break;
                case 'documents':
                    $sql .= " WHERE (mime_type LIKE 'application/%' OR mime_type LIKE 'text/%')";
                    break;
                case 'videos':
                    $sql .= " WHERE mime_type LIKE 'video/%'";
                    break;
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    public function formatFileSize($bytes) {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}

?>
