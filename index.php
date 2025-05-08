<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require 'includes/db.php';

// Fetch all plans
$stmt = $pdo->prepare('SELECT titulo, descripcion, fecha, lugar FROM planes');
$stmt->execute();
$planes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a PlanesApp</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
    
</head>
<body>

    <main>
        <div class="hero-section bg-primary text-white">
            <h1 class="display-4">Bienvenido a PlanesApp</h1>
            <p class="lead">Conéctate con otros y comparte tus planes.</p>
            <div class="mt-4">
    <a href="login.php" class="btn btn-light btn-lg px-4 me-2 shadow-sm">Iniciar sesión</a>
    <a href="register.php" class="btn btn-outline-light btn-lg px-4 shadow-sm">Registrarse</a>
</div>
        </div>

        <div class="cards-container">
            <h2 class="text-center text-secondary mb-4">Planes disponibles</h2>

            <?php if ($planes): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($planes as $plan): ?>
                        <div class="col">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title text-info"><?= htmlspecialchars($plan['titulo']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($plan['descripcion']) ?></p>
                                </div>
                                <div class="card-footer bg-light text-muted small">
                                    <div>Fecha: <?= htmlspecialchars($plan['fecha']) ?></div>
                                    <div>Lugar: <?= htmlspecialchars($plan['lugar']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">No hay planes disponibles en este momento.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        &copy; <?= date('Y') ?> PlanesApp. Todos los derechos reservados.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
