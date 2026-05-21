<?php
require_once 'config/conexion.php';
session_start();
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-6 fw-bold" style="color: #1e293b;">
                    <i class="fas fa-trash-alt text-success"></i> Recolección de Basura
                </h1>
                <p class="text-muted">Selecciona múltiples puntos en el mapa para crear rutas</p>
            </div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#rutaModal" onclick="resetFormularioRuta()">
                <i class="fas fa-plus"></i> Nueva Ruta
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div><div class="stat-value" id="totalRutas">0</div><div class="stat-label">Rutas Activas</div></div>
                        <i class="fas fa-route fa-2x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div><div class="stat-value" id="totalPuntos">0</div><div class="stat-label">Puntos en Rutas</div></div>
                        <i class="fas fa-map-pin fa-2x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div><div class="stat-value" id="totalKm">0</div><div class="stat-label">Kilómetros</div></div>
                        <i class="fas fa-road fa-2x text-info opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div><div class="stat-value" id="totalSuscriptores">0</div><div class="stat-label">Suscriptores</div></div>
                        <i class="fas fa-bell fa-2x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Panel izquierdo: Lista de Rutas -->
            <div class="col-md-4">
                <div class="stat-card">
                    <h5 class="mb-3"><i class="fas fa-list"></i> Mis Rutas</h5>
                    <div id="rutasList" class="rutas-container"></div>
                </div>
            </div>
            
            <!-- Panel derecho: Mapa -->
            <div class="col-md-8">
                <div class="stat-card">
                    <h5 class="mb-3">
                        <i class="fas fa-map-marked-alt"></i> Mapa - Haz clic para agregar puntos
                        <span id="rutaSeleccionadaNombre" class="badge bg-secondary float-end">Selecciona una ruta</span>
                    </h5>
                    
                    <div id="map" style="height: 450px; border-radius: 12px; margin-bottom: 15px;"></div>
                    
                    <div id="infoRuta" class="alert alert-info small mb-2" style="display: none;"></div>
                    
                    <div id="puntosPanel" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6><i class="fas fa-list-ol"></i> Puntos de la ruta (en orden)</h6>
                            <div>
                                <button class="btn btn-sm btn-danger" onclick="limpiarTodosLosPuntos()">
                                    <i class="fas fa-trash"></i> Limpiar todos
                                </button>
                            </div>
                        </div>
                        <div id="puntosList" class="puntos-container"></div>
                        <div class="mt-3 text-center">
                            <button class="btn btn-primary" onclick="guardarRutaActual()" id="btnGuardarRuta">
                                <i class="fas fa-save"></i> Guardar Ruta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botones de acción -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="stat-card text-center">
                    <button class="btn btn-warning btn-lg w-100" id="btnNotificar" onclick="enviarNotificacion()" disabled>
                        <i class="fas fa-bell"></i> Notificar a suscriptores
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card text-center">
                    <button class="btn btn-info btn-lg w-100" onclick="simularRecorrido()">
                        <i class="fas fa-play"></i> Simular Recorrido
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL RUTA -->
<div class="modal fade" id="rutaModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Nueva Ruta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRuta">
                    <input type="hidden" name="id" id="rutaId">
                    <div class="mb-3">
                        <label>Nombre de la Ruta</label>
                        <input type="text" class="form-control" name="nombre" id="rutaNombre" required>
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <textarea class="form-control" name="descripcion" id="rutaDescripcion" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Días de Recolección</label>
                        <div class="row">
                            <div class="col-4"><label><input type="checkbox" name="dias[]" value="lunes"> Lunes</label></div>
                            <div class="col-4"><label><input type="checkbox" name="dias[]" value="martes"> Martes</label></div>
                            <div class="col-4"><label><input type="checkbox" name="dias[]" value="miercoles"> Miércoles</label></div>
                            <div class="col-4"><label><input type="checkbox" name="dias[]" value="jueves"> Jueves</label></div>
                            <div class="col-4"><label><input type="checkbox" name="dias[]" value="viernes"> Viernes</label></div>
                            <div class="col-4"><label><input type="checkbox" name="dias[]" value="sabado"> Sábado</label></div>
                            <div class="col-4"><label><input type="checkbox" name="dias[]" value="domingo"> Domingo</label></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6"><label>Hora Inicio</label><input type="time" class="form-control" name="hora_inicio" id="rutaHoraInicio" required></div>
                        <div class="col-6"><label>Hora Fin</label><input type="time" class="form-control" name="hora_fin" id="rutaHoraFin" required></div>
                    </div>
                    <div class="mb-3"><label>Color</label><input type="color" class="form-control form-control-color" name="color" id="rutaColor" value="#10b981"></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="activo" id="rutaActivo" checked><label class="form-check-label">Ruta activa</label></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="crearRutaVacia()">Crear Ruta</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
