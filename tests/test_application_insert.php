<?php
require_once __DIR__ . '/../shared/Core/Database.php';

try {
    echo "Testing INSERT into applications table...\n";
    
    $db = (new Database())->connect();
    
    // Generate test data
    $applicationNumber = 'TEST-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    
    // Test INSERT with entity columns
    $stmt = $db->prepare("
        INSERT INTO applications (
            program_id, application_number, first_name, last_name, email, phone, address,
            education_details, work_experience, motivation, ip_address, entity_type, entity_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        1, // program_id
        $applicationNumber,
        'Test',
        'User',
        'test@example.com',
        '+1234567890',
        '123 Test Street',
        'Test education details',
        'Test work experience',
        'Test motivation',
        '127.0.0.1',
        'program',
        1
    ]);
    
    if ($result) {
        $applicationId = $db->lastInsertId();
        echo "✓ INSERT successful! Application ID: $applicationId\n";
        
        // Clean up test data
        $stmt = $db->prepare("DELETE FROM applications WHERE id = ?");
        $stmt->execute([$applicationId]);
        echo "✓ Test data cleaned up.\n";
    } else {
        echo "✗ INSERT failed!\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
