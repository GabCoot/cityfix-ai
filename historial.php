<?php
require_once 'config/conexion.php';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="display-6 fw-bold mb-4"><i class="fas fa-history text-primary"></i> Historial de Reportes Resueltos</h1>
        
        <!-- Filtros -->
        <div class="filter-bar mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Buscar</label>
                    <input type="text" id="filtroBusqueda" class="form-control" placeholder="Buscar por ciudadano o título..." onkeyup="cargarHistorial()">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Filtrar por prioridad</label>
                    <select id="filtroPrioridad" class="form-select" onchange="cargarHistorial()">
                        <option value="">Todas</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Filtrar por fecha</label>
                    <input type="date" id="filtroFecha" class="form-control" onchange="cargarHistorial()">
                </div>
            </div>
        </div>

        <!-- Tabla de historial -->
        <div class="stat-card p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Ciudadano</th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Prioridad</th>
                            <th>Ubicación</th>
                            <th>Fecha Reporte</th>
                            <th>Fecha Resolución</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaHistorial">
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Cargando historial...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalle completo con imágenes y mapa -->
<div class="modal fade" id="detalleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detalle del Reporte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p>Cargando detalles...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver mapa completo -->
<div class="modal fade" id="mapaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-map-marked-alt"></i> Ubicación del Reporte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="mapaCompleto" style="height: 400px; border-radius: 12px;"></div>
                <div class="mt-2 text-center">
                    <p id="coordenadasTexto" class="text-muted"></p>
                    <a id="abrirGoogleMaps" href="#" target="_blank" class="btn btn-success btn-sm">
                        <i class="fab fa-google"></i> Abrir en Google Maps
                    </a>
                    <a id="abrirWaze" href="#" target="_blank" class="btn btn-info btn-sm text-white">
                        <i class="fab fa-waze"></i> Abrir en Waze
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let reporteActual = null;

