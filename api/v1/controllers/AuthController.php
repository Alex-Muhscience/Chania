<?php
/**
 * Authentication API Controller
 * Handles login, logout, token refresh, and user validation
 */

require_once __DIR__ . '/BaseApiController.php';
require_once __DIR__ . '/../../../shared/Core/JWT.php';

class AuthController extends BaseApiController {
    
    /**
     * User login endpoint
     * POST /api/v1/auth/login
     */
    public function login($params = []) {
        // Validate required fields
        $this->validateRequired(['email', 'password']);
        
        $data = $this->request['body'];
        $email = $this->sanitize($data['email']);
        $password = $data['password']; // Don't sanitize password
        
        // Validate email format
        $this->validateEmail($email);
        
        try {
            // Get user from database
            $stmt = $this->db->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                LEFT JOIN user_roles r ON u.role_id = r.id 
                WHERE u.email = ? AND u.is_active = 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $this->sendError('Invalid credentials', 401, 'INVALID_CREDENTIALS');
            }
            
            // Generate JWT token
            $userPayload = JWT::createUserPayload([
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role_name'] ?? 'user'
            ]);
            
            $token = JWT::encode($userPayload);
            
            // Update last login
            $updateStmt = $this->db->prepare("
                UPDATE users SET last_login = NOW() WHERE id = ?
            ");
            $updateStmt->execute([$user['id']]);
            
            // Log login activity
            $this->logActivity(
                'USER_LOGIN',
                'users',
                $user['id'],
                "User {$user['email']} logged in via API",
                $user['id']
            );
            
            $this->sendSuccess([
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'role' => $user['role_name'] ?? 'user'
                ],
                'expires_in' => 86400 // 24 hours
            ], 'Login successful');
            
        } catch (Exception $e) {
            error_log('Auth login error: ' . $e->getMessage());
            $this->sendError('Authentication failed', 500);
        }
    }
    
    /**
     * Token validation endpoint
     * POST /api/v1/auth/validate
     */
    public function validate($params = []) {
        $authHeader = $this->request['headers']['Authorization'] ?? '';
        
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->sendError('Authorization header required', 401, 'MISSING_TOKEN');
        }
        
        $token = $matches[1];
        
        try {
            $payload = JWT::decode($token);
            
            // Get fresh user data
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
            
            $this->sendSuccess([
                'valid' => true,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'role' => $user['role_name'] ?? 'user'
                ],
                'expires_at' => $payload['exp']
            ], 'Token is valid');
            
        } catch (Exception $e) {
            $this->sendError('Invalid or expired token', 401, 'INVALID_TOKEN');
        }
    }
    
    /**
     * Token refresh endpoint
     * POST /api/v1/auth/refresh
     */
    public function refresh($params = []) {
        $authHeader = $this->request['headers']['Authorization'] ?? '';
        
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->sendError('Authorization header required', 401, 'MISSING_TOKEN');
        }
        
        $token = $matches[1];
        
        try {
            // Decode token (even if expired, we still want the payload)
            $tokenParts = explode('.', $token);
            if (count($tokenParts) !== 3) {
                throw new Exception('Invalid token structure');
            }
            
            $payloadData = base64_decode(str_pad(strtr($tokenParts[1], '-_', '+/'), strlen($tokenParts[1]) % 4, '=', STR_PAD_RIGHT));
            $payload = json_decode($payloadData, true);
            
            if (!$payload || !isset($payload['user_id'])) {
                throw new Exception('Invalid token payload');
            }
            
            // Check if token is not too old (allow refresh within 7 days of expiration)
            $maxRefreshTime = $payload['exp'] + (7 * 24 * 60 * 60); // 7 days
            if (time() > $maxRefreshTime) {
                $this->sendError('Token too old to refresh', 401, 'TOKEN_TOO_OLD');
            }
            
            // Get fresh user data
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
            
            // Generate new token
            $newPayload = JWT::createUserPayload([
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role_name'] ?? 'user'
            ]);
            
            $newToken = JWT::encode($newPayload);
            
            $this->sendSuccess([
                'token' => $newToken,
                'expires_in' => 86400
            ], 'Token refreshed successfully');
            
        } catch (Exception $e) {
            error_log('Token refresh error: ' . $e->getMessage());
            $this->sendError('Failed to refresh token', 401, 'REFRESH_FAILED');
        }
    }
    
    /**
     * User logout endpoint (optional - mainly for logging)
     * POST /api/v1/auth/logout
     */
    public function logout($params = []) {
        $authHeader = $this->request['headers']['Authorization'] ?? '';
        
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            
            try {
                $payload = JWT::decode($token);
                
                // Log logout activity
                $this->logActivity(
                    'USER_LOGOUT',
                    'users',
                    $payload['user_id'],
                    "User logged out via API",
                    $payload['user_id']
                );
                
            } catch (Exception $e) {
                // Token invalid, but that's okay for logout
            }
        }
        
        $this->sendSuccess(null, 'Logout successful');
    }
    
    /**
     * Get current user profile
     * GET /api/v1/auth/me
     */
    public function me($params = []) {
        $user = $this->getCurrentUser();
        
        $this->sendSuccess([
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role_name'] ?? 'user',
            'last_login' => $user['last_login'],
            'created_at' => $user['created_at']
        ], 'User profile retrieved');
    }
    
    /**
     * Get current authenticated user (overrides parent method)
     */
    protected function getCurrentUser() {
        $authHeader = $this->request['headers']['Authorization'] ?? '';
        
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->sendError('Authorization required', 401, 'AUTH_REQUIRED');
        }
        
        $token = $matches[1];
        
        try {
            $payload = JWT::decode($token);
            
            $stmt = $this->db->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                LEFT JOIN user_roles r ON u.role_id = r.id 
                WHERE u.id = ? AND u.is_active = 1
            ");
            $stmt->execute([$payload['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $this->sendError('User not found', 401, 'USER_NOT_FOUND');
            }
            
            return $user;
            
        } catch (Exception $e) {
            $this->sendError('Invalid or expired token', 401, 'INVALID_TOKEN');
        }
    }
}
?>
