<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Crear nueva notificación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO Notificacion (ID_Paciente, Mensaje, Fecha_Programada) VALUES (:id_paciente, :mensaje, :fecha)");
    $stmt->execute([
        'id_paciente' => $_POST['id_paciente'],
        'mensaje' => $_POST['mensaje'],
        'fecha' => $_POST['fecha_programada']
    ]);
    header("Location: notificacion.php");
    exit;
}

// Eliminar notificación
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM Notificacion WHERE ID_Notificacion = :id");
    $stmt->execute(['id' => $_GET['delete']]);
    header("Location: notificacion.php");
    exit;
}

// Obtener todas las notificaciones con nombre del paciente
$stmt = $pdo->query("
    SELECT n.*, p.Nombre, p.Apellido
    FROM Notificacion n
    JOIN Paciente p ON n.ID_Paciente = p.ID_Paciente
    ORDER BY n.Fecha_Programada DESC
");
$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener pacientes para el formulario
$pacientes = $pdo->query("SELECT ID_Paciente, Nombre, Apellido FROM Paciente ORDER BY Nombre")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones - Sistema Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <h2 class="mb-4 text-primary">Gestión de Notificaciones</h2>

    <form method="POST" class="row g-3 mb-4">
        <input type="hidden" name="add" value="1">
        <div class="col-md-4">
            <label for="id_paciente" class="form-label">Paciente</label>
            <select name="id_paciente" id="id_paciente" class="form-select" required>
                <option value="" disabled selected>Seleccione un paciente</option>
                <?php foreach ($pacientes as $p): ?>
                    <option value="<?= $p['ID_Paciente'] ?>"><?= htmlspecialchars($p['Nombre'] . ' ' . $p['Apellido']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="mensaje" class="form-label">Mensaje</label>
            <input type="text" class="form-control" name="mensaje" id="mensaje" required>
        </div>
        <div class="col-md-3">
            <label for="fecha_programada" class="form-label">Fecha Programada</label>
            <input type="date" class="form-control" name="fecha_programada" id="fecha_programada" required>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100">Agregar</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle bg-white">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Paciente</th>
                    <th>Mensaje</th>
                    <th>Fecha Programada</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notificaciones as $n): ?>
                    <tr>
                        <td><?= $n['ID_Notificacion'] ?></td>
                        <td><?= htmlspecialchars($n['Nombre'] . ' ' . $n['Apellido']) ?></td>
                        <td><?= htmlspecialchars($n['Mensaje']) ?></td>
                        <td><?= htmlspecialchars($n['Fecha_Programada']) ?></td>
                        <td>
                            <a href="?delete=<?= $n['ID_Notificacion'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que desea eliminar esta notificación?');">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($notificaciones)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No hay notificaciones registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
