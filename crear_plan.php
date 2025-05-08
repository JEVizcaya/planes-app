<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $fecha = $_POST['fecha'];
    $lugar = trim($_POST['lugar']);
    $capacidad = (int) $_POST['capacidad'];

    if (empty($titulo) || empty($descripcion) || empty($fecha) || empty($lugar) || $capacidad <= 0) {
        $errors[] = 'Todos los campos son obligatorios y la capacidad debe ser mayor a 0.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO planes (titulo, descripcion, fecha, lugar, capacidad, creador_id) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$titulo, $descripcion, $fecha, $lugar, $capacidad, $_SESSION['user_id']]);
        header('Location: dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Plan</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/login_register.css"> <!-- Aquí debe estar el archivo CSS que contiene los estilos -->
</head>
<body class="crear-plan-page">

    <main class="form-container">
        <h2 class="text-center mb-4">Crear nuevo plan</h2>

        <div class="text-center mb-4">
            <a href="dashboard.php" class="btn btn-outline-secondary">← Volver al panel</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control form-input" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control form-input" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control form-input" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Lugar</label>
                <input type="text" name="lugar" class="form-control form-input" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Capacidad</label>
                <input type="number" name="capacidad" class="form-control form-input" min="1" required>
            </div>
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Crear plan</button>
            </div>
        </form>
    </main>

</body>
</html>
