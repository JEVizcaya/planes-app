<?php
session_start();
require 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        header('Location: dashboard.php');
        exit;
    } else {
        $errors[] = 'Credenciales incorrectas.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - ¿Quedamos?</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/login_register.css">
</head>
<body class="login-page d-flex align-items-center justify-content-center">

    <div class="form-container bg-white p-5 rounded shadow">
        <h2 class="text-center text-primary mb-4"><i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión</h2>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">Registro exitoso. Ahora puedes iniciar sesión.</div>
        <?php endif; ?>

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
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" name="email" id="email" class="form-control form-input" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control form-input" required>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-arrow-right-to-bracket me-1"></i> Entrar</button>
                <a href="register.php" class="btn btn-outline-secondary btn-lg">¿No tienes cuenta? Regístrate</a>
                <a href="index.php" class="btn btn-link text-decoration-none"><i class="fas fa-home me-1"></i> Volver al inicio</a>
            </div>
        </form>
    </div>

</body>
</html>
