<?php
require_once 'config/conexion.php';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-6 fw-bold" style="color: #1e293b;">Panel de Control</h1>
                <p class="text-muted">Bienvenido de vuelta</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newReportModal">
                <i class="fas fa-plus"></i> Nuevo Reporte
            </button>
        </div>

        <?php
        // Verificar conexión y obtener datos
        try {
            $total = $pdo->query("SELECT COUNT(*) as total FROM reports")->fetch()['total'];
            $resueltos = $pdo->query("SELECT COUNT(*) as resueltos FROM reports WHERE estado = 'resuelto'")->fetch()['resueltos'];
            $pendientes = $pdo->query("SELECT COUNT(*) as pendientes FROM reports WHERE estado = 'pendiente'")->fetch()['pendientes'];
            $en_proceso = $pdo->query("SELECT COUNT(*) as en_proceso FROM reports WHERE estado = 'en_proceso'")->fetch()['en_proceso'];
            $porcentaje = $total > 0 ? round(($resueltos / $total) * 100) : 0;
        } catch(PDOException $e) {
            echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
            $total = $resueltos = $pendientes = $en_proceso = 0;
        }
        ?>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-icon"><i class="fas fa-flag-checkered"></i></div>
                            <div class="stat-value"><?php echo $total; ?></div>
                            <div class="stat-label">Total Reportes</div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary">Total</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-value"><?php echo $resueltos; ?></div>
                            <div class="stat-label">Resueltos</div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success"><?php echo $porcentaje; ?>%</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-icon"><i class="fas fa-clock"></i></div>
                            <div class="stat-value"><?php echo $pendientes + $en_proceso; ?></div>
                            <div class="stat-label">Activos</div>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-warning"><?php echo $pendientes; ?> pend.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-icon"><i class="fas fa-tachometer-alt"></i></div>
                            <div class="stat-value"><?php echo $total > 0 ? round(($pendientes/$total)*100) : 0; ?>%</div>
                            <div class="stat-label">Tasa Pendiente</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="stat-card">
                    <h5 class="mb-3">Reportes por Categoría</h5>
                    <canvas id="categoriasChart" height="250"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <h5 class="mb-3">Reportes por Prioridad</h5>
                    <canvas id="prioridadChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- MAPA MEJORADO - UBICACIÓN AUTOMÁTICA -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="stat-card" style="position: relative;">
                    <h5 class="mb-3">
                        <i class="fas fa-map-marked-alt text-danger"></i> Mapa de Incidentes en Tiempo Real
                        <div class="float-end">
                            <span id="ubicacionStatus" class="badge bg-secondary me-2">
                                <i class="fas fa-sync-alt fa-spin"></i> Detectando ubicación...
                            </span>
                            <button id="btnMiUbicacion" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-location-dot"></i> Mi ubicación
                            </button>
                        </div>
                    </h5>
                    <div id="map" style="height: 500px; border-radius: 12px;"></div>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="stat-card">
            <h5 class="mb-3">Últimos Reportes</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Ciudadano</th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Ubicación</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM reports ORDER BY fecha_reporte DESC LIMIT 20");
                            while($row = $stmt->fetch()):
                                $badgeClass = $row['prioridad'] == 'alta' ? 'badge-alta' : ($row['prioridad'] == 'media' ? 'badge-media' : 'badge-baja');
                                $estadoClass = $row['estado'] == 'pendiente' ? 'badge-warning' : ($row['estado'] == 'en_proceso' ? 'badge-info' : 'badge-success');
                                $ubicacion = ($row['latitud'] && $row['longitud'] && $row['latitud'] != 0) ? '📍 Sí' : '❌ No';
                        ?>
                        <tr>
                            <td class="fw-bold">#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['ciudadano_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $row['categoria'])); ?></td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($row['prioridad']); ?></span></td>
                            <td><span class="badge <?php echo $estadoClass; ?>"><?php echo ucfirst($row['estado']); ?></span></td>
                            <td><?php echo $ubicacion; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_reporte'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="verDetalle(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="cambiarEstado(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        } catch(PDOException $e) {
                            echo '<tr><td colspan="9" class="text-center text-danger">Error al cargar reportes: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detalle del Reporte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado -->
<div class="modal fade" id="estadoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Cambiar Estado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="estadoReporteId">
                <label class="form-label fw-bold">Nuevo estado:</label>
                <select id="nuevoEstado" class="form-select">
                    <option value="pendiente">⏳ Pendiente</option>
                    <option value="en_proceso">🔄 En Proceso</option>
                    <option value="resuelto">✅ Resuelto</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="actualizarEstado()">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- MEJOR MAPA: Usando Leaflet con capa satelital y calles -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Control de geolocalización mejorado -->
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<script>
// Variables globales
let map;
let userMarker = null;
let reportsMarkers = [];
let ubicacionObtenida = false;
let watchId = null;

