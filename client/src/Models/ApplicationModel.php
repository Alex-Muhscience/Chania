<?php
class ApplicationModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function submitApplication($data) {
        $stmt = $this->db->prepare("
            INSERT INTO applications (
                program_id, first_name, last_name, email, phone, address,
                education, experience, motivation, ip_address
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['program_id'],
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['education'],
            $data['experience'] ?? null,
            $data['motivation'],
            $_SERVER['REMOTE_ADDR']
        ]);
    }

    public function getApplicationById($id) {
        $stmt = $this->db->prepare("
            SELECT a.*, p.title as program_title 
            FROM applications a
            JOIN programs p ON a.program_id = p.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>

