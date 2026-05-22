<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/conexion.php';

try {
    $stmt = $pdo->query("SELECT id, nombre, descripcion, dias, hora_inicio, hora_fin, color, activo FROM basura_rutas WHERE activo = 1 ORDER BY id");
    $rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'rutas' => $rutas]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>