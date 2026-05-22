<?php
session_start();
header('Content-Type: application/json');

if(isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_nombre'])) {
    echo json_encode([
        'success' => true,
        'usuario' => [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'],
            'email' => $_SESSION['usuario_email']
        ]
    ]);
} else {
    echo json_encode(['success' => false]);
}
?>