let map;
let rutasData = [];
let rutaActualId = null;
let rutaActualObjeto = null;
let markersMapa = [];
let puntosTemporales = [];
let routingControl = null;
let lineasRuta = [];
let vehiculoMarker = null;
let animacionInterval = null;

// Coordenadas de Oxkutzcab
const OXKUTZCAB = { lat: 20.3051, lng: -89.4179 };

// ============================================
// INICIALIZAR MAPA Y CARGAR RUTAS
// ============================================
function initMap() {
    map = L.map('map').setView([OXKUTZCAB.lat, OXKUTZCAB.lng], 14);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '© CityFix',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);
    
    // Evento para agregar puntos
    map.on('click', function(e) {
        if(rutaActualObjeto) {
            agregarPuntoTemporal(e.latlng.lat, e.latlng.lng);
        }
    });
    
    cargarTodasLasRutas();
}

// ============================================
// CARGAR RUTAS DE TODAS LAS VERSIONES ANTERIORES
// ============================================
function cargarTodasLasRutas() {
    let todasLasRutas = [];
    
    // Buscar en diferentes localStorage keys
    const keys = ['basura_rutas_final', 'basura_rutas_pro', 'basura_rutas_v2', 'basura_rutas'];
    
    for(let k of keys) {
        let data = localStorage.getItem(k);
        if(data && data !== '[]' && data !== 'null') {
            try {
                let rutas = JSON.parse(data);
                if(rutas && rutas.length > 0) {
                    todasLasRutas = todasLasRutas.concat(rutas);
                }
            } catch(e) {}
        }
    }
    
    // Eliminar duplicados por id
    const rutasUnicas = [];
    const idsExistentes = new Set();
    
    for(let ruta of todasLasRutas) {
        if(!idsExistentes.has(ruta.id)) {
            idsExistentes.add(ruta.id);
            // Convertir colonias a puntos si es necesario
            if(ruta.colonias && !ruta.puntos) {
                ruta.puntos = ruta.colonias;
                delete ruta.colonias;
            }
            if(!ruta.puntos) ruta.puntos = [];
            rutasUnicas.push(ruta);
        }
    }
    
    if(rutasUnicas.length > 0) {
        rutasData = rutasUnicas;
        guardarRutas();
        mostrarToast('✅ Rutas cargadas', `Se encontraron ${rutasUnicas.length} rutas guardadas`, 'success');
    } else {
        // Datos de ejemplo
        rutasData = [
            {
                id: 1,
                nombre: 'Ruta Centro',
                descripcion: 'Centro de Oxkutzcab',
                dias: ['lunes', 'miercoles', 'viernes'],
                hora_inicio: '07:00',
                hora_fin: '09:00',
                color: '#ef4444',
                activo: true,
                puntos: [
                    { id: 1, nombre: 'Parque Principal', orden: 1, lat: 20.3051, lng: -89.4179 },
                    { id: 2, nombre: 'Calle 20', orden: 2, lat: 20.3055, lng: -89.4185 }
                ]
            },
            {
                id: 2,
                nombre: 'Ruta Norte',
                descripcion: 'Colonias del norte',
                dias: ['martes', 'jueves', 'sabado'],
                hora_inicio: '08:00',
                hora_fin: '10:30',
                color: '#10b981',
                activo: true,
                puntos: [
                    { id: 3, nombre: 'Calle 25', orden: 1, lat: 20.3080, lng: -89.4160 },
                    { id: 4, nombre: 'Calle 27', orden: 2, lat: 20.3090, lng: -89.4150 }
                ]
            }
        ];
        guardarRutas();
    }
    
    mostrarRutas();
    actualizarStats();
}

