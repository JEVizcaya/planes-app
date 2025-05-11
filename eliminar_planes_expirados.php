<?php
// Incluir la conexión a la base de datos
require_once 'includes/db.php';

function eliminarPlanesExpirados($pdo) {
    try {
        // Forzar la zona horaria a Europa Central
        date_default_timezone_set('Europe/Berlin');

        // Obtener la fecha y hora actual
        $fecha_actual = date('Y-m-d');
        $hora_actual = date('H:i:s');

        // Registrar la fecha y hora actual para depuración
        error_log("Fecha actual utilizada en la consulta: " . $fecha_actual);
        error_log("Hora actual utilizada en la consulta: " . $hora_actual);

        // Verificar los planes que deberían ser eliminados
        $query_verificar = "SELECT id, titulo, fecha FROM planes WHERE fecha < :fecha_actual";
        $stmt_verificar = $pdo->prepare($query_verificar);
        $stmt_verificar->execute([':fecha_actual' => $fecha_actual]);
        $planes_a_eliminar = $stmt_verificar->fetchAll();

        if ($planes_a_eliminar) {
            foreach ($planes_a_eliminar as $plan) {
                error_log("Plan a eliminar: ID=" . $plan['id'] . ", Título=" . $plan['titulo'] . ", Fecha=" . $plan['fecha']);
            }
        } else {
            error_log("No se encontraron planes para eliminar.");
        }

        // Preparar la consulta para eliminar planes cuya fecha ya ha pasado
        $query = "DELETE FROM planes WHERE fecha < :fecha_actual";
        $stmt = $pdo->prepare($query);

        // Registrar la consulta SQL para depuración
        error_log("Consulta SQL ejecutada: " . $query);

        // Ejecutar la consulta
        $stmt->execute([':fecha_actual' => $fecha_actual]);

        // Verificar si la consulta afecta filas
        $filas_afectadas = $stmt->rowCount();
        error_log("Planes eliminados: " . $filas_afectadas);

        return $filas_afectadas;
    } catch (PDOException $e) {
        // Manejar errores si es necesario
        error_log("Error al eliminar planes expirados: " . $e->getMessage());
        return 0;
    }
}
