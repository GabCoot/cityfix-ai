<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$ruta_id = $_GET['ruta_id'] ?? 0;

if(!$ruta_id) {
    echo json_encode(['success' => false, 'error' => 'Ruta no especificada']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM basura_suscripciones WHERE ruta_id = ? AND activo = 1");
    $stmt->execute([$ruta_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'total' => $result['total']]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>