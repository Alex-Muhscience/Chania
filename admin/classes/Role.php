<?php
class Role {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM roles ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPermissions($role_id) {
        $stmt = $this->db->prepare("SELECT p.name FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = ?");
        $stmt->execute([$role_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function create($name, $description, $permissions) {
        $this->db->beginTransaction();
        $stmt = $this->db->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        $roleId = $this->db->lastInsertId();
        $this->updatePermissions($roleId, $permissions);
        return $this->db->commit();
    }

    public function update($id, $name, $description, $permissions) {
        $this->db->beginTransaction();
        $stmt = $this->db->prepare("UPDATE roles SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);
        $this->updatePermissions($id, $permissions);
        return $this->db->commit();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM roles WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllPermissions() {
        return $this->db->query("SELECT * FROM permissions ORDER BY category, name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updatePermissions($role_id, $permissions) {
        // First, remove all existing permissions for this role
        $stmt = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$role_id]);

        // Now, add the new permissions
        $stmt = $this->db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($permissions as $permission_id) {
            $stmt->execute([$role_id, $permission_id]);
        }
    }
}
?>
