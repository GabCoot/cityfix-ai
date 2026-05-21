<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/conexion.php';

$usuario_id = $_GET['usuario_id'] ?? 'guest';
$action = $_GET['action'] ?? 'todos';

try {
    if($action == 'recientes') {
        $stmt = $pdo->prepare("SELECT * FROM reports WHERE usuario_id = ? ORDER BY fecha_reporte DESC LIMIT 5");
        $stmt->execute([$usuario_id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM reports WHERE usuario_id = ? ORDER BY fecha_reporte DESC");
        $stmt->execute([$usuario_id]);
    }
    
    $reportes = $stmt->fetchAll();
    echo json_encode(['success' => true, 'reportes' => $reportes]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>