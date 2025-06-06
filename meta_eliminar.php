<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM Meta_Anual WHERE ID_Meta = ?");
    $stmt->execute([$id]);
}

header("Location: meta.php");
exit;
