<?php
require_once '../../shared/Core/Database.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: team_members.php');
    exit;
}

try {
    $db = (new Database())->connect();
    $stmt = $db->prepare("UPDATE team_members SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: team_members.php?deleted=1');
} catch (Exception $e) {
    header('Location: team_members.php?error=' . urlencode($e->getMessage()));
}
?>
