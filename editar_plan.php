<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$plan_id = $_GET['id'];

// Verificar que el plan pertenece al usuario
$stmt = $pdo->prepare('SELECT * FROM planes WHERE id = ? AND creador_id = ?');
$stmt->execute([$plan_id, $user_id]);
$plan = $stmt->fetch();

if (!$plan) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $fecha = trim($_POST['fecha']);
    $lugar = trim($_POST['lugar']);
    $capacidad = (int) $_POST['capacidad'];

    if (!empty($titulo) && !empty($descripcion) && !empty($fecha) && !empty($lugar) && $capacidad > 0) {
        $stmt = $pdo->prepare('UPDATE planes SET titulo = ?, descripcion = ?, fecha = ?, lugar = ?, capacidad = ? WHERE id = ? AND creador_id = ?');
        $stmt->execute([$titulo, $descripcion, $fecha, $lugar, $capacidad, $plan_id, $user_id]);
        header('Location: dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Plan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Editar Plan</h1>
        <form action="editar_plan.php?id=<?= $plan_id ?>" method="POST">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($plan['titulo']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required><?= htmlspecialchars($plan['descripcion']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" value="<?= htmlspecialchars($plan['fecha']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="lugar" class="form-label">Lugar</label>
                <input type="text" class="form-control" id="lugar" name="lugar" value="<?= htmlspecialchars($plan['lugar']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="capacidad" class="form-label">Capacidad</label>
                <input type="number" class="form-control" id="capacidad" name="capacidad" value="<?= htmlspecialchars($plan['capacidad']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
