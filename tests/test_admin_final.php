<?php
// Final Admin Panel Test Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Admin Panel Final Test</h2>\n";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>\n";
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=chania_db;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    echo "✓ Database connection successful<br>\n";
    
    // Check admin user exists
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✓ Admin user found: {$admin['username']} ({$admin['email']}) - Role: {$admin['role']}<br>\n";
    } else {
        echo "✗ Admin user not found<br>\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>\n";
}

// Test 2: Required Files
echo "<h3>2. Required Files Test</h3>\n";
$required_files = [
    'admin/includes/config.php',
    'admin/includes/simple_session.php', 
    'admin/includes/auth.php',
    'admin/includes/header.php',
    'admin/includes/footer.php',
    'admin/public/login.php',
    'admin/public/index.php',
    'admin/public/logout.php'
];

foreach ($required_files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "✓ {$file} exists<br>\n";
    } else {
        echo "✗ {$file} missing<br>\n";
    }
}

// Test 3: Session Functionality
echo "<h3>3. Session Test</h3>\n";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    echo "✓ Session started successfully<br>\n";
} else {
    echo "✓ Session already active<br>\n";
}

// Test 4: Admin URLs
echo "<h3>4. Admin URL Test</h3>\n";
$base_url = 'http://localhost/chania';
$admin_url = $base_url . '/admin';

echo "Base URL: {$base_url}<br>\n";
echo "Admin URL: {$admin_url}<br>\n";
echo "Login URL: {$admin_url}/public/login.php<br>\n";
echo "Dashboard URL: {$admin_url}/public/index.php<br>\n";

// Test 5: File Permissions
echo "<h3>5. File Permissions Test</h3>\n";
$check_dirs = [
    'uploads' => __DIR__ . '/uploads',
    'logs' => __DIR__ . '/logs'
];

foreach ($check_dirs as $name => $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✓ {$name} directory is writable<br>\n";
        } else {
            echo "✗ {$name} directory is not writable<br>\n";
        }
    } else {
        echo "✗ {$name} directory does not exist<br>\n";
    }
}

// Test 6: Login Simulation
echo "<h3>6. Login Simulation Test</h3>\n";
if (isset($pdo)) {
    try {
        $username = 'admin';
        $password = 'admin123';
        
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role, is_active FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            if (password_verify($password, $user['password_hash'])) {
                echo "✓ Password verification successful<br>\n";
                echo "✓ Login simulation would succeed<br>\n";
                
                // Test session variables that would be set
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role'] = $user['role'];
                
                echo "✓ Session variables set successfully<br>\n";
                
            } else {
                echo "✗ Password verification failed<br>\n";
            }
        } else {
            echo "✗ User not found for login test<br>\n"; 
        }
        
    } catch (Exception $e) {
        echo "✗ Login simulation error: " . $e->getMessage() . "<br>\n";
    }
}

// Test 7: Authentication Check
echo "<h3>7. Authentication Check</h3>\n";
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    echo "✓ Authentication check would pass<br>\n";
    echo "✓ User ID: " . ($_SESSION['admin_user_id'] ?? 'Not set') . "<br>\n";
    echo "✓ Username: " . ($_SESSION['admin_username'] ?? 'Not set') . "<br>\n";
    echo "✓ Role: " . ($_SESSION['admin_role'] ?? 'Not set') . "<br>\n";
} else {
    echo "✗ Authentication check would fail<br>\n";
}

echo "<h3>Test Complete</h3>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Access login page: <a href='/chania/admin/public/login.php' target='_blank'>Login</a></li>\n";
echo "<li>Use credentials: admin / admin123</li>\n";
echo "<li>After login, access dashboard: <a href='/chania/admin/public/index.php' target='_blank'>Dashboard</a></li>\n";
echo "</ul>\n";
?>