// Datos para gráficas
<?php
$categorias = $pdo->query("SELECT categoria, COUNT(*) as total FROM reports GROUP BY categoria")->fetchAll();
$prioridades = $pdo->query("SELECT prioridad, COUNT(*) as total FROM reports GROUP BY prioridad")->fetchAll();
$cats = ['bache'=>0, 'fuga_agua'=>0, 'basura'=>0, 'luminaria'=>0, 'otros'=>0];
$prios = ['alta'=>0, 'media'=>0, 'baja'=>0];
foreach($categorias as $c) $cats[$c['categoria']] = $c['total'];
foreach($prioridades as $p) $prios[$p['prioridad']] = $p['total'];
?>

// Inicializar gráficas
new Chart(document.getElementById('categoriasChart'), {
    type: 'doughnut',
    data: {
        labels: ['🚧 Baches', '💧 Fugas', '🗑️ Basura', '💡 Luminarias', '📌 Otros'],
        datasets: [{
            data: [<?php echo $cats['bache']; ?>, <?php echo $cats['fuga_agua']; ?>, <?php echo $cats['basura']; ?>, <?php echo $cats['luminaria']; ?>, <?php echo $cats['otros']; ?>],
            backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('prioridadChart'), {
    type: 'bar',
    data: {
        labels: ['🔴 Alta', '🟡 Media', '🟢 Baja'],
        datasets: [{
            label: 'Reportes',
            data: [<?php echo $prios['alta']; ?>, <?php echo $prios['media']; ?>, <?php echo $prios['baja']; ?>],
            backgroundColor: ['#ef4444', '#f59e0b', '#10b981']
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

// ============================================
// MAPA MEJORADO - MÚLTIPLES CAPAS
// ============================================
function iniciarMapa() {
    // Crear el mapa con una vista mundial inicial
    map = L.map('map').setView([20, -90], 5);
    
    // CAPA 1: Mapa de calles estilo Google (más bonito)
    const streetLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19,
        minZoom: 1
    });
    
    // CAPA 2: Mapa satelital (opcional)
    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
        maxZoom: 18
    });
    
    // CAPA 3: Mapa oscuro (para noche)
    const darkLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19
    });
    
    // Agregar capa principal por defecto
    streetLayer.addTo(map);
    
    // Control de capas para que el usuario elija
    const baseMaps = {
        "🗺️ Mapa Calles": streetLayer,
        "🛰️ Satelital": satelliteLayer,
        "🌙 Modo Oscuro": darkLayer
    };
    
    L.control.layers(baseMaps).addTo(map);
    
    // Escala en el mapa
    L.control.scale({ metric: true, imperial: false, position: 'bottomleft' }).addTo(map);
    
    // Actualizar estado
    actualizarEstadoUbicacion('info', '📍 Detectando ubicación automáticamente...');
    
    // OBTENER UBICACIÓN AUTOMÁTICA SIN INTERVENCIÓN HUMANA
    obtenerUbicacionAutomatica();
}

// ============================================
// OBTENER UBICACIÓN AUTOMÁTICA (SILENCIOSA)
// ============================================
function obtenerUbicacionAutomatica() {
    if (navigator.geolocation) {
        // Opciones para obtener ubicación silenciosamente
        const options = {
            enableHighAccuracy: true,  // Alta precisión
            timeout: 5000,             // 5 segundos máximo
            maximumAge: 300000         // Cache de 5 minutos (no pide permiso si ya lo tiene)
        };
        
        // Intentar obtener ubicación
        navigator.geolocation.getCurrentPosition(
            function(position) {
                // ÉXITO - Ubicación obtenida automáticamente
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                const accuracy = position.coords.accuracy;
                
                ubicacionObtenida = true;
                
                // Centrar mapa con zoom dinámico según precisión
                let zoom = 15;
                if (accuracy > 500) zoom = 13;
                if (accuracy > 1000) zoom = 11;
                
                map.setView([userLat, userLng], zoom);
                
                // Agregar marcador de usuario mejorado
                agregarMarcadorUsuario(userLat, userLng, accuracy);
                
                // Mostrar precisión
                let precisionTexto = '';
                if (accuracy < 20) precisionTexto = 'Excelente (GPS)';
                else if (accuracy < 100) precisionTexto = 'Muy buena';
                else if (accuracy < 500) precisionTexto = 'Buena';
                else precisionTexto = 'Aproximada';
                
                actualizarEstadoUbicacion('success', `✅ Ubicación automática: ${precisionTexto} (±${Math.round(accuracy)}m)`);
                
                // Cargar reportes
                cargarReportesMapa();
                
                // Iniciar seguimiento en segundo plano (opcional)
                iniciarSeguimientoUbicacion();
            },
            function(error) {
                // ERROR - Pero no mostramos alertas molestas
                console.warn('Error automático:', error);
                
                let errorSilencioso = '';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorSilencioso = 'Permiso denegado - usa el botón "Mi ubicación"';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorSilencioso = 'GPS no disponible - intentando nuevamente...';
                        setTimeout(() => obtenerUbicacionAutomatica(), 3000);
                        break;
                    case error.TIMEOUT:
                        errorSilencioso = 'Tiempo agotado - reintentando...';
                        setTimeout(() => obtenerUbicacionAutomatica(), 2000);
                        break;
                }
                
                actualizarEstadoUbicacion('warning', `⚠️ ${errorSilencioso}`);
                cargarReportesMapa();
            },
            options
        );
    } else {
        actualizarEstadoUbicacion('error', '❌ Navegador no soporta geolocalización');
        cargarReportesMapa();
    }
}

// ============================================
// SEGUIMIENTO DE UBICACIÓN EN TIEMPO REAL
// ============================================
function iniciarSeguimientoUbicacion() {
    if (watchId) {
        navigator.geolocation.clearWatch(watchId);
    }
    
    watchId = navigator.geolocation.watchPosition(
        function(position) {
            // Actualizar marcador si el usuario se mueve
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            
            if (userMarker) {
                userMarker.setLatLng([userLat, userLng]);
                // Solo actualizar vista si el mapa no está siendo interactuado manualmente
                if (!map._interacting) {
                    map.setView([userLat, userLng], map.getZoom());
                }
            }
        },
        function(error) {
            console.log('Seguimiento detenido:', error);
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 10000
        }
    );
}

// ============================================
// MARCADOR DE USUARIO MEJORADO
// ============================================
function agregarMarcadorUsuario(lat, lng, accuracy) {
    if (userMarker) {
        map.removeLayer(userMarker);
    }
    
    // Marcador principal
    const userIcon = L.divIcon({
        html: `<div style="background-color: #3b82f6; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 0 2px rgba(59,130,246,0.5); position: relative;">
                    <div style="position: absolute; top: 6px; left: 6px; width: 8px; height: 8px; background-color: white; border-radius: 50%;"></div>
               </div>`,
        iconSize: [26, 26],
        className: 'user-location-marker'
    });
    
    userMarker = L.marker([lat, lng], { icon: userIcon })
        .addTo(map)
        .bindPopup(`
            <div style="text-align: center;">
                <strong>📍 Tu ubicación actual</strong><br>
                <small>Precisión: ±${Math.round(accuracy)} metros</small><br>
                <small>Lat: ${lat.toFixed(6)} | Lng: ${lng.toFixed(6)}</small>
            </div>
        `)
        .openPopup();
    
    // Círculo de precisión
    L.circle([lat, lng], {
        radius: accuracy,
        color: '#3b82f6',
        fillColor: '#3b82f6',
        fillOpacity: 0.1,
        weight: 1
    }).addTo(map);
}

// ============================================
// ACTUALIZAR ESTADO DE UBICACIÓN
// ============================================
function actualizarEstadoUbicacion(tipo, mensaje) {
    const statusSpan = $('#ubicacionStatus');
    
    let icono = '';
    let color = '';
    
    switch(tipo) {
        case 'success':
            icono = '<i class="fas fa-check-circle"></i>';
            color = 'bg-success';
            break;
        case 'warning':
            icono = '<i class="fas fa-exclamation-triangle"></i>';
            color = 'bg-warning text-dark';
            break;
        case 'error':
            icono = '<i class="fas fa-times-circle"></i>';
            color = 'bg-danger';
            break;
        default:
            icono = '<i class="fas fa-sync-alt fa-spin"></i>';
            color = 'bg-secondary';
    }
    
    statusSpan.html(`${icono} ${mensaje}`);
    statusSpan.removeClass('bg-secondary bg-success bg-warning bg-danger');
    statusSpan.addClass(color);
    
    // Auto-ocultar después de 8 segundos
    setTimeout(() => {
        if (!ubicacionObtenida && tipo !== 'success') {
            // Mantener si no hay ubicación
        } else if (tipo === 'success') {
            setTimeout(() => {
                statusSpan.fadeOut();
                setTimeout(() => statusSpan.fadeIn().html('<i class="fas fa-map-marker-alt"></i> Ubicación activa'), 3000);
            }, 5000);
        }
    }, 8000);
}

// ============================================
// CARGAR REPORTES EN EL MAPA
// ============================================
function cargarReportesMapa() {
    reportsMarkers.forEach(marker => map.removeLayer(marker));
    reportsMarkers = [];
    
    <?php
    $stmt = $pdo->query("SELECT id, titulo, descripcion, latitud, longitud, prioridad, estado, categoria FROM reports WHERE latitud IS NOT NULL AND latitud != 0");
    $hayReportes = false;
    while($row = $stmt->fetch()):
        $hayReportes = true;
        $colorIcon = $row['prioridad'] == 'alta' ? '#ef4444' : ($row['prioridad'] == 'media' ? '#f59e0b' : '#10b981');
        $iconHtml = $row['estado'] == 'resuelto' ? '✅' : '⚠️';
    ?>
    var reportIcon<?php echo $row['id']; ?> = L.divIcon({
        html: `<div style="background-color: <?php echo $colorIcon; ?>; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.3); transition: all 0.2s;">
                    <div style="position: absolute; top: 3px; left: 3px; width: 8px; height: 8px; background-color: rgba(255,255,255,0.5); border-radius: 50%;"></div>
               </div>`,
        iconSize: [18, 18],
        className: 'report-marker'
    });
    
    var marker<?php echo $row['id']; ?> = L.marker([<?php echo $row['latitud']; ?>, <?php echo $row['longitud']; ?>], { icon: reportIcon<?php echo $row['id']; ?> })
        .addTo(map)
        .bindPopup(`
            <div style="min-width: 250px; font-family: sans-serif;">
                <div style="border-left: 4px solid <?php echo $colorIcon; ?>; padding-left: 10px;">
                    <strong style="color: <?php echo $colorIcon; ?>;">📌 <?php echo addslashes($row['titulo']); ?></strong>
                </div>
                <small style="color: #666;"><?php echo substr(addslashes($row['descripcion']), 0, 100); ?>...</small><br>
                <div class="mt-2">
                    <span class="badge" style="background-color: <?php echo $colorIcon; ?>;"><?php echo ucfirst($row['prioridad']); ?></span>
                    <span class="badge bg-secondary"><?php echo ucfirst($row['estado']); ?></span>
                </div>
                <button class="btn btn-sm btn-primary mt-2 w-100" onclick="verDetalle(<?php echo $row['id']; ?>)">
                    👁️ Ver detalles completos
                </button>
            </div>
        `);
    
    reportsMarkers.push(marker<?php echo $row['id']; ?>);
    <?php endwhile; ?>
    
    if (<?php echo $hayReportes ? 'false' : 'true'; ?>) {
        console.log('No hay reportes con ubicación para mostrar');
    }
}

// ============================================
// CENTRAR EN MI UBICACIÓN (MANUAL)
// ============================================
function centrarEnMiUbicacion() {
    actualizarEstadoUbicacion('info', '📍 Obteniendo tu ubicación...');
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                
                map.setView([userLat, userLng], 17);
                
                actualizarEstadoUbicacion('success', '✅ Centrado en tu ubicación');
                
                setTimeout(() => {
                    $('#ubicacionStatus').fadeOut();
                    setTimeout(() => $('#ubicacionStatus').fadeIn().html('<i class="fas fa-map-marker-alt"></i> Listo'), 2000);
                }, 3000);
            },
            function(error) {
                let errorMsg = 'No se pudo obtener ubicación';
                if(error.code === error.PERMISSION_DENIED) {
                    errorMsg = 'Permiso denegado - activa ubicación en tu navegador';
                }
                actualizarEstadoUbicacion('error', errorMsg);
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }
}

// ============================================
// FUNCIONES DE REPORTES
// ============================================
let reporteIdActual = null;

function verDetalle(id) {
    $.get('api/obtener_reporte.php', { id: id }, function(data) {
        if(data.success) {
            const r = data.reporte;
            const fotos = data.fotos || [];
            const ubicacion = (r.latitud && r.longitud && r.latitud != 0) ? 
                `<iframe width="100%" height="250" frameborder="0" style="border:0; border-radius: 12px;" 
                src="https://www.openstreetmap.org/export/embed.html?bbox=${r.longitud-0.005},${r.latitud-0.005},${r.longitud+0.005},${r.latitud+0.005}&layer=mapnik&marker=${r.latitud},${r.longitud}"></iframe>
                <div class="text-center mt-1"><small>📌 Lat: ${r.latitud} | Lng: ${r.longitud}</small></div>` : 
                '<div class="alert alert-warning">📍 No se especificó ubicación para este reporte</div>';
            
            let fotosHtml = '';
            if(fotos.length > 0) {
                fotosHtml = '<div class="mb-3"><strong>📸 Fotografías:</strong><div class="d-flex gap-2 flex-wrap mt-2">';
                fotos.forEach(foto => {
                    fotosHtml += `<img src="../${foto.foto_url}" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; cursor: pointer;" onclick="window.open('../${foto.foto_url}')">`;
                });
                fotosHtml += '</div></div>';
            }
            
            $('#detalleContent').html(`
                <div class="card border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">${escapeHtml(r.titulo)}</h5>
                        <p class="card-text">${escapeHtml(r.descripcion)}</p>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="bg-light p-2 rounded"><strong>👤 Ciudadano:</strong> ${escapeHtml(r.ciudadano_nombre)}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="bg-light p-2 rounded"><strong>📧 Email:</strong> ${escapeHtml(r.ciudadano_email) || 'No especificado'}</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="bg-light p-2 rounded"><strong>📂 Categoría:</strong> ${r.categoria.replace('_', ' ')}</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="bg-light p-2 rounded"><strong>⚠️ Prioridad:</strong> <span class="badge ${r.prioridad == 'alta' ? 'badge-alta' : (r.prioridad == 'media' ? 'badge-media' : 'badge-baja')}">${r.prioridad}</span></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="bg-light p-2 rounded"><strong>📊 Estado:</strong> <span class="badge ${r.estado == 'resuelto' ? 'badge-success' : (r.estado == 'en_proceso' ? 'badge-info' : 'badge-warning')}">${r.estado}</span></div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="bg-light p-2 rounded"><strong>📅 Fecha:</strong> ${new Date(r.fecha_reporte).toLocaleString()}</div>
                            </div>
                            <div class="col-12 mt-2">
                                <strong>📍 Ubicación en mapa:</strong>
                                ${ubicacion}
                            </div>
                            ${fotosHtml}
                        </div>
                    </div>
                </div>
            `);
            $('#detalleModal').modal('show');
        } else {
            alert('❌ Error al cargar el reporte');
        }
    }, 'json');
}

function cambiarEstado(id) {
    reporteIdActual = id;
    $('#estadoReporteId').val(id);
    $('#estadoModal').modal('show');
}

function actualizarEstado() {
    const id = reporteIdActual;
    const estado = $('#nuevoEstado').val();
    
    $.post('api/cambiar_estado.php', { id: id, estado: estado }, function(data) {
        if(data.success) {
            alert('✅ Estado actualizado');
            location.reload();
        } else {
            alert('❌ Error al actualizar');
        }
    }, 'json');
}

function escapeHtml(text) {
    if(!text) return '';
    return $('<div>').text(text).html();
}

// ============================================
// INICIALIZACIÓN
// ============================================
$(document).ready(function() {
    iniciarMapa();
    
    $('#btnMiUbicacion').on('click', function() {
        centrarEnMiUbicacion();
    });
});
</script>

<!-- Estilos mejorados -->
<style>
.user-location-marker {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    70% {
        transform: scale(1.3);
        opacity: 0.7;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.report-marker {
    transition: transform 0.2s, filter 0.2s;
}

.report-marker:hover {
    transform: scale(1.3);
    filter: drop-shadow(0 0 4px rgba(0,0,0,0.3));
    cursor: pointer;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.badge-alta {
    background-color: #fee2e2;
    color: #ef4444;
    padding: 6px 12px;
    border-radius: 20px;
}

.badge-media {
    background-color: #fed7aa;
    color: #f59e0b;
    padding: 6px 12px;
    border-radius: 20px;
}

.badge-baja {
    background-color: #d1fae5;
    color: #10b981;
    padding: 6px 12px;
    border-radius: 20px;
}

.badge-warning {
    background-color: #fef3c7;
    color: #d97706;
    padding: 6px 12px;
    border-radius: 20px;
}

.badge-info {
    background-color: #dbeafe;
    color: #2563eb;
    padding: 6px 12px;
    border-radius: 20px;
}

.badge-success {
    background-color: #d1fae5;
    color: #059669;
    padding: 6px 12px;
    border-radius: 20px;
}

/* Animación de carga del mapa */
#map {
    transition: all 0.3s ease;
}

/* Scrollbar personalizado */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<?php include 'includes/footer.php'; ?>