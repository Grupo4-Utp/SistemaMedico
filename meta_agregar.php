<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $programa = $_POST['ID_Programa'];
    $anio = $_POST['Año'];
    $tipo = $_POST['Tipo_Procedimiento'];
    $cantidad = $_POST['Cantidad_Esperada'];

    $stmt = $pdo->prepare("INSERT INTO Meta_Anual (ID_Programa, Año, Tipo_Procedimiento, Cantidad_Esperada) 
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$programa, $anio, $tipo, $cantidad]);
}

header("Location: meta.php");
exit;
