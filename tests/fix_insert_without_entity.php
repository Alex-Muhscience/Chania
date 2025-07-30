<?php
echo "Creating temporary fix by removing entity columns from INSERT statements...\n";

// Create backup versions without entity columns
$files = [
    'client/src/Models/ApplicationModel.php',
    'client/src/Services/submit_application.php'
];

foreach ($files as $file) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        // Create backup
        $backupPath = $fullPath . '.backup.' . date('Y-m-d-H-i-s');
        file_put_contents($backupPath, $content);
        echo "✓ Backup created: " . basename($backupPath) . "\n";
        
        // Remove entity columns from INSERT statements
        $content = str_replace(
            ', entity_type, entity_id',
            '',
            $content
        );
        
        $content = str_replace(
            ', ?, ?)',
            ')',
            $content
        );
        
        // Remove entity parameters from execute arrays
        $content = preg_replace(
            '/(\$data\[\'entity_type\'\] \?\? \'program\',\s*\$data\[\'entity_id\'\] \?\? \$data\[\'program_id\'\])/',
            '',
            $content
        );
        
        // Clean up trailing commas
        $content = str_replace(',)', ')', $content);
        $content = preg_replace('/,(\s*\])/', '$1', $content);
        
        file_put_contents($fullPath, $content);
        echo "✓ Updated: " . basename($file) . "\n";
    }
}

echo "\nTemporary fix applied. Please test the application submission now.\n";
echo "If it works, we can then investigate why the entity columns aren't being recognized.\n";
?>
