<?php
// Test password verification
try {
    $pdo = new PDO('mysql:host=localhost;dbname=chania_db', 'root', '');
    
    // Get the admin user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute(['admin', 'admin']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "User found:\n";
        echo "ID: {$user['id']}\n";
        echo "Username: {$user['username']}\n";
        echo "Email: {$user['email']}\n";
        echo "Role: {$user['role']}\n";
        echo "Active: {$user['is_active']}\n";
        echo "Password Hash: {$user['password_hash']}\n\n";
        
        // Test password verification
        $testPassword = 'password'; // Default password for the hash
        if (password_verify($testPassword, $user['password_hash'])) {
            echo "✓ Password verification successful for 'password'\n";
        } else {
            echo "✗ Password verification failed for 'password'\n";
            
            // Let's create a new hash for 'admin123'
            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            echo "New hash for 'admin123': $newHash\n";
            
            // Update user with new password
            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $updateStmt->execute([$newHash, $user['id']]);
            echo "✓ Password updated to 'admin123'\n";
        }
    } else {
        echo "User not found\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
