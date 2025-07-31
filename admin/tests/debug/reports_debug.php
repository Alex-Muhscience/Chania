<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/User.php';

if (!isset($_SESSION['user_id'])) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo "Not logged in. Please login first.";
    exit();
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

echo "<h2>Debug Information</h2>";
echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
echo "<p>Username: " . ($_SESSION['username'] ?? 'N/A') . "</p>";
echo "<p>User Role: " . ($_SESSION['role'] ?? 'N/A') . "</p>";

// Get user details
$user = $userModel->getById($_SESSION['user_id']);
echo "<h3>User Data from Database:</h3>";
echo "<pre>";
print_r($user);
echo "</pre>";

// Check permissions
$hasReportsPermission = $userModel->hasPermission($_SESSION['user_id'], 'reports');
$hasAdminPermission = $userModel->hasPermission($_SESSION['user_id'], '*');

echo "<h3>Permission Check:</h3>";
echo "<p>Has 'reports' permission: " . ($hasReportsPermission ? 'YES' : 'NO') . "</p>";
echo "<p>Has '*' (admin) permission: " . ($hasAdminPermission ? 'YES' : 'NO') . "</p>";

// Show permissions array
if ($user && $user['permissions']) {
    $permissions = json_decode($user['permissions'], true);
    echo "<h3>User Permissions Array:</h3>";
    echo "<pre>";
    print_r($permissions);
    echo "</pre>";
} else {
    echo "<h3>No permissions found for user</h3>";
}

?>
