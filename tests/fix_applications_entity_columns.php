<?php
require_once __DIR__ . '/../shared/Core/Database.php';

try {
    echo "Checking and adding entity columns to applications table...\n";
    
    $db = (new Database())->connect();
    
    // Check if columns exist
    $stmt = $db->query("SHOW COLUMNS FROM applications LIKE 'entity_type'");
    $entityTypeExists = $stmt->fetch();
    
    $stmt = $db->query("SHOW COLUMNS FROM applications LIKE 'entity_id'");
    $entityIdExists = $stmt->fetch();
    
    if (!$entityTypeExists) {
        echo "Adding entity_type column...\n";
        $db->exec("ALTER TABLE applications ADD COLUMN entity_type VARCHAR(50) NULL AFTER status");
        echo "entity_type column added successfully.\n";
    } else {
        echo "entity_type column already exists.\n";
    }
    
    if (!$entityIdExists) {
        echo "Adding entity_id column...\n";
        $db->exec("ALTER TABLE applications ADD COLUMN entity_id INT UNSIGNED NULL AFTER entity_type");
        echo "entity_id column added successfully.\n";
    } else {
        echo "entity_id column already exists.\n";
    }
    
    // Add indexes if columns were created
    if (!$entityTypeExists || !$entityIdExists) {
        echo "Adding indexes...\n";
        try {
            $db->exec("ALTER TABLE applications ADD INDEX idx_applications_entity (entity_type, entity_id)");
            echo "Index added successfully.\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "Index already exists.\n";
            } else {
                throw $e;
            }
        }
    }
    
    echo "All done! Applications table now has entity_type and entity_id columns.\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
