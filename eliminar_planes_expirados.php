<?php
// Incluir la conexión a la base de datos
require_once 'includes/db.php';

try {
    // Obtener la fecha actual
    $fecha_actual = date('Y-m-d');

    // Preparar la consulta para eliminar planes cuya fecha ya ha pasado
    $query = "DELETE FROM planes WHERE fecha < :fecha_actual";
    $stmt = $pdo->prepare($query);

    // Ejecutar la consulta
    $stmt->execute([':fecha_actual' => $fecha_actual]);

    echo "Planes expirados eliminados correctamente.";
} catch (PDOException $e) {
    echo "Error al eliminar planes expirados: " . $e->getMessage();
}
