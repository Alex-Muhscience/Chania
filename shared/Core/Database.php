<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'chania_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }

        return $this->conn;
    }

    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map('Database::sanitize', $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}
?>