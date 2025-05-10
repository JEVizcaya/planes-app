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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap & Estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/crear_plan.css"> <!-- Aquí debe estar el archivo CSS que contiene los estilos -->
</head>
<body class="login-page d-flex align-items-center justify-content-center">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card mt-5 shadow-sm border-0 rounded-4">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Crear un nuevo plan</h2>

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

                            <div class="d-grid gap-2 mt-3">
                                <a href="dashboard.php" class="btn btn-primary btn-lg">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
