<?php
require_once __DIR__ . '/../shared/Core/Database.php';

try {
    echo "Checking applications table structure...\n";
    
    $db = (new Database())->connect();
    
    // Show all columns in applications table
    $stmt = $db->query("SHOW COLUMNS FROM applications");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current columns in applications table:\n";
    echo "=====================================\n";
    
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) " . 
             ($column['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . 
             ($column['Default'] ? " DEFAULT {$column['Default']}" : '') . "\n";
    }
    
    echo "\nLooking for entity_type and entity_id columns...\n";
    
    $hasEntityType = false;
    $hasEntityId = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'entity_type') {
            $hasEntityType = true;
            echo "✓ entity_type column found\n";
        }
        if ($column['Field'] === 'entity_id') {
            $hasEntityId = true;
            echo "✓ entity_id column found\n";
        }
    }
    
    if (!$hasEntityType) {
        echo "✗ entity_type column NOT found\n";
    }
    
    if (!$hasEntityId) {
        echo "✗ entity_id column NOT found\n";
    }
    
    echo "\nTable structure check completed.\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
