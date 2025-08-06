<?php
/**
 * Base API Controller
 * Provides common functionality for all API controllers
 */

require_once __DIR__ . '/../../../shared/Core/Environment.php';
require_once __DIR__ . '/../../../shared/Core/Database.php';
require_once __DIR__ . '/../../../shared/Core/ApiConfig.php';
require_once __DIR__ . '/../../../shared/Core/Cache.php';
require_once __DIR__ . '/../../../shared/Core/JWT.php';
require_once __DIR__ . '/../../../shared/Core/RateLimiter.php';

abstract class BaseApiController {
    protected $db;
    protected $request;
    protected $response;
    protected $currentUser;
    protected $currentUserId;
    
    public function __construct() {
        // Load environment
        Environment::load();
        
        // Initialize database connection
        try {
            $this->db = (new Database())->connect();
        } catch (Exception $e) {
            $this->sendError('Database connection failed', 500);
            exit;
        }
        
        // Parse request data
        $this->parseRequest();
        
        // Enable CORS for API endpoints
        $this->enableCors();
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        // Apply rate limiting (except for auth endpoints which have their own limits)
        $this->applyRateLimiting();
    }
    
    /**
     * Parse incoming request data
     */
    private function parseRequest() {
        $this->request = [
            'method' => $_SERVER['REQUEST_METHOD'],
            'headers' => getallheaders() ?: [],
            'query' => $_GET,
            'body' => []
        ];
        
        // Parse request body based on content type
        $contentType = $this->request['headers']['Content-Type'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $this->request['body'] = json_decode($input, true) ?: [];
        } else {
            $this->request['body'] = $_POST;
        }
    }
    
    /**
     * Enable CORS headers
     */
    private function enableCors() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
    }
    
    /**
     * Send successful response
     */
    protected function sendSuccess($data = null, $message = 'Success', $statusCode = 200) {
        Cache::cleanup();
        http_response_code($statusCode);
        echo json_encode(ApiConfig::createResponse(
            ApiConfig::STATUS_SUCCESS,
            $message,
            $data
        ));
        exit;
    }
    
    /**
     * Send error response
     */
    protected function sendError($message = 'An error occurred', $statusCode = 400, $errorCode = null) {
        http_response_code($statusCode);
        echo json_encode(ApiConfig::createResponse(
            ApiConfig::STATUS_ERROR,
            $message,
            null,
            $errorCode
        ));
        exit;
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($fields, $data = null) {
        $data = $data ?: $this->request['body'];
        $missing = [];
        
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->sendError(
                'Missing required fields: ' . implode(', ', $missing),
                400,
                'MISSING_FIELDS'
            );
        }
        
        return true;
    }
    
    /**
     * Validate email format
     */
    protected function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->sendError(
                'Invalid email format',
                400,
                'INVALID_EMAIL'
            );
        }
        return true;
    }
    
    /**
     * Sanitize input data
     */
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Get paginated results
     */
    protected function paginate($query, $params = [], $page = 1, $limit = 20) {
        $page = max(1, intval($page));
        $limit = max(1, min(100, intval($limit))); // Max 100 items per page
        $offset = ($page - 1) * $limit;
        
        // Cache key for this specific query
        $cacheKey = 'paginate_' . md5($query . serialize($params) . $page . $limit);
        
        return Cache::remember($cacheKey, function() use ($query, $params, $page, $limit, $offset) {
            // Get total count - handle complex SELECT clauses better
            $countQuery = preg_replace('/SELECT[\s\S]+?FROM/i', 'SELECT COUNT(*) as total FROM', $query);
            $countQuery = preg_replace('/ORDER BY[\s\S]+$/i', '', $countQuery);
            
            try {
                $stmt = $this->db->prepare($countQuery);
                $stmt->execute($params);
                $result = $stmt->fetch();
                $total = $result && isset($result['total']) ? $result['total'] : 0;
                
                // Get paginated results
                $query .= " LIMIT {$limit} OFFSET {$offset}";
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
                $data = $stmt->fetchAll();
                
                return [
                    'data' => $data,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $total,
                        'pages' => ceil($total / $limit),
                        'has_next' => $page < ceil($total / $limit),
                        'has_prev' => $page > 1
                    ]
                ];
                
            } catch (Exception $e) {
                error_log('Pagination error: ' . $e->getMessage());
                throw $e; // Re-throw to be handled by the caller
            }
        }, 300); // Cache for 5 minutes
    }
    
    /**
     * Check authentication using JWT tokens
     */
    protected function requireAuth($requiredRole = null) {
        $authHeader = $this->request['headers']['Authorization'] ?? '';
        
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->sendError('Authorization header required', 401, 'AUTH_REQUIRED');
        }
        
        $token = $matches[1];
        
        try {
            $payload = JWT::decode($token);
            
            // Get fresh user data to ensure user is still active
            $stmt = $this->db->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                LEFT JOIN user_roles r ON u.role_id = r.id 
                WHERE u.id = ? AND u.is_active = 1
            ");
            $stmt->execute([$payload['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $this->sendError('User not found or inactive', 401, 'USER_NOT_FOUND');
            }
            
            // Check role permissions if required
            if ($requiredRole && $user['role_name'] !== $requiredRole && $user['role_name'] !== 'admin') {
                $this->sendError('Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            // Store user info for use in controller methods
            $this->currentUser = $user;
            $this->currentUserId = $user['id'];
            
            return $user;
            
        } catch (Exception $e) {
            $this->sendError('Invalid or expired token', 401, 'INVALID_TOKEN');
        }
    }
    
    /**
     * Log activity for admin synchronization
     */
    protected function logActivity($action, $entityType, $entityId, $details, $userId = null) {
        ApiConfig::logActivity($this->db, $action, $entityType, $entityId, $details, $userId);
    }
    
    /**
     * Get client IP address
     */
    protected function getClientIp() {
        return ApiConfig::getClientIp();
    }
    
    /**
     * Get current authenticated user (requires requireAuth to be called first)
     */
    protected function getCurrentUser() {
        return $this->currentUser;
    }
    
    /**
     * Get current authenticated user ID (requires requireAuth to be called first)
     */
    protected function getCurrentUserId() {
        return $this->currentUserId;
    }
    
    /**
     * Check if current user has specific role
     */
    protected function hasRole($role) {
        return $this->currentUser && $this->currentUser['role_name'] === $role;
    }
    
    /**
     * Check if current user is admin
     */
    protected function isAdmin() {
        return $this->hasRole('admin');
    }
    
    /**
     * Apply rate limiting to API requests
     */
    private function applyRateLimiting() {
        // Skip rate limiting for auth endpoints (they have stricter limits)
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (strpos($currentPath, '/auth/') !== false) {
            return;
        }
        
        $rateLimiter = new RateLimiter($this->db);
        $ipAddress = $this->getClientIp();
        $endpoint = $_SERVER['REQUEST_METHOD'] . ' ' . $currentPath;
        
        if (!$rateLimiter->check($ipAddress, $endpoint)) {
            $this->sendError('Rate limit exceeded. Please try again later.', 429, 'RATE_LIMIT_EXCEEDED');
        }
    }
}
?>
