<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
include 'includes/db.php';
include 'includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("INSERT INTO Medico (Nombre, Especialidad, Telefono, Correo_Electronico, Disponibilidad) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nombre'],
        $_POST['especialidad'],
        $_POST['telefono'],
        $_POST['correo'],
        $_POST['disponibilidad']
    ]);
    header("Location: medico.php");
    exit;
}
?>

<h2>Agregar Médico</h2>
<form method="post">
    <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
    <div class="mb-3"><label>Especialidad</label><input type="text" name="especialidad" class="form-control" required></div>
    <div class="mb-3"><label>Teléfono</label><input type="text" name="telefono" class="form-control"></div>
    <div class="mb-3"><label>Correo Electrónico</label><input type="email" name="correo" class="form-control"></div>
    <div class="mb-3"><label>Disponibilidad</label><input type="text" name="disponibilidad" class="form-control"></div>
    <button type="submit" class="btn btn-success">Guardar</button>
    <a href="medico.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php include 'includes/footer.php'; ?>
