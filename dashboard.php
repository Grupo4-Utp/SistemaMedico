<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$nombre_real = $user['usuario'];

try {
    if ($user['tipo'] === 'personal_salud' && !empty($user['id_medico'])) {
        $stmt = $pdo->prepare("SELECT Nombre FROM Medico WHERE ID_Medico = :id_medico");
        $stmt->execute(['id_medico' => $user['id_medico']]);
        $medico = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($medico) {
            $nombre_real = $medico['Nombre'];
        }
    } elseif ($user['tipo'] === 'paciente' && !empty($user['id_paciente'])) {
        $stmt = $pdo->prepare("SELECT Nombre, Apellido FROM Paciente WHERE ID_Paciente = :id_paciente");
        $stmt->execute(['id_paciente' => $user['id_paciente']]);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($paciente) {
            $nombre_real = $paciente['Nombre'] . ' ' . $paciente['Apellido'];
        }
    }
} catch (Exception $e) {
    // En caso de error, mantener el username
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel Principal - Sistema Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  <h1 class="mb-5 text-center fw-bold text-primary">Panel Principal</h1>

  <div class="row g-4 justify-content-center">

    <div class="col-12 col-md-6 col-lg-4">
      <a href="pacientes.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 p-4 text-center">
          <div class="card-icon mb-3"><i class="bi bi-people-fill"></i></div>
          <h5 class="card-title">Gestión de Pacientes</h5>
          <p class="card-text text-muted">Agregar, editar y consultar pacientes.</p>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <a href="medico.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 p-4 text-center">
          <div class="card-icon mb-3"><i class="bi bi-person-badge-fill"></i></div>
          <h5 class="card-title">Gestión de Médicos</h5>
          <p class="card-text text-muted">Administrar información de médicos.</p>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <a href="consultas.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 p-4 text-center">
          <div class="card-icon mb-3"><i class="bi bi-calendar-check-fill"></i></div>
          <h5 class="card-title">Consultas Programadas</h5>
          <p class="card-text text-muted">Ver y gestionar citas médicas.</p>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <a href="procedimiento.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 p-4 text-center">
          <div class="card-icon mb-3"><i class="bi bi-clipboard2-check-fill"></i></div>
          <h5 class="card-title">Procedimientos Médicos</h5>
          <p class="card-text text-muted">Registrar y consultar procedimientos.</p>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <a href="reportes.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 p-4 text-center">
          <div class="card-icon mb-3"><i class="bi bi-bar-chart-fill"></i></div>
          <h5 class="card-title">Reportes</h5>
          <p class="card-text text-muted">Generar y visualizar reportes del sistema.</p>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <a href="programas.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 p-4 text-center">
          <div class="card-icon mb-3"><i class="bi bi-journal-medical"></i></div>
          <h5 class="card-title">Programas Médicos</h5>
          <p class="card-text text-muted">Gestionar programas por especialidad.</p>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <a href="visitas.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 p-4 text-center">
          <div class="card-icon mb-3"><i class="bi bi-journal-check"></i></div>
          <h5 class="card-title">Visitas Médicas</h5>
          <p class="card-text text-muted">Registrar visitas médicas realizadas.</p>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <a href="historial.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 p-4 text-center">
          <div class="card-icon mb-3"><i class="bi bi-file-medical"></i></div>
          <h5 class="card-title">Historial Médico</h5>
          <p class="card-text text-muted">Ver historial clínico de pacientes.</p>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <a href="metas.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 p-4 text-center">
          <div class="card-icon mb-3"><i class="bi bi-flag-fill"></i></div>
          <h5 class="card-title">Metas Anuales</h5>
          <p class="card-text text-muted">Establecer y revisar metas del año.</p>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <a href="notificaciones.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 p-4 text-center">
          <div class="card-icon mb-3"><i class="bi bi-bell-fill"></i></div>
          <h5 class="card-title">Notificaciones</h5>
          <p class="card-text text-muted">Alertas y recordatorios para pacientes.</p>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <a href="usuarios.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 p-4 text-center">
          <div class="card-icon mb-3"><i class="bi bi-person-lines-fill"></i></div>
          <h5 class="card-title">Usuarios</h5>
          <p class="card-text text-muted">Gestionar accesos de pacientes y médicos.</p>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
      <a href="seguridad.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 p-4 text-center">
          <div class="card-icon mb-3"><i class="bi bi-shield-lock-fill"></i></div>
          <h5 class="card-title">Seguridad</h5>
          <p class="card-text text-muted">Auditoría de accesos y seguridad.</p>
        </div>
      </a>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
