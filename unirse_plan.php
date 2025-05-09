<?php
// unirse_plan.php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$plan_id = $_GET['id'];

// Check if the user is already a participant in the plan
$stmt = $pdo->prepare('SELECT * FROM participantes WHERE usuario_id = ? AND plan_id = ?');
$stmt->execute([$user_id, $plan_id]);
$already_participant = $stmt->fetch();

if ($already_participant) {
    header('Location: dashboard.php?error=already_joined');
    exit;
}

// Check the plan's capacity
$stmt = $pdo->prepare('SELECT capacidad FROM planes WHERE id = ?');
$stmt->execute([$plan_id]);
$plan = $stmt->fetch();

if (!$plan || $plan['capacidad'] <= 0) {
    header('Location: dashboard.php?error=no_capacity');
    exit;
}

// Reduce the plan's capacity by 1
$stmt = $pdo->prepare('UPDATE planes SET capacidad = capacidad - 1 WHERE id = ?');
$stmt->execute([$plan_id]);

// Add the user as a participant in the plan
$stmt = $pdo->prepare('INSERT INTO participantes (usuario_id, plan_id) VALUES (?, ?)');
$stmt->execute([$user_id, $plan_id]);

header('Location: dashboard.php?success=joined');
exit;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Unirse al Plan</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/login_register.css"> <!-- Aquí debe estar el archivo CSS que contiene los estilos -->
</head>
<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card mt-5 shadow-sm border-0 rounded-4">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Unirse al Plan</h2>

                        <?php 
                        // Mostrar error si ya está apuntado al plan
                        if (isset($_GET['error']) && $_GET['error'] == 'already_joined'): ?>
                            <div class="alert alert-danger">Ya estás apuntado a este plan.</div>
                        <?php endif; ?>

                        <!-- Mostrar mensaje si no hay capacidad -->
                        <?php if (isset($_GET['error']) && $_GET['error'] == 'no_capacity'): ?>
                            <div class="alert alert-warning">Este plan ya no tiene capacidad disponible.</div>
                        <?php endif; ?>

                        <!-- Información del plan -->
                        <h4 class="mb-4"><?= htmlspecialchars($plan['titulo']) ?></h4>
                        <p><strong>Descripción:</strong> <?= htmlspecialchars($plan['descripcion']) ?></p>
                        <p><strong>Fecha:</strong> <?= $plan['fecha'] ?></p>
                        <p><strong>Lugar:</strong> <?= htmlspecialchars($plan['lugar']) ?></p>
                        <p><strong>Capacidad restante:</strong> <?= $plan['capacidad'] ?></p>

                        <!-- Botón para unirse -->
                        <div class="text-center mt-4">
                            <a href="unirse_plan.php?id=<?= $plan['id'] ?>" class="btn btn-primary btn-lg">Unirse al plan</a>
                        </div>

                        <div class="text-center mt-4">
                            <a href="dashboard.php" class="btn btn-outline-secondary">← Volver al panel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