function guardarRutas() {
    localStorage.setItem('basura_rutas_final', JSON.stringify(rutasData));
    // También guardar en versión anterior por compatibilidad
    localStorage.setItem('basura_rutas', JSON.stringify(rutasData));
}

function actualizarStats() {
    const rutasActivas = rutasData.filter(r => r.activo !== false).length;
    const totalPuntos = rutasData.reduce((acc, r) => acc + (r.puntos?.length || 0), 0);
    let totalKm = 0;
    rutasData.forEach(r => { if(r.distanciaTotal) totalKm += r.distanciaTotal; });
    
    $('#totalRutas').text(rutasActivas);
    $('#totalPuntos').text(totalPuntos);
    $('#totalKm').text(totalKm.toFixed(1));
    $('#totalSuscriptores').text(Math.floor(Math.random() * 30) + 10);
}

// ============================================
// MOSTRAR LISTA DE RUTAS
// ============================================
function mostrarRutas() {
    const container = $('#rutasList');
    container.empty();
    
    if(rutasData.length === 0) {
        container.html('<div class="text-center text-muted p-4">No hay rutas. Crea una nueva ruta</div>');
        return;
    }
    
    rutasData.forEach(ruta => {
        const diasMap = { 'lunes':'Lun','martes':'Mar','miercoles':'Mié','jueves':'Jue','viernes':'Vie','sabado':'Sáb','domingo':'Dom' };
        let diasHtml = ruta.dias ? ruta.dias.map(d => `<span class="badge bg-secondary me-1">${diasMap[d] || d.substring(0,3)}</span>`).join('') : '';
        const estadoBadge = ruta.activo !== false ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-danger">Inactiva</span>';
        const puntosCount = ruta.puntos ? ruta.puntos.length : 0;
        
        const card = $(`
            <div class="ruta-card ${rutaActualId === ruta.id ? 'active' : ''}" data-id="${ruta.id}" style="border-left-color: ${ruta.color || '#10b981'}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div><i class="fas fa-route" style="color: ${ruta.color || '#10b981'}"></i> <strong>${escapeHtml(ruta.nombre)}</strong> ${estadoBadge}</div>
                        <div class="small text-muted">${escapeHtml(ruta.descripcion) || ''}</div>
                        <div class="mt-1">${diasHtml} <span class="badge bg-info">${ruta.hora_inicio || '--:--'} - ${ruta.hora_fin || '--:--'}</span></div>
                        <div class="small"><i class="fas fa-map-pin"></i> ${puntosCount} puntos | ${ruta.distanciaTotal ? ruta.distanciaTotal.toFixed(1) + ' km' : 'Sin trazar'}</div>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-primary mb-1" onclick="event.stopPropagation(); editarInfoRuta(${ruta.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); eliminarRuta(${ruta.id})"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `);
        card.on('click', () => cargarRutaParaEdicion(ruta.id));
        container.append(card);
    });
}

// ============================================
// CREAR NUEVA RUTA
// ============================================
function resetFormularioRuta() {
    $('#formRuta')[0].reset();
    $('#rutaId').val('');
    $('#rutaNombre').val('');
    $('#rutaDescripcion').val('');
    $('#rutaHoraInicio').val('08:00');
    $('#rutaHoraFin').val('10:00');
    $('#rutaColor').val('#10b981');
    $('input[name="dias[]"]').prop('checked', false);
    $('#rutaActivo').prop('checked', true);
}

