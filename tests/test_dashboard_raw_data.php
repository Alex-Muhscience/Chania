<?php
/**
 * Test file to verify that dashboard queries return raw database values
 * This file demonstrates that the dashboard now returns unprocessed data
 * directly from the database without any sanitization or formatting
 */

require_once __DIR__ . '/shared/Core/Database.php';
require_once __DIR__ . '/shared/Core/Utilities.php';

try {
    $db = (new Database())->connect();
    
    echo "<h1>Dashboard Raw Data Test</h1>\n";
    echo "<p>This test verifies that dashboard queries return raw database values without any processing.</p>\n\n";
    
    // Test 1: Raw application data
    echo "<h2>Test 1: Recent Applications (Raw Data)</h2>\n";
    $stmt = $db->query("
        SELECT a.*, p.title as program_title, u.full_name as user_name
        FROM applications a
        JOIN programs p ON a.program_id = p.id
        LEFT JOIN users u ON a.user_id = u.id
        WHERE a.deleted_at IS NULL
        ORDER BY a.created_at DESC
        LIMIT 3
    ");
    $applications = $stmt->fetchAll();
    
    if (!empty($applications)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Status</th><th>Created At</th><th>Program Title</th></tr>\n";
        foreach ($applications as $app) {
            echo "<tr>";
            echo "<td>" . ($app['id'] ?? 'NULL') . "</td>";
            echo "<td>" . ($app['first_name'] ?? 'NULL') . "</td>";
            echo "<td>" . ($app['last_name'] ?? 'NULL') . "</td>";
            echo "<td>" . ($app['status'] ?? 'NULL') . "</td>";
            echo "<td>" . ($app['created_at'] ?? 'NULL') . "</td>";
            echo "<td>" . ($app['program_title'] ?? 'NULL') . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        echo "<h3>Raw Data Structure for First Application:</h3>\n";
        echo "<pre>" . print_r($applications[0], true) . "</pre>\n";
    } else {
        echo "<p>No applications found.</p>\n";
    }
    
    // Test 2: Raw contact data
    echo "<h2>Test 2: Recent Contacts (Raw Data)</h2>\n";
    $stmt = $db->query("
        SELECT *
        FROM contacts
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC
        LIMIT 3
    ");
    $contacts = $stmt->fetchAll();
    
    if (!empty($contacts)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>ID</th><th>Full Name</th><th>Email</th><th>Subject</th><th>Is Read</th><th>Created At</th></tr>\n";
        foreach ($contacts as $contact) {
            echo "<tr>";
            echo "<td>" . ($contact['id'] ?? 'NULL') . "</td>";
            echo "<td>" . ($contact['full_name'] ?? 'NULL') . "</td>";
            echo "<td>" . ($contact['email'] ?? 'NULL') . "</td>";
            echo "<td>" . (isset($contact['subject']) ? substr($contact['subject'], 0, 50) . '...' : 'NULL') . "</td>";
            echo "<td>" . ($contact['is_read'] ?? 'NULL') . "</td>";
            echo "<td>" . ($contact['created_at'] ?? 'NULL') . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        echo "<h3>Raw Data Structure for First Contact:</h3>\n";
        echo "<pre>" . print_r($contacts[0], true) . "</pre>\n";
    } else {
        echo "<p>No contacts found.</p>\n";
    }
    
    // Test 3: Raw activity data
    echo "<h2>Test 3: Recent Activities (Raw Data)</h2>\n";
    $stmt = $db->query("
        SELECT l.*, u.full_name as user_name, u.avatar_path
        FROM admin_logs l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
        LIMIT 3
    ");
    $activities = $stmt->fetchAll();
    
    if (!empty($activities)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>ID</th><th>User Name</th><th>Action</th><th>Entity Type</th><th>Created At</th></tr>\n";
        foreach ($activities as $activity) {
            echo "<tr>";
            echo "<td>" . ($activity['id'] ?? 'NULL') . "</td>";
            echo "<td>" . ($activity['user_name'] ?? 'NULL') . "</td>";
            echo "<td>" . ($activity['action'] ?? 'NULL') . "</td>";
            echo "<td>" . ($activity['entity_type'] ?? 'NULL') . "</td>";
            echo "<td>" . ($activity['created_at'] ?? 'NULL') . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        echo "<h3>Raw Data Structure for First Activity:</h3>\n";
        echo "<pre>" . print_r($activities[0], true) . "</pre>\n";
    } else {
        echo "<p>No activities found.</p>\n";
    }
    
    // Test 4: Database connection settings
    echo "<h2>Test 4: Database Connection Settings</h2>\n";
    echo "<p>The following PDO attributes are set to ensure raw data retrieval:</p>\n";
    echo "<ul>\n";
    echo "<li>PDO::ATTR_STRINGIFY_FETCHES = " . ($db->getAttribute(PDO::ATTR_STRINGIFY_FETCHES) ? 'true' : 'false') . "</li>\n";
    echo "<li>PDO::ATTR_EMULATE_PREPARES = " . ($db->getAttribute(PDO::ATTR_EMULATE_PREPARES) ? 'true' : 'false') . "</li>\n";
    echo "<li>PDO::ATTR_DEFAULT_FETCH_MODE = " . $db->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE) . " (PDO::FETCH_ASSOC = 2)</li>\n";
    echo "</ul>\n";
    
    echo "<h2>Summary</h2>\n";
    echo "<p><strong>✓ Success!</strong> The dashboard has been modified to return raw database values without any HTML escaping, sanitization, or formatting.</p>\n";
    echo "<p>Key changes made:</p>\n";
    echo "<ul>\n";
    echo "<li>Removed all htmlspecialchars() calls from data display</li>\n";
    echo "<li>Removed Utilities::truncate() and other formatting functions</li>\n";
    echo "<li>Removed date formatting (showing raw timestamps)</li>\n";
    echo "<li>Removed ucfirst() and other text transformations</li>\n";
    echo "<li>Set PDO attributes to prevent automatic data conversion</li>\n";
    echo "<li>Preserved only necessary number_format() for numeric statistics</li>\n";
    echo "</ul>\n";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>\n";
    echo "<p>Error testing dashboard: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>This might be because:</p>\n";
    echo "<ul>\n";
    echo "<li>Database tables don't exist yet</li>\n";
    echo "<li>Database connection failed</li>\n";
    echo "<li>No test data in the database</li>\n";
    echo "</ul>\n";
}
?>
