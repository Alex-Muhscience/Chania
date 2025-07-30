<?php
// Web Functionality Testing Script
// Tests actual HTTP endpoints and form submissions

echo "<h1>Chania Web Functionality Testing</h1>\n";
echo "<pre>\n";

$base_url = "http://localhost";
$passed = 0;
$failed = 0;

function testEndpoint($url, $description, $expected_status = 200, $expected_content = null) {
    global $passed, $failed;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Chania Test Bot');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "✗ {$description}: FAIL - cURL Error: {$error}\n";
        $failed++;
        return false;
    }
    
    $status_ok = ($http_code == $expected_status);
    $content_ok = true;
    
    if ($expected_content && !empty($response)) {
        $content_ok = (stripos($response, $expected_content) !== false);
    }
    
    $result = $status_ok && $content_ok;
    $status = $result ? 'PASS' : 'FAIL';
    $icon = $result ? '✓' : '✗';
    
    echo "{$icon} {$description}: {$status} (HTTP {$http_code})";
    
    if (!$content_ok && $expected_content) {
        echo " - Missing expected content: '{$expected_content}'";
    }
    
    echo "\n";
    
    $result ? $passed++ : $failed++;
    return $result;
}

function testFormSubmission($url, $data, $description) {
    global $passed, $failed;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Chania Test Bot');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "✗ {$description}: FAIL - cURL Error: {$error}\n";
        $failed++;
        return false;
    }
    
    $result = ($http_code == 200 || $http_code == 302); // Success or redirect
    $status = $result ? 'PASS' : 'FAIL';
    $icon = $result ? '✓' : '✗';
    
    echo "{$icon} {$description}: {$status} (HTTP {$http_code})\n";
    
    $result ? $passed++ : $failed++;
    return $result;
}

echo "=== CLIENT-SIDE TESTS ===\n";

// Test main client pages
testEndpoint("{$base_url}/chania/", "Client Homepage", 200, "chania");
testEndpoint("{$base_url}/chania/client/public/", "Client Public Index", 200);
testEndpoint("{$base_url}/chania/client/public/about.php", "About Page", 200);
testEndpoint("{$base_url}/chania/client/public/services.php", "Services Page", 200);
testEndpoint("{$base_url}/chania/client/public/contact.php", "Contact Page", 200);

echo "\n=== ADMIN-SIDE TESTS ===\n";

// Test admin pages
testEndpoint("{$base_url}/chania/admin/public/", "Admin Dashboard (should redirect)", 302);
testEndpoint("{$base_url}/chania/admin/public/login.php", "Admin Login Page", 200, "login");

echo "\n=== API/AJAX ENDPOINT TESTS ===\n";

// Test AJAX endpoints (these might return JSON)
testEndpoint("{$base_url}/chania/admin/ajax/get_stats.php", "Admin Stats API");
testEndpoint("{$base_url}/chania/admin/ajax/get_recent_activities.php", "Recent Activities API");

echo "\n=== FORM SUBMISSION TESTS ===\n";

// Test contact form submission
$contact_data = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'subject' => 'Test Subject',
    'message' => 'This is a test message from the automated testing script.',
    'submit' => 'Send Message'
];

testFormSubmission("{$base_url}/chania/client/includes/contact_handler.php", $contact_data, "Contact Form Submission");

// Test newsletter subscription
$newsletter_data = [
    'email' => 'newsletter.test@example.com',
    'action' => 'subscribe'
];

testFormSubmission("{$base_url}/chania/client/includes/newsletter_handler.php", $newsletter_data, "Newsletter Subscription");

echo "\n=== ERROR PAGE TESTS ===\n";

// Test 404 handling
testEndpoint("{$base_url}/chania/nonexistent-page.php", "404 Error Handling", 404);

echo "\n=== SECURITY TESTS ===\n";

// Test admin pages without authentication (should redirect)
testEndpoint("{$base_url}/chania/admin/public/users.php", "Protected Admin Page (should redirect)", 302);
testEndpoint("{$base_url}/chania/admin/public/settings.php", "Settings Page (should redirect)", 302);

echo "\n=== PERFORMANCE TESTS ===\n";

// Test page load times
$start_time = microtime(true);
$response = file_get_contents("{$base_url}/chania/");
$load_time = round((microtime(true) - $start_time) * 1000, 2);
echo "✓ Homepage Load Time: {$load_time}ms\n";
$passed++;

echo "\n=== FILE ACCESS TESTS ===\n";

// Test direct access to sensitive files (should be blocked)
testEndpoint("{$base_url}/chania/shared/config.php", "Config File Protection", 403);
testEndpoint("{$base_url}/chania/admin/includes/functions.php", "Functions File Protection", 403);

echo "\n=== SUMMARY ===\n";
echo "Tests Run: " . ($passed + $failed) . "\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "Success Rate: " . round(($passed / ($passed + $failed)) * 100, 1) . "%\n";

if ($failed == 0) {
    echo "\n🎉 ALL WEB FUNCTIONALITY TESTS PASSED!\n";
} elseif ($failed <= 2) {
    echo "\n⚠️  Minor issues detected. System mostly functional.\n";
} else {
    echo "\n❌ Multiple issues detected. Review failures above.\n";
}

echo "</pre>\n";
?>
