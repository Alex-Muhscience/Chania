<?php
require_once __DIR__ . '/shared/Core/Database.php';

try {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE username = 'admin'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        $hash = $result['password_hash'];
        echo "Hash from database: " . $hash . "\n";
        echo "Hash length: " . strlen($hash) . "\n";
        
        $password = 'admin123';
        $verification = password_verify($password, $hash);
        
        echo "Testing password '$password': " . ($verification ? "SUCCESS" : "FAILED") . "\n";
        
        if (!$verification) {
            // Create a new hash
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            echo "Creating new hash: " . $new_hash . "\n";
            
            // Update database
            $update = $db->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
            if ($update->execute([$new_hash])) {
                echo "Database updated successfully\n";
                echo "New verification: " . (password_verify($password, $new_hash) ? "SUCCESS" : "FAILED") . "\n";
            }
        }
    } else {
        echo "No admin user found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
