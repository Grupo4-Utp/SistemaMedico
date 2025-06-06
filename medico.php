<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $especialidad = $_POST['especialidad'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $disponibilidad = $_POST['disponibilidad'] ?? '';

    if ($nombre && $especialidad) {
        $stmt = $pdo->prepare("INSERT INTO Medico (Nombre, Especialidad, Telefono, Correo_Electronico, Disponibilidad) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $especialidad, $telefono, $correo, $disponibilidad]);
        header('Location: medico.php');
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM Medico ORDER BY ID_Medico DESC");
$medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user = $_SESSION['user'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Médicos - Sistema Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
  <h3>Gestión de Médicos</h3>

  <form method="post" class="mb-4">
    <div class="row g-3">
      <div class="col-md-3">
        <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
      </div>
      <div class="col-md-3">
        <input type="text" name="especialidad" class="form-control" placeholder="Especialidad" required>
      </div>
      <div class="col-md-2">
        <input type="text" name="telefono" class="form-control" placeholder="Teléfono">
      </div>
      <div class="col-md-3">
        <input type="email" name="correo" class="form-control" placeholder="Correo Electrónico">
      </div>
      <div class="col-md-2">
        <input type="text" name="disponibilidad" class="form-control" placeholder="Disponibilidad">
      </div>
      <div class="col-md-1 d-grid">
        <button type="submit" class="btn btn-primary">Agregar</button>
      </div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-primary">
        <tr>
          <th>Nombre</th>
          <th>Especialidad</th>
          <th>Teléfono</th>
          <th>Correo Electrónico</th>
          <th>Disponibilidad</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($medicos): ?>
          <?php foreach ($medicos as $med): ?>
            <tr>
              <td><?= htmlspecialchars($med['Nombre']) ?></td>
              <td><?= htmlspecialchars($med['Especialidad']) ?></td>
              <td><?= htmlspecialchars($med['Telefono']) ?></td>
              <td><?= htmlspecialchars($med['Correo_Electronico']) ?></td>
              <td><?= htmlspecialchars($med['Disponibilidad']) ?></td>
              <td>
                <a href="medico_editar.php?id=<?= $med['ID_Medico'] ?>" class="btn btn-sm btn-warning">Editar</a>
                <a href="medico_eliminar.php?id=<?= $med['ID_Medico'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este médico?');">Eliminar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center">No hay médicos registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
