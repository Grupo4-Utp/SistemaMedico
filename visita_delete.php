<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM Visita_Medica WHERE ID_Visita = ?");
    $stmt->execute([$id]);
}
header("Location: visita.php");
exit;
