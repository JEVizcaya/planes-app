<?php
// register.php
session_start();
require 'includes/db.php';

// Manejo del formulario
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    // Validaciones
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
        // Verificar email único
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Este email ya está registrado.';
        } else {
            // Hashear contraseña
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO usuarios (email, password) VALUES (?, ?)');
            if ($stmt->execute([$email, $hash])) {
                // Redirigir a login
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
    <title>Registro</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/login_register.css">
</head>
<body class="register-page">

    <form method="POST" class="form-container">
        <h2 class="text-center mb-4 text-primary">Registro de usuario</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" name="email" id="email" class="form-control form-input" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control form-input" required>
        </div>

        <div class="mb-3">
            <label for="password2" class="form-label">Repite Contraseña</label>
            <input type="password" name="password2" id="password2" class="form-control form-input" required>
        </div>

        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg">Registrar</button>
            <a href="login.php" class="btn btn-outline-secondary btn-lg">¿Ya tienes cuenta? Inicia sesión</a>
        </div>
    </form>

</body>
</html>
