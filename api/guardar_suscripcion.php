<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$usuario_id = $_POST['usuario_id'] ?? null;
$ruta_id = $_POST['ruta_id'] ?? null;
$ruta_nombre = $_POST['ruta_nombre'] ?? '';

if(!$usuario_id || !$ruta_id) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos']);
    exit;
}

try {
    // Verificar si ya existe la suscripción para este usuario y ruta
    $stmt = $pdo->prepare("SELECT id FROM basura_suscripciones WHERE usuario_id = ? AND ruta_id = ? AND activo = 1");
    $stmt->execute([$usuario_id, $ruta_id]);
    
    if($stmt->fetch()) {
        echo json_encode(['success' => false, 'already' => true, 'error' => 'Ya estás suscrito a esta ruta']);
        exit;
    }
    
    // Guardar suscripción
    $stmt = $pdo->prepare("INSERT INTO basura_suscripciones (usuario_id, ruta_id, ruta_nombre, activo) VALUES (?, ?, ?, 1)");
    $result = $stmt->execute([$usuario_id, $ruta_id, $ruta_nombre]);
    
    if($result) {
        echo json_encode(['success' => true, 'message' => 'Suscripción guardada']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al guardar']);
    }
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>