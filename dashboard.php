<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM planes WHERE creador_id = ?');
$stmt->execute([$user_id]);
$mis_planes = $stmt->fetchAll();

$stmt2 = $pdo->prepare('SELECT p.* FROM planes p 
                        JOIN participantes pa ON p.id = pa.plan_id 
                        WHERE pa.usuario_id = ?');
$stmt2->execute([$user_id]);
$planes_apuntado = $stmt2->fetchAll();

$stmt3 = $pdo->prepare('SELECT p.* FROM planes p
                        WHERE p.creador_id != ? AND p.id NOT IN (
                            SELECT plan_id FROM participantes WHERE usuario_id = ?
                        )');
$stmt3->execute([$user_id, $user_id]);
$planes_disponibles = $stmt3->fetchAll();

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
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<section class="hero-section text-white text-center bg-primary py-4 mb-4">
        <div class="container">
            <h1 class="display-5 mb-2">Panel de Usuario</h1>
            <p class="lead">Bienvenido, <?= htmlspecialchars($_SESSION['email']) ?></p>
            <div class="d-flex justify-content-center flex-wrap gap-2 mt-3">
                <a href="crear_plan.php" class="btn btn-light btn-lg">Crear nuevo plan</a>
                <a href="logout.php" class="btn btn-outline-light btn-lg">Cerrar sesión</a>
            </div>
        </div>
    </section>

        <div class="container pb-5">
            <section class="mb-5">
                <h2 class="text-secondary mb-3">Mis planes creados</h2>
                <?php if ($mis_planes): ?>
                    <div class="row g-4">
                        <?php foreach ($mis_planes as $plan): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title text-info"><?= htmlspecialchars($plan['titulo']) ?></h5>
                                        <p class="card-text"><?= htmlspecialchars($plan['descripcion']) ?></p>
                                        <p class="text-muted small">
                                            Fecha: <?= $plan['fecha'] ?><br>
                                            Lugar: <?= htmlspecialchars($plan['lugar']) ?><br>
                                            Capacidad: <?= $plan['capacidad'] ?>
                                        </p>
                                        <hr>
                                        <p class="mb-1"><strong>Participantes:</strong></p>
                                        <?php if (!empty($participantes_por_plan[$plan['id']])): ?>
                                            <ul class="list-unstyled small mb-0">
                                                <?php foreach ($participantes_por_plan[$plan['id']] as $email): ?>
                                                    <li>• <?= htmlspecialchars($email) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p class="text-muted small"><em>Sin participantes aún.</em></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No has creado ningún plan aún.</p>
                <?php endif; ?>
            </section>

            <section class="mb-5">
                <h2 class="text-secondary mb-3">Planes en los que participo</h2>
                <?php if ($planes_apuntado): ?>
                    <div class="row g-4">
                        <?php foreach ($planes_apuntado as $plan): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title text-info"><?= htmlspecialchars($plan['titulo']) ?></h5>
                                        <p class="card-text"><?= htmlspecialchars($plan['descripcion']) ?></p>
                                        <p class="text-muted small">
                                            Fecha: <?= $plan['fecha'] ?><br>
                                            Lugar: <?= htmlspecialchars($plan['lugar']) ?><br>
                                            Capacidad: <?= $plan['capacidad'] ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No te has apuntado a ningún plan todavía.</p>
                <?php endif; ?>
            </section>

            <section>
                <h2 class="text-secondary mb-3">Planes disponibles para unirse</h2>
                <?php if ($planes_disponibles): ?>
                    <div class="row g-4">
                        <?php foreach ($planes_disponibles as $plan): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm d-flex flex-column justify-content-between">
                                    <div class="card-body">
                                        <h5 class="card-title text-info"><?= htmlspecialchars($plan['titulo']) ?></h5>
                                        <p class="card-text"><?= htmlspecialchars($plan['descripcion']) ?></p>
                                        <p class="text-muted small">
                                            Fecha: <?= $plan['fecha'] ?><br>
                                            Lugar: <?= htmlspecialchars($plan['lugar']) ?><br>
                                            Capacidad: <?= $plan['capacidad'] ?>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 text-end">
                                        <a href="unirse_plan.php?id=<?= $plan['id'] ?>" class="btn btn-primary btn-sm">Unirse</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No hay planes disponibles para unirse.</p>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer class="text-center py-3 bg-light text-muted">
        &copy; <?= date('Y') ?> PlanesApp. Todos los derechos reservados.
    </footer>
</body>
</html>
