<?php
require_once 'config/conexion.php';
session_start();

// Procesar guardado de configuración
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Guardar logo si se subió
        if (isset($_FILES['empresa_logo']) && $_FILES['empresa_logo']['error'] == 0) {
            $uploadDir = 'assets/img/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $extension = pathinfo($_FILES['empresa_logo']['name'], PATHINFO_EXTENSION);
            $nombreArchivo = 'logo_empresa.' . $extension;
            $rutaArchivo = $uploadDir . $nombreArchivo;
            
            if (move_uploaded_file($_FILES['empresa_logo']['tmp_name'], $rutaArchivo)) {
                // Guardar ruta en BD
                $stmt = $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'empresa_logo'");
                $stmt->execute(['assets/img/' . $nombreArchivo]);
                $mensaje = "Logo actualizado correctamente";
            }
        }
        
        // Guardar configuración de texto
        $campos = ['empresa_nombre', 'email_contacto', 'tiempo_respuesta_dias', 'color_primario', 'color_secundario'];
        foreach ($campos as $campo) {
            if (isset($_POST[$campo])) {
                $stmt = $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?");
                $stmt->execute([$_POST[$campo], $campo]);
            }
        }
        
        // Guardar checkboxes
        $checkboxes = ['modo_oscuro', 'notificaciones_email'];
        foreach ($checkboxes as $checkbox) {
            $valor = isset($_POST[$checkbox]) ? '1' : '0';
            $stmt = $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?");
            $stmt->execute([$valor, $checkbox]);
        }
        
        // Guardar tema
        if (isset($_POST['tema_color'])) {
            $stmt = $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'tema_color'");
            $stmt->execute([$_POST['tema_color']]);
        }
        
        $mensaje = "✅ Configuración guardada correctamente";
        
        // Recargar página para aplicar cambios
        header("Location: configuracion.php?success=1");
        exit;
        
    } catch(Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// Obtener configuración actual
$config = [];
$stmt = $pdo->query("SELECT clave, valor FROM configuracion");
while ($row = $stmt->fetch()) {
    $config[$row['clave']] = $row['valor'];
}

// Valores por defecto
$empresa_nombre = $config['empresa_nombre'] ?? 'CityFix AI';
$empresa_logo = $config['empresa_logo'] ?? '';
$tema_color = $config['tema_color'] ?? 'azul';
$modo_oscuro = ($config['modo_oscuro'] ?? '0') == '1';
$color_primario = $config['color_primario'] ?? '#2563eb';
$color_secundario = $config['color_secundario'] ?? '#10b981';
$notificaciones_email = ($config['notificaciones_email'] ?? '1') == '1';
$email_contacto = $config['email_contacto'] ?? 'soporte@cityfix.ai';
$tiempo_respuesta = $config['tiempo_respuesta_dias'] ?? '3';

// Aplicar modo oscuro si está activado
if ($modo_oscuro) {
    echo "<script>document.documentElement.setAttribute('data-theme', 'dark');</script>";
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $modo_oscuro ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - CityFix AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: <?php echo $color_primario; ?>;
            --secondary-color: <?php echo $color_secundario; ?>;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-color: #1e293b;
            --border-color: #e2e8f0;
        }
        
        [data-theme="dark"] {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --text-color: #e2e8f0;
            --border-color: #334155;
        }
        
        body {
            background: var(--bg-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, #1e3a5f 0%, #0f2b42 100%);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        
        .sidebar-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar-header h2 {
            color: white;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .sidebar-nav {
            flex: 1;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s;
            margin-bottom: 0.5rem;
        }
        
        .nav-link:hover, .nav-link.active {
            background: var(--primary-color);
            color: white;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }
        
        /* Cards */
        .config-card {
            background: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .config-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
            display: inline-block;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
        }
        
        .logo-preview {
            width: 150px;
            height: 150px;
            object-fit: contain;
            border-radius: 1rem;
            border: 2px solid var(--border-color);
            padding: 0.5rem;
            background: white;
        }
        
        .color-preview {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: inline-block;
            margin-right: 10px;
            vertical-align: middle;
            border: 1px solid var(--border-color);
        }
        
        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border: none;
            border-radius: 0.5rem;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <?php if($empresa_logo && file_exists($empresa_logo)): ?>
            <img src="<?php echo $empresa_logo; ?>" alt="Logo" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 10px;">
        <?php else: ?>
            <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                <i class="fas fa-city fa-3x" style="color: var(--primary-color);"></i>
            </div>
        <?php endif; ?>
        <h2><span class="gradient-text">CityFix</span> AI</h2>
        <p class="small text-white-50">Dashboard Municipal</p>
    </div>
    
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-link">
            <i class="fas fa-chart-line"></i>
            <span>Panel Principal</span>
        </a>
        <a href="reportes_activos.php" class="nav-link">
            <i class="fas fa-map-marker-alt"></i>
            <span>Reportes Activos</span>
        </a>
        <a href="historial.php" class="nav-link">
            <i class="fas fa-history"></i>
            <span>Historial</span>
        </a>
        <a href="estadisticas.php" class="nav-link">
            <i class="fas fa-chart-bar"></i>
            <span>Estadísticas</span>
        </a>
        <a href="configuracion.php" class="nav-link active">
            <i class="fas fa-cog"></i>
            <span>Configuración</span>
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info text-white">
            <div class="user-avatar">
                <i class="fas fa-user-circle fa-2x"></i>
            </div>
            <div>
                <div class="user-name">Administrador</div>
                <div class="user-email small text-white-50">admin@cityfix.ai</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-6 fw-bold"><i class="fas fa-cog text-primary"></i> Configuración del Sistema</h1>
                <p class="text-muted">Personaliza tu dashboard a tu gusto</p>
            </div>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> ✅ Configuración guardada correctamente
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <!-- Sección: Identidad de la Empresa -->
            <div class="config-card">
                <h3 class="config-title"><i class="fas fa-building"></i> Identidad de la Empresa</h3>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre de la empresa</label>
                        <input type="text" name="empresa_nombre" class="form-control" value="<?php echo htmlspecialchars($empresa_nombre); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email de contacto</label>
                        <input type="email" name="email_contacto" class="form-control" value="<?php echo htmlspecialchars($email_contacto); ?>">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Logo de la empresa</label>
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <?php if($empresa_logo && file_exists($empresa_logo)): ?>
                                    <img src="<?php echo $empresa_logo; ?>" class="logo-preview" id="logoPreview" alt="Logo actual">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/150x150?text=Sin+Logo" class="logo-preview" id="logoPreview" alt="Logo">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <input type="file" name="empresa_logo" class="form-control" accept="image/*" onchange="previewLogo(this)">
                                <small class="text-muted">Formatos: JPG, PNG, GIF. Tamaño recomendado: 200x200px</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sección: Apariencia -->
            <div class="config-card">
                <h3 class="config-title"><i class="fas fa-palette"></i> Apariencia</h3>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Modo oscuro</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="modo_oscuro" class="form-check-input" id="modoOscuro" value="1" <?php echo $modo_oscuro ? 'checked' : ''; ?> onchange="toggleDarkMode(this)">
                            <label class="form-check-label" for="modoOscuro">Activar modo oscuro</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tema de color</label>
                        <select name="tema_color" class="form-select" onchange="cambiarTema(this.value)">
                            <option value="azul" <?php echo $tema_color == 'azul' ? 'selected' : ''; ?>>🔵 Azul (Default)</option>
                            <option value="verde" <?php echo $tema_color == 'verde' ? 'selected' : ''; ?>>🟢 Verde</option>
                            <option value="morado" <?php echo $tema_color == 'morado' ? 'selected' : ''; ?>>🟣 Morado</option>
                            <option value="rojo" <?php echo $tema_color == 'rojo' ? 'selected' : ''; ?>>🔴 Rojo</option>
                            <option value="naranja" <?php echo $tema_color == 'naranja' ? 'selected' : ''; ?>>🟠 Naranja</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Color primario</label>
                        <div class="d-flex align-items-center">
                            <div class="color-preview" style="background-color: <?php echo $color_primario; ?>"></div>
                            <input type="color" name="color_primario" class="form-control w-auto" value="<?php echo $color_primario; ?>">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Color secundario</label>
                        <div class="d-flex align-items-center">
                            <div class="color-preview" style="background-color: <?php echo $color_secundario; ?>"></div>
                            <input type="color" name="color_secundario" class="form-control w-auto" value="<?php echo $color_secundario; ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sección: Notificaciones -->
            <div class="config-card">
                <h3 class="config-title"><i class="fas fa-bell"></i> Notificaciones</h3>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="notificaciones_email" class="form-check-input" id="notificacionesEmail" value="1" <?php echo $notificaciones_email ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="notificacionesEmail">Recibir notificaciones por email</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tiempo de respuesta (días)</label>
                        <input type="number" name="tiempo_respuesta_dias" class="form-control" value="<?php echo $tiempo_respuesta; ?>" min="1" max="30">
                        <small class="text-muted">Tiempo objetivo para resolver reportes</small>
                    </div>
                </div>
            </div>
            
            <!-- Sección: Información del Sistema -->
            <div class="config-card">
                <h3 class="config-title"><i class="fas fa-info-circle"></i> Información del Sistema</h3>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Versión del sistema</label>
                        <input type="text" class="form-control" value="CityFix AI v2.0.0" readonly disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Total de reportes</label>
                        <?php $total = $pdo->query("SELECT COUNT(*) FROM reports")->fetch()['COUNT(*)']; ?>
                        <input type="text" class="form-control" value="<?php echo $total; ?> reportes registrados" readonly disabled>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Base de datos</label>
                        <input type="text" class="form-control" value="MySQL - Conexión activa ✅" readonly disabled>
                    </div>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Guardar Configuración
                </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="location.reload()">
                    <i class="fas fa-undo"></i> Cancelar
                </button>
                <button type="button" class="btn btn-outline-danger btn-lg" onclick="resetearConfiguracion()">
                    <i class="fas fa-trash"></i> Resetear a valores por defecto
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Previsualizar logo
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Toggle modo oscuro en tiempo real
function toggleDarkMode(checkbox) {
    if(checkbox.checked) {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.body.style.background = '#0f172a';
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
        document.body.style.background = '#f8fafc';
    }
}

// Cambiar tema de color
function cambiarTema(tema) {
    const colores = {
        'azul': { primario: '#2563eb', secundario: '#10b981' },
        'verde': { primario: '#10b981', secundario: '#34d399' },
        'morado': { primario: '#8b5cf6', secundario: '#a78bfa' },
        'rojo': { primario: '#ef4444', secundario: '#f87171' },
        'naranja': { primario: '#f59e0b', secundario: '#fbbf24' }
    };
    
    if(colores[tema]) {
        document.documentElement.style.setProperty('--primary-color', colores[tema].primario);
        document.documentElement.style.setProperty('--secondary-color', colores[tema].secundario);
        document.querySelector('input[name="color_primario"]').value = colores[tema].primario;
        document.querySelector('input[name="color_secundario"]').value = colores[tema].secundario;
        
        // Actualizar preview de colores
        document.querySelectorAll('.color-preview')[0].style.backgroundColor = colores[tema].primario;
        document.querySelectorAll('.color-preview')[1].style.backgroundColor = colores[tema].secundario;
    }
}

// Resetear configuración
function resetearConfiguracion() {
    if(confirm('¿Estás seguro de que quieres resetear toda la configuración a los valores por defecto?')) {
        $.post('api/resetear_config.php', function(data) {
            if(data.success) {
                alert('Configuración reseteada correctamente');
                location.reload();
            } else {
                alert('Error al resetear: ' + data.error);
            }
        }, 'json');
    }
}

// Actualizar colores en tiempo real
document.querySelector('input[name="color_primario"]').addEventListener('change', function() {
    document.documentElement.style.setProperty('--primary-color', this.value);
    document.querySelectorAll('.color-preview')[0].style.backgroundColor = this.value;
});

document.querySelector('input[name="color_secundario"]').addEventListener('change', function() {
    document.documentElement.style.setProperty('--secondary-color', this.value);
    document.querySelectorAll('.color-preview')[1].style.backgroundColor = this.value;
});
</script>

</body>
</html>