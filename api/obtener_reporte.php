<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id == 0) {
    echo json_encode(['success' => false, 'error' => 'ID no válido']);
    exit;
}

try {
    // Obtener datos del reporte
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([$id]);
    $reporte = $stmt->fetch();
    
    if($reporte) {
        // Obtener fotos del reporte
        $stmtFotos = $pdo->prepare("SELECT * FROM reporte_fotos WHERE reporte_id = ? ORDER BY id ASC");
        $stmtFotos->execute([$id]);
        $fotos = $stmtFotos->fetchAll();
        
        echo json_encode(['success' => true, 'reporte' => $reporte, 'fotos' => $fotos]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Reporte no encontrado']);
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>