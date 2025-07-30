<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Page Access Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;}</style>";

// Check if user is logged in first
if (!isset($_SESSION['user_id'])) {
    echo "<div class='error'>✗ User not logged in. Please log in first.</div>";
    echo "<a href='login.php'>Go to Login</a>";
    exit;
}

echo "<div class='success'>✓ User is logged in (ID: " . $_SESSION['user_id'] . ")</div>";

$testPages = [
    'users.php' => 'Users Management',
    'applications.php' => 'Applications',
    'events.php' => 'Events', 
    'contacts.php' => 'Contacts',
    'programs.php' => 'Programs',
    'partners.php' => 'Partners',
    'team_members.php' => 'Team Members'
];

echo "<h2>Testing Page Access</h2>";

foreach ($testPages as $file => $name) {
    echo "<div style='border:1px solid #ccc; margin:10px 0; padding:10px;'>";
    echo "<h3>Testing: $name ($file)</h3>";
    
    $filePath = __DIR__ . '/' . $file;
    
    if (!file_exists($filePath)) {
        echo "<div class='error'>✗ File does not exist</div>";
        continue;
    }
    
    // Create a separate PHP process to test the page
    $testUrl = "http://localhost/chania/admin/public/$file";
    
    // Use cURL to test the page access
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    
    // Forward session cookies
    $sessionName = session_name();
    $sessionId = session_id();
    curl_setopt($ch, CURLOPT_COOKIE, "$sessionName=$sessionId");
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<div class='error'>✗ cURL Error: $error</div>";
    } else {
        echo "<div class='info'>HTTP Status: $httpCode</div>";
        
        if ($httpCode == 200) {
            echo "<div class='success'>✓ Page accessible</div>";
            
            // Check if it's a redirect or actual content
            if (strpos($response, 'Location:') !== false) {
                preg_match('/Location: (.+)/', $response, $matches);
                if (isset($matches[1])) {
                    echo "<div class='warning'>⚠ Page redirects to: " . trim($matches[1]) . "</div>";
                }
            }
            
            // Check content length
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($response, $headerSize);
            echo "<div class='info'>Content Length: " . strlen($body) . " characters</div>";
            
            // Check if it contains expected admin content
            if (strpos($body, 'sidebar') !== false || strpos($body, 'admin') !== false) {
                echo "<div class='success'>✓ Contains admin layout</div>";
            } else {
                echo "<div class='warning'>⚠ May not contain admin layout</div>";
            }
            
        } elseif ($httpCode == 302) {
            echo "<div class='warning'>⚠ Page redirects (302)</div>";
            // Extract redirect location
            if (strpos($response, 'Location:') !== false) {
                preg_match('/Location: (.+)/', $response, $matches);
                if (isset($matches[1])) {
                    echo "<div class='info'>Redirects to: " . trim($matches[1]) . "</div>";
                }
            }
        } elseif ($httpCode == 500) {
            echo "<div class='error'>✗ Server Error (500)</div>";
        } else {
            echo "<div class='error'>✗ HTTP Error: $httpCode</div>";
        }
    }
    
    echo "<div style='margin-top:10px;'>";
    echo "<a href='$file' target='_blank' style='margin-right:10px;'>→ Open in New Tab</a>";
    echo "<a href='#' onclick=\"window.open('$file', '_blank'); return false;\">→ Test Direct Access</a>";
    echo "</div>";
    
    echo "</div>";
}

echo "<h2>Direct File Test</h2>";
echo "<p>Test direct access to pages:</p>";

// Test if we can include a simple page directly
echo "<h3>Testing Direct Include of users.php</h3>";
ob_start();
$includeError = null;

try {
    // Set error handler to catch any errors
    set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$includeError) {
        $includeError = "Error $errno: $errstr in $errfile on line $errline";
        return true;
    });
    
    // Try to include the file
    include 'users.php';
    
    restore_error_handler();
    
    $output = ob_get_contents();
    ob_end_clean();
    
    if ($includeError) {
        echo "<div class='error'>✗ Include Error: $includeError</div>";
    } else {
        echo "<div class='success'>✓ File included successfully</div>";
        echo "<div class='info'>Output length: " . strlen($output) . " characters</div>";
        
        if (strlen($output) > 0) {
            echo "<div class='success'>✓ Generated output</div>";
        } else {
            echo "<div class='warning'>⚠ No output generated</div>";
        }
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "<div class='error'>✗ Exception: " . $e->getMessage() . "</div>";
} catch (Error $e) {
    ob_end_clean();
    echo "<div class='error'>✗ Fatal Error: " . $e->getMessage() . "</div>";
}

echo "<h2>Recommendations</h2>";
echo "<div class='info'>";
echo "<p>If pages are redirecting or not loading:</p>";
echo "<ul>";
echo "<li>Check if there are database connection issues</li>";
echo "<li>Verify all required database tables exist</li>";
echo "<li>Check for missing include files</li>";
echo "<li>Look for PHP errors in the server logs</li>";
echo "<li>Ensure proper session handling</li>";
echo "</ul>";
echo "</div>";
?>
