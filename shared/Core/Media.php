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

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'video/mp4', 'video/avi'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('File type not allowed');
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('File size too large');
        }

        $fileName = time() . '_' . basename($file['name']);
        $filePath = $uploadPath . $fileName;
        $relativePath = '/uploads/media/' . $fileName;

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

    public function search($term) {
        $stmt = $this->db->prepare("
            SELECT * FROM media_library 
            WHERE original_name LIKE ? OR file_name LIKE ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute(['%' . $term . '%', '%' . $term . '%']);
        return $stmt->fetchAll();
    }
}

?>
