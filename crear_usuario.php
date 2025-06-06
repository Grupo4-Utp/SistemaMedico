<?php
require_once 'includes/db.php';

$usuario = 'nuevo_usuario';
$password = password_hash('password_seguro', PASSWORD_DEFAULT);
$tipo = 'Paciente'; // o 'Medico', 'Admin'

try {
    $stmt = $pdo->prepare("INSERT INTO Acceso_Movil (Usuario, contrasena, Tipo_Usuario) VALUES (?, ?, ?)");
    $stmt->execute([$usuario, $password, $tipo]);
    echo "Usuario creado correctamente.";
} catch (PDOException $e) {
    echo "Error al crear el usuario: " . $e->getMessage();
}
?>
