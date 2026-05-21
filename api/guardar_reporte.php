<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/conexion.php';

$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
$categoria = isset($_POST['categoria']) ? $_POST['categoria'] : 'otros';
$prioridad = isset($_POST['prioridad']) ? $_POST['prioridad'] : 'media';
$descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';

if(empty($nombre) || empty($titulo) || empty($descripcion)) {
    echo json_encode(['success' => false, 'error' => 'Campos requeridos faltantes']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO reports (titulo, descripcion, categoria, prioridad, ciudadano_nombre, ciudadano_email, estado, fecha_reporte) VALUES (?, ?, ?, ?, ?, ?, 'pendiente', NOW())");
    $stmt->execute([$titulo, $descripcion, $categoria, $prioridad, $nombre, $email]);
    
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>