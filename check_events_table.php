<?php
require_once 'shared/Core/Database.php';

try {
    $db = (new Database())->connect();
    $stmt = $db->query('DESCRIBE events');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Events table structure:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\nSample event data:\n";
    $stmt = $db->query('SELECT * FROM events LIMIT 1');
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($event) {
        foreach ($event as $key => $value) {
            echo $key . ": " . $value . "\n";
        }
    } else {
        echo "No events found in the table.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
