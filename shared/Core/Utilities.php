<?php
class Utilities {
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function redirect($url) {
        // Handle relative URLs
        if (strpos($url, '/') === 0) {
            // Use BASE_URL if defined, otherwise construct it
            if (defined('BASE_URL')) {
                $fullUrl = BASE_URL . $url;
            } else {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $fullUrl = $protocol . $host . '/chania' . $url;
            }
        } else {
            // Handle absolute URLs
            $fullUrl = $url;
        }

        header('Location: ' . $fullUrl);
        exit;
    }

    public static function requireLogin() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!self::isLoggedIn()) {
            // Clear any existing problematic redirect URLs
            unset($_SESSION['redirect_url']);
            
            // Only store valid admin page URLs for redirect after login
            if (isset($_SERVER['REQUEST_URI'])) {
                $requestUri = $_SERVER['REQUEST_URI'];
                // Only store URLs that are admin pages and not assets
                if (strpos($requestUri, '/admin/public/') !== false && 
                    !strpos($requestUri, '.css') && 
                    !strpos($requestUri, '.js') && 
                    !strpos($requestUri, '.png') && 
                    !strpos($requestUri, '.jpg') && 
                    !strpos($requestUri, '.jpeg') && 
                    !strpos($requestUri, '.gif') && 
                    !strpos($requestUri, '.ico') && 
                    !strpos($requestUri, '/assets/')) {
                    $_SESSION['redirect_url'] = $requestUri;
                }
            }
            self::redirect('/admin/public/login.php');
        }
    }

    public static function requireRole($requiredRole) {
        self::requireLogin();

        $userRole = $_SESSION['role'] ?? 'guest';
        
        // Check if user has required role (admin has access to all)
        $allowedRoles = [$requiredRole];
        if ($requiredRole !== 'admin') {
            $allowedRoles[] = 'admin'; // Admin can access everything
        }
        
        if (!in_array($userRole, $allowedRoles)) {
            // Redirect to dashboard with error message
            $_SESSION['error_message'] = 'You do not have permission to access this resource.';
            self::redirect('/admin/public/index.php');
        }
    }

    public static function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function formatDate($date, $format = 'Y-m-d H:i:s') {
        return date($format, strtotime($date));
    }

    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Added method to format file size from bytes to human-readable format
    public static function formatFileSize(int $bytes, int $decimals = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = 0;
        $size = $bytes;

        while ($size >= 1024 && $factor < count($units) - 1) {
            $size /= 1024;
            $factor++;
        }

        return sprintf("%.{$decimals}f %s", $size, $units[$factor]);
    }

    // Added method to truncate text with ellipsis
    public static function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }

    // Added method to format time ago
    public static function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) {
            return 'just now';
        } elseif ($time < 3600) {
            return floor($time / 60) . ' minutes ago';
        } elseif ($time < 86400) {
            return floor($time / 3600) . ' hours ago';
        } elseif ($time < 2592000) {
            return floor($time / 86400) . ' days ago';
        } elseif ($time < 31536000) {
            return floor($time / 2592000) . ' months ago';
        } else {
            return floor($time / 31536000) . ' years ago';
        }
    }

    // Method to generate pagination
    public static function generatePagination($currentPage, $totalPages, $baseUrl, $queryParams = []) {
        $pagination = '';
        $visiblePages = 5;
        
        if ($totalPages <= 1) {
            return $pagination;
        }
        
        // Build query string
        $queryString = '';
        if (!empty($queryParams)) {
            unset($queryParams['page']); // Remove page parameter as we'll add it manually
            if (!empty($queryParams)) {
                $queryString = '&' . http_build_query($queryParams);
            }
        }
        
        $pagination .= '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        
        // Previous button
        if ($currentPage > 1) {
            $prevPage = $currentPage - 1;
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $prevPage . $queryString . '">Previous</a></li>';
        } else {
            $pagination .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }
        
        // Page numbers
        $startPage = max(1, $currentPage - floor($visiblePages / 2));
        $endPage = min($totalPages, $startPage + $visiblePages - 1);
        
        if ($startPage > 1) {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=1' . $queryString . '">1</a></li>';
            if ($startPage > 2) {
                $pagination .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $currentPage) {
                $pagination .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . $queryString . '">' . $i . '</a></li>';
            }
        }
        
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                $pagination .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $totalPages . $queryString . '">' . $totalPages . '</a></li>';
        }
        
        // Next button
        if ($currentPage < $totalPages) {
            $nextPage = $currentPage + 1;
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $nextPage . $queryString . '">Next</a></li>';
        } else {
            $pagination .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }
        
        $pagination .= '</ul></nav>';
        
        return $pagination;
    }

    // Enhanced method to log admin activities with comprehensive audit trail
    public static function logActivity($userId, $action, $entityType = null, $entityId = null, $details = null, $metadata = null) {
        try {
            $db = (new Database())->connect();
            
            // Prepare comprehensive log data
            $logData = [
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'ip_address' => self::getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
                'referer' => $_SERVER['HTTP_REFERER'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Enhanced details structure
            $enhancedDetails = [];
            
            // Include original details if provided
            if ($details !== null) {
                if (is_array($details)) {
                    $enhancedDetails = array_merge($enhancedDetails, $details);
                } else {
                    $enhancedDetails['description'] = $details;
                }
            }
            
            // Add metadata if provided
            if ($metadata !== null && is_array($metadata)) {
                $enhancedDetails['metadata'] = $metadata;
            }
            
            // Add session information if available
            if (session_status() === PHP_SESSION_ACTIVE) {
                $enhancedDetails['session_data'] = [
                    'session_id' => session_id(),
                    'admin_user_id' => $_SESSION['admin_user_id'] ?? null,
                    'login_time' => $_SESSION['login_time'] ?? null
                ];
            }
            
            // Add request data for certain actions
            if (in_array($action, ['create', 'update', 'delete']) && !empty($_POST)) {
                $enhancedDetails['form_data'] = self::sanitizeLogData($_POST);
            }
            
            // Add GET parameters for view actions
            if ($action === 'view' && !empty($_GET)) {
                $enhancedDetails['query_params'] = self::sanitizeLogData($_GET);
            }
            
            $stmt = $db->prepare("
                INSERT INTO admin_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $logData['user_id'],
                $logData['action'],
                $logData['entity_type'],
                $logData['entity_id'],
                !empty($enhancedDetails) ? json_encode($enhancedDetails, JSON_PRETTY_PRINT) : null,
                $logData['ip_address'],
                $logData['user_agent'],
                $logData['created_at']
            ]);
            
        } catch (Exception $e) {
            error_log('Failed to log activity: ' . $e->getMessage());
        }
    }
    
    // Helper method to get real client IP address
    private static function getClientIp() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    // Helper method to sanitize sensitive data from logs
    private static function sanitizeLogData($data) {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key', 'secret', 'csrf_token'];
        
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                if (in_array(strtolower($key), $sensitiveFields)) {
                    $sanitized[$key] = '[REDACTED]';
                } else if (is_array($value)) {
                    $sanitized[$key] = self::sanitizeLogData($value);
                } else {
                    $sanitized[$key] = $value;
                }
            }
            return $sanitized;
        }
        
        return $data;
    }
    
    // Convenience methods for common audit actions
    public static function logLogin($userId, $success = true, $details = []) {
        $action = $success ? 'login_success' : 'login_failed';
        $metadata = [
            'authentication_method' => 'password',
            'browser' => self::getBrowserInfo(),
            'timestamp' => time()
        ];
        
        self::logActivity($userId, $action, 'user', $userId, $details, $metadata);
    }
    
    public static function logLogout($userId, $details = []) {
        $metadata = [
            'session_duration' => isset($_SESSION['login_time']) ? (time() - $_SESSION['login_time']) : null,
            'timestamp' => time()
        ];
        
        self::logActivity($userId, 'logout', 'user', $userId, $details, $metadata);
    }
    
    public static function logSecurityEvent($userId, $event, $severity = 'medium', $details = []) {
        $metadata = [
            'security_event' => true,
            'severity' => $severity,
            'requires_review' => in_array($severity, ['high', 'critical']),
            'timestamp' => time()
        ];
        
        self::logActivity($userId, 'security_event', 'security', null, $details, $metadata);
    }
    
    public static function logDataChange($userId, $entityType, $entityId, $oldData, $newData, $action = 'update') {
        $changes = [];
        
        // Calculate what changed
        if (is_array($oldData) && is_array($newData)) {
            foreach ($newData as $key => $newValue) {
                $oldValue = $oldData[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            }
        }
        
        $details = [
            'changes' => $changes,
            'fields_changed' => array_keys($changes),
            'change_count' => count($changes)
        ];
        
        $metadata = [
            'data_integrity' => true,
            'backup_recommended' => count($changes) > 5,
            'timestamp' => time()
        ];
        
        self::logActivity($userId, $action, $entityType, $entityId, $details, $metadata);
    }
    
    // Helper method to extract basic browser information
    private static function getBrowserInfo() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $browsers = [
            'Chrome' => 'Chrome',
            'Firefox' => 'Firefox',
            'Safari' => 'Safari',
            'Edge' => 'Edge',
            'Opera' => 'Opera',
            'Internet Explorer' => 'MSIE'
        ];
        
        foreach ($browsers as $browser => $pattern) {
            if (strpos($userAgent, $pattern) !== false) {
                return $browser;
            }
        }
        
        return 'Unknown';
    }

    // Method to generate secure random passwords
    public static function generatePassword($length = 12) {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $password = '';
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $symbols[rand(0, strlen($symbols) - 1)];
        
        $all = $uppercase . $lowercase . $numbers . $symbols;
        
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[rand(0, strlen($all) - 1)];
        }
        
        return str_shuffle($password);
    }
    
    // Method to generate URL-friendly slugs
    public static function slugify($text) {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Replace non-alphanumeric characters with dashes
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Remove leading and trailing dashes
        $text = trim($text, '-');
        
        // Ensure uniqueness by adding timestamp if needed
        if (empty($text)) {
            $text = 'item-' . time();
        }
        
        return $text;
    }
    
    // Alias for slugify method for backward compatibility
    public static function generateSlug($text) {
        return self::slugify($text);
    }
    
    // CSRF Token methods
    public static function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    public static function validateCsrfToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        
        // Optionally remove the token after validation (one-time use)
        // unset($_SESSION['csrf_token']);
        
        return true;
    }

    // Method to upload files securely and track them in the database
    public static function uploadFile($file, $entityType = null, $entityId = null, $uploadSubDir = 'general', $allowedTypes = []) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'No valid file uploaded'];
        }

        // File size check
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'error' => 'File size exceeds maximum allowed size'];
        }

        // Get file info
        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $mimeType = $file['type'];
        $fileSize = $file['size'];
        
        // Allowed types check
        $allAllowedTypes = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOCUMENT_TYPES);
        if (!empty($allowedTypes)) {
            $allAllowedTypes = $allowedTypes;
        }

        if (!in_array($extension, $allAllowedTypes)) {
            return ['success' => false, 'error' => 'File type not allowed'];
        }

        // Generate unique filename and path
        $storedName = uniqid() . '_' . time() . '.' . $extension;
        $destinationDir = UPLOAD_PATH . '/' . $uploadSubDir;
        $destinationPath = $destinationDir . '/' . $storedName;

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        // Move the file
        if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
            return ['success' => false, 'error' => 'Failed to move uploaded file'];
        }

        // Insert file record into database
        try {
            $db = (new Database())->connect();
            $stmt = $db->prepare("INSERT INTO file_uploads (original_name, stored_name, file_path, file_size, file_type, mime_type, entity_type, entity_id, uploaded_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            
            $fileType = 'document'; // Default type
            if (in_array($extension, ALLOWED_IMAGE_TYPES)) {
                $fileType = 'image';
            }

            $stmt->execute([
                $originalName,
                $storedName,
                $destinationPath,
                $fileSize,
                $fileType,
                $mimeType,
                $entityType,
                $entityId,
                $_SESSION['user_id'] ?? null
            ]);

            return ['success' => true, 'file_id' => $db->lastInsertId()];

        } catch (PDOException $e) {
            // Clean up by deleting the uploaded file if DB insert fails
            if (file_exists($destinationPath)) {
                unlink($destinationPath);
            }
            error_log('File upload DB error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error during file upload'];
        }
    }
}
