<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;
$editando = false;

$pacientes = $pdo->query("SELECT ID_Paciente, Nombre FROM Paciente")->fetchAll(PDO::FETCH_ASSOC);
$medicos = $pdo->query("SELECT ID_Medico, Nombre FROM Medico")->fetchAll(PDO::FETCH_ASSOC);

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM Visita_Medica WHERE ID_Visita = ?");
    $stmt->execute([$id]);
    $visita = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($visita) $editando = true;
} else {
    $visita = ['ID_Paciente' => '', 'ID_Medico' => '', 'Fecha_Visita' => '', 'Motivo' => '', 'Diagnostico' => '', 'Recomendaciones' => ''];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= $editando ? 'Editar' : 'Agregar' ?> Visita Médica</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container mt-4">
  <h2><?= $editando ? 'Editar' : 'Agregar' ?> Visita Médica</h2>
  <form method="post" action="visita_save.php">
    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
    <div class="mb-3">
      <label>Paciente</label>
      <select name="ID_Paciente" class="form-select" required>
        <option value="">Seleccione</option>
        <?php foreach ($pacientes as $p): ?>
          <option value="<?= $p['ID_Paciente'] ?>" <?= $p['ID_Paciente'] == $visita['ID_Paciente'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($p['Nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Médico</label>
      <select name="ID_Medico" class="form-select" required>
        <option value="">Seleccione</option>
        <?php foreach ($medicos as $m): ?>
          <option value="<?= $m['ID_Medico'] ?>" <?= $m['ID_Medico'] == $visita['ID_Medico'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($m['Nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Fecha de Visita</label>
      <input type="date" name="Fecha_Visita" class="form-control" value="<?= htmlspecialchars($visita['Fecha_Visita']) ?>" required>
    </div>
    <div class="mb-3">
      <label>Motivo</label>
      <input type="text" name="Motivo" class="form-control" value="<?= htmlspecialchars($visita['Motivo']) ?>" required>
    </div>
    <div class="mb-3">
      <label>Diagnóstico</label>
      <input type="text" name="Diagnostico" class="form-control" value="<?= htmlspecialchars($visita['Diagnostico']) ?>">
    </div>
    <div class="mb-3">
      <label>Recomendaciones</label>
      <input type="text" name="Recomendaciones" class="form-control" value="<?= htmlspecialchars($visita['Recomendaciones']) ?>">
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
    <a href="visita.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>
