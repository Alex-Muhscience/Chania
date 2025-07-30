<?php

class User {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAll($limit = null, $offset = 0, $search = '', $roleId = null, $status = null) {
        $sql = "SELECT u.*, ur.display_name as role_name, ur.name as role_slug 
                FROM users u 
                LEFT JOIN user_roles ur ON u.role_id = ur.id 
                WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if ($roleId !== null) {
            $sql .= " AND u.role_id = ?";
            $params[] = $roleId;
        }

        if ($status !== null) {
            $sql .= " AND u.is_active = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY u.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, ur.display_name as role_name, ur.name as role_slug, ur.permissions 
            FROM users u 
            LEFT JOIN user_roles ur ON u.role_id = ur.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByUsername($username) {
        $stmt = $this->db->prepare("
            SELECT u.*, ur.display_name as role_name, ur.name as role_slug, ur.permissions 
            FROM users u 
            LEFT JOIN user_roles ur ON u.role_id = ur.id 
            WHERE u.username = ? AND u.is_active = 1
        ");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function getByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT u.*, ur.display_name as role_name, ur.name as role_slug, ur.permissions 
            FROM users u 
            LEFT JOIN user_roles ur ON u.role_id = ur.id 
            WHERE u.email = ? AND u.is_active = 1
        ");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, role_id, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['first_name'],
            $data['last_name'],
            $data['role_id'] ?? 4, // Default to viewer role
            $data['is_active'] ?? 1
        ]);
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if ($key === 'password' && !empty($value)) {
                $fields[] = "password = ?";
                $params[] = password_hash($value, PASSWORD_DEFAULT);
            } elseif (in_array($key, ['username', 'email', 'first_name', 'last_name', 'role_id', 'is_active', 'avatar_path'])) {
                $fields[] = "{$key} = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        // Don't allow deletion of the last admin user
        $adminCount = $this->getAdminCount();
        $user = $this->getById($id);
        
        if ($adminCount <= 1 && $user['role_slug'] === 'admin') {
            throw new Exception('Cannot delete the last administrator account');
        }

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function authenticate($username, $password) {
        $user = $this->getByUsername($username);
        
        if (!$user) {
            $user = $this->getByEmail($username);
        }

        if (!$user) {
            return false;
        }

        // Check if account is locked
        if ($user['locked_until'] && new DateTime() < new DateTime($user['locked_until'])) {
            throw new Exception('Account is temporarily locked due to multiple failed login attempts');
        }

        // Check if password field exists and verify password
        if (!isset($user['password_hash']) || empty($user['password_hash'])) {
            // Password field missing or empty - authentication fails
            $this->incrementLoginAttempts($user['id']);
            return false;
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            $this->incrementLoginAttempts($user['id']);
            return false;
        }

        // Reset login attempts and update last login
        $this->resetLoginAttempts($user['id']);
        $this->updateLastLogin($user['id']);

        return $user;
    }

    public function incrementLoginAttempts($userId) {
        $stmt = $this->db->prepare("
            UPDATE users SET 
                login_attempts = login_attempts + 1,
                locked_until = CASE 
                    WHEN login_attempts >= ? THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                    ELSE locked_until 
                END
            WHERE id = ?
        ");
        $stmt->execute([MAX_LOGIN_ATTEMPTS, $userId]);
    }

    public function resetLoginAttempts($userId) {
        $stmt = $this->db->prepare("
            UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }

    public function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$userId]);
    }

    public function changePassword($userId, $newPassword) {
        $stmt = $this->db->prepare("
            UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?
        ");
        return $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
    }

    public function updateProfile($userId, $data) {
        $allowedFields = ['first_name', 'last_name', 'email', 'avatar_path'];
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "{$key} = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function activate($id) {
        $stmt = $this->db->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deactivate($id) {
        // Don't allow deactivation of the last admin user
        $adminCount = $this->getActiveAdminCount();
        $user = $this->getById($id);
        
        if ($adminCount <= 1 && $user['role_slug'] === 'admin' && $user['is_active']) {
            throw new Exception('Cannot deactivate the last active administrator account');
        }

        $stmt = $this->db->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAdminCount() {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM users u 
            JOIN user_roles ur ON u.role_id = ur.id 
            WHERE ur.name = 'admin'
        ");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getActiveAdminCount() {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM users u 
            JOIN user_roles ur ON u.role_id = ur.id 
            WHERE ur.name = 'admin' AND u.is_active = 1
        ");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getTotalCount($search = '', $roleId = null, $status = null) {
        $sql = "SELECT COUNT(*) FROM users u WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if ($roleId !== null) {
            $sql .= " AND u.role_id = ?";
            $params[] = $roleId;
        }

        if ($status !== null) {
            $sql .= " AND u.is_active = ?";
            $params[] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getRecentUsers($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT u.*, ur.display_name as role_name 
            FROM users u 
            LEFT JOIN user_roles ur ON u.role_id = ur.id 
            ORDER BY u.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getStatistics() {
        $stats = [];

        // Total users
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        $stats['total'] = $stmt->fetchColumn();

        // Active users
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1");
        $stmt->execute();
        $stats['active'] = $stmt->fetchColumn();

        // Users created this month
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        $stmt->execute();
        $stats['this_month'] = $stmt->fetchColumn();

        // Users by role
        $stmt = $this->db->prepare("
            SELECT ur.display_name, COUNT(u.id) as count 
            FROM user_roles ur 
            LEFT JOIN users u ON ur.id = u.role_id 
            GROUP BY ur.id, ur.display_name
        ");
        $stmt->execute();
        $stats['by_role'] = $stmt->fetchAll();

        return $stats;
    }

    public function hasPermission($userId, $permission) {
        $user = $this->getById($userId);
        if (!$user || !$user['permissions']) {
            return false;
        }

        $permissions = json_decode($user['permissions'], true);
        
        // Admin has all permissions
        if (in_array('*', $permissions)) {
            return true;
        }

        return in_array($permission, $permissions);
    }

    public function generatePasswordResetToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = new DateTime();
        $expiresAt->add(new DateInterval('PT1H')); // 1 hour

        $stmt = $this->db->prepare("
            INSERT INTO password_resets (user_id, token, expires_at) 
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$userId, $token, $expiresAt->format('Y-m-d H:i:s')])) {
            return $token;
        }
        
        return false;
    }

    public function validatePasswordResetToken($token) {
        $stmt = $this->db->prepare("
            SELECT pr.*, u.email, u.username 
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function resetPassword($token, $newPassword) {
        $resetData = $this->validatePasswordResetToken($token);
        if (!$resetData) {
            return false;
        }

        // Update password
        $stmt = $this->db->prepare("
            UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?
        ");
        $passwordUpdated = $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $resetData['user_id']]);

        if ($passwordUpdated) {
            // Mark token as used
            $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            return true;
        }

        return false;
    }

    public function logActivity($userId, $action, $description = null) {
        $stmt = $this->db->prepare("
            INSERT INTO user_activity_logs (user_id, action, description, ip_address, user_agent, session_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $userId,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            session_id(),
        ]);
    }

    public function getActivityLogs($userId, $limit = 20, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT * FROM user_activity_logs
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getTotalActivityLogs($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_activity_logs WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    // Two-Factor Authentication Methods
    
    public function enableTwoFactor($userId, $secret) {
        $stmt = $this->db->prepare("
            UPDATE users SET 
            two_factor_secret = ?, 
            two_factor_enabled = 1,
            updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$secret, $userId]);
    }
    
    public function disableTwoFactor($userId) {
        $stmt = $this->db->prepare("
            UPDATE users SET 
            two_factor_secret = NULL, 
            two_factor_enabled = 0,
            updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$userId]);
    }
    
    public function getTwoFactorSecret($userId) {
        $stmt = $this->db->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    public function isTwoFactorEnabled($userId) {
        $stmt = $this->db->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return (bool) $stmt->fetchColumn();
    }
    
    public function verifyTwoFactorSetup($userId, $code) {
        $secret = $this->getTwoFactorSecret($userId);
        if (!$secret) {
            return false;
        }
        
        require_once __DIR__ . '/TOTP.php';
        return TOTP::verifyTOTP($secret, $code);
    }
}

?>
