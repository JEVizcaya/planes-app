<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_id = $_POST['plan_id'];
    $comentario = trim($_POST['comentario']);

    if (!empty($comentario)) {
        $stmt = $pdo->prepare('INSERT INTO comentarios (plan_id, usuario_id, comentario) VALUES (?, ?, ?)');
        $stmt->execute([$plan_id, $user_id, $comentario]);
    }

    header('Location: dashboard.php');
    exit;
}

header('Location: dashboard.php');
exit;
?>
