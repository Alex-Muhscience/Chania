<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    
    public function __construct() {
        // Use environment variables or fallback to defaults
        $this->host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
        $this->db_name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'chania_db';
        $this->username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
        $this->password = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
    }

    public function connect(): PDO
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            // Ensure we get raw data from database without any automatic conversion
            $this->conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            // Temporarily show actual error for debugging
            throw new Exception("Database Error: " . $e->getMessage());
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