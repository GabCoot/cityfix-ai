<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/conexion.php';

$usuario_id = $_GET['usuario_id'] ?? 'guest';

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reports WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $total = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as resueltos FROM reports WHERE usuario_id = ? AND estado = 'resuelto'");
    $stmt->execute([$usuario_id]);
    $resueltos = $stmt->fetch()['resueltos'];
    
    echo json_encode(['success' => true, 'total' => $total, 'resueltos' => $resueltos]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>