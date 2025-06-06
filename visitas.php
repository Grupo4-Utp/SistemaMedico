<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

$stmt = $pdo->query("SELECT v.*, p.Nombre AS Nombre_Paciente, m.Nombre AS Nombre_Medico
                     FROM Visita_Medica v
                     JOIN Paciente p ON v.ID_Paciente = p.ID_Paciente
                     JOIN Medico m ON v.ID_Medico = m.ID_Medico
                     ORDER BY Fecha_Visita DESC");
$visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Visitas Médicas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container mt-4">
  <h2 class="mb-4">Visitas Médicas</h2>
  <a href="visita_form.php" class="btn btn-primary mb-3">Agregar Visita</a>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Paciente</th>
        <th>Médico</th>
        <th>Fecha</th>
        <th>Motivo</th>
        <th>Diagnóstico</th>
        <th>Recomendaciones</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($visitas as $v): ?>
      <tr>
        <td><?= htmlspecialchars($v['Nombre_Paciente']) ?></td>
        <td><?= htmlspecialchars($v['Nombre_Medico']) ?></td>
        <td><?= htmlspecialchars($v['Fecha_Visita']) ?></td>
        <td><?= htmlspecialchars($v['Motivo']) ?></td>
        <td><?= htmlspecialchars($v['Diagnostico']) ?></td>
        <td><?= htmlspecialchars($v['Recomendaciones']) ?></td>
        <td>
          <a href="visita_form.php?id=<?= $v['ID_Visita'] ?>" class="btn btn-sm btn-warning">Editar</a>
          <a href="visita_delete.php?id=<?= $v['ID_Visita'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta visita?')">Eliminar</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
