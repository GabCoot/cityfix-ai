<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/conexion.php';

$ruta_id = $_GET['ruta_id'] ?? 0;

if(!$ruta_id) {
    echo json_encode(['success' => false, 'error' => 'Ruta no especificada']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, nombre, orden, latitud, longitud FROM basura_puntos WHERE ruta_id = ? ORDER BY orden ASC");
    $stmt->execute([$ruta_id]);
    $puntos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'puntos' => $puntos]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>