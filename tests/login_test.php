<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Login Status Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

echo "<h2>Session Information</h2>";
echo "<div class='info'>Session ID: " . session_id() . "</div>";
echo "<div class='info'>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</div>";

echo "<h2>Current Session Data</h2>";
if (empty($_SESSION)) {
    echo "<div class='error'>✗ No session data found - User is not logged in</div>";
} else {
    echo "<div class='success'>✓ Session data exists:</div>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
}

echo "<h2>Login Check</h2>";
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    echo "<div class='success'>✓ User is logged in (ID: " . $_SESSION['user_id'] . ")</div>";
    if (isset($_SESSION['username'])) {
        echo "<div class='info'>Username: " . htmlspecialchars($_SESSION['username']) . "</div>";
    }
    if (isset($_SESSION['role'])) {
        echo "<div class='info'>Role: " . htmlspecialchars($_SESSION['role']) . "</div>";
    }
} else {
    echo "<div class='error'>✗ User is NOT logged in</div>";
    echo "<div class='info'>This explains why other pages are not loading - they require login</div>";
    echo "<div class='info'><a href='login.php'>→ Go to Login Page</a></div>";
}

echo "<h2>Test Links</h2>";
echo "<p>Try these links to test page access:</p>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>Dashboard</a></li>";
echo "<li><a href='users.php' target='_blank'>Users</a></li>";
echo "<li><a href='applications.php' target='_blank'>Applications</a></li>";
echo "<li><a href='events.php' target='_blank'>Events</a></li>";
echo "</ul>";
?>
