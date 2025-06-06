<?php
session_start();
require_once 'includes/db.php';

$error = '';
$mensaje = '';
$mostrar_form_cambio = false;
$usuario_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['usuario']) && !isset($_POST['password']) && !isset($_POST['password_confirm'])) {
        $usuario_input = trim($_POST['usuario']);
        if (empty($usuario_input)) {
            $error = "Por favor ingresa tu usuario.";
        } else {
            $stmt = $pdo->prepare("SELECT ID_Usuario FROM Acceso_Movil WHERE usuario = :usuario LIMIT 1");
            $stmt->execute(['usuario' => $usuario_input]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $mostrar_form_cambio = true;
            } else {
                $error = "Usuario no encontrado.";
            }
        }
    }
    elseif (isset($_POST['password'], $_POST['password_confirm'], $_POST['usuario_hidden'])) {
        $usuario_input = trim($_POST['usuario_hidden']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];

        $errores = [];
        if (strlen($password) < 8) $errores[] = "La contrasena debe tener al menos 8 caracteres.";
        if (!preg_match('/[A-Z]/', $password)) $errores[] = "Debe contener al menos una letra mayúscula.";
        if (!preg_match('/[a-z]/', $password)) $errores[] = "Debe contener al menos una letra minúscula.";
        if (!preg_match('/[0-9]/', $password)) $errores[] = "Debe contener al menos un número.";
        if (!preg_match('/[\W_]/', $password)) $errores[] = "Debe contener al menos un símbolo especial.";
        if ($password !== $password_confirm) $errores[] = "Las contrasenas no coinciden.";

        if (empty($errores)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE Acceso_Movil SET contrasena = :pass WHERE usuario = :usuario");
            $update->execute(['pass' => $hash, 'usuario' => $usuario_input]);
            $mensaje = "contrasena actualizada correctamente. <a href='login.php'>Iniciar sesión</a>";
        } else {
            $error = implode('<br>', $errores);
            $mostrar_form_cambio = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar y Cambiar contrasena</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .input-group .form-control:focus {
      z-index: 2;
    }
    .password-strength-text {
      font-weight: 500;
      font-size: 0.875rem;
      margin-top: 5px;
    }
    .progress {
      height: 5px;
    }
  </style>
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow p-4" style="max-width: 420px;">
    <h4 class="mb-3 text-center">Recuperar y Cambiar contrasena</h4>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($mensaje): ?>
      <div class="alert alert-success"><?= $mensaje ?></div>
    <?php endif; ?>

    <?php if (!$mostrar_form_cambio): ?>
      <!-- Formulario: Usuario -->
      <form method="post" novalidate>
        <div class="mb-3">
          <label for="usuario" class="form-label">Usuario</label>
          <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Tu usuario" required value="<?= htmlspecialchars($usuario_input) ?>">
        </div>
        <button type="submit" class="btn btn-primary w-100">Continuar</button>
        <a href="login.php" class="btn btn-link w-100 mt-2">Volver al login</a>
      </form>
    <?php else: ?>
      <!-- Formulario: Nueva contrasena -->
      <form method="post" novalidate>
        <input type="hidden" name="usuario_hidden" value="<?= htmlspecialchars($usuario_input) ?>">

        <div class="mb-3">
          <label for="password" class="form-label">Nueva contrasena</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" required>
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">👁️</button>
          </div>
          <div class="progress mt-2">
            <div id="password-progress" class="progress-bar" role="progressbar" style="width: 0%"></div>
          </div>
          <div id="password-strength-text" class="password-strength-text"></div>
          <div class="form-text">
            Utilizar una combinación de letras mayúsculas y minúsculas, números y símbolos, con una longitud mínima de 8 caracteres.
          </div>
        </div>

        <div class="mb-3">
          <label for="password_confirm" class="form-label">Confirmar contrasena</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirm')">👁️</button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Actualizar contrasena</button>
        <a href="login.php" class="btn btn-link w-100 mt-2">Volver al login</a>
      </form>
    <?php endif; ?>
  </div>
</div>

<script>
  function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
  }

  const passwordInput = document.getElementById('password');
  const progress = document.getElementById('password-progress');
  const strengthText = document.getElementById('password-strength-text');

  passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    let strength = 0;

    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[a-z]/.test(val)) strength++;
    if (/\d/.test(val)) strength++;
    if (/[\W_]/.test(val)) strength++;

    let width = 0;
    let text = '';
    let color = '';

    if (strength <= 2) {
      width = 33;
      text = 'contrasena débil';
      color = 'bg-danger';
    } else if (strength <= 4) {
      width = 66;
      text = 'contrasena media';
      color = 'bg-warning';
    } else {
      width = 100;
      text = 'contrasena fuerte';
      color = 'bg-success';
    }

    progress.style.width = width + '%';
    progress.className = 'progress-bar ' + color;
    strengthText.textContent = text;
  });
</script>
</body>
</html>
