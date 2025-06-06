<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'] ?? null;

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

$params = [];
$where = '';

if ($fecha_inicio && $fecha_fin) {
    $where = "WHERE c.Fecha_Consulta BETWEEN :fecha_inicio AND :fecha_fin";
    $params = [
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin,
    ];
}

// Consulta con filtro de fechas
$query = "
    SELECT m.Nombre AS Medico, COUNT(c.ID_Consulta) AS TotalConsultas
    FROM Consulta_Programada c
    JOIN Medico m ON c.ID_Medico = m.ID_Medico
    $where
    GROUP BY m.ID_Medico
    ORDER BY TotalConsultas DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Reportes - Sistema Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
  <h3>Reportes</h3>

  <form method="GET" class="row g-3 mb-4">
    <div class="col-auto">
      <label for="fecha_inicio" class="col-form-label">Fecha Inicio:</label>
      <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
    </div>
    <div class="col-auto">
      <label for="fecha_fin" class="col-form-label">Fecha Fin:</label>
      <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
    </div>
    <div class="col-auto align-self-end">
      <button type="submit" class="btn btn-primary">Filtrar</button>
      <a href="reportes.php" class="btn btn-secondary">Limpiar</a>
    </div>
  </form>

  <div class="mb-3">
    <a href="exportar_reportes.php?fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>" class="btn btn-success">Exportar a Excel</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-primary">
        <tr>
          <th>Médico</th>
          <th>Total Consultas Programadas</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($reportes): ?>
          <?php foreach ($reportes as $rep): ?>
            <tr>
              <td><?= htmlspecialchars($rep['Medico']) ?></td>
              <td><?= (int)$rep['TotalConsultas'] ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="2" class="text-center">No hay datos para mostrar.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
