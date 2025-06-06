<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Verificar que el usuario esté autenticado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Inicializar mensajes
$mensaje = $_SESSION['mensaje'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['error']);

// Manejo de eliminación de registros de seguridad vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = filter_var($_POST['delete'], FILTER_VALIDATE_INT);
    if ($id !== false) {
        $stmt = $pdo->prepare("DELETE FROM Seguridad_Acceso WHERE ID_Log = :id");
        if ($stmt->execute([':id' => $id])) {
            $_SESSION['mensaje'] = "Registro eliminado correctamente.";
        } else {
            $_SESSION['error'] = "No se pudo eliminar el registro.";
        }
    } else {
        $_SESSION['error'] = "ID inválido para eliminación.";
    }
    header("Location: seguridad.php");
    exit;
}

// Consulta los logs de seguridad con info del usuario (solo Acceso_Movil)
$stmt = $pdo->query("
    SELECT s.ID_Log, s.Fecha_Hora, s.Accion_Realizada, s.IP_Dispositivo, a.usuario
    FROM Seguridad_Acceso s
    LEFT JOIN Acceso_Movil a ON s.ID_Usuario = a.ID_Usuario
    ORDER BY s.Fecha_Hora DESC
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Seguridad y Accesos - Sistema Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <h2 class="mb-4 text-primary">Registro de Seguridad y Accesos</h2>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle bg-white">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Fecha y Hora</th>
                    <th>Acción Realizada</th>
                    <th>IP / Dispositivo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= (int)$log['ID_Log'] ?></td>
                            <td><?= htmlspecialchars($log['usuario'] ?? 'Desconocido') ?></td>
                            <td><?= htmlspecialchars($log['Fecha_Hora']) ?></td>
                            <td><?= htmlspecialchars($log['Accion_Realizada']) ?></td>
                            <td><?= htmlspecialchars($log['IP_Dispositivo']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que desea eliminar este registro?');">
                                    <input type="hidden" name="delete" value="<?= (int)$log['ID_Log'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay registros de seguridad.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
