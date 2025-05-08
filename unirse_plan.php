<?php
// dashboard.php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtener los planes creados por el usuario
$stmt = $pdo->prepare('SELECT * FROM planes WHERE creador_id = ?');
$stmt->execute([$user_id]);
$mis_planes = $stmt->fetchAll();

// Obtener los planes donde el usuario se ha apuntado
$stmt2 = $pdo->prepare('SELECT p.* FROM planes p 
                        JOIN participantes pa ON p.id = pa.plan_id 
                        WHERE pa.usuario_id = ?');
$stmt2->execute([$user_id]);
$planes_apuntado = $stmt2->fetchAll();

// Obtener planes disponibles para apuntarse (no creados por el usuario ni ya apuntados)
$stmt3 = $pdo->prepare('SELECT p.* FROM planes p
                        WHERE p.creador_id != ? AND p.id NOT IN (
                            SELECT plan_id FROM participantes WHERE usuario_id = ?
                        )');
$stmt3->execute([$user_id, $user_id]);
$planes_disponibles = $stmt3->fetchAll();

// Obtener participantes por plan (solo para los creados por el usuario)
$participantes_por_plan = [];
foreach ($mis_planes as $plan) {
    $stmt_part = $pdo->prepare('SELECT u.email FROM participantes pa JOIN usuarios u ON pa.usuario_id = u.id WHERE pa.plan_id = ?');
    $stmt_part->execute([$plan['id']]);
    $participantes_por_plan[$plan['id']] = $stmt_part->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Panel</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>Bienvenido, <?= htmlspecialchars($_SESSION['email']) ?></h2>
    <a href="crear_plan.php" class="btn btn-success mb-3">Crear nuevo plan</a>
    <a href="logout.php" class="btn btn-danger mb-3">Cerrar sesión</a>

    <h4>Mis planes creados</h4>
    <?php if ($mis_planes): ?>
        <ul class="list-group mb-4">
            <?php foreach ($mis_planes as $plan): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($plan['titulo']) ?></strong><br>
                    <?= htmlspecialchars($plan['descripcion']) ?> <br>
                    Fecha: <?= $plan['fecha'] ?> - Lugar: <?= htmlspecialchars($plan['lugar']) ?> <br>
                    Capacidad: <?= $plan['capacidad'] ?><br>
                    <strong>Participantes:</strong>
                    <?php if (!empty($participantes_por_plan[$plan['id']])): ?>
                        <ul>
                            <?php foreach ($participantes_por_plan[$plan['id']] as $email): ?>
                                <li><?= htmlspecialchars($email) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <em>Sin participantes aún.</em>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No has creado ningún plan.</p>
    <?php endif; ?>

    <h4>Planes en los que participo</h4>
    <?php if ($planes_apuntado): ?>
        <ul class="list-group mb-4">
            <?php foreach ($planes_apuntado as $plan): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($plan['titulo']) ?></strong><br>
                    <?= htmlspecialchars($plan['descripcion']) ?> <br>
                    Fecha: <?= $plan['fecha'] ?> - Lugar: <?= htmlspecialchars($plan['lugar']) ?> <br>
                    Capacidad: <?= $plan['capacidad'] ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No te has apuntado a ningún plan todavía.</p>
    <?php endif; ?>

    <h4>Planes disponibles para unirse</h4>
    <?php if ($planes_disponibles): ?>
        <ul class="list-group">
            <?php foreach ($planes_disponibles as $plan): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= htmlspecialchars($plan['titulo']) ?></strong><br>
                        <?= htmlspecialchars($plan['descripcion']) ?> <br>
                        Fecha: <?= $plan['fecha'] ?> - Lugar: <?= htmlspecialchars($plan['lugar']) ?> <br>
                        Capacidad: <?= $plan['capacidad'] ?>
                    </div>
                    <a href="unirse_plan.php?id=<?= $plan['id'] ?>" class="btn btn-primary">Unirse</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No hay planes disponibles para unirse.</p>
    <?php endif; ?>
</body>
</html>

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
