<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $plan_id = intval($_GET['id']);

    // Verificar si el usuario está participando en el plan
    $query = "SELECT * FROM participantes WHERE plan_id = :plan_id AND usuario_id = :usuario_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':plan_id', $plan_id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Eliminar la participación del usuario
        $delete_query = "DELETE FROM participantes WHERE plan_id = :plan_id AND usuario_id = :usuario_id";
        $delete_stmt = $pdo->prepare($delete_query);
        $delete_stmt->bindParam(':plan_id', $plan_id, PDO::PARAM_INT);
        $delete_stmt->bindParam(':usuario_id', $_SESSION['user_id'], PDO::PARAM_INT);

        if ($delete_stmt->execute()) {
            // Incrementar la capacidad del plan
            $update_query = "UPDATE planes SET capacidad = capacidad + 1 WHERE id = :plan_id";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':plan_id', $plan_id, PDO::PARAM_INT);
            $update_stmt->execute();

            header('Location: dashboard.php?success=participacion_anulada');
            exit;
        } else {
            header('Location: dashboard.php?error=anular_participacion_error');
            exit;
        }
    } else {
        header('Location: dashboard.php?error=no_participacion');
        exit;
    }
} else {
    header('Location: dashboard.php?error=invalid_request');
    exit;
}
