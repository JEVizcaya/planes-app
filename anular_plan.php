<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $plan_id = intval($_GET['id']);

    // Verificar si el plan existe y pertenece al usuario actual
    $query = "SELECT id FROM planes WHERE id = :id AND creador_id = :creador_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $plan_id, PDO::PARAM_INT);
    $stmt->bindParam(':creador_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Eliminar el plan de la base de datos
        $delete_query = "DELETE FROM planes WHERE id = :id";
        $delete_stmt = $pdo->prepare($delete_query);
        $delete_stmt->bindParam(':id', $plan_id, PDO::PARAM_INT);

        if ($delete_stmt->execute()) {
            header('Location: dashboard.php?success=plan_eliminado');
            exit;
        } else {
            header('Location: dashboard.php?error=eliminar_error');
            exit;
        }
    } else {
        header('Location: dashboard.php?error=plan_not_found');
        exit;
    }
} else {
    header('Location: dashboard.php?error=invalid_request');
    exit;
}
