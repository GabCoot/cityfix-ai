<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    // ============================================
    // RUTAS
    // ============================================
    case 'getRutas':
        $stmt = $pdo->query("SELECT * FROM basura_rutas ORDER BY hora_inicio");
        $rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'rutas' => $rutas]);
        break;
        
    case 'getRuta':
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM basura_rutas WHERE id = ?");
        $stmt->execute([$id]);
        $ruta = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'ruta' => $ruta]);
        break;
        
    case 'guardarRuta':
        $id = $_POST['id'] ?? 0;
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'] ?? '';
        $dias = implode(',', $_POST['dias'] ?? []);
        $hora_inicio = $_POST['hora_inicio'];
        $hora_fin = $_POST['hora_fin'];
        $color = $_POST['color'] ?? '#10b981';
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        if($id) {
            $stmt = $pdo->prepare("UPDATE basura_rutas SET nombre=?, descripcion=?, dias=?, hora_inicio=?, hora_fin=?, color=?, activo=? WHERE id=?");
            $stmt->execute([$nombre, $descripcion, $dias, $hora_inicio, $hora_fin, $color, $activo, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO basura_rutas (nombre, descripcion, dias, hora_inicio, hora_fin, color, activo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $dias, $hora_inicio, $hora_fin, $color, $activo]);
        }
        echo json_encode(['success' => true]);
        break;
        
    case 'eliminarRuta':
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM basura_rutas WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;
        
    // ============================================
    // COLONIAS
    // ============================================
    case 'getColonias':
        $ruta_id = $_GET['ruta_id'];
        $stmt = $pdo->prepare("SELECT * FROM basura_colonias WHERE ruta_id = ? ORDER BY nombre");
        $stmt->execute([$ruta_id]);
        $colonias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'colonias' => $colonias]);
        break;
        
    case 'guardarColonia':
        $id = $_POST['id'] ?? 0;
        $ruta_id = $_POST['ruta_id'];
        $nombre = $_POST['nombre'];
        
        if($id) {
            $stmt = $pdo->prepare("UPDATE basura_colonias SET nombre=?, ruta_id=? WHERE id=?");
            $stmt->execute([$nombre, $ruta_id, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO basura_colonias (ruta_id, nombre) VALUES (?, ?)");
            $stmt->execute([$ruta_id, $nombre]);
        }
        echo json_encode(['success' => true]);
        break;
        
    case 'eliminarColonia':
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM basura_colonias WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;
        
    // ============================================
    // SUSCRIPTORES
    // ============================================
    case 'getSuscriptores':
        $colonia_id = $_GET['colonia_id'];
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM basura_suscripciones WHERE colonia_id = ? AND activo = 1");
        $stmt->execute([$colonia_id]);
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'total' => $total['total']]);
        break;
        
    case 'suscribir':
        $telefono = $_POST['telefono'];
        $colonia_id = $_POST['colonia_id'];
        $colonia_nombre = $_POST['colonia_nombre'];
        
        // Verificar si ya existe
        $stmt = $pdo->prepare("SELECT id FROM basura_suscripciones WHERE telefono = ? AND colonia_id = ?");
        $stmt->execute([$telefono, $colonia_id]);
        
        if($stmt->fetch()) {
            echo json_encode(['success' => true, 'message' => 'Ya estás suscrito']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO basura_suscripciones (telefono, colonia_id, colonia_nombre) VALUES (?, ?, ?)");
            $stmt->execute([$telefono, $colonia_id, $colonia_nombre]);
            echo json_encode(['success' => true, 'message' => 'Suscripción exitosa']);
        }
        break;
        
    case 'notificarRuta':
        $ruta_id = $_POST['ruta_id'];
        
        // Obtener información de la ruta
        $stmt = $pdo->prepare("SELECT * FROM basura_rutas WHERE id = ?");
        $stmt->execute([$ruta_id]);
        $ruta = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Obtener suscriptores de las colonias de esta ruta
        $stmt = $pdo->prepare("
            SELECT DISTINCT s.telefono, s.colonia_nombre 
            FROM basura_suscripciones s
            JOIN basura_colonias c ON c.id = s.colonia_id
            WHERE c.ruta_id = ? AND s.activo = 1
        ");
        $stmt->execute([$ruta_id]);
        $suscriptores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Aquí enviarías notificaciones (SMS, WhatsApp, o Push)
        // Por ahora solo registramos el intento
        $enviados = count($suscriptores);
        
        // Guardar historial
        $hoy = date('Y-m-d');
        $stmt = $pdo->prepare("INSERT INTO basura_collection_history (ruta_id, collection_date, status) VALUES (?, ?, 'completado') ON DUPLICATE KEY UPDATE status = 'completado'");
        $stmt->execute([$ruta_id, $hoy]);
        
        echo json_encode(['success' => true, 'enviados' => $enviados]);
        break;
        
    case 'getStats':
        $totalRutas = $pdo->query("SELECT COUNT(*) as total FROM basura_rutas WHERE activo = 1")->fetch()['total'];
        $totalColonias = $pdo->query("SELECT COUNT(*) as total FROM basura_colonias")->fetch()['total'];
        $totalSuscriptores = $pdo->query("SELECT COUNT(*) as total FROM basura_suscripciones WHERE activo = 1")->fetch()['total'];
        
        echo json_encode([
            'success' => true,
            'total_rutas' => $totalRutas,
            'total_colonias' => $totalColonias,
            'total_suscriptores' => $totalSuscriptores
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
}
?>