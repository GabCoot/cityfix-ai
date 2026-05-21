<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$action = $_GET['action'] ?? '';

if($action == 'activos') {
    $sql = "SELECT * FROM reports WHERE estado IN ('pendiente', 'en_proceso')";
    $params = [];
    
    if(!empty($_GET['prioridad'])) { $sql .= " AND prioridad = ?"; $params[] = $_GET['prioridad']; }
    if(!empty($_GET['estado'])) { $sql .= " AND estado = ?"; $params[] = $_GET['estado']; }
    if(!empty($_GET['busqueda'])) { $sql .= " AND (ciudadano_nombre LIKE ? OR titulo LIKE ?)"; $params[] = "%{$_GET['busqueda']}%"; $params[] = "%{$_GET['busqueda']}%"; }
    
    $sql .= " ORDER BY FIELD(prioridad, 'alta', 'media', 'baja'), fecha_reporte DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true, 'reportes' => $stmt->fetchAll()]);
}
elseif($action == 'historial') {
    $sql = "SELECT * FROM reports WHERE estado = 'resuelto'";
    $params = [];
    if(!empty($_GET['busqueda'])) { $sql .= " AND (ciudadano_nombre LIKE ? OR titulo LIKE ?)"; $params[] = "%{$_GET['busqueda']}%"; $params[] = "%{$_GET['busqueda']}%"; }
    if(!empty($_GET['prioridad'])) { $sql .= " AND prioridad = ?"; $params[] = $_GET['prioridad']; }
    if(!empty($_GET['fecha'])) { $sql .= " AND DATE(fecha_reporte) = ?"; $params[] = $_GET['fecha']; }
    $sql .= " ORDER BY fecha_reporte DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true, 'reportes' => $stmt->fetchAll()]);
}
else {
    echo json_encode(['success' => false]);
}
?>