function crearRutaVacia() {
    const nombre = $('#rutaNombre').val().trim();
    const descripcion = $('#rutaDescripcion').val();
    const dias = $('input[name="dias[]"]:checked').map(function() { return $(this).val(); }).get();
    const hora_inicio = $('#rutaHoraInicio').val();
    const hora_fin = $('#rutaHoraFin').val();
    const color = $('#rutaColor').val();
    const activo = $('#rutaActivo').is(':checked');
    
    if(!nombre) {
        alert('El nombre de la ruta es requerido');
        return;
    }
    
    const nuevaRuta = {
        id: Date.now(),
        nombre: nombre,
        descripcion: descripcion,
        dias: dias,
        hora_inicio: hora_inicio,
        hora_fin: hora_fin,
        color: color,
        activo: activo,
        puntos: []
    };
    
    rutasData.push(nuevaRuta);
    guardarRutas();
    mostrarRutas();
    actualizarStats();
    
    $('#rutaModal').modal('hide');
    resetFormularioRuta();
    
    // Cargar la nueva ruta automáticamente
    setTimeout(() => cargarRutaParaEdicion(nuevaRuta.id), 100);
    
    mostrarToast('✅ Ruta creada', `"${nombre}" - Haz clic en el mapa para agregar puntos`, 'success');
}

function editarInfoRuta(id) {
    const ruta = rutasData.find(r => r.id === id);
    if(ruta) {
        $('#rutaId').val(ruta.id);
        $('#rutaNombre').val(ruta.nombre);
        $('#rutaDescripcion').val(ruta.descripcion || '');
        $('#rutaHoraInicio').val(ruta.hora_inicio);
        $('#rutaHoraFin').val(ruta.hora_fin);
        $('#rutaColor').val(ruta.color);
        $('#rutaActivo').prop('checked', ruta.activo !== false);
        $('input[name="dias[]"]').prop('checked', false);
        if(ruta.dias) ruta.dias.forEach(dia => $(`input[name="dias[]"][value="${dia}"]`).prop('checked', true));
        $('#rutaModal .modal-title').html('<i class="fas fa-edit"></i> Editar Ruta');
        $('#rutaModal').modal('show');
    }
}

function eliminarRuta(id) {
    const ruta = rutasData.find(r => r.id === id);
    if(confirm(`¿Eliminar la ruta "${ruta?.nombre}"?`)) {
        rutasData = rutasData.filter(r => r.id !== id);
        guardarRutas();
        
        if(rutaActualId === id) {
            rutaActualId = null;
            rutaActualObjeto = null;
            puntosTemporales = [];
            $('#puntosPanel').hide();
            $('#infoRuta').hide();
            $('#rutaSeleccionadaNombre').html('Selecciona una ruta');
            $('#btnNotificar').prop('disabled', true);
            limpiarMapaCompleto();
        }
        
        mostrarRutas();
        actualizarStats();
        mostrarToast('🗑️ Ruta eliminada', 'Se ha eliminado correctamente', 'danger');
    }
}

// ============================================
// CARGAR RUTA PARA EDITAR PUNTOS
// ============================================
function cargarRutaParaEdicion(id) {
    rutaActualId = id;
    rutaActualObjeto = rutasData.find(r => r.id === id);
    if(!rutaActualObjeto) return;
    
    $('#rutaSeleccionadaNombre').html(`<i class="fas fa-edit"></i> Editando: ${escapeHtml(rutaActualObjeto.nombre)}`);
    $('#btnNotificar').prop('disabled', false);
    $('#puntosPanel').show();
    
    // Limpiar mapa
    limpiarMapaCompleto();
    
    // Cargar puntos existentes
    puntosTemporales = rutaActualObjeto.puntos ? [...rutaActualObjeto.puntos] : [];
    
    // Reordenar puntos
    puntosTemporales.sort((a,b) => (a.orden || 0) - (b.orden || 0));
    
    // Dibujar puntos existentes
    puntosTemporales.forEach((punto, idx) => {
        agregarMarcadorPunto(punto.lat, punto.lng, punto.nombre, idx + 1);
    });
    
    mostrarListaPuntos();
    
    // Trazar ruta si hay puntos
    if(puntosTemporales.length >= 2) {
        trazarRutaCompleta();
    } else if(puntosTemporales.length === 1) {
        map.setView([puntosTemporales[0].lat, puntosTemporales[0].lng], 16);
    }
    
    $('#infoRuta').show().html('<i class="fas fa-info-circle"></i> Modo edición: Haz clic en el mapa para agregar nuevos puntos');
}

