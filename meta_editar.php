<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: meta.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $programa = $_POST['ID_Programa'];
    $anio = $_POST['Año'];
    $tipo = $_POST['Tipo_Procedimiento'];
    $cantidad = $_POST['Cantidad_Esperada'];

    $stmt = $pdo->prepare("UPDATE Meta_Anual SET ID_Programa=?, Año=?, Tipo_Procedimiento=?, Cantidad_Esperada=? WHERE ID_Meta=?");
    $stmt->execute([$programa, $anio, $tipo, $cantidad, $id]);

    header("Location: meta.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Meta_Anual WHERE ID_Meta = ?");
$stmt->execute([$id]);
$meta = $stmt->fetch();

$programas = $pdo->query("SELECT ID_Programa, Nombre_Programa FROM Programa_Medico")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
<div class="container mt-4">
  <h2 class="mb-4">Editar Meta Anual</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-4">
      <label for="programa" class="form-label">Programa Médico</label>
      <select name="ID_Programa" class="form-select" required>
        <?php foreach ($programas as $prog): ?>
          <option value="<?= $prog['ID_Programa'] ?>" <?= $prog['ID_Programa'] == $meta['ID_Programa'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($prog['Nombre_Programa']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Año</label>
      <input type="number" name="Año" class="form-control" required value="<?= $meta['Año'] ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Tipo Procedimiento</label>
      <input type="text" name="Tipo_Procedimiento" class="form-control" required value="<?= htmlspecialchars($meta['Tipo_Procedimiento']) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Cantidad Esperada</label>
      <input type="number" name="Cantidad_Esperada" class="form-control" required value="<?= $meta['Cantidad_Esperada'] ?>">
    </div>
    <div class="col-12 text-end">
      <button type="submit" class="btn btn-primary">Actualizar</button>
      <a href="meta.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
