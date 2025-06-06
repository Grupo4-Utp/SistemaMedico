<?php
session_start();
require_once 'includes/db.php';

$maxIntentos = 3;
$bloqueoTiempo = 50;

if (!isset($_SESSION['intentos'])) {
    $_SESSION['intentos'] = 0;
}
if (!isset($_SESSION['bloqueo_hasta'])) {
    $_SESSION['bloqueo_hasta'] = 0;
}

$error = '';
$bloqueado = false;
$segundosRestantes = 0;

if ($_SESSION['bloqueo_hasta'] > time()) {
    $bloqueado = true;
    $segundosRestantes = $_SESSION['bloqueo_hasta'] - time();
    $error = "Demasiados intentos. Espera para intentar de nuevo.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$bloqueado) {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['contrasena'] ?? '';
    $captcha = $_POST['captcha'] ?? '';
    $recordar = isset($_POST['recordar']);

    if (!isset($_SESSION['captcha_text']) || strtolower($captcha) !== strtolower($_SESSION['captcha_text'])) {
        $error = "Captcha incorrecto.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Acceso_Movil WHERE usuario = :usuario LIMIT 1");
        $stmt->execute(['usuario' => $usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['contrasena'])) {
            $_SESSION['user'] = [
                'id' => $user['ID_Usuario'],
                'usuario' => $user['usuario'],
                'tipo' => $user['tipo_usuario'],
                'id_paciente' => $user['ID_Paciente'],
                'id_medico' => $user['ID_Medico']
            ];
            $_SESSION['intentos'] = 0;
            $_SESSION['bloqueo_hasta'] = 0;

            if ($recordar) {
                setcookie('usuario_recordado', $usuario, time() + (86400 * 30), "/");
            } else {
                setcookie('usuario_recordado', '', time() - 3600, "/");
            }

            if ($user['tipo_usuario'] === 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = "Credenciales incorrectas.";
            $_SESSION['intentos']++;
            if ($_SESSION['intentos'] >= $maxIntentos) {
                $_SESSION['bloqueo_hasta'] = time() + $bloqueoTiempo;
                $bloqueado = true;
                $segundosRestantes = $bloqueoTiempo;
                $error = "Demasiados intentos. Espera para intentar de nuevo.";
            }
        }
    }
}

$captcha_text = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 6);
$_SESSION['captcha_text'] = $captcha_text;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Iniciar Sesión - Sistema Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      position: relative;
      padding-top: 60px;
      background-color: #f8f9fa;
    }
    .logo-img {
      display: block;
      margin: 0 auto 20px auto;
      max-width: 120px;
      height: auto;
    }
    #contadorBloqueo {
      position: fixed;
      top: 10px;
      left: 10px;
      z-index: 1050;
      font-size: 1rem;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      display: none;
    }
    #captcha-img {
      border: 1px solid #ccc;
      height: 60px !important;
      width: 150px !important;
      image-rendering: pixelated;
    }
  </style>
</head>
<body>

<?php if ($bloqueado): ?>
  <div id="contadorBloqueo" class="badge bg-danger">
    ⏱️ Espera <span id="tiempoBloqueo"></span>
  </div>
<?php endif; ?>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow w-100" style="max-width: 420px;">
    <div class="card-body">
      <h4 class="card-title text-center mb-3">🔐 Iniciar Sesión</h4>
      <img src="img/logo.png" alt="Logo del hospital" class="logo-img">

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="mb-3">
          <label for="usuario" class="form-label">Usuario</label>
          <input type="text" class="form-control form-control-sm" id="usuario" name="usuario"
            placeholder="Usuario" required
            value="<?= htmlspecialchars($_COOKIE['usuario_recordado'] ?? '') ?>"
            <?= $bloqueado ? 'disabled' : '' ?>>
        </div>
        <div class="mb-3">
          <label for="contrasena" class="form-label">contrasena</label>
          <input type="password" class="form-control form-control-sm" id="contrasena" name="contrasena"
            placeholder="contrasena" required <?= $bloqueado ? 'disabled' : '' ?>>
        </div>
        <div class="mb-3">
          <label for="captcha" class="form-label">Escribe el texto que ves:</label>
          <div class="d-flex align-items-center gap-2 mb-2">
            <img src="captcha.php?<?= time() ?>" alt="Captcha" id="captcha-img">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refrescarCaptcha()" <?= $bloqueado ? 'disabled' : '' ?>>🔄</button>
          </div>
          <input type="text" class="form-control form-control-sm" id="captcha" name="captcha" placeholder="Captcha" required <?= $bloqueado ? 'disabled' : '' ?>>
        </div>
        <div class="form-check mb-3">
          <input type="checkbox" class="form-check-input" id="recordar" name="recordar" <?= $bloqueado ? 'disabled' : '' ?>
            <?= isset($_COOKIE['usuario_recordado']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="recordar">Recordar usuario</label>
        </div>
        <button type="submit" class="btn btn-primary w-100" <?= $bloqueado ? 'disabled' : '' ?>>Ingresar</button>
      </form>

      <div class="mt-3 text-center">
        <a href="recuperar_contrasena.php">¿Olvidaste tu contrasena?</a>
      </div>

      <div class="d-flex justify-content-end mt-2">
        <div style="width: 150px;">
          <label for="idioma" class="form-label mb-1">🌐 Idioma</label>
          <select id="idioma" class="form-select form-select-sm">
            <option value="es">Español</option>
            <option value="en">English</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function refrescarCaptcha() {
    const captchaImg = document.getElementById('captcha-img');
    captchaImg.src = 'captcha.php?' + Date.now();
  }

  <?php if ($bloqueado): ?>
    const tiempoRestante = <?= $segundosRestantes ?>;
    const contador = document.getElementById('contadorBloqueo');
    const tiempoSpan = document.getElementById('tiempoBloqueo');
    contador.style.display = 'inline-block';

    let segundos = tiempoRestante;
    function actualizarContador() {
      const minutos = Math.floor(segundos / 60);
      const segundosRest = segundos % 60;
      tiempoSpan.textContent = `${minutos}:${segundosRest.toString().padStart(2, '0')}`;
      if (segundos > 0) {
        segundos--;
        setTimeout(actualizarContador, 1000);
      } else {
        location.reload();
      }
    }
    actualizarContador();
  <?php endif; ?>
</script>

</body>
</html>
