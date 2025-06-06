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
    header('Location: consultas.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Consulta_Programada WHERE ID_Consulta = ?");
$stmt->execute([$id]);
$consulta = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$consulta) {
    header('Location: consultas.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_paciente = $_POST['id_paciente'] ?? null;
    $id_medico = $_POST['id_medico'] ?? null;
    $fecha = $_POST['fecha'] ?? null;
    $motivo = $_POST['motivo'] ?? '';
    $estado = $_POST['estado'] ?? 'Pendiente';

    $stmt = $pdo->prepare("UPDATE Consulta_Programada SET ID_Paciente=?, ID_Medico=?, Fecha_Consulta=?, Motivo=?, Estado=? WHERE ID_Consulta=?");
    $stmt->execute([$id_paciente, $id_medico, $fecha, $motivo, $estado, $id]);

    header("Location: consultas.php");
    exit;
}

$pacientes = $pdo->query("SELECT ID_Paciente, Nombre FROM Paciente")->fetchAll(PDO::FETCH_ASSOC);
$medicos = $pdo->query("SELECT ID_Medico, Nombre FROM Medico")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Consulta Programada</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h3>Editar Consulta Programada</h3>
  <form method="post" class="mt-3">
    <div class="mb-3">
      <label>Paciente</label>
      <select name="id_paciente" class="form-select" required>
        <option value="">Seleccione</option>
        <?php foreach ($pacientes as $p): ?>
          <option value="<?= $p['ID_Paciente'] ?>" <?= $p['ID_Paciente'] == $consulta['ID_Paciente'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($p['Nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Médico</label>
      <select name="id_medico" class="form-select" required>
        <option value="">Seleccione</option>
        <?php foreach ($medicos as $m): ?>
          <option value="<?= $m['ID_Medico'] ?>" <?= $m['ID_Medico'] == $consulta['ID_Medico'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($m['Nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Fecha de Consulta</label>
      <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($consulta['Fecha_Consulta']) ?>" required>
    </div>
    <div class="mb-3">
      <label>Motivo</label>
      <textarea name="motivo" class="form-control" required><?= htmlspecialchars($consulta['Motivo']) ?></textarea>
    </div>
    <div class="mb-3">
      <label>Estado</label>
      <select name="estado" class="form-select" required>
        <option value="Pendiente" <?= $consulta['Estado'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
        <option value="Confirmada" <?= $consulta['Estado'] == 'Confirmada' ? 'selected' : '' ?>>Confirmada</option>
        <option value="Cancelada" <?= $consulta['Estado'] == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="consultas.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>
