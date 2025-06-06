<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'] ?? null;

header('Content-Type: text/html; charset=utf-8');

// Procesar AJAX para crear/actualizar paciente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] == '1') {
    $id_paciente = $_POST['id_paciente'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $edad = (int)($_POST['edad'] ?? 0);
    $genero = $_POST['genero'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    $response = ['success' => false, 'message' => ''];

    if (!$nombre || !$apellido || !$edad || !$genero) {
        $response['message'] = 'Por favor completa todos los campos obligatorios.';
        echo json_encode($response);
        exit;
    }

    try {
        if ($id_paciente) {
            $sql = "UPDATE Paciente SET Nombre = :nombre, Apellido = :apellido, Edad = :edad, Genero = :genero, Telefono = :telefono, Correo_Electronico = :correo WHERE ID_Paciente = :id";
            $stmt = $pdo->prepare($sql);
            $resultado = $stmt->execute([
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':edad' => $edad,
                ':genero' => $genero,
                ':telefono' => $telefono,
                ':correo' => $correo,
                ':id' => $id_paciente,
            ]);
            $response['message'] = $resultado ? "Paciente actualizado correctamente." : "Error al actualizar paciente.";
            $response['success'] = $resultado;
        } else {
            $sql = "INSERT INTO Paciente (Nombre, Apellido, Edad, Genero, Telefono, Correo_Electronico, Fecha_Registro) VALUES (:nombre, :apellido, :edad, :genero, :telefono, :correo, NOW())";
            $stmt = $pdo->prepare($sql);
            $resultado = $stmt->execute([
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':edad' => $edad,
                ':genero' => $genero,
                ':telefono' => $telefono,
                ':correo' => $correo,
            ]);
            $response['message'] = $resultado ? "Paciente creado correctamente." : "Error al crear paciente.";
            $response['success'] = $resultado;
        }
    } catch (Exception $e) {
        $response['message'] = "Error en la base de datos: " . $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

// Eliminar paciente (puedes dejarlo igual o implementarlo con AJAX también)
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM Paciente WHERE ID_Paciente = :id");
    if ($stmt->execute([':id' => $id_eliminar])) {
        $mensaje = "Paciente eliminado correctamente.";
    } else {
        $error = "Error al eliminar paciente.";
    }
}

// Obtener lista completa de pacientes para mostrar
$stmt = $pdo->query("SELECT * FROM Paciente ORDER BY ID_Paciente DESC");
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Pacientes - Sistema Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">

  <h3>Gestión de Pacientes</h3>

  <?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Botón para abrir modal -->
  <button class="btn btn-primary mb-3" id="btnAgregarPaciente">Agregar Paciente</button>

  <!-- Tabla de pacientes -->
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle" id="tablaPacientes">
      <thead class="table-primary">
        <tr>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Edad</th>
          <th>Género</th>
          <th>Teléfono</th>
          <th>Correo</th>
          <th>Fecha Registro</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($pacientes): ?>
          <?php foreach ($pacientes as $p): ?>
            <tr data-id="<?= (int)$p['ID_Paciente'] ?>">
              <td><?= htmlspecialchars($p['Nombre']) ?></td>
              <td><?= htmlspecialchars($p['Apellido']) ?></td>
              <td><?= (int)$p['Edad'] ?></td>
              <td><?= htmlspecialchars($p['Genero']) ?></td>
              <td><?= htmlspecialchars($p['Telefono']) ?></td>
              <td><?= htmlspecialchars($p['Correo_Electronico']) ?></td>
              <td><?= htmlspecialchars($p['Fecha_Registro']) ?></td>
              <td>
                <button class="btn btn-sm btn-warning btnEditar" data-id="<?= (int)$p['ID_Paciente'] ?>">Editar</button>
                <a href="pacientes.php?eliminar=<?= (int)$p['ID_Paciente'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este paciente?');" class="btn btn-sm btn-danger">Eliminar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center">No hay pacientes registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal para agregar/editar paciente -->
<div class="modal fade" id="modalPaciente" tabindex="-1" aria-labelledby="modalPacienteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formPaciente" class="modal-content needs-validation" novalidate>
      <div class="modal-header">
        <h5 class="modal-title" id="modalPacienteLabel">Agregar Paciente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_paciente" id="id_paciente" value="">
        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre *</label>
          <input type="text" id="nombre" name="nombre" class="form-control" required>
          <div class="invalid-feedback">Por favor ingresa el nombre.</div>
        </div>
        <div class="mb-3">
          <label for="apellido" class="form-label">Apellido *</label>
          <input type="text" id="apellido" name="apellido" class="form-control" required>
          <div class="invalid-feedback">Por favor ingresa el apellido.</div>
        </div>
        <div class="mb-3">
          <label for="edad" class="form-label">Edad *</label>
          <input type="number" id="edad" name="edad" min="0" max="130" class="form-control" required>
          <div class="invalid-feedback">Por favor ingresa una edad válida.</div>
        </div>
        <div class="mb-3">
          <label for="genero" class="form-label">Género *</label>
          <select id="genero" name="genero" class="form-select" required>
            <option value="" disabled selected>Seleccione</option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
            <option value="Otro">Otro</option>
          </select>
          <div class="invalid-feedback">Por favor selecciona un género.</div>
        </div>
        <div class="mb-3">
          <label for="telefono" class="form-label">Teléfono</label>
          <input type="text" id="telefono" name="telefono" class="form-control">
        </div>
        <div class="mb-3">
          <label for="correo" class="form-label">Correo Electrónico</label>
          <input type="email" id="correo" name="correo" class="form-control">
        </div>
        <div id="mensajeError" class="alert alert-danger d-none"></div>
        <div id="mensajeExito" class="alert alert-success d-none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <p id="textoConfirmacion">Paciente registrado correctamente.</p>
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const modalPaciente = new bootstrap.Modal(document.getElementById('modalPaciente'));
  const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
  const formPaciente = document.getElementById('formPaciente');
  const mensajeError = document.getElementById('mensajeError');
  const mensajeExito = document.getElementById('mensajeExito');
  const tablaPacientes = document.getElementById('tablaPacientes').getElementsByTagName('tbody')[0];

  // Abrir modal para agregar paciente
  document.getElementById('btnAgregarPaciente').addEventListener('click', () => {
    formPaciente.reset();
    formPaciente.classList.remove('was-validated');
    mensajeError.classList.add('d-none');
    mensajeExito.classList.add('d-none');
    document.getElementById('modalPacienteLabel').textContent = 'Agregar Paciente';
    document.getElementById('id_paciente').value = '';
    modalPaciente.show();
  });

  // Abrir modal para editar paciente
  document.querySelectorAll('.btnEditar').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const id = e.target.getAttribute('data-id');
      try {
        const res = await fetch(`pacientes.php?ajax=1&id=${id}`);
        const data = await res.json();
        if (data.success) {
          const p = data.paciente;
          document.getElementById('modalPacienteLabel').textContent = 'Editar Paciente';
          document.getElementById('id_paciente').value = p.ID_Paciente;
          document.getElementById('nombre').value = p.Nombre;
          document.getElementById('apellido').value = p.Apellido;
          document.getElementById('edad').value = p.Edad;
          document.getElementById('genero').value = p.Genero;
          document.getElementById('telefono').value = p.Telefono;
          document.getElementById('correo').value = p.Correo_Electronico;
          mensajeError.classList.add('d-none');
          mensajeExito.classList.add('d-none');
          formPaciente.classList.remove('was-validated');
          modalPaciente.show();
        } else {
          alert('Error al obtener datos del paciente.');
        }
      } catch (error) {
        alert('Error en la solicitud.');
      }
    });
  });

  // Validación y envío del formulario con AJAX
  formPaciente.addEventListener('submit', async (e) => {
    e.preventDefault();
    formPaciente.classList.add('was-validated');
    mensajeError.classList.add('d-none');
    mensajeExito.classList.add('d-none');

    if (!formPaciente.checkValidity()) {
      return;
    }

    const formData = new FormData(formPaciente);
    formData.append('ajax', '1');

    try {
      const res = await fetch('pacientes.php', {
        method: 'POST',
        body: formData,
      });
      const data = await res.json();

      if (data.success) {
        mensajeExito.textContent = data.message;
        mensajeExito.classList.remove('d-none');
        mensajeError.classList.add('d-none');

        // Actualizar tabla (opcional: recargar lista o agregar fila)
        await actualizarTabla();

        // Mostrar modal confirmación y cerrar modal formulario
        modalPaciente.hide();
        modalConfirmacion.show();
      } else {
        mensajeError.textContent = data.message;
        mensajeError.classList.remove('d-none');
        mensajeExito.classList.add('d-none');
      }
    } catch (error) {
      mensajeError.textContent = 'Error en la solicitud.';
      mensajeError.classList.remove('d-none');
      mensajeExito.classList.add('d-none');
    }
  });

  // Función para actualizar tabla de pacientes sin recargar
  async function actualizarTabla() {
    try {
      const res = await fetch('pacientes.php?ajax=2');
      const data = await res.json();
      if (data.success) {
        tablaPacientes.innerHTML = '';
        if (data.pacientes.length === 0) {
          tablaPacientes.innerHTML = `<tr><td colspan="8" class="text-center">No hay pacientes registrados.</td></tr>`;
          return;
        }
        data.pacientes.forEach(p => {
          const tr = document.createElement('tr');
          tr.setAttribute('data-id', p.ID_Paciente);
          tr.innerHTML = `
            <td>${escapeHtml(p.Nombre)}</td>
            <td>${escapeHtml(p.Apellido)}</td>
            <td>${p.Edad}</td>
            <td>${escapeHtml(p.Genero)}</td>
            <td>${escapeHtml(p.Telefono)}</td>
            <td>${escapeHtml(p.Correo_Electronico)}</td>
            <td>${escapeHtml(p.Fecha_Registro)}</td>
            <td>
              <button class="btn btn-sm btn-warning btnEditar" data-id="${p.ID_Paciente}">Editar</button>
              <a href="pacientes.php?eliminar=${p.ID_Paciente}" onclick="return confirm('¿Seguro que deseas eliminar este paciente?');" class="btn btn-sm btn-danger">Eliminar</a>
            </td>
          `;
          tablaPacientes.appendChild(tr);
        });
        // Reasignar eventos a botones editar nuevos
        asignarEventosEditar();
      }
    } catch (error) {
      console.error('Error al actualizar tabla:', error);
    }
  }

  // Escapar HTML para evitar XSS
  function escapeHtml(text) {
    return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // Asignar eventos click a botones editar
  function asignarEventosEditar() {
    document.querySelectorAll('.btnEditar').forEach(btn => {
      btn.removeEventListener('click', editarPacienteHandler);
      btn.addEventListener('click', editarPacienteHandler);
    });
  }

  // Handler para editar paciente
  async function editarPacienteHandler(e) {
    const id = e.target.getAttribute('data-id');
    try {
      const res = await fetch(`pacientes.php?ajax=1&id=${id}`);
      const data = await res.json();
      if (data.success) {
        const p = data.paciente;
        document.getElementById('modalPacienteLabel').textContent = 'Editar Paciente';
        document.getElementById('id_paciente').value = p.ID_Paciente;
        document.getElementById('nombre').value = p.Nombre;
        document.getElementById('apellido').value = p.Apellido;
        document.getElementById('edad').value = p.Edad;
        document.getElementById('genero').value = p.Genero;
        document.getElementById('telefono').value = p.Telefono;
        document.getElementById('correo').value = p.Correo_Electronico;
        mensajeError.classList.add('d-none');
        mensajeExito.classList.add('d-none');
        formPaciente.classList.remove('was-validated');
        modalPaciente.show();
      } else {
        alert('Error al obtener datos del paciente.');
      }
    } catch (error) {
      alert('Error en la solicitud.');
    }
  }

  // Inicializar eventos editar
  asignarEventosEditar();

</script>

</body>
</html>
