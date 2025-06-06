<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO Acceso_Movil (ID_Usuario, Dispositivo, Fecha_Hora) VALUES (:id_usuario, :dispositivo, :fecha_hora)");
    $stmt->execute([
        'id_usuario' => $_POST['id_usuario'],
        'dispositivo' => $_POST['dispositivo'],
        'fecha_hora' => $_POST['fecha_hora']
    ]);
    header("Location: acceso.php");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM Acceso_Movil WHERE ID_Acceso = :id");
    $stmt->execute(['id' => $_GET['delete']]);
    header("Location: acceso.php");
    exit;
}

$stmt = $pdo->query("
    SELECT a.*, u.usuario 
    FROM Acceso_Movil a
    JOIN Usuarios u ON a.ID_Usuario = u.ID_Usuario
    ORDER BY a.Fecha_Hora DESC
");
$accesos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$usuarios = $pdo->query("SELECT ID_Usuario, usuario FROM Usuarios ORDER BY usuario")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Accesos Móviles - Sistema Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <h2 class="mb-4 text-primary">Gestión de Accesos Móviles</h2>

    <form method="POST" class="row g-3 mb-4">
        <input type="hidden" name="add" value="1" />
        <div class="col-md-4">
            <label for="id_usuario" class="form-label">Usuario</label>
            <select name="id_usuario" id="id_usuario" class="form-select" required>
                <option value="" disabled selected>Seleccione un usuario</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['ID_Usuario'] ?>"><?= htmlspecialchars($u['usuario']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="dispositivo" class="form-label">Dispositivo</label>
            <input type="text" name="dispositivo" id="dispositivo" class="form-control" required />
        </div>
        <div class="col-md-3">
            <label for="fecha_hora" class="form-label">Fecha y Hora</label>
            <input type="datetime-local" name="fecha_hora" id="fecha_hora" class="form-control" required />
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
                    <th>Usuario</th>
                    <th>Dispositivo</th>
                    <th>Fecha y Hora</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accesos as $a): ?>
                    <tr>
                        <td><?= $a['ID_Acceso'] ?></td>
                        <td><?= htmlspecialchars($a['usuario']) ?></td>
                        <td><?= htmlspecialchars($a['Dispositivo']) ?></td>
                        <td><?= htmlspecialchars($a['Fecha_Hora']) ?></td>
                        <td>
                            <a href="?delete=<?= $a['ID_Acceso'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que desea eliminar este acceso?');">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($accesos)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No hay registros de accesos móviles.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
