<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM Procedimiento_Medico WHERE ID_Procedimiento = ?");
    $stmt->execute([$id]);
}

header('Location: procedimiento.php');
exit;
