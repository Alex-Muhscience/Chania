USE chania_db;

CREATE TABLE users (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  email varchar(100) NOT NULL,
  password_hash varchar(255) NOT NULL,
  full_name varchar(100) NOT NULL DEFAULT '',
  role enum('admin','editor','moderator') DEFAULT 'admin',
  is_active tinyint(1) DEFAULT 1,
  failed_login_attempts int(11) DEFAULT 0,
  last_failed_login timestamp NULL DEFAULT NULL,
  account_locked_until timestamp NULL DEFAULT NULL,
  last_login timestamp NULL DEFAULT NULL,
  last_login_ip varchar(45) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY unique_username (username),
  UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert a default admin user
INSERT INTO users (username, email, password_hash, full_name, role) 
VALUES ('admin', 'admin@skillsforafrica.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');
