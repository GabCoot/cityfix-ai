<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/conexion.php';

// Crear carpeta de fotos dentro de la carpeta publica img
$uploadDir = __DIR__ . '/../img/reportes/';  // Cambiado a img/reportes
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$nombre = $_POST['nombre'] ?? '';
$email = $_POST['email'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$titulo = $_POST['titulo'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$categoria = $_POST['categoria'] ?? 'otros';
$latitud = $_POST['latitud'] ?? null;
$longitud = $_POST['longitud'] ?? null;
$usuario_id = $_POST['usuario_id'] ?? 'guest';

// Validar coordenadas
if($latitud && $longitud) {
    $latitud = floatval($latitud);
    $longitud = floatval($longitud);
} else {
    $latitud = null;
    $longitud = null;
}

if(empty($nombre) || empty($titulo) || empty($descripcion)) {
    echo json_encode(['success' => false, 'error' => 'Campos requeridos: nombre, título y descripción']);
    exit;
}

try {
    // Insertar reporte
    $stmt = $pdo->prepare("INSERT INTO reports (titulo, descripcion, categoria, prioridad, latitud, longitud, ciudadano_nombre, ciudadano_email, telefono, usuario_id, estado, fecha_reporte) VALUES (?, ?, ?, 'media', ?, ?, ?, ?, ?, ?, 'pendiente', NOW())");
    $stmt->execute([$titulo, $descripcion, $categoria, $latitud, $longitud, $nombre, $email, $telefono, $usuario_id]);
    $reporteId = $pdo->lastInsertId();
    
    // Guardar fotos
    $fotosSubidas = 0;
    if(isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
        $files = $_FILES['fotos'];
        for($i = 0; $i < count($files['name']); $i++) {
            if($files['error'][$i] === UPLOAD_ERR_OK) {
                $extension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                // Generar nombre único para la imagen
                $nombreArchivo = 'reporte_' . $reporteId . '_' . time() . '_' . $i . '.' . $extension;
                $rutaArchivo = $uploadDir . $nombreArchivo;
                
                if(move_uploaded_file($files['tmp_name'][$i], $rutaArchivo)) {
                    // Guardar ruta relativa desde la raíz del proyecto
                    $rutaRelativa = 'img/reportes/' . $nombreArchivo;
                    $stmtFoto = $pdo->prepare("INSERT INTO reporte_fotos (reporte_id, foto_url, fecha_subida) VALUES (?, ?, NOW())");
                    $stmtFoto->execute([$reporteId, $rutaRelativa]);
                    $fotosSubidas++;
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true, 
        'id' => $reporteId, 
        'fotos' => $fotosSubidas,
        'ubicacion' => $latitud ? 'guardada' : 'no guardada'
    ]);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>