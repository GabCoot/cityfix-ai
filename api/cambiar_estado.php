<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/conexion.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$estado = isset($_POST['estado']) ? $_POST['estado'] : '';

$estados_validos = ['pendiente', 'en_proceso', 'resuelto'];

if($id == 0) {
    echo json_encode(['success' => false, 'error' => 'ID no válido']);
    exit;
}

if(!in_array($estado, $estados_validos)) {
    echo json_encode(['success' => false, 'error' => 'Estado inválido']);
    exit;
}

try {
    $fecha_resuelto = $estado == 'resuelto' ? date('Y-m-d H:i:s') : null;
    $stmt = $pdo->prepare("UPDATE reports SET estado = ?, fecha_resuelto = ? WHERE id = ?");
    $stmt->execute([$estado, $fecha_resuelto, $id]);
    
    echo json_encode(['success' => true]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>