<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtener los datos del usuario (nombre y apellidos)
$stmt_user = $pdo->prepare('SELECT nombre, apellidos, email FROM usuarios WHERE id = ?');
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

// Modificar la consulta SQL para incluir el nombre del creador
$stmt3 = $pdo->prepare('SELECT p.*, u.nombre AS creador_nombre FROM planes p
                        JOIN usuarios u ON p.creador_id = u.id
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

// Obtener comentarios para cada plan
$comentarios_por_plan = [];
foreach (array_merge($mis_planes, $planes_apuntado) as $plan) {
    $stmt_comentarios = $pdo->prepare('SELECT c.*, u.nombre FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE c.plan_id = ? ORDER BY c.fecha DESC');
    $stmt_comentarios->execute([$plan['id']]);
    $comentarios_por_plan[$plan['id']] = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);
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

    <style>
        @keyframes disintegrate {
            0% {
                opacity: 1;
                transform: translateY(0);
            }
            100% {
                opacity: 0;
                transform: translateY(-50px);
                filter: blur(5px);
            }
        }

        .disintegrate {
            animation: disintegrate 1s forwards;
        }
    </style>
</head>

<body>

    <section class="hero-section text-white text-center py-5 mb-4">
        <div class="container">
            <h1 class="display-5 mb-2"><i class="fas fa-user-circle me-2"></i>Panel de Usuario</h1>
            <p class="lead">¡Hola <?= $nombre_completo ?>! Gestiona tus planes desde aquí.</p>
            <div class="d-flex justify-content-center flex-wrap gap-2 mt-4">
                <a href="crear_plan.php" class="btn btn-light btn-lg"><i class="fas fa-plus-circle me-1"></i>Crear plan</a>
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-lg dropdown-toggle" type="button" id="perfilDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user me-1"></i>Perfil
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="perfilDropdown">
                        <li><a class="dropdown-item" href="ver_perfil.php">Ver perfil</a></li>
                        <li><a class="dropdown-item" href="editar_perfil.php">Editar perfil</a></li>
                    </ul>
                </div>
                <a href="logout.php" class="btn btn-outline-light btn-lg"><i class="fas fa-sign-out-alt me-1"></i>Salir</a>
            </div>
        </div>
    </section>

    <div class="container pb-5">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center">
                <?php if ($_GET['error'] == 'no_capacity'): ?>
                    Este plan ya no tiene capacidad disponible.
                <?php elseif ($_GET['error'] == 'already_joined'): ?>
                    Ya estás apuntado a este plan.
                <?php elseif ($_GET['error'] == 'plan_not_found'): ?>
                    El plan no existe.
                <?php endif; ?>
            </div>
        <?php endif; ?>

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

                                    <!-- Mostrar número de comentarios en lugar de los comentarios directamente -->
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-comments me-1"></i>
                                        <?= count($comentarios_por_plan[$plan['id']] ?? []) ?> comentario<?= count($comentarios_por_plan[$plan['id']] ?? []) !== 1 ? 's' : '' ?>
                                    </p>

                                    <!-- Botón para abrir el modal de comentarios -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2" data-bs-toggle="modal"
                                        data-bs-target="#modalComentarios<?= $plan['id'] ?>">
                                        <i class="fas fa-comments me-1"></i>Ver comentarios
                                    </button>

                                    <!-- Modal de comentarios -->
                                    <div class="modal fade" id="modalComentarios<?= $plan['id'] ?>" tabindex="-1"
                                        aria-labelledby="modalComentariosLabel<?= $plan['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalComentariosLabel<?= $plan['id'] ?>">Comentarios del plan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <ul class="list-unstyled">
                                                        <?php if (!empty($comentarios_por_plan[$plan['id']])): ?>
                                                            <?php foreach ($comentarios_por_plan[$plan['id']] as $comentario): ?>
                                                                <li class="mb-2">
                                                                    <strong><?= htmlspecialchars($comentario['nombre']) ?>:</strong>
                                                                    <p class="mb-0 small text-muted">"<?= htmlspecialchars($comentario['comentario']) ?>"</p>
                                                                    <small class="text-muted">Publicado el <?= $comentario['fecha'] ?></small>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <li class="text-muted">No hay comentarios aún.</li>
                                                        <?php endif; ?>
                                                    </ul>
                                                    <!-- Formulario para agregar un nuevo comentario -->
                                                    <div class="mt-3">
                                                        <form action="agregar_comentario.php" method="POST">
                                                            <div class="mb-3">
                                                                <label for="comentario<?= $plan['id'] ?>" class="form-label">Añadir un comentario:</label>
                                                                <textarea class="form-control" id="comentario<?= $plan['id'] ?>" name="comentario" rows="3" required></textarea>
                                                            </div>
                                                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                                                            <button type="submit" class="btn btn-primary btn-sm">Enviar</button>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-footer bg-transparent border-0 text-end">
                                        <a href="editar_plan.php?id=<?= $plan['id'] ?>" class="btn btn-warning btn-sm me-2">
                                            <i class="fas fa-edit me-1"></i>Editar Plan
                                        </a>
                                        <a href="anular_plan.php?id=<?= $plan['id'] ?>" class="btn btn-danger btn-sm btn-anular-plan">
                                            <i class="fas fa-times-circle me-1"></i>Anular Plan
                                        </a>
                                    </div>
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
                                        🧑 <?= htmlspecialchars($plan['creador_nombre']) ?>
                                    </p>

                                    <!-- Botón para abrir el modal de descripción -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2" data-bs-toggle="modal"
                                        data-bs-target="#modalDescripcionParticipante<?= $plan['id'] ?>">
                                        <i class="fas fa-info-circle me-1"></i>Ver descripción
                                    </button>

                                    <!-- Modal de descripción -->
                                    <div class="modal fade" id="modalDescripcionParticipante<?= $plan['id'] ?>" tabindex="-1"
                                        aria-labelledby="modalDescripcionLabelParticipante<?= $plan['id'] ?>" aria-hidden="true">
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

                                    <!-- Mostrar número de comentarios en lugar de los comentarios directamente -->
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-comments me-1"></i>
                                        <?= count($comentarios_por_plan[$plan['id']] ?? []) ?> comentario<?= count($comentarios_por_plan[$plan['id']] ?? []) !== 1 ? 's' : '' ?>
                                    </p>

                                    <!-- Botón para abrir el modal de comentarios -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2" data-bs-toggle="modal"
                                        data-bs-target="#modalComentariosParticipante<?= $plan['id'] ?>">
                                        <i class="fas fa-comments me-1"></i>Ver comentarios
                                    </button>

                                    <!-- Modal de comentarios -->
                                    <div class="modal fade" id="modalComentariosParticipante<?= $plan['id'] ?>" tabindex="-1"
                                        aria-labelledby="modalComentariosLabelParticipante<?= $plan['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalComentariosLabelParticipante<?= $plan['id'] ?>">Comentarios del plan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <ul class="list-unstyled">
                                                        <?php if (!empty(
                                                            $comentarios_por_plan[$plan['id']])): ?>
                                                            <?php foreach ($comentarios_por_plan[$plan['id']] as $comentario): ?>
                                                                <li class="mb-2">
                                                                    <strong><?= htmlspecialchars($comentario['nombre']) ?>:</strong>
                                                                    <p class="mb-0 small text-muted">"<?= htmlspecialchars($comentario['comentario']) ?>"</p>
                                                                    <small class="text-muted">Publicado el <?= $comentario['fecha'] ?></small>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <li class="text-muted">No hay comentarios aún.</li>
                                                        <?php endif; ?>
                                                    </ul>
                                                    <!-- Formulario para agregar un nuevo comentario -->
                                                    <div class="mt-3">
                                                        <form action="agregar_comentario.php" method="POST">
                                                            <div class="mb-3">
                                                                <label for="comentarioParticipante<?= $plan['id'] ?>" class="form-label">Añadir un comentario:</label>
                                                                <textarea class="form-control" id="comentarioParticipante<?= $plan['id'] ?>" name="comentario" rows="3" required></textarea>
                                                            </div>
                                                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                                                            <button type="submit" class="btn btn-primary btn-sm">Enviar</button>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botón para anular participación -->
                                    <a href="anular_participacion.php?id=<?= $plan['id'] ?>" class="btn btn-danger btn-sm float-end btn-anular-participacion">
                                        <i class="fas fa-times-circle me-1"></i>Anular Participación
                                    </a>
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
                                        👥 <?= $plan['capacidad'] ?><br>
                                        🧑 <?= htmlspecialchars($plan['creador_nombre']) ?>
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

    <footer class="text-center py-3 text-white">
        &copy; <?= date('Y') ?> ¿Quedamos?. Todos los derechos reservados.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Desaparecer alertas después de 3 segundos
            setTimeout(() => {
                const alert = document.querySelector('.alert');
                if (alert) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 3000);

            const anularButtons = document.querySelectorAll('.btn-anular-plan');

            anularButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();

                    // Mostrar cuadro de confirmación
                    const confirmacion = confirm('¿Estás seguro de que deseas anular este plan?');
                    if (!confirmacion) {
                        return; // Salir si el usuario cancela
                    }

                    const card = button.closest('.card');
                    card.classList.add('disintegrate');

                    // Redirigir después de la animación
                    setTimeout(() => {
                        window.location.href = button.getAttribute('href');
                    }, 1000); // Esperar a que termine la animación
                });
            });

            const anularParticipacionButtons = document.querySelectorAll('.btn-anular-participacion');

            anularParticipacionButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();

                    // Mostrar cuadro de confirmación
                    const confirmacion = confirm('¿Estás seguro de que deseas anular tu participación en este plan?');
                    if (!confirmacion) {
                        return; // Salir si el usuario cancela
                    }

                    const card = button.closest('.card');
                    card.classList.add('disintegrate');

                    // Redirigir después de la animación
                    setTimeout(() => {
                        window.location.href = button.getAttribute('href');
                    }, 1000); // Esperar a que termine la animación
                });
            });
        });
    </script>
</body>

</html>