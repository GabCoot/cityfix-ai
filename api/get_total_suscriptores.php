<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM basura_suscripciones WHERE activo = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'total' => $result['total']]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>