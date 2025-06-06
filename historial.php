<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['crear'])) {
    $stmt = $pdo->prepare("INSERT INTO Historial_Medico (ID_Paciente, Diagnostico, Alergias, Enfermedades_Cronicas, Notas_Medicas)
                           VALUES (:id_paciente, :diagnostico, :alergias, :cronicas, :notas)");
    $stmt->execute([
        'id_paciente' => $_POST['id_paciente'],
        'diagnostico' => $_POST['diagnostico'],
        'alergias' => $_POST['alergias'],
        'cronicas' => $_POST['cronicas'],
        'notas' => $_POST['notas']
    ]);
    header("Location: historial.php");
    exit;
}

if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM Historial_Medico WHERE ID_Historial = ?");
    $stmt->execute([$_GET['eliminar']]);
    header("Location: historial.php");
    exit;
}

$stmt = $pdo->query("SELECT hm.*, p.Nombre, p.Apellido FROM Historial_Medico hm
                     JOIN Paciente p ON hm.ID_Paciente = p.ID_Paciente
                     ORDER BY hm.ID_Historial DESC");
$historiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pacientes = $pdo->query("SELECT ID_Paciente, Nombre, Apellido FROM Paciente ORDER BY Nombre")->fetchAll(PDO::FETCH_ASSOC);
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

<div class="container py-5">
    <h2 class="mb-4">Historial Médico</h2>

    <form method="POST" class="row g-3 mb-4">
        <input type="hidden" name="crear" value="1">
        <div class="col-md-4">
            <label for="id_paciente" class="form-label">Paciente</label>
            <select name="id_paciente" id="id_paciente" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach ($pacientes as $paciente): ?>
                    <option value="<?= $paciente['ID_Paciente'] ?>">
                        <?= htmlspecialchars($paciente['Nombre'] . ' ' . $paciente['Apellido']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Diagnóstico</label>
            <input type="text" name="diagnostico" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Alergias</label>
            <input type="text" name="alergias" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Enfermedades Crónicas</label>
            <input type="text" name="cronicas" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Notas Médicas</label>
            <input type="text" name="notas" class="form-control">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Agregar Historial</button>
        </div>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Paciente</th>
                <th>Diagnóstico</th>
                <th>Alergias</th>
                <th>Enfermedades Crónicas</th>
                <th>Notas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historiales as $h): ?>
                <tr>
                    <td><?= $h['ID_Historial'] ?></td>
                    <td><?= htmlspecialchars($h['Nombre'] . ' ' . $h['Apellido']) ?></td>
                    <td><?= htmlspecialchars($h['Diagnostico']) ?></td>
                    <td><?= htmlspecialchars($h['Alergias']) ?></td>
                    <td><?= htmlspecialchars($h['Enfermedades_Cronicas']) ?></td>
                    <td><?= htmlspecialchars($h['Notas_Medicas']) ?></td>
                    <td>
                        <a href="?eliminar=<?= $h['ID_Historial'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar historial?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