function limpiarMapaCompleto() {
    if(routingControl) map.removeControl(routingControl);
    lineasRuta.forEach(line => map.removeLayer(line));
    lineasRuta = [];
    markersMapa.forEach(m => map.removeLayer(m));
    markersMapa = [];
}

// ============================================
// AGREGAR PUNTO (CLIC EN MAPA)
// ============================================
function agregarPuntoTemporal(lat, lng) {
    if(!rutaActualObjeto) {
        mostrarToast('⚠️ Primero selecciona una ruta', '', 'warning');
        return;
    }
    
    const nuevoPunto = {
        id: Date.now() + Math.random(),
        nombre: `Punto ${puntosTemporales.length + 1}`,
        lat: lat,
        lng: lng,
        orden: puntosTemporales.length + 1
    };
    
    puntosTemporales.push(nuevoPunto);
    
    // Agregar marcador
    agregarMarcadorPunto(lat, lng, nuevoPunto.nombre, puntosTemporales.length);
    
    // Actualizar lista
    mostrarListaPuntos();
    
    // Trazar ruta
    if(puntosTemporales.length >= 2) {
        trazarRutaCompleta();
    }
    
    // Centrar vista
    map.setView([lat, lng], 17);
    
    mostrarToast('📍 Punto agregado', `${nuevoPunto.nombre} - Orden ${puntosTemporales.length}`, 'success');
}

function agregarMarcadorPunto(lat, lng, nombre, orden) {
    const color = rutaActualObjeto?.color || '#10b981';
    const marker = L.marker([lat, lng], {
        icon: L.divIcon({
            html: `<div style="background-color: ${color}; width: 18px; height: 18px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.3); position: relative;">
                       <span style="position: absolute; top: -20px; left: -2px; font-size: 11px; font-weight: bold; background: white; padding: 2px 7px; border-radius: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">${orden}</span>
                   </div>`,
            iconSize: [28, 28]
        })
    }).bindPopup(`<strong>${escapeHtml(nombre)}</strong><br>Orden: ${orden}<br>📌 ${lat.toFixed(5)}, ${lng.toFixed(5)}`);
    marker.addTo(map);
    markersMapa.push(marker);
}

function mostrarListaPuntos() {
    const container = $('#puntosList');
    container.empty();
    
    if(puntosTemporales.length === 0) {
        container.html('<div class="text-center text-muted p-3">No hay puntos. Haz clic en el mapa para agregar calles</div>');
        return;
    }
    
    puntosTemporales.forEach((punto, idx) => {
        const div = $(`
            <div class="punto-item" data-id="${punto.id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary rounded-pill me-2">${idx + 1}</span>
                        <i class="fas fa-map-pin text-success"></i>
                        <strong>${escapeHtml(punto.nombre)}</strong>
                        <span class="badge bg-secondary ms-2">${punto.lat.toFixed(5)}, ${punto.lng.toFixed(5)}</span>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarPuntoTemporal(${punto.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `);
        container.append(div);
    });
}

function eliminarPuntoTemporal(id) {
    puntosTemporales = puntosTemporales.filter(p => p.id !== id);
    // Reordenar
    puntosTemporales.forEach((p, idx) => { p.orden = idx + 1; });
    
    // Redibujar todo
    limpiarMapaCompleto();
    puntosTemporales.forEach((punto, idx) => {
        agregarMarcadorPunto(punto.lat, punto.lng, punto.nombre, idx + 1);
    });
    mostrarListaPuntos();
    
    if(puntosTemporales.length >= 2) {
        trazarRutaCompleta();
    } else if(puntosTemporales.length === 1) {
        map.setView([puntosTemporales[0].lat, puntosTemporales[0].lng], 16);
    }
    
    mostrarToast('🗑️ Punto eliminado', 'Se ha eliminado de la ruta', 'warning');
}

