<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'] ?? null;

$sql = "SELECT c.*, p.Nombre AS Paciente, m.Nombre AS Medico
        FROM Consulta_Programada c
        JOIN Paciente p ON c.ID_Paciente = p.ID_Paciente
        JOIN Medico m ON c.ID_Medico = m.ID_Medico
        ORDER BY c.ID_Consulta DESC";
$stmt = $pdo->query($sql);
$consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Consultas Programadas - Sistema Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
  <h3>Consultas Programadas</h3>
  <a href="dashboard.php" class="btn btn-secondary btn-sm mb-3">Volver al Panel</a>
  <a href="consultas_crear.php" class="btn btn-success btn-sm mb-3">Nueva Consulta</a>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-primary">
        <tr>
          <th>Paciente</th>
          <th>Médico</th>
          <th>Fecha</th>
          <th>Motivo</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($consultas): ?>
          <?php foreach ($consultas as $c): ?>
            <tr>
              <td><?= htmlspecialchars($c['Paciente']) ?></td>
              <td><?= htmlspecialchars($c['Medico']) ?></td>
              <td><?= htmlspecialchars($c['Fecha_Consulta']) ?></td>
              <td><?= htmlspecialchars($c['Motivo']) ?></td>
              <td><?= htmlspecialchars($c['Estado']) ?></td>
              <td>
                <a href="consultas_editar.php?id=<?= $c['ID_Consulta'] ?>" class="btn btn-sm btn-primary">Editar</a>
                <a href="consultas_eliminar.php?id=<?= $c['ID_Consulta'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta consulta?')">Eliminar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center">No hay consultas programadas.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
