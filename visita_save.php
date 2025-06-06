<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

$id = $_POST['id'] ?? null;
$data = [
    'ID_Paciente' => $_POST['ID_Paciente'],
    'ID_Medico' => $_POST['ID_Medico'],
    'Fecha_Visita' => $_POST['Fecha_Visita'],
    'Motivo' => $_POST['Motivo'],
    'Diagnostico' => $_POST['Diagnostico'],
    'Recomendaciones' => $_POST['Recomendaciones']
];

if ($id) {
    $sql = "UPDATE Visita_Medica SET ID_Paciente = :ID_Paciente, ID_Medico = :ID_Medico, Fecha_Visita = :Fecha_Visita,
            Motivo = :Motivo, Diagnostico = :Diagnostico, Recomendaciones = :Recomendaciones WHERE ID_Visita = :id";
    $data['id'] = $id;
    $stmt = $pdo->prepare($sql);
} else {
    $sql = "INSERT INTO Visita_Medica (ID_Paciente, ID_Medico, Fecha_Visita, Motivo, Diagnostico, Recomendaciones)
            VALUES (:ID_Paciente, :ID_Medico, :Fecha_Visita, :Motivo, :Diagnostico, :Recomendaciones)";
    $stmt = $pdo->prepare($sql);
}
$stmt->execute($data);
header("Location: visita.php");
exit;
