<?php
require_once 'config/conexion.php';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="display-6 fw-bold mb-4"><i class="fas fa-map-marker-alt text-primary"></i> Reportes Activos</h1>
        
        <!-- Filtros -->
        <div class="filter-bar mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Filtrar por prioridad</label>
                    <select id="filtroPrioridad" class="form-select" onchange="cargarReportes()">
                        <option value="">Todas</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Filtrar por estado</label>
                    <select id="filtroEstado" class="form-select" onchange="cargarReportes()">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En Proceso</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Buscar</label>
                    <input type="text" id="filtroBusqueda" class="form-control" placeholder="Buscar por ciudadano o título..." onkeyup="cargarReportes()">
                </div>
            </div>
        </div>

        <div class="stat-card p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th><th>Ciudadano</th><th>Título</th><th>Categoría</th><th>Prioridad</th><th>Estado</th><th>Ubicación</th><th>Fecha</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaReportes">
                        <tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary"></div><p>Cargando...</p></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detalle del Reporte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="abrirModalCambiarEstado()">Cambiar Estado</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Cambiar Estado -->
<div class="modal fade" id="cambiarEstadoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-exchange-alt"></i> Cambiar Estado del Reporte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="estadoReporteId">
                <div class="mb-3">
                    <label class="form-label fw-bold">Estado actual:</label>
                    <p id="estadoActual" class="form-control bg-light"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Selecciona el nuevo estado:</label>
                    <select id="nuevoEstadoSelect" class="form-select form-select-lg">
                        <option value="pendiente">⏳ Pendiente</option>
                        <option value="en_proceso">🔄 En Proceso</option>
                        <option value="resuelto">✅ Resuelto</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarCambiarEstado()">Actualizar Estado</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Mapa -->
<div class="modal fade" id="mapaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-map-marked-alt"></i> Ubicación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="mapaCompleto" style="height: 400px; border-radius: 12px;"></div>
                <div class="mt-2 text-center">
                    <p id="coordenadasTexto" class="text-muted"></p>
                    <a id="abrirGoogleMaps" href="#" target="_blank" class="btn btn-success btn-sm"><i class="fab fa-google"></i> Google Maps</a>
                    <a id="abrirWaze" href="#" target="_blank" class="btn btn-info btn-sm text-white"><i class="fab fa-waze"></i> Waze</a>
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

