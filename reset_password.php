<?php
session_start();
require_once 'includes/db.php';

$error = '';
$mensaje = '';
$mostrar_form = false;

$token = $_GET['token'] ?? '';

if (!$token) {
    $error = "Token inválido.";
} else {
    // Buscar usuario con token válido y no expirado
    $stmt = $pdo->prepare("SELECT id FROM acceso_movil WHERE token_recuperacion = :token AND token_expira > NOW() LIMIT 1");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = "Token inválido o expirado.";
    } else {
        $mostrar_form = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            // Validar robustez
            $errores_pass = [];
            if (strlen($password) < 8) $errores_pass[] = "La contrasena debe tener al menos 8 caracteres.";
            if (!preg_match('/[A-Z]/', $password)) $errores_pass[] = "Debe contener al menos una letra mayúscula.";
            if (!preg_match('/[a-z]/', $password)) $errores_pass[] = "Debe contener al menos una letra minúscula.";
            if (!preg_match('/[0-9]/', $password)) $errores_pass[] = "Debe contener al menos un número.";
            if (!preg_match('/[\W_]/', $password)) $errores_pass[] = "Debe contener al menos un símbolo especial.";

            if ($password !== $password_confirm) {
                $errores_pass[] = "Las contrasenas no coinciden.";
            }

            if (empty($errores_pass)) {
                // Guardar nueva contrasena hasheada y eliminar token
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE acceso_movil SET password = :pass, token_recuperacion = NULL, token_expira = NULL WHERE id = :id");
                $stmt->execute(['pass' => $hash, 'id' => $user['id']]);

                $mensaje = "contrasena actualizada correctamente. <a href='login.php'>Iniciar sesión</a>";
                $mostrar_form = false;
            } else {
                $error = implode('<br>', $errores_pass);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Restablecer contrasena</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script>
    function checkPasswordStrength() {
      var pass = document.getElementById('password').value;
      var strength = "Débil";
      var color = "red";
      var regexes = [
        /.{8,}/,
        /[A-Z]/,
        /[a-z]/,
        /[0-9]/,
        /[\W_]/
      ];
      var passed = regexes.reduce((acc, regex) => acc + regex.test(pass), 0);
      if (passed >= 5) { strength = "Fuerte"; color = "green"; }
      else if (passed >= 3) { strength = "Media"; color = "orange"; }
      document.getElementById('passwordStrength').textContent = "Nivel de contrasena: " + strength;
      document.getElementById('passwordStrength').style.color = color;
    }
  </script>
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow p-4" style="max-width: 420px;">
    <h4 class="mb-3 text-center">Restablecer contrasena</h4>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($mensaje): ?>
      <div class="alert alert-success"><?= $mensaje ?></div>
    <?php endif; ?>

    <?php if ($mostrar_form): ?>
    <form method="post" novalidate>
      <div class="mb-3">
        <label for="password" class="form-label">Nueva contrasena</label>
        <input type="password" class="form-control" id="password" name="password" oninput="checkPasswordStrength()" required>
        <div id="passwordStrength" class="form-text"></div>
      </div>
      <div class="mb-3">
        <label for="password_confirm" class="form-label">Confirmar contrasena</label>
        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Actualizar contrasena</button>
    </form>
    <?php else: ?>
      <a href="login.php" class="btn btn-link w-100 mt-2">Volver al login</a>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
