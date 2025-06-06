<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Agregar nuevo programa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO Programa_Medico (Nombre_Programa, Especialidad, Enfermedad_Condicion, Descripcion, Año, Meta_Anual) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nombre'],
        $_POST['especialidad'],
        $_POST['condicion'],
        $_POST['descripcion'],
        $_POST['anio'],
        $_POST['meta']
    ]);
    header("Location: programas.php");
    exit;
}

// Actualizar programa existente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $stmt = $pdo->prepare("UPDATE Programa_Medico SET Nombre_Programa=?, Especialidad=?, Enfermedad_Condicion=?, Descripcion=?, Año=?, Meta_Anual=? WHERE ID_Programa=?");
    $stmt->execute([
        $_POST['nombre'],
        $_POST['especialidad'],
        $_POST['condicion'],
        $_POST['descripcion'],
        $_POST['anio'],
        $_POST['meta'],
        $_POST['id']
    ]);
    header("Location: programas.php");
    exit;
}

// Eliminar programa
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM Programa_Medico WHERE ID_Programa = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: programas.php");
    exit;
}

// Obtener todos los programas
$stmt = $pdo->query("SELECT * FROM Programa_Medico ORDER BY Año DESC");
$programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Programas Médicos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
  <h2 class="mb-4">Gestión de Programas Médicos</h2>

  <form method="POST" class="card card-body mb-4 shadow-sm">
    <h5>Agregar Programa</h5>
    <div class="row g-2">
      <div class="col-md-4">
        <input type="text" name="nombre" class="form-control" placeholder="Nombre del Programa" required>
      </div>
      <div class="col-md-3">
        <input type="text" name="especialidad" class="form-control" placeholder="Especialidad" required>
      </div>
      <div class="col-md-3">
        <input type="text" name="condicion" class="form-control" placeholder="Condición" required>
      </div>
      <div class="col-md-6 mt-2">
        <input type="text" name="descripcion" class="form-control" placeholder="Descripción" required>
      </div>
      <div class="col-md-2 mt-2">
        <input type="number" name="anio" class="form-control" placeholder="Año" required>
      </div>
      <div class="col-md-2 mt-2">
        <input type="number" name="meta" class="form-control" placeholder="Meta Anual" required>
      </div>
      <div class="col-md-2 mt-2">
        <button type="submit" name="add" class="btn btn-primary w-100">Agregar</button>
      </div>
    </div>
  </form>

  <table class="table table-bordered bg-white shadow-sm">
    <thead class="table-primary">
      <tr>
        <th>#</th>
        <th>Programa</th>
        <th>Especialidad</th>
        <th>Condición</th>
        <th>Descripción</th>
        <th>Año</th>
        <th>Meta</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($programas as $prog): ?>
        <tr>
          <td><?= htmlspecialchars($prog['ID_Programa']) ?></td>
          <td><?= htmlspecialchars($prog['Nombre_Programa']) ?></td>
          <td><?= htmlspecialchars($prog['Especialidad']) ?></td>
          <td><?= htmlspecialchars($prog['Enfermedad_Condicion']) ?></td>
          <td><?= htmlspecialchars($prog['Descripcion']) ?></td>
          <td><?= htmlspecialchars($prog['Año']) ?></td>
          <td><?= htmlspecialchars($prog['Meta_Anual']) ?></td>
          <td>
            <a href="?delete=<?= $prog['ID_Programa'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar programa?')">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

</body>
</html>