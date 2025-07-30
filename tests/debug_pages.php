<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Admin Pages Debug Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// Test basic includes
echo "<h2>1. Testing Basic Includes</h2>";
try {
    require_once __DIR__ . '/../includes/config.php';
    echo "<div class='success'>✓ Config loaded successfully</div>";
} catch (Exception $e) {
    echo "<div class='error'>✗ Config failed: " . $e->getMessage() . "</div>";
}

try {
    require_once __DIR__ . '/../../shared/Core/Database.php';
    echo "<div class='success'>✓ Database class loaded</div>";
} catch (Exception $e) {
    echo "<div class='error'>✗ Database class failed: " . $e->getMessage() . "</div>";
}

try {
    require_once __DIR__ . '/../../shared/Core/Utilities.php';
    echo "<div class='success'>✓ Utilities class loaded</div>";
} catch (Exception $e) {
    echo "<div class='error'>✗ Utilities class failed: " . $e->getMessage() . "</div>";
}

// Test database connection
echo "<h2>2. Testing Database Connection</h2>";
try {
    $db = (new Database())->connect();
    echo "<div class='success'>✓ Database connected</div>";
} catch (Exception $e) {
    echo "<div class='error'>✗ Database connection failed: " . $e->getMessage() . "</div>";
}

// Test session
echo "<h2>3. Testing Session</h2>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<div class='success'>✓ Session started</div>";
    if (isset($_SESSION['user_id'])) {
        echo "<div class='success'>✓ User logged in (ID: " . $_SESSION['user_id'] . ")</div>";
    } else {
        echo "<div class='warning'>⚠ User not logged in</div>";
    }
} else {
    echo "<div class='error'>✗ Session failed</div>";
}

// Test individual page loads
echo "<h2>4. Testing Individual Page Access</h2>";
$testPages = [
    'users.php' => 'Users Management',
    'applications.php' => 'Applications',
    'events.php' => 'Events',
    'contacts.php' => 'Contacts',
    'programs.php' => 'Programs',
    'partners.php' => 'Partners',
    'team_members.php' => 'Team Members'
];

foreach ($testPages as $file => $name) {
    echo "<h3>Testing: $name ($file)</h3>";
    
    // Check if file exists and is readable
    $filePath = __DIR__ . '/' . $file;
    if (!file_exists($filePath)) {
        echo "<div class='error'>✗ File does not exist: $filePath</div>";
        continue;
    }
    
    if (!is_readable($filePath)) {
        echo "<div class='error'>✗ File is not readable: $filePath</div>";
        continue;
    }
    
    echo "<div class='success'>✓ File exists and is readable</div>";
    
    // Try to include and execute (capture output)
    ob_start();
    $error = null;
    try {
        // Capture any errors during include
        set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$error) {
            $error = "Error $errno: $errstr in $errfile on line $errline";
        });
        
        include $filePath;
        
        restore_error_handler();
        
        $output = ob_get_contents();
        if (strlen($output) > 0) {
            echo "<div class='success'>✓ Page executed successfully (" . number_format(strlen($output)) . " characters output)</div>";
        } else {
            echo "<div class='warning'>⚠ Page executed but produced no output</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>✗ Exception during execution: " . $e->getMessage() . "</div>";
    } catch (Error $e) {
        echo "<div class='error'>✗ Fatal error during execution: " . $e->getMessage() . "</div>";
    }
    
    if ($error) {
        echo "<div class='error'>✗ PHP Error: $error</div>";
    }
    
    ob_end_clean();
    
    echo "<div style='margin:10px 0;'><a href='$file' target='_blank'>→ Test $name in new tab</a></div>";
    echo "<hr>";
}

echo "<h2>5. URL Rewriting Test</h2>";
echo "<p>Current REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "</p>";
echo "<p>Current SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "</p>";
echo "<p>Current HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "</p>";

// Check .htaccess
$htaccessPath = __DIR__ . '/.htaccess';
if (file_exists($htaccessPath)) {
    echo "<div class='success'>✓ .htaccess file exists</div>";
    echo "<pre>" . htmlspecialchars(file_get_contents($htaccessPath)) . "</pre>";
} else {
    echo "<div class='error'>✗ .htaccess file missing</div>";
}
?>
