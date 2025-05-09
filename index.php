<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require 'includes/db.php';

// Fetch all plans
$stmt = $pdo->prepare('SELECT id, titulo, descripcion, fecha, lugar FROM planes');
$stmt->execute();
$planes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¿Quedamos?</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <!-- HERO SECTION -->
    <section class="hero-section text-white d-flex align-items-center justify-content-center">
        <div class="text-center">
            <h1 class="display-3 fw-bold mb-3">¿Quedamos?</h1>
            <p class="lead fs-4 mb-4">Descubre planes increíbles y conoce gente con tus mismos intereses.</p>
            <div>
                <a href="login.php" class="btn btn-light btn-lg me-2 shadow"><i class="fas fa-sign-in-alt me-1"></i> Iniciar sesión</a>
                <a href="register.php" class="btn btn-outline-light btn-lg shadow"><i class="fas fa-user-plus me-1"></i> Registrarse</a>
            </div>
        </div>
    </section>

    <!-- PLANES DISPONIBLES -->
    <section class="container my-5">
        <h2 class="text-center text-acento mb-5">Planes disponibles</h2>

        <?php if ($planes): ?>
            <div class="row g-4 justify-content-center">
                <?php foreach ($planes as $plan): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <h5 class="card-title text-primary fw-bold"><?= htmlspecialchars($plan['titulo']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars(substr($plan['descripcion'], 0, 100)) ?>...</p>
                                <!-- Botón para abrir el modal de descripción -->
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#modalDescripcion<?= $plan['id'] ?>">
                                    <i class="fas fa-info-circle me-1"></i> Ver descripción
                                </button>
                            </div>
                            <div class="card-footer bg-light text-muted small d-flex justify-content-between">
                                <span><i class="far fa-calendar-alt me-1"></i><?= htmlspecialchars($plan['fecha']) ?></span>
                                <span><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($plan['lugar']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de descripción -->
                    <div class="modal fade" id="modalDescripcion<?= $plan['id'] ?>" tabindex="-1"
                        aria-labelledby="modalDescripcionLabel<?= $plan['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalDescripcionLabel<?= $plan['id'] ?>">Descripción del plan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <?= nl2br(htmlspecialchars($plan['descripcion'])) ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No hay planes disponibles en este momento.</p>
        <?php endif; ?>
    </section>

    <footer class="footer">
        &copy; <?= date('Y') ?> ¿Quedamos? Todos los derechos reservados.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