function cargarReportes() {
    $.ajax({
        url: 'api/obtener_stats.php',
        type: 'GET',
        data: {
            action: 'activos',
            prioridad: $('#filtroPrioridad').val(),
            estado: $('#filtroEstado').val(),
            busqueda: $('#filtroBusqueda').val()
        },
        dataType: 'json',
        success: function(data) {
            if(data.success && data.reportes) {
                let html = '';
                if(data.reportes.length === 0) {
                    html = '<tr><td colspan="9" class="text-center py-4">📭 No hay reportes activos</td></tr>';
                } else {
                    data.reportes.forEach(r => {
                        const prioridadClass = r.prioridad == 'alta' ? 'badge-alta' : (r.prioridad == 'media' ? 'badge-media' : 'badge-baja');
                        html += `
                            <tr>
                                <td class="fw-bold">#${r.id}</td>
                                <td>${escapeHtml(r.ciudadano_nombre)}</td>
                                <td>${escapeHtml(r.titulo)}</td>
                                <td>${r.categoria.replace('_', ' ')}</td>
                                <td><span class="badge ${prioridadClass}">${r.prioridad}</span></td>
                                <td>
                                    <select class="form-select form-select-sm estado-select" data-id="${r.id}" style="width:120px">
                                        <option value="pendiente" ${r.estado=='pendiente'?'selected':''}>⏳ Pendiente</option>
                                        <option value="en_proceso" ${r.estado=='en_proceso'?'selected':''}>🔄 Proceso</option>
                                        <option value="resuelto" ${r.estado=='resuelto'?'selected':''}>✅ Resuelto</option>
                                    </select>
                                </td>
                                <td>${r.latitud && r.latitud!=0 ? `<button class="btn btn-sm btn-outline-info" onclick="verMapa(${r.id})">📍 Ver mapa</button>` : '❌ No'}</td>
                                <td>${new Date(r.fecha_reporte).toLocaleDateString()}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="verDetalleCompleto(${r.id})"><i class="fas fa-eye"></i> Ver</button>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#tablaReportes').html(html);
            }
        }
    });
}

// Función para corregir la ruta de la imagen
function corregirRutaImagen(url) {
    if(!url) return '';
    if(url.startsWith('http://') || url.startsWith('https://')) return url;
    if(url.startsWith('../uploads/')) return url;
    if(url.startsWith('uploads/')) return '../' + url;
    if(url.startsWith('/uploads/')) return '..' + url;
    if(url.match(/^\d+_\d+_\d+\.\w+$/)) return '../uploads/' + url;
    return '../uploads/' + url.split('/').pop();
}

function verDetalleCompleto(id) {
    $('#detalleModal').modal('show');
    $('#detalleContent').html('<div class="text-center py-4"><div class="spinner-border"></div><p>Cargando...</p></div>');
    
    $.get('api/obtener_reporte.php', { id: id }, function(data) {
        if(data.success) {
            reporteActual = data.reporte;
            const r = data.reporte;
            const fotos = data.fotos || [];
            const prioridadClass = r.prioridad == 'alta' ? 'danger' : (r.prioridad == 'media' ? 'warning' : 'success');
            
            // Fotos
            let fotosHtml = '';
            if(fotos.length > 0) {
                fotosHtml = '<div class="mb-4"><h6><i class="fas fa-images text-primary"></i> Fotos ('+fotos.length+')</h6><div class="row g-2">';
                fotos.forEach((foto, i) => {
                    let imgUrl = corregirRutaImagen(foto.foto_url);
                    fotosHtml += `
                        <div class="col-md-3 col-4">
                            <div class="card h-100">
                                <img src="${imgUrl}" class="card-img-top" style="height:150px;object-fit:cover;cursor:pointer" 
                                     onclick="window.open('${imgUrl}','_blank')" 
                                     onerror="this.src='https://placehold.co/300x200?text=Error+al+cargar+foto'">
                                <div class="card-footer p-1 text-center">
                                    <small>Foto ${i+1}</small>
                                </div>
                            </div>
                        </div>
                    `;
                });
                fotosHtml += '</div></div>';
            } else {
                fotosHtml = '<div class="mb-4"><p class="text-muted">📷 No se subieron fotos</p></div>';
            }
            
            // Ubicación
            let ubicacionHtml = '';
            if(r.latitud && r.latitud != 0) {
                ubicacionHtml = `
                    <div class="mb-4">
                        <h6><i class="fas fa-map-marker-alt text-danger"></i> Ubicación</h6>
                        <div id="miniMapa" style="height:200px;border-radius:12px;"></div>
                        <div class="mt-2"><small><strong>Lat:</strong> ${r.latitud} | <strong>Lng:</strong> ${r.longitud}</small></div>
                        <div class="mt-2">
                            <a href="https://www.google.com/maps?q=${r.latitud},${r.longitud}" target="_blank" class="btn btn-success btn-sm">Google Maps</a>
                            <a href="https://waze.com/ul?ll=${r.latitud},${r.longitud}&navigate=yes" target="_blank" class="btn btn-info btn-sm text-white">Waze</a>
                        </div>
                    </div>
                `;
                setTimeout(() => {
                    if(document.getElementById('miniMapa')) {
                        const map = L.map('miniMapa').setView([parseFloat(r.latitud), parseFloat(r.longitud)], 15);
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(map);
                        L.marker([parseFloat(r.latitud), parseFloat(r.longitud)]).addTo(map).bindPopup(r.titulo);
                    }
                }, 300);
            } else {
                ubicacionHtml = '<div class="mb-4"><p class="text-muted">📍 No se especificó ubicación</p></div>';
            }
            
            const html = `
                <div class="row">
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header bg-light"><h5 class="mb-0">Reporte #${r.id}</h5></div>
                            <div class="card-body">
                                <h4 class="text-primary">${escapeHtml(r.titulo)}</h4>
                                <p>${escapeHtml(r.descripcion)}</p>
                                <hr>
                                <div class="row">
                                    <div class="col-6"><small>📅 Fecha</small><br><strong>${new Date(r.fecha_reporte).toLocaleString()}</strong></div>
                                    <div class="col-6"><small>👤 Ciudadano</small><br><strong>${escapeHtml(r.ciudadano_nombre)}</strong></div>
                                    <div class="col-6 mt-2"><small>📂 Categoría</small><br><strong>${r.categoria.replace('_', ' ')}</strong></div>
                                    <div class="col-6 mt-2"><small>⚠️ Prioridad</small><br><span class="badge bg-${prioridadClass}">${r.prioridad}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">${ubicacionHtml}</div>
                </div>
                ${fotosHtml}
            `;
            $('#detalleContent').html(html);
        }
    },'json');
}

function verMapa(id) {
    $.get('api/obtener_reporte.php', { id: id }, function(data) {
        if(data.success && data.reporte.latitud) {
            const r = data.reporte;
            const lat = parseFloat(r.latitud);
            const lng = parseFloat(r.longitud);
            $('#coordenadasTexto').html(`📍 ${lat}, ${lng}`);
            $('#abrirGoogleMaps').attr('href', `https://www.google.com/maps?q=${lat},${lng}`);
            $('#abrirWaze').attr('href', `https://waze.com/ul?ll=${lat},${lng}&navigate=yes`);
            $('#mapaModal').modal('show');
            setTimeout(() => {
                const map = L.map('mapaCompleto').setView([lat, lng], 16);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(map);
                L.marker([lat, lng]).addTo(map).bindPopup(r.titulo).openPopup();
            }, 300);
        }
    },'json');
}

// Abrir modal para cambiar estado (sin alert)
function abrirModalCambiarEstado() {
    if(reporteActual) {
        $('#estadoReporteId').val(reporteActual.id);
        $('#estadoActual').html(reporteActual.estado == 'pendiente' ? '⏳ Pendiente' : (reporteActual.estado == 'en_proceso' ? '🔄 En Proceso' : '✅ Resuelto'));
        $('#nuevoEstadoSelect').val(reporteActual.estado);
        $('#cambiarEstadoModal').modal('show');
    }
}

// Confirmar cambio de estado (sin alert)
function confirmarCambiarEstado() {
    const id = $('#estadoReporteId').val();
    const nuevoEstado = $('#nuevoEstadoSelect').val();
    
    $.post('api/cambiar_estado.php', { id: id, estado: nuevoEstado }, function(data) {
        if(data.success) {
            $('#cambiarEstadoModal').modal('hide');
            $('#detalleModal').modal('hide');
            cargarReportes();
            mostrarNotificacion('✅ Estado actualizado correctamente', 'success');
        } else {
            mostrarNotificacion('❌ Error al actualizar el estado', 'error');
        }
    },'json');
}

// Mostrar notificación toast
function mostrarNotificacion(mensaje, tipo) {
    const toast = $(`
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
            <div class="toast align-items-center text-white bg-${tipo == 'success' ? 'success' : 'danger'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${mensaje}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    `);
    $('body').append(toast);
    const toastElement = new bootstrap.Toast(toast.find('.toast')[0], { autohide: true, delay: 3000 });
    toastElement.show();
    setTimeout(() => toast.remove(), 3500);
}

// Cambiar estado desde el select en la tabla (también sin alert)
$(document).on('change', '.estado-select', function() {
    const id = $(this).data('id');
    const nuevoEstado = $(this).val();
    const selectElement = $(this);
    
    $.post('api/cambiar_estado.php', { id: id, estado: nuevoEstado }, function(data) {
        if(data.success) {
            mostrarNotificacion('✅ Estado actualizado correctamente', 'success');
            cargarReportes();
        } else {
            selectElement.val(selectElement.data('old-value'));
            mostrarNotificacion('❌ Error al actualizar el estado', 'error');
        }
    },'json');
});

// Guardar valor anterior del select
$(document).on('focus', '.estado-select', function() {
    $(this).data('old-value', $(this).val());
});

function escapeHtml(text) {
    if(!text) return '';
    return $('<div>').text(text).html();
}

$(document).ready(function() { cargarReportes(); });
</script>

<?php include 'includes/footer.php'; ?>