<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: medico.php');
    exit;
}

// Obtener médico a editar
$stmt = $pdo->prepare("SELECT * FROM Medico WHERE ID_Medico = ?");
$stmt->execute([$id]);
$medico = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$medico) {
    header('Location: medico.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $especialidad = $_POST['especialidad'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $disponibilidad = $_POST['disponibilidad'] ?? '';

    $stmt = $pdo->prepare("UPDATE Medico SET Nombre = ?, Especialidad = ?, Telefono = ?, Correo_Electronico = ?, Disponibilidad = ? WHERE ID_Medico = ?");
    $stmt->execute([$nombre, $especialidad, $telefono, $correo, $disponibilidad, $id]);

    header('Location: medico.php');
    exit;
}

$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Editar Médico - Sistema Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
  <h3>Editar Médico</h3>
  <form method="post" class="mt-3">
    <div class="mb-3">
      <label>Nombre</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($medico['Nombre']) ?>" required>
    </div>
    <div class="mb-3">
      <label>Especialidad</label>
      <input type="text" name="especialidad" class="form-control" value="<?= htmlspecialchars($medico['Especialidad']) ?>" required>
    </div>
    <div class="mb-3">
      <label>Teléfono</label>
      <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($medico['Telefono']) ?>">
    </div>
    <div class="mb-3">
      <label>Correo Electrónico</label>
      <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($medico['Correo_Electronico']) ?>">
    </div>
    <div class="mb-3">
      <label>Disponibilidad</label>
      <input type="text" name="disponibilidad" class="form-control" value="<?= htmlspecialchars($medico['Disponibilidad']) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="medico.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>
