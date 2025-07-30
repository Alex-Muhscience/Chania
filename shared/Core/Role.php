<?php

class Role {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM user_roles ORDER BY display_name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM user_roles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO user_roles (name, display_name, description, permissions, is_active) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['display_name'],
            $data['description'],
            json_encode($data['permissions'] ?? []),
            $data['is_active'] ?? 1
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE user_roles 
            SET name = ?, display_name = ?, description = ?, permissions = ?, is_active = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['display_name'],
            $data['description'],
            json_encode($data['permissions'] ?? []),
            $data['is_active'] ?? 1,
            $id
        ]);
    }

    public function delete($id) {
        // Don't allow deletion of the default admin role
        if ($id == 1) {
            throw new Exception('Cannot delete the default Administrator role');
        }

        // Check if any users are assigned to this role
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Cannot delete a role that is assigned to users. Please reassign users first.');
        }

        $stmt = $this->db->prepare("DELETE FROM user_roles WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAvailablePermissions() {
        return [
            '*' => 'All Permissions',
            'content' => 'Content Management (Pages, Media, Templates)',
            'users' => 'User Management',
            'applications' => 'Application Management',
            'events' => 'Event Management',
            'contacts' => 'Contact Management',
            'settings' => 'System Settings',
            'system' => 'System Monitoring & Logs',
            'view' => 'Read-only Access'
        ];
    }
}

?>