function limpiarTodosLosPuntos() {
    if(confirm('¿Eliminar TODOS los puntos de esta ruta?')) {
        puntosTemporales = [];
        limpiarMapaCompleto();
        mostrarListaPuntos();
        $('#infoRuta').hide();
        mostrarToast('🗑️ Todos los puntos eliminados', 'La ruta está vacía', 'warning');
    }
}

// ============================================
// TRAZAR RUTA SOBRE CALLES REALES
// ============================================
async function trazarRutaCompleta() {
    if(puntosTemporales.length < 2) return;
    
    const puntosValidos = puntosTemporales.filter(p => p.lat && p.lng);
    if(puntosValidos.length < 2) return;
    
    let coordinates = puntosValidos.map(p => `${p.lng},${p.lat}`).join(';');
    const url = `https://router.project-osrm.org/route/v1/driving/${coordinates}?overview=full&geometries=geojson&steps=true`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if(data.code === 'Ok' && data.routes && data.routes.length > 0) {
            const route = data.routes[0];
            
            if(routingControl) map.removeControl(routingControl);
            lineasRuta.forEach(line => map.removeLayer(line));
            lineasRuta = [];
            
            const routeLine = L.geoJSON(route.geometry, {
                style: { color: rutaActualObjeto.color, weight: 5, opacity: 0.8 }
            }).addTo(map);
            lineasRuta.push(routeLine);
            
            const distanciaKm = route.distance / 1000;
            const tiempoMin = Math.round(route.duration / 60);
            
            rutaActualObjeto.distanciaTemporal = distanciaKm;
            
            $('#infoRuta').show().html(`
                <i class="fas fa-route"></i> 
                <strong>Ruta calculada:</strong> ${distanciaKm.toFixed(2)} km | 
                <strong>Tiempo estimado:</strong> ${tiempoMin} minutos
            `);
            
            map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });
        } else {
            const coords = puntosValidos.map(p => [p.lat, p.lng]);
            const fallbackLine = L.polyline(coords, { color: rutaActualObjeto.color, weight: 4, opacity: 0.6, dashArray: '5, 10' }).addTo(map);
            lineasRuta.push(fallbackLine);
            $('#infoRuta').show().html('<span class="text-warning">⚠️ Mostrando línea recta (servicio de rutas no disponible)</span>');
        }
    } catch(error) {
        console.error('Error:', error);
        $('#infoRuta').show().html('<span class="text-danger">❌ Error al conectar con el servicio de mapas</span>');
    }
}

// ============================================
// GUARDAR RUTA (SIN RECARGAR)
// ============================================
function guardarRutaActual() {
    if(!rutaActualObjeto) {
        mostrarToast('⚠️ No hay ruta seleccionada', '', 'warning');
        return;
    }
    
    // Deshabilitar botón mientras se guarda
    $('#btnGuardarRuta').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    // Actualizar puntos
    rutaActualObjeto.puntos = [...puntosTemporales];
    rutaActualObjeto.distanciaTotal = rutaActualObjeto.distanciaTemporal || 0;
    
    // Guardar en localStorage
    guardarRutas();
    
    // Actualizar lista
    mostrarRutas();
    actualizarStats();
    
    // Re-habilitar botón
    $('#btnGuardarRuta').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Ruta');
    
    mostrarToast('✅ Ruta guardada', `"${rutaActualObjeto.nombre}" con ${puntosTemporales.length} puntos`, 'success');
}

