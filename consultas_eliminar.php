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
    $stmt = $pdo->prepare("DELETE FROM Consulta_Programada WHERE ID_Consulta = ?");
    $stmt->execute([$id]);
}

header('Location: consultas.php');
exit;
