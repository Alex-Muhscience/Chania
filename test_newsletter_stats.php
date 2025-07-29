<?php
// Test script to verify newsletter statistics queries
require_once __DIR__ . '/shared/Core/Database.php';

try {
    $pdo = Database::getConnection();
    
    echo "Testing newsletter statistics queries...\n\n";
    
    // Test 1: Status counts
    $statusQuery = "SELECT status, COUNT(*) as count FROM newsletter_subscribers GROUP BY status";
    $statusStmt = $pdo->prepare($statusQuery);
    $statusStmt->execute();
    $statusCounts = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    echo "Status Counts:\n";
    var_dump($statusCounts);
    echo "\n";
    
    // Test 2: Recent subscribers (last 30 days)
    $recentQuery = "SELECT DATE(subscribed_at) as date, COUNT(*) as count 
                    FROM newsletter_subscribers 
                    WHERE subscribed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                    GROUP BY DATE(subscribed_at) 
                    ORDER BY date DESC";
    $recentStmt = $pdo->prepare($recentQuery);
    $recentStmt->execute();
    $recentSubscribers = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Recent Subscribers (last 30 days):\n";
    var_dump($recentSubscribers);
    echo "\n";
    
    // Test 3: All subscribers
    $allQuery = "SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC";
    $allStmt = $pdo->prepare($allQuery);
    $allStmt->execute();
    $allSubscribers = $allStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "All Subscribers:\n";
    var_dump($allSubscribers);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
