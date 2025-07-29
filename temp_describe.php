<?php
$db = new PDO('mysql:host=localhost;dbname=chania_db', 'root', '');
$stmt = $db->query('DESCRIBE admin_logs');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Columns in admin_logs table:\n";
foreach ($columns as $column) {
    echo $column['Field'] . " (" . $column['Type'] . ")\n";
}
?>
