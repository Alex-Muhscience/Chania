<?php
class SessionManager {
    
    /**
     * Initialize session with secure settings
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure session settings
            ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
            ini_set('session.cookie_lifetime', SESSION_TIMEOUT);
            ini_set('session.cookie_httponly', true);
            ini_set('session.cookie_secure', false); // Set to true for HTTPS
            ini_set('session.use_strict_mode', true);
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start([
                'name' => 'ADMIN_SESSION',
                'cookie_lifetime' => SESSION_TIMEOUT,
                'cookie_secure' => false, // Set to true for HTTPS
                'cookie_httponly' => true,
                'cookie_samesite' => 'Strict'
            ]);
            
            // Regenerate session ID periodically for security
            self::regenerateSessionId();
        }
    }
    
    /**
     * Check if user is logged in and session is valid
     */
    public static function isLoggedIn() {
        self::init();
        
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            return false;
        }
        
        // Check if session has timed out
        if (self::hasSessionTimedOut()) {
            self::logout('Session timeout');
            return false;
        }
        
        // Update last activity time
        self::updateActivity();
        
        return true;
    }
    
    /**
     * Check if session has timed out
     */
    public static function hasSessionTimedOut() {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }
        
        $timeSinceLastActivity = time() - $_SESSION['last_activity'];
        return $timeSinceLastActivity > SESSION_TIMEOUT;
    }
    
    /**
     * Get remaining session time in seconds
     */
    public static function getRemainingTime() {
        if (!isset($_SESSION['last_activity'])) {
            return 0;
        }
        
        $timeSinceLastActivity = time() - $_SESSION['last_activity'];
        $remainingTime = SESSION_TIMEOUT - $timeSinceLastActivity;
        
        return max(0, $remainingTime);
    }
    
    /**
     * Update last activity timestamp
     */
    public static function updateActivity() {
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Login user with proper session setup
     */
    public static function login($user) {
        self::init();
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user';
        $_SESSION['role_slug'] = $user['role_slug'] ?? 'user';
        $_SESSION['permissions'] = $user['permissions'] ? json_decode($user['permissions'], true) : [];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['session_token'] = bin2hex(random_bytes(32));
        
        // Store session in database for tracking
        self::storeSessionInDatabase($user['id']);
        
        // Clear any login attempts
        unset($_SESSION['login_attempts']);
        unset($_SESSION['last_login_attempt']);
    }
    
    /**
     * Logout user and cleanup session
     */
    public static function logout($reason = 'Manual logout') {
        self::init();
        
        $userId = $_SESSION['user_id'] ?? null;
        
        // Log logout activity
        if ($userId) {
            try {
                $db = (new Database())->connect();
                $stmt = $db->prepare("
                    INSERT INTO admin_logs (user_id, action, ip_address, user_agent, details, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $userId,
                    'logout',
                    self::getClientIp(),
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                    $reason
                ]);
                
                // Remove session from database
                self::removeSessionFromDatabase();
            } catch (Exception $e) {
                error_log("Failed to log logout: " . $e->getMessage());
            }
        }
        
        // Clear all session data
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Extend session (for AJAX requests)
     */
    public static function extendSession() {
        if (self::isLoggedIn()) {
            self::updateActivity();
            return self::getRemainingTime();
        }
        return 0;
    }
    
    /**
     * Get session info for frontend
     */
    public static function getSessionInfo() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'remaining_time' => self::getRemainingTime(),
            'login_time' => $_SESSION['login_time'] ?? time(),
            'last_activity' => $_SESSION['last_activity'] ?? time(),
            'timeout_warning' => max(0, self::getRemainingTime() - 300), // 5 minutes warning
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ];
    }
    
    /**
     * Store session in database for tracking
     */
    private static function storeSessionInDatabase($userId) {
        try {
            $db = (new Database())->connect();
            
            // Clean up old sessions for this user
            $stmt = $db->prepare("DELETE FROM user_sessions WHERE user_id = ? AND expires_at < NOW()");
            $stmt->execute([$userId]);
            
            // Insert new session
            $stmt = $db->prepare("
                INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, created_at, expires_at, last_activity)
                VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND), NOW())
                ON DUPLICATE KEY UPDATE
                ip_address = VALUES(ip_address),
                user_agent = VALUES(user_agent),
                expires_at = VALUES(expires_at),
                last_activity = NOW()
            ");
            $stmt->execute([
                $userId,
                session_id(),
                self::getClientIp(),
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                SESSION_TIMEOUT
            ]);
        } catch (Exception $e) {
            error_log("Failed to store session in database: " . $e->getMessage());
        }
    }
    
    /**
     * Remove session from database
     */
    private static function removeSessionFromDatabase() {
        try {
            $db = (new Database())->connect();
            $stmt = $db->prepare("DELETE FROM user_sessions WHERE session_id = ?");
            $stmt->execute([session_id()]);
        } catch (Exception $e) {
            error_log("Failed to remove session from database: " . $e->getMessage());
        }
    }
    
    /**
     * Regenerate session ID periodically
     */
    private static function regenerateSessionId() {
        // Regenerate session ID every 30 minutes
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    /**
     * Get client IP address
     */
    private static function getClientIp() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Check for brute force attempts
     */
    public static function checkBruteForce() {
        if (isset($_SESSION['login_attempts'])) {
            if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
                if (time() - ($_SESSION['last_login_attempt'] ?? 0) < LOGIN_LOCKOUT_TIME) {
                    return true;
                } else {
                    // Reset attempts after timeout
                    unset($_SESSION['login_attempts']);
                    unset($_SESSION['last_login_attempt']);
                }
            }
        }
        return false;
    }
    
    /**
     * Record failed login attempt
     */
    public static function recordFailedLogin() {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['last_login_attempt'] = time();
    }
    
    /**
     * Clean up expired sessions from database
     */
    public static function cleanupExpiredSessions() {
        try {
            $db = (new Database())->connect();
            $stmt = $db->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Failed to cleanup expired sessions: " . $e->getMessage());
        }
    }
}
?>
