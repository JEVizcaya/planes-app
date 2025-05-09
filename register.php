<?php
session_start();
require 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    // Validaciones
    if (empty($nombre)) {
        $errors[] = 'El nombre es obligatorio.';
    }
    if (empty($apellidos)) {
        $errors[] = 'Los apellidos son obligatorios.';
    }
    if (!$email) {
        $errors[] = 'Introduce un email válido.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    if ($password !== $password2) {
        $errors[] = 'Las contraseñas no coinciden.';
    }

    // Si no hay errores, registrar
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Este email ya está registrado.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, apellidos, email, password) VALUES (?, ?, ?, ?)');
            if ($stmt->execute([$nombre, $apellidos, $email, $hash])) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $errors[] = 'Error al registrar. Intenta de nuevo.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - ¿Quedamos?</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/login_register.css">
</head>
<body class="login-page d-flex align-items-center justify-content-center">

<div class="form-container bg-white p-5 rounded shadow">
    <h2 class="text-center text-primary mb-4"><i class="fas fa-user-plus me-2"></i>Registro de Usuario</h2>

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
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="apellidos" class="form-label">Apellidos</label>
            <input type="text" name="apellidos" id="apellidos" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password2" class="form-label">Repite contraseña</label>
            <input type="password" name="password2" id="password2" class="form-control" required>
        </div>

        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-user-plus me-1"></i> Registrar</button>
            <a href="login.php" class="btn btn-outline-secondary btn-lg">¿Ya tienes cuenta? Inicia sesión</a>
            <a href="index.php" class="btn btn-link text-decoration-none"><i class="fas fa-home me-1"></i> Volver al inicio</a>
        </div>
    </form>
</div>

</body>
</html>
