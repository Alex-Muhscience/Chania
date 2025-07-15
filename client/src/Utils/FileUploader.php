<?php
class FileUploader {
    private $allowedExtensions;
    private $maxSize;
    private $uploadPath;

    public function __construct($uploadPath, $allowedExtensions = [], $maxSize = 5242880) {
        $this->uploadPath = $uploadPath;
        $this->allowedExtensions = $allowedExtensions;
        $this->maxSize = $maxSize;

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    public function upload($file, $prefix = 'file') {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }

        // Validate file size
        if ($file['size'] > $this->maxSize) {
            throw new Exception('File is too large. Maximum size is ' . ($this->maxSize / 1024 / 1024) . 'MB');
        }

        // Validate file extension
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($this->allowedExtensions) && !in_array($fileExt, $this->allowedExtensions)) {
            throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $this->allowedExtensions));
        }

        // Generate unique filename
        $filename = $prefix . '-' . uniqid() . '.' . $fileExt;
        $destination = $this->uploadPath . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to move uploaded file');
        }

        return $filename;
    }

    public static function deleteFile($filePath) {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
}
?>