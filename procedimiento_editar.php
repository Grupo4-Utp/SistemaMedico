<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Insertar nuevo procedimiento médico
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_paciente = $_POST['id_paciente'] ?? '';
    $id_medico = $_POST['id_medico'] ?? '';
    $tipo_procedimiento = $_POST['tipo_procedimiento'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $resultados = $_POST['resultados'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';

    if ($id_paciente && $id_medico && $tipo_procedimiento && $fecha) {
        $stmt = $pdo->prepare("INSERT INTO Procedimiento_Medico (ID_Paciente, ID_Medico, Tipo_Procedimiento, Fecha, Resultados, Observaciones) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_paciente, $id_medico, $tipo_procedimiento, $fecha, $resultados, $observaciones]);
        header('Location: procedimiento.php');
        exit;
    }
}

// Obtener lista procedimientos con JOIN para mostrar datos legibles
$stmt = $pdo->query("SELECT p.ID_Procedimiento, pa.Nombre AS NombrePaciente, pa.Apellido AS ApellidoPaciente, m.Nombre AS NombreMedico, p.Tipo_Procedimiento, p.Fecha, p.Resultados, p.Observaciones
                     FROM Procedimiento_Medico p
                     JOIN Paciente pa ON p.ID_Paciente = pa.ID_Paciente
                     JOIN Medico m ON p.ID_Medico = m.ID_Medico
                     ORDER BY p.ID_Procedimiento DESC");
$procedimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener pacientes y médicos para el formulario
$pacientes = $pdo->query("SELECT ID_Paciente, Nombre, Apellido FROM Paciente ORDER BY Nombre")->fetchAll(PDO::FETCH_ASSOC);
$medicos = $pdo->query("SELECT ID_Medico, Nombre FROM Medico ORDER BY Nombre")->fetchAll(PDO::FETCH_ASSOC);

$user = $_SESSION['user'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Procedimientos Médicos - Sistema Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Sistema Médico</a>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item me-3 text-white">
          Bienvenido, <strong><?= htmlspecialchars($user['usuario']) ?></strong> (<?= htmlspecialchars($user['tipo']) ?>)
        </li>
        <li class="nav-item">
          <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h3>Procedimientos Médicos</h3>

  <!-- Formulario para añadir procedimiento -->
  <form method="post" class="mb-4">
    <div class="row g-3">
      <div class="col-md-3">
        <label for="id_paciente" class="form-label">Paciente</label>
        <select id="id_paciente" name="id_paciente" class="form-select" required>
          <option value="" selected disabled>Seleccione paciente</option>
          <?php foreach ($pacientes as $pac): ?>
            <option value="<?= $pac['ID_Paciente'] ?>">
              <?= htmlspecialchars($pac['Nombre'] . ' ' . $pac['Apellido']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label for="id_medico" class="form-label">Médico</label>
        <select id="id_medico" name="id_medico" class="form-select" required>
          <option value="" selected disabled>Seleccione médico</option>
          <?php foreach ($medicos as $med): ?>
            <option value="<?= $med['ID_Medico'] ?>">
              <?= htmlspecialchars($med['Nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label for="tipo_procedimiento" class="form-label">Tipo de Procedimiento</label>
        <input type="text" id="tipo_procedimiento" name="tipo_procedimiento" class="form-control" required>
      </div>

      <div class="col-md-3">
        <label for="fecha" class="form-label">Fecha</label>
        <input type="date" id="fecha" name="fecha" class="form-control" required>
      </div>

      <div class="col-md-6">
        <label for="resultados" class="form-label">Resultados</label>
        <input type="text" id="resultados" name="resultados" class="form-control">
      </div>

      <div class="col-md-6">
        <label for="observaciones" class="form-label">Observaciones</label>
        <input type="text" id="observaciones" name="observaciones" class="form-control">
      </div>

      <div class="col-12 d-grid">
        <button type="submit" class="btn btn-primary">Agregar Procedimiento</button>
      </div>
    </div>
  </form>

  <!-- Tabla procedimientos -->
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-primary">
        <tr>
          <th>Paciente</th>
          <th>Médico</th>
          <th>Tipo de Procedimiento</th>
          <th>Fecha</th>
          <th>Resultados</th>
          <th>Observaciones</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($procedimientos): ?>
          <?php foreach ($procedimientos as $proc): ?>
            <tr>
              <td><?= htmlspecialchars($proc['NombrePaciente']) ?></td>
              <td><?= htmlspecialchars($proc['NombreMedico']) ?></td>
              <td><?= htmlspecialchars($proc['Tipo_Procedimiento']) ?></td>
              <td><?= htmlspecialchars($proc['Fecha']) ?></td>
              <td><?= htmlspecialchars($proc['Resultados']) ?></td>
              <td><?= htmlspecialchars($proc['Observaciones']) ?></td>
              <td>
                <a href="procedimiento_editar.php?id=<?= $proc['ID_Procedimiento'] ?>" class="btn btn-sm btn-warning">Editar</a>
                <a href="procedimiento_eliminar.php?id=<?= $proc['ID_Procedimiento'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este procedimiento?');">Eliminar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center">No hay procedimientos registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
