<?php
session_start();
require_once 'includes/auth.php'; // archivo para verificar sesión activa y permisos
require_once 'includes/db.php';

$errors = [];
$success = '';

// Obtener listas para selects
$pacientes = $pdo->query("SELECT ID_Paciente, nombre FROM Paciente ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$medicos = $pdo->query("SELECT ID_Medico, nombre FROM Medico ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario agregar o editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $usuario = trim($_POST['usuario'] ?? '');
    $tipo_usuario = trim($_POST['tipo_usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $id_paciente = $_POST['id_paciente'] ?? null;
    $id_medico = $_POST['id_medico'] ?? null;

    if ($usuario === '' || $tipo_usuario === '') {
        $errors[] = "Usuario y Tipo de usuario son obligatorios.";
    }

    // Validar la restricción CHECK
    if ($tipo_usuario === 'paciente') {
        if (empty($id_paciente)) {
            $errors[] = "Debe seleccionar un paciente.";
        }
        $id_medico = null;
    } elseif ($tipo_usuario === 'personal_salud') {
        if (empty($id_medico)) {
            $errors[] = "Debe seleccionar un médico.";
        }
        $id_paciente = null;
    } else {
        $id_paciente = null;
        $id_medico = null;
    }

    if (empty($errors)) {
        try {
            if ($id) {
                // Actualizar
                $stmt = $pdo->prepare("UPDATE Acceso_Movil 
                    SET usuario = :usuario, tipo_usuario = :tipo_usuario, email = :email,
                        ID_Paciente = :id_paciente, ID_Medico = :id_medico
                    WHERE ID_Usuario = :id");
                $stmt->execute([
                    ':usuario' => $usuario,
                    ':tipo_usuario' => $tipo_usuario,
                    ':email' => $email,
                    ':id_paciente' => $id_paciente ?: null,
                    ':id_medico' => $id_medico ?: null,
                    ':id' => $id,
                ]);
                $success = "Usuario actualizado correctamente.";
            } else {
                // Insertar nuevo usuario (contrasena por defecto '123456' hashed)
                $passHash = password_hash('123456', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO Acceso_Movil 
                    (usuario, contrasena, tipo_usuario, email, ID_Paciente, ID_Medico) 
                    VALUES (:usuario, :contrasena, :tipo_usuario, :email, :id_paciente, :id_medico)");
                $stmt->execute([
                    ':usuario' => $usuario,
                    ':contrasena' => $passHash,
                    ':tipo_usuario' => $tipo_usuario,
                    ':email' => $email,
                    ':id_paciente' => $id_paciente ?: null,
                    ':id_medico' => $id_medico ?: null,
                ]);
                $success = "Usuario agregado correctamente. La contrasena inicial es '123456'.";
            }
        } catch (Exception $e) {
            $errors[] = "Error al guardar en la base de datos: " . $e->getMessage();
        }
    }
}

// Eliminar usuario
if (isset($_GET['delete'])) {
    $idToDelete = (int) $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM Acceso_Movil WHERE ID_Usuario = :id");
        $stmt->execute([':id' => $idToDelete]);
        header("Location: usuarios.php?deleted=1");
        exit;
    } catch (Exception $e) {
        $errors[] = "Error al eliminar usuario: " . $e->getMessage();
    }
}

// Obtener lista de usuarios
$stmt = $pdo->query("SELECT ID_Usuario, usuario, tipo_usuario, email FROM Acceso_Movil ORDER BY ID_Usuario DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Para edición
$editUser = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM Acceso_Movil WHERE ID_Usuario = :id LIMIT 1");
    $stmt->execute([':id' => $editId]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestión de Usuarios - Sistema Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  <h1 class="mb-4">Gestión de Usuarios</h1>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card mb-5">
    <div class="card-header"><?= $editUser ? 'Editar Usuario' : 'Agregar Usuario' ?></div>
    <div class="card-body">
      <form method="POST" action="usuarios.php">
        <?php if ($editUser): ?>
          <input type="hidden" name="id" value="<?= htmlspecialchars($editUser['ID_Usuario']) ?>" />
        <?php endif; ?>

        <div class="mb-3">
          <label for="usuario" class="form-label">Usuario *</label>
          <input type="text" id="usuario" name="usuario" class="form-control" required
            value="<?= htmlspecialchars($editUser['usuario'] ?? '') ?>" />
        </div>

        <div class="mb-3">
          <label for="tipo_usuario" class="form-label">Tipo de Usuario *</label>
          <select id="tipo_usuario" name="tipo_usuario" class="form-select" required onchange="toggleRelacionados()">
            <option value="">-- Seleccione --</option>
            <option value="personal_salud" <?= (isset($editUser['tipo_usuario']) && $editUser['tipo_usuario'] === 'personal_salud') ? 'selected' : '' ?>>Personal Salud</option>
            <option value="paciente" <?= (isset($editUser['tipo_usuario']) && $editUser['tipo_usuario'] === 'paciente') ? 'selected' : '' ?>>Paciente</option>
            <option value="administrador" <?= (isset($editUser['tipo_usuario']) && $editUser['tipo_usuario'] === 'administrador') ? 'selected' : '' ?>>Administrador</option>
          </select>
        </div>

        <div class="mb-3" id="paciente-select" style="display: none;">
          <label for="id_paciente" class="form-label">Paciente</label>
          <select id="id_paciente" name="id_paciente" class="form-select">
            <option value="">-- Seleccione Paciente --</option>
            <?php foreach ($pacientes as $p): ?>
              <option value="<?= $p['ID_Paciente'] ?>" <?= (isset($editUser['ID_Paciente']) && $editUser['ID_Paciente'] == $p['ID_Paciente']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3" id="medico-select" style="display: none;">
          <label for="id_medico" class="form-label">Médico</label>
          <select id="id_medico" name="id_medico" class="form-select">
            <option value="">-- Seleccione Médico --</option>
            <?php foreach ($medicos as $m): ?>
              <option value="<?= $m['ID_Medico'] ?>" <?= (isset($editUser['ID_Medico']) && $editUser['ID_Medico'] == $m['ID_Medico']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Correo Electrónico</label>
          <input type="email" id="email" name="email" class="form-control"
            value="<?= htmlspecialchars($editUser['email'] ?? '') ?>" />
        </div>

        <button type="submit" class="btn btn-primary"><?= $editUser ? 'Actualizar' : 'Agregar' ?></button>
        <?php if ($editUser): ?>
          <a href="usuarios.php" class="btn btn-secondary ms-2">Cancelar</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <h2>Listado de Usuarios</h2>

  <table class="table table-striped table-bordered align-middle">
    <thead class="table-primary">
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Tipo Usuario</th>
        <th>Email</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($usuarios as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['ID_Usuario']) ?></td>
        <td><?= htmlspecialchars($u['usuario']) ?></td>
        <td><?= htmlspecialchars($u['tipo_usuario']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td>
          <a href="usuarios.php?edit=<?= htmlspecialchars($u['ID_Usuario']) ?>" class="btn btn-sm btn-warning">Editar</a>
          <a href="usuarios.php?delete=<?= htmlspecialchars($u['ID_Usuario']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que desea eliminar este usuario?');">Eliminar</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (count($usuarios) === 0): ?>
      <tr>
        <td colspan="5" class="text-center">No hay usuarios registrados.</td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
function toggleRelacionados() {
  const tipo = document.getElementById('tipo_usuario').value;
  document.getElementById('paciente-select').style.display = (tipo === 'paciente') ? 'block' : 'none';
  document.getElementById('medico-select').style.display = (tipo === 'personal_salud') ? 'block' : 'none';
}

// Ejecutar al cargar para edición
document.addEventListener('DOMContentLoaded', toggleRelacionados);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
