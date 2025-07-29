<?php
// Test the actual login flow
?>
<?php
// Test Login Flow Script
echo "<h2>Admin Login Flow Test</h2>\n";
echo "<pre>\n";

// Test 1: Check if login page loads
echo "1. Testing login page accessibility...\n";
$login_url = 'http://localhost/chania/admin/login.php';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10
    ]
]);

$login_response = @file_get_contents($login_url, false, $context);
if ($login_response !== false) {
    echo "   ✓ Login page loads successfully\n";
} else {
    echo "   ✗ Login page failed to load\n";
}

// Test 2: Check database connection
echo "\n2. Testing database connection...\n";
require_once 'admin/includes/Database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    if ($conn) {
        echo "   ✓ Database connection successful\n";
        
        // Test user exists
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $username = 'admin';
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "   ✓ Admin user found in database (ID: {$user['id']})\n";
            
            // Test password verification
            if (password_verify('admin123', $user['password_hash'])) {
                echo "   ✓ Password verification successful\n";
            } else {
                echo "   ✗ Password verification failed\n";
            }
        } else {
            echo "   ✗ Admin user not found in database\n";
        }
        
        $stmt->close();
    } else {
        echo "   ✗ Database connection failed\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
}

// Test 3: Session functionality
echo "\n3. Testing session functionality...\n";
session_start();
$_SESSION['test'] = 'session_working';
if (isset($_SESSION['test']) && $_SESSION['test'] === 'session_working') {
    echo "   ✓ Session functionality working\n";
    unset($_SESSION['test']);
} else {
    echo "   ✗ Session functionality failed\n";
}

// Test 4: Check auth.php inclusion
echo "\n4. Testing auth.php inclusion...\n";
if (file_exists('admin/includes/auth.php')) {
    echo "   ✓ auth.php file exists\n";
    
    // Check if it includes simple_session.php
    $auth_content = file_get_contents('admin/includes/auth.php');
    if (strpos($auth_content, 'simple_session.php') !== false) {
        echo "   ✓ auth.php uses simple_session.php\n";
    } else {
        echo "   ✗ auth.php may not be using simple_session.php\n";
    }
} else {
    echo "   ✗ auth.php file not found\n";
}

// Test 5: Check dashboard accessibility (should redirect to login if not authenticated)
echo "\n5. Testing dashboard redirect behavior...\n";
$dashboard_url = 'http://localhost/chania/admin/index.php';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'follow_redirects' => 0,
        'timeout' => 10
    ]
]);

$dashboard_response = @file_get_contents($dashboard_url, false, $context);
$response_headers = $http_response_header ?? [];

$redirect_found = false;
foreach ($response_headers as $header) {
    if (strpos(strtolower($header), 'location:') === 0) {
        echo "   ✓ Dashboard properly redirects when not authenticated\n";
        echo "   Redirect header: $header\n";
        $redirect_found = true;
        break;
    }
}

if (!$redirect_found && $dashboard_response !== false) {
    if (strpos($dashboard_response, 'login') !== false || strpos($dashboard_response, 'Login') !== false) {
        echo "   ✓ Dashboard shows login content when not authenticated\n";
    } else {
        echo "   ⚠ Dashboard response unclear - may need investigation\n";
    }
} elseif (!$redirect_found) {
    echo "   ✗ Dashboard request failed\n";
}

// Test 6: Simulate login POST request
echo "\n6. Testing actual login POST request...\n";
$login_data = http_build_query([
    'username' => 'admin',
    'password' => 'admin123',
    'login' => 'Login'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'content' => $login_data,
        'follow_redirects' => 0,
        'timeout' => 10
    ]
]);

$post_response = @file_get_contents($login_url, false, $context);
$post_headers = $http_response_header ?? [];

$login_redirect = false;
foreach ($post_headers as $header) {
    if (strpos(strtolower($header), 'location:') === 0) {
        echo "   ✓ Login POST triggers redirect\n";
        echo "   Redirect: $header\n";
        $login_redirect = true;
        break;
    }
}

if (!$login_redirect) {
    if ($post_response && (strpos($post_response, 'error') !== false || strpos($post_response, 'Error') !== false)) {
        echo "   ⚠ Login may have validation errors - check response\n";
    } else {
        echo "   ⚠ Login POST behavior unclear\n";
    }
}

echo "\n=== Test Summary ===\n";
echo "Check the results above to identify any issues.\n";
echo "If all tests pass with ✓, the login system should be working.\n";
echo "If you see ✗ or ⚠, those areas need attention.\n";

echo "</pre>\n";
?>
