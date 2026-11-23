<?php
session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
    http_response_code(403); exit('Solicitud no válida.');
}
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit; }
require_once 'conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id && $id > 0) {
        $stmt = $conexion->prepare('UPDATE contactos SET favorito = 1 - favorito WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$id, $_SESSION['usuario_id']]);
    }
}
header('Location: index.php'); exit;
