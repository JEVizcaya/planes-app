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
<body class="bg-light full-height-center">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="display-4 text-primary mb-4">Bienvenido a PlanesApp</h1>
                <p class="lead mb-4">Conéctate con otros y comparte tus planes.</p>
                <div>
                    <a href="login.php" class="btn btn-primary btn-lg m-2">Iniciar sesión</a>
                    <a href="register.php" class="btn btn-success btn-lg m-2">Registrarse</a>
                </div>

                <h2 class="mt-5 text-secondary">Planes disponibles</h2>
                <div class="mt-4">
                    <?php if ($planes): ?>
                        <ul class="list-group">
                            <?php foreach ($planes as $plan): ?>
                                <li class="list-group-item shadow-sm mb-3 rounded">
                                    <h5 class="mb-1 text-info"><?= htmlspecialchars($plan['titulo']) ?></h5>
                                    <p class="mb-1"><?= htmlspecialchars($plan['descripcion']) ?></p>
                                    <p class="small text-muted mb-0">
                                        Fecha: <?= htmlspecialchars($plan['fecha']) ?><br>
                                        Lugar: <?= htmlspecialchars($plan['lugar']) ?>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No hay planes disponibles en este momento.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>