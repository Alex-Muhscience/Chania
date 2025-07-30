<?php
require_once '../../shared/Core/Database.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: partners.php');
    exit;
}

try {
    $db = (new Database())->connect();
    $stmt = $db->prepare("UPDATE partners SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: partners.php?deleted=1');
} catch (Exception $e) {
    header('Location: partners.php?error=' . urlencode($e->getMessage()));
}
?>
