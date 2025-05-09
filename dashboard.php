<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtener los datos del usuario (nombre y apellidos)
$stmt_user = $pdo->prepare('SELECT nombre, apellidos FROM usuarios WHERE id = ?');
$stmt_user->execute([$user_id]);
$usuario = $stmt_user->fetch();

$nombre_completo = htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']);

// Obtener los planes creados por el usuario
$stmt = $pdo->prepare('SELECT * FROM planes WHERE creador_id = ?');
$stmt->execute([$user_id]);
$mis_planes = $stmt->fetchAll();

// Obtener los planes en los que el usuario está apuntado
$stmt2 = $pdo->prepare('SELECT p.*, u.nombre AS creador_nombre FROM planes p 
                        JOIN participantes pa ON p.id = pa.plan_id 
                        JOIN usuarios u ON p.creador_id = u.id
                        WHERE pa.usuario_id = ?');
$stmt2->execute([$user_id]);
$planes_apuntado = $stmt2->fetchAll();

// Obtener los planes disponibles para unirse
$stmt3 = $pdo->prepare('SELECT p.* FROM planes p
                        WHERE p.creador_id != ? AND p.id NOT IN (
                            SELECT plan_id FROM participantes WHERE usuario_id = ? 
                        )');
$stmt3->execute([$user_id, $user_id]);
$planes_disponibles = $stmt3->fetchAll();

// Obtener los participantes de cada plan
$participantes_por_plan = [];
foreach ($mis_planes as $plan) {
    $stmt_part = $pdo->prepare('SELECT u.nombre, u.apellidos FROM participantes pa JOIN usuarios u ON pa.usuario_id = u.id WHERE pa.plan_id = ?');
    $stmt_part->execute([$plan['id']]);
    $participantes_por_plan[$plan['id']] = $stmt_part->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & Estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

    <section class="hero-section text-white text-center py-5 mb-4">
        <div class="container">
            <h1 class="display-5 mb-2"><i class="fas fa-user-circle me-2"></i>Panel de Usuario</h1>
            <p class="lead">¡Hola <?= $nombre_completo ?>! Gestiona tus planes desde aquí.</p>
            <div class="d-flex justify-content-center flex-wrap gap-2 mt-4">
                <a href="crear_plan.php" class="btn btn-light btn-lg"><i class="fas fa-plus-circle me-1"></i>Crear
                    plan</a>
                <a href="logout.php" class="btn btn-outline-light btn-lg"><i
                        class="fas fa-sign-out-alt me-1"></i>Salir</a>
            </div>
        </div>
    </section>

    <div class="container pb-5">
    <section class="mb-5">
    <h2 class="section-title mb-3">Mis planes creados</h2>
    <?php if ($mis_planes): ?>
        <div class="row g-4">
            <?php foreach ($mis_planes as $plan): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($plan['titulo']) ?></h5>
                            <p class="text-muted small mb-1">
                                <strong>📅 Fecha:</strong> <?= $plan['fecha'] ?><br>
                                <strong>📍 Lugar:</strong> <?= htmlspecialchars($plan['lugar']) ?><br>
                                <strong>👥 Capacidad:</strong> <?= $plan['capacidad'] ?>
                            </p>

                            <!-- Botón para abrir el modal de descripción -->
                            <button type="button" class="btn btn-sm btn-outline-secondary mb-2" data-bs-toggle="modal"
                                data-bs-target="#modalDescripcion<?= $plan['id'] ?>">
                                <i class="fas fa-info-circle me-1"></i>Ver descripción
                            </button>

                            <!-- Modal de descripción -->
                            <div class="modal fade" id="modalDescripcion<?= $plan['id'] ?>" tabindex="-1"
                                aria-labelledby="modalDescripcionLabel<?= $plan['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalDescripcionLabel<?= $plan['id'] ?>">
                                                Descripción del plan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?= nl2br(htmlspecialchars($plan['descripcion'])) ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <p class="mb-1"><strong>Participantes:</strong></p>
                            <?php 
                                $num_participantes = !empty($participantes_por_plan[$plan['id']]) ? count($participantes_por_plan[$plan['id']]) : 0;
                            ?>
                            <p class="text-muted small mb-1"><?= $num_participantes ?> participante<?= $num_participantes > 1 ? 's' : '' ?></p>

                            <!-- Botón para abrir el modal de participantes -->
                            <?php if ($num_participantes > 0): ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary mb-2" data-bs-toggle="modal"
                                    data-bs-target="#modalParticipantes<?= $plan['id'] ?>">
                                    <i class="fas fa-users me-1"></i>Ver participantes
                                </button>
                            <?php endif; ?>

                            <!-- Modal de participantes -->
                            <?php if ($num_participantes > 0): ?>
                                <div class="modal fade" id="modalParticipantes<?= $plan['id'] ?>" tabindex="-1"
                                    aria-labelledby="modalParticipantesLabel<?= $plan['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalParticipantesLabel<?= $plan['id'] ?>">Participantes del plan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <ul class="list-unstyled">
                                                    <?php foreach ($participantes_por_plan[$plan['id']] as $participante): ?>
                                                        <li><?= htmlspecialchars($participante['nombre'] . ' ' . $participante['apellidos']) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
            <h2 class="section-title mb-3">Planes en los que participo</h2>
            <?php if ($planes_apuntado): ?>
                <div class="row g-4">
                    <?php foreach ($planes_apuntado as $plan): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($plan['titulo']) ?></h5>
                                    <p class="text-muted small">
                                        📅 <?= $plan['fecha'] ?><br>
                                        📍 <?= htmlspecialchars($plan['lugar']) ?><br>
                                        👥 <?= $plan['capacidad'] ?><br>
                                        🧑‍💼 <?= htmlspecialchars($plan['creador_nombre']) ?>
                                    </p>

                                    <!-- Botón para abrir el modal de descripción -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2" data-bs-toggle="modal"
                                        data-bs-target="#modalDescripcionParticipante<?= $plan['id'] ?>">
                                        <i class="fas fa-info-circle me-1"></i>Ver descripción
                                    </button>

                                    <!-- Modal de descripción -->
                                    <div class="modal fade" id="modalDescripcionParticipante<?= $plan['id'] ?>" tabindex="-1"
                                        aria-labelledby="modalDescripcionLabelParticipante<?= $plan['id'] ?>"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="modalDescripcionLabelParticipante<?= $plan['id'] ?>">Descripción del
                                                        plan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Cerrar"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?= nl2br(htmlspecialchars($plan['descripcion'])) ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
            <h2 class="section-title mb-3">Planes disponibles para unirse</h2>
            <?php if ($planes_disponibles): ?>
                <div class="row g-4">
                    <?php foreach ($planes_disponibles as $plan): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm d-flex flex-column justify-content-between">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($plan['titulo']) ?></h5>
                                    <p class="text-muted small">
                                        📅 <?= $plan['fecha'] ?><br>
                                        📍 <?= htmlspecialchars($plan['lugar']) ?><br>
                                        👥 <?= $plan['capacidad'] ?>
                                    </p>

                                    <!-- Botón para abrir el modal de descripción -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2" data-bs-toggle="modal"
                                        data-bs-target="#modalDescripcionDisponible<?= $plan['id'] ?>">
                                        <i class="fas fa-info-circle me-1"></i>Ver descripción
                                    </button>

                                    <!-- Modal de descripción -->
                                    <div class="modal fade" id="modalDescripcionDisponible<?= $plan['id'] ?>" tabindex="-1"
                                        aria-labelledby="modalDescripcionLabelDisponible<?= $plan['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="modalDescripcionLabelDisponible<?= $plan['id'] ?>">Descripción del
                                                        plan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Cerrar"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?= nl2br(htmlspecialchars($plan['descripcion'])) ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0 text-end">
                                    <a href="unirse_plan.php?id=<?= $plan['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-sign-in-alt me-1"></i>Unirse
                                    </a>
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

    <footer class="text-center py-3 text-muted">
        &copy; <?= date('Y') ?> PlanesApp. Todos los derechos reservados.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>