<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtener los datos del usuario
$stmt_user = $pdo->prepare('SELECT nombre, apellidos, email FROM usuarios WHERE id = ?');
$stmt_user->execute([$user_id]);
$usuario = $stmt_user->fetch();

if (!$usuario) {
    echo "Usuario no encontrado.";
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ver Perfil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/ver_perfil.css">
    <link rel="stylesheet" href="css/login_register.css">
</head>

<body>
    <div class="container py-5">
        <h1 class="mb-4">Mi Perfil</h1>
        <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
        <p><strong>Apellidos:</strong> <?= htmlspecialchars($usuario['apellidos']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
        <a href="dashboard.php" class="btn btn-primary">Volver al Panel</a>
    </div>
</body>

</html>