// ============================================
// SIMULAR RECORRIDO
// ============================================
function simularRecorrido() {
    if(!rutaActualObjeto || puntosTemporales.length < 2) {
        mostrarToast('⚠️ Selecciona una ruta con al menos 2 puntos', '', 'warning');
        return;
    }
    
    if(animacionInterval) clearInterval(animacionInterval);
    if(vehiculoMarker) map.removeLayer(vehiculoMarker);
    
    const puntos = [...puntosTemporales];
    let puntoActual = 0;
    
    const camionIcon = L.divIcon({
        html: `<div style="background-color: ${rutaActualObjeto.color}; width: 32px; height: 32px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                   <i class="fas fa-truck" style="color: white; font-size: 16px;"></i>
               </div>`,
        iconSize: [38, 38]
    });
    
    vehiculoMarker = L.marker([puntos[0].lat, puntos[0].lng], { icon: camionIcon }).addTo(map);
    vehiculoMarker.bindPopup('🚛 Inicio del recorrido').openPopup();
    
    animacionInterval = setInterval(() => {
        puntoActual++;
        if(puntoActual >= puntos.length) {
            clearInterval(animacionInterval);
            vehiculoMarker.bindPopup('✅ Recorrido completado').openPopup();
            mostrarToast('✅ Recorrido completado', 'El camión ha terminado la ruta', 'success');
            return;
        }
        vehiculoMarker.setLatLng([puntos[puntoActual].lat, puntos[puntoActual].lng]);
        vehiculoMarker.bindPopup(`🚛 Llegando a: ${puntos[puntoActual].nombre}`).openPopup();
        map.setView([puntos[puntoActual].lat, puntos[puntoActual].lng], 17);
    }, 2000);
    
    mostrarToast('🚛 Simulación iniciada', 'El camión comenzará el recorrido', 'info');
}

function enviarNotificacion() {
    if(!rutaActualObjeto) return;
    const notificados = Math.floor(Math.random() * 20) + 5;
    mostrarToast('📢 Notificación enviada', `Se notificó a ${notificados} suscriptores sobre "${rutaActualObjeto.nombre}"`, 'success');
    
    if(Notification.permission === 'granted') {
        new Notification('🚛 CityFix - Recolección', { 
            body: `El camión de "${rutaActualObjeto.nombre}" está en recorrido. ¡Saca la basura!`,
            icon: '/favicon.ico'
        });
    } else if(Notification.permission !== 'denied') {
        Notification.requestPermission();
    }
}

function mostrarToast(titulo, mensaje, tipo = 'success') {
    const colors = { success: '#10b981', danger: '#ef4444', info: '#3b82f6', warning: '#f59e0b' };
    const toast = $(`<div style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; background: ${colors[tipo]}; color: white; padding: 12px 20px; border-radius: 12px; animation: slideIn 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 10000;"><strong>${titulo}</strong><br><small>${mensaje}</small></div>`);
    $('body').append(toast);
    setTimeout(() => toast.remove(), 3000);
}

function escapeHtml(text) { if(!text) return ''; return $('<div>').text(text).html(); }

$(document).ready(function() { 
    initMap(); 
});
</script>

<style>
.stat-card { background: white; border-radius: 20px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.2s; }
.stat-card:hover { transform: translateY(-3px); }
.stat-value { font-size: 2rem; font-weight: bold; color: #1e293b; }
.stat-label { color: #64748b; font-size: 0.875rem; }

.rutas-container, .puntos-container { max-height: 450px; overflow-y: auto; }

.ruta-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 10px; border-left-width: 4px; border-left-style: solid; cursor: pointer; transition: all 0.2s; }
.ruta-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateX(3px); }
.ruta-card.active { background: #f0fdf4; border-color: #10b981; }

.punto-item { background: #f8fafc; border-radius: 10px; padding: 10px 12px; margin-bottom: 8px; border: 1px solid #e2e8f0; }
.punto-item:hover { background: #f1f5f9; }

@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

.leaflet-routing-container { display: none !important; }

.btn-sm { padding: 4px 10px; }

#infoRuta { font-size: 12px; padding: 8px 12px; margin-bottom: 10px; background: #e0f2fe; border: 1px solid #7dd3fc; border-radius: 10px; }
</style>

<?php include 'includes/footer.php'; ?>