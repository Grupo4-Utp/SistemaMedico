<?php
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

$user = $_SESSION['user'] ?? null;
$nombre_real = $user['usuario'] ?? 'Usuario';
$saludo = 'Bienvenido';

try {
    if ($user && $user['tipo'] === 'personal_salud' && !empty($user['id_medico'])) {
        $stmt = $pdo->prepare("SELECT Nombre FROM Medico WHERE ID_Medico = :id_medico");
        $stmt->execute(['id_medico' => $user['id_medico']]);
        $medico = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($medico) {
            $primer_nombre = explode(' ', trim($medico['Nombre']))[0];
            $primer_apellido = explode(' ', trim($medico['Apellido']))[0];
            $nombre_real = $primer_nombre . ' ' . $primer_apellido;
            if (strtolower($medico['Genero']) === 'femenino') {
                $saludo = 'Bienvenida';
            }
        }
    } elseif ($user && $user['tipo'] === 'paciente' && !empty($user['id_paciente'])) {
        $stmt = $pdo->prepare("SELECT Nombre FROM Paciente WHERE ID_Paciente = :id_paciente");
        $stmt->execute(['id_paciente' => $user['id_paciente']]);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($paciente) {
            $primer_nombre = explode(' ', trim($paciente['Nombre']))[0];
            $primer_apellido = explode(' ', trim($paciente['Apellido']))[0];
            $nombre_real = $primer_nombre . ' ' . $primer_apellido;
            if (strtolower($paciente['Genero']) === 'femenino') {
                $saludo = 'Bienvenida';
            }
        }
    }
} catch (Exception $e) {
    // Mantener nombre de usuario si falla
}
?>

<style>
    body {
      background-color: #f8f9fa;
    }
    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
    }
    .welcome-text {
      font-size: 1.1rem;
      color: #fff;
    }
    .card-icon {
      font-size: 3.5rem;
      color: #0d6efd;
    }
    .card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border-radius: 1rem;
    }
    .card:hover {
      box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
      transform: translateY(-8px);
      text-decoration: none;
    }
    .card-title {
      font-weight: 600;
      font-size: 1.3rem;
    }
    .card-text {
      font-size: 0.95rem;
    }
    @media (min-width: 768px) {
      .col-md-6.col-lg-4 {
        flex: 0 0 48%;
        max-width: 48%;
      }
    }
    @media (min-width: 992px) {
      .col-md-6.col-lg-4 {
        flex: 0 0 30%;
        max-width: 30%;
      }
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Sistema Médico</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item me-3">
          <span class="text-white">
            <?= htmlspecialchars($saludo) ?>, <strong><?= htmlspecialchars($nombre_real) ?></strong>
          </span>
        </li>
        <li class="nav-item">
          <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
