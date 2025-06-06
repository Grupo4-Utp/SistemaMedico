<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$stmt = $pdo->query("SELECT ma.*, pm.Nombre_Programa 
                     FROM Meta_Anual ma 
                     JOIN Programa_Medico pm ON ma.ID_Programa = pm.ID_Programa");
$metas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de programas médicos para el formulario
$programas = $pdo->query("SELECT ID_Programa, Nombre_Programa FROM Programa_Medico")->fetchAll(PDO::FETCH_ASSOC);
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
  <h2 class="mb-4">Gestión de Metas Anuales</h2>

  <!-- Formulario -->
  <form action="meta_agregar.php" method="POST" class="row g-3 mb-4">
    <div class="col-md-4">
      <label for="programa" class="form-label">Programa Médico</label>
      <select name="ID_Programa" class="form-select" required>
        <option value="">Seleccione...</option>
        <?php foreach ($programas as $prog): ?>
          <option value="<?= $prog['ID_Programa'] ?>"><?= htmlspecialchars($prog['Nombre_Programa']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label for="anio" class="form-label">Año</label>
      <input type="number" name="Año" class="form-control" required min="2020" max="2100">
    </div>
    <div class="col-md-3">
      <label for="tipo" class="form-label">Tipo Procedimiento</label>
      <input type="text" name="Tipo_Procedimiento" class="form-control" required>
    </div>
    <div class="col-md-3">
      <label for="cantidad" class="form-label">Cantidad Esperada</label>
      <input type="number" name="Cantidad_Esperada" class="form-control" required min="1">
    </div>
    <div class="col-12 text-end">
      <button type="submit" class="btn btn-primary">Agregar Meta</button>
    </div>
  </form>

  <!-- Tabla -->
  <div class="table-responsive">
    <table class="table table-striped">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>Programa</th>
          <th>Año</th>
          <th>Tipo Procedimiento</th>
          <th>Cantidad Esperada</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($metas as $meta): ?>
          <tr>
            <td><?= $meta['ID_Meta'] ?></td>
            <td><?= htmlspecialchars($meta['Nombre_Programa']) ?></td>
            <td><?= $meta['Año'] ?></td>
            <td><?= htmlspecialchars($meta['Tipo_Procedimiento']) ?></td>
            <td><?= $meta['Cantidad_Esperada'] ?></td>
            <td>
              <a href="meta_editar.php?id=<?= $meta['ID_Meta'] ?>" class="btn btn-sm btn-warning">Editar</a>
              <a href="meta_eliminar.php?id=<?= $meta['ID_Meta'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta meta?')">Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
