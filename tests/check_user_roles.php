<?php
require_once __DIR__ . '/shared/Core/Database.php';

try {
    $db = (new Database())->connect();
    
    echo "<h2>User Roles Analysis</h2>\n";
    
    // Check if role column exists
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Users table structure:</h3>\n";
    echo "<table border='1'>\n";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n\n";
    
    // Check all users and their roles
    $stmt = $db->query("SELECT id, username, email, role, created_at FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>All users and their roles:</h3>\n";
    echo "<table border='1'>\n";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created At</th></tr>\n";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($user['role']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n\n";
    
    // Count users by role
    $stmt = $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $roleCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Role distribution:</h3>\n";
    echo "<ul>\n";
    foreach ($roleCounts as $roleCount) {
        echo "<li><strong>" . htmlspecialchars($roleCount['role']) . "</strong>: " . $roleCount['count'] . " users</li>\n";
    }
    echo "</ul>\n";
    
} catch (PDOException $e) {
    echo "Database error: " . htmlspecialchars($e->getMessage()) . "\n";
}
?>