function cargarHistorial() {
    const busqueda = $('#filtroBusqueda').val();
    const prioridad = $('#filtroPrioridad').val();
    const fecha = $('#filtroFecha').val();
    
    console.log('Cargando historial con filtros:', {busqueda, prioridad, fecha});
    
    $.ajax({
        url: 'api/obtener_stats.php',
        type: 'GET',
        data: {
            action: 'historial',
            busqueda: busqueda,
            prioridad: prioridad,
            fecha: fecha
        },
        dataType: 'json',
        success: function(data) {
            console.log('Respuesta del servidor:', data);
            
            if(data.success) {
                if(data.reportes && data.reportes.length > 0) {
                    let html = '';
                    data.reportes.forEach(r => {
                        const prioridadClass = r.prioridad == 'alta' ? 'badge-alta' : (r.prioridad == 'media' ? 'badge-media' : 'badge-baja');
                        const fechaReporte = new Date(r.fecha_reporte).toLocaleString();
                        const fechaResuelto = r.fecha_resuelto ? new Date(r.fecha_resuelto).toLocaleString() : 'No registrada';
                        const ubicacion = (r.latitud && r.longitud && r.latitud != 0) ? '📍 Sí' : '❌ No';
                        
                        html += `
                            <tr>
                                <td class="fw-bold">#${r.id}</td>
                                <td>${escapeHtml(r.ciudadano_nombre)}</td>
                                <td>${escapeHtml(r.titulo)}</td>
                                <td>${r.categoria.replace('_', ' ')}</td>
                                <td><span class="badge ${prioridadClass}">${r.prioridad}</span></td>
                                <td>
                                    ${r.latitud && r.latitud != 0 ? 
                                        `<button class="btn btn-sm btn-outline-info" onclick="verMapa(${r.id})" title="Ver ubicación">
                                            <i class="fas fa-map-marker-alt"></i> Ver mapa
                                        </button>` : 
                                        '❌ No disponible'}
                                </td>
                                <td>${fechaReporte}</td>
                                <td><span class="badge badge-success">${fechaResuelto}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="verDetalleCompleto(${r.id})" title="Ver detalle">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#tablaHistorial').html(html);
                } else {
                    $('#tablaHistorial').html(`
                        <tr><td colspan="9" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            No hay reportes resueltos en el historial
                        </td></tr>
                    `);
                }
            } else {
                $('#tablaHistorial').html(`
                    <tr><td colspan="9" class="text-center text-danger">
                        ❌ Error: ${data.error || 'No se pudieron cargar los datos'}
                    </td></tr>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            console.error('Respuesta:', xhr.responseText);
            $('#tablaHistorial').html(`
                <tr><td colspan="9" class="text-center text-danger">
                    ❌ Error de conexión: ${error}<br>
                    <small>Revisa la consola para más detalles</small>
                </td></tr>
            `);
        }
    });
}

// Ver detalle completo con imágenes y mapa
function verDetalleCompleto(id) {
    $('#detalleModal').modal('show');
    $('#detalleContent').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Cargando detalles...</p></div>');
    
    $.get('api/obtener_reporte.php', { id: id }, function(data) {
        if(data.success) {
            reporteActual = data.reporte;
            const r = data.reporte;
            const fotos = data.fotos || [];
            
            // Estado y prioridad
            const prioridadClass = r.prioridad == 'alta' ? 'danger' : (r.prioridad == 'media' ? 'warning' : 'success');
            
            // Generar HTML de fotos - RUTA CORREGIDA
            let fotosHtml = '';
            if(fotos.length > 0) {
                fotosHtml = `
                    <div class="mb-4">
                        <h6><i class="fas fa-images text-primary"></i> Fotos del problema (${fotos.length})</h6>
                        <div class="row g-2" id="galeriaFotos">
                            ${fotos.map((foto, index) => {
                                // CORREGIDO: Usar la ruta directamente de la base de datos
                                // La ruta ya incluye 'img/reportes/'
                                const rutaImagen = '../' + foto.foto_url;
                                return `
                                    <div class="col-md-3 col-4">
                                        <div class="card h-100">
                                            <img src="${rutaImagen}" class="card-img-top" style="height: 150px; object-fit: cover; cursor: pointer;" 
                                                 onclick="verImagenGrande('${rutaImagen}')" 
                                                 onerror="this.onerror=null; this.src='../img/reportes/placeholder.jpg';"
                                                 alt="Foto del reporte">
                                            <div class="card-footer text-center p-1">
                                                <small class="text-muted">Foto ${index + 1}</small>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
            } else {
                fotosHtml = `
                    <div class="mb-4">
                        <h6><i class="fas fa-camera text-muted"></i> Fotos</h6>
                        <div class="alert alert-secondary text-center">
                            <i class="fas fa-image fa-2x mb-2 d-block"></i>
                            <p class="mb-0">No se subieron fotos para este reporte</p>
                        </div>
                    </div>
                `;
            }
            
            // Generar HTML de ubicación
            let ubicacionHtml = '';
            if(r.latitud && r.longitud && r.latitud != 0) {
                ubicacionHtml = `
                    <div class="mb-4">
                        <h6><i class="fas fa-map-marker-alt text-danger"></i> Ubicación exacta</h6>
                        <div id="miniMapa" style="height: 250px; border-radius: 12px; margin-bottom: 10px;"></div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <small class="text-muted">Coordenadas:</small><br>
                                <strong>Latitud:</strong> ${r.latitud}<br>
                                <strong>Longitud:</strong> ${r.longitud}
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="https://www.google.com/maps?q=${r.latitud},${r.longitud}" target="_blank" class="btn btn-success btn-sm">
                                    <i class="fab fa-google"></i> Google Maps
                                </a>
                                <a href="https://waze.com/ul?ll=${r.latitud},${r.longitud}&navigate=yes" target="_blank" class="btn btn-info btn-sm text-white">
                                    <i class="fab fa-waze"></i> Waze
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                
                // Inicializar mini mapa después de cargar
                setTimeout(() => {
                    if(document.getElementById('miniMapa')) {
                        try {
                            const miniMap = L.map('miniMapa').setView([parseFloat(r.latitud), parseFloat(r.longitud)], 15);
                            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                                attribution: '© CityFix AI'
                            }).addTo(miniMap);
                            L.marker([parseFloat(r.latitud), parseFloat(r.longitud)]).addTo(miniMap)
                                .bindPopup(`<b>${escapeHtml(r.titulo)}</b><br>${escapeHtml(r.descripcion.substring(0, 100))}`);
                        } catch(e) {
                            console.error('Error al cargar el mapa:', e);
                        }
                    }
                }, 500);
            } else {
                ubicacionHtml = `
                    <div class="mb-4">
                        <h6><i class="fas fa-map-marker-alt text-muted"></i> Ubicación</h6>
                        <div class="alert alert-secondary text-center">
                            <i class="fas fa-map fa-2x mb-2 d-block"></i>
                            <p class="mb-0">No se especificó ubicación para este reporte</p>
                        </div>
                    </div>
                `;
            }
            
            const html = `
                <div class="row">
                    <div class="col-md-7">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">📋 Información del Reporte #${r.id}</h5>
                            </div>
                            <div class="card-body">
                                <h4 class="text-primary">${escapeHtml(r.titulo)}</h4>
                                <p class="text-muted">${escapeHtml(r.descripcion)}</p>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">📅 Fecha de reporte</small>
                                        <strong>${new Date(r.fecha_reporte).toLocaleString()}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">✅ Fecha de resolución</small>
                                        <strong class="text-success">${r.fecha_resuelto ? new Date(r.fecha_resuelto).toLocaleString() : 'No registrada'}</strong>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <small class="text-muted d-block">👤 Reportado por</small>
                                        <strong>${escapeHtml(r.ciudadano_nombre)}</strong>
                                        ${r.ciudadano_email ? `<br><small>${escapeHtml(r.ciudadano_email)}</small>` : ''}
                                        ${r.telefono ? `<br><small>📞 ${escapeHtml(r.telefono)}</small>` : ''}
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <small class="text-muted d-block">📂 Categoría</small>
                                        <strong>${r.categoria.replace('_', ' ')}</strong>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <small class="text-muted d-block">⚠️ Prioridad</small>
                                        <span class="badge bg-${prioridadClass}">${r.prioridad.toUpperCase()}</span>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <small class="text-muted d-block">📊 Estado</small>
                                        <span class="badge bg-success">RESUELTO</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        ${ubicacionHtml}
                    </div>
                </div>
                ${fotosHtml}
            `;
            
            $('#detalleContent').html(html);
        } else {
            $('#detalleContent').html('<div class="alert alert-danger">❌ Error al cargar los detalles: ' + (data.error || 'Error desconocido') + '</div>');
        }
    }, 'json');
}

// Ver mapa completo en modal
function verMapa(id) {
    $.get('api/obtener_reporte.php', { id: id }, function(data) {
        if(data.success && data.reporte.latitud) {
            const r = data.reporte;
            $('#coordenadasTexto').html(`📍 Latitud: ${r.latitud} | Longitud: ${r.longitud}`);
            $('#abrirGoogleMaps').attr('href', `https://www.google.com/maps?q=${r.latitud},${r.longitud}`);
            $('#abrirWaze').attr('href', `https://waze.com/ul?ll=${r.latitud},${r.longitud}&navigate=yes`);
            
            $('#mapaModal').modal('show');
            
            setTimeout(() => {
                if(document.getElementById('mapaCompleto')) {
                    try {
                        const fullMap = L.map('mapaCompleto').setView([parseFloat(r.latitud), parseFloat(r.longitud)], 16);
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                            attribution: '© CityFix AI'
                        }).addTo(fullMap);
                        L.marker([parseFloat(r.latitud), parseFloat(r.longitud)]).addTo(fullMap)
                            .bindPopup(`<b>${escapeHtml(r.titulo)}</b><br>${escapeHtml(r.descripcion.substring(0, 100))}`)
                            .openPopup();
                    } catch(e) {
                        console.error('Error al cargar el mapa:', e);
                    }
                }
            }, 300);
        }
    }, 'json');
}

// Ver imagen grande
function verImagenGrande(url) {
    const win = window.open();
    win.document.write(`
        <html>
        <head>
            <title>Imagen del Reporte</title>
            <style>
                body { margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #000; }
                img { max-width: 100%; max-height: 100vh; object-fit: contain; }
                button { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; }
                button:hover { background: #f0f0f0; }
            </style>
        </head>
        <body>
            <img src="${url}" alt="Imagen del reporte">
            <button onclick="window.close()">Cerrar</button>
        </body>
        </html>
    `);
}

function escapeHtml(text) {
    if(!text) return '';
    return $('<div>').text(text).html();
}

// Cargar historial al iniciar
$(document).ready(function() {
    cargarHistorial();
});
</script>

<style>
.badge-alta {
    background-color: #dc3545;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
}
.badge-media {
    background-color: #ffc107;
    color: #000;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
}
.badge-baja {
    background-color: #28a745;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
}
.badge-success {
    background-color: #198754;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
}
.stat-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 20px;
    transition: all 0.3s ease;
}
.table th {
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.table td {
    vertical-align: middle;
    font-size: 14px;
}
</style>

<?php include 'includes/footer.php'; ?>