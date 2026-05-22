<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="theme-color" content="#10b981">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>CityFix AI - Recolección de Basura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="css/app.css">
    <style>
        #map { height: 350px; width: 100%; border-radius: 16px; }
        .map-container { margin: 12px; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); position: relative; }
        .location-btn { position: absolute; bottom: 15px; right: 15px; z-index: 1000; background: white; border: none; width: 44px; height: 44px; border-radius: 50%; box-shadow: 0 2px 10px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; cursor: pointer; color: #10b981; }
        .selected-route-card { background: white; margin: 12px; border-radius: 16px; padding: 12px 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #10b981; }
        .routes-list { padding: 0 12px; max-height: 250px; overflow-y: auto; }
        .route-item { background: white; border-radius: 14px; padding: 12px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); cursor: pointer; border-left: 3px solid transparent; transition: all 0.2s; }
        .route-item.active { background: #f0fdf4; border-left-color: #10b981; }
        .route-name { font-weight: 600; font-size: 0.95rem; }
        .route-schedule { font-size: 0.7rem; color: #64748b; margin-top: 5px; }
        .route-days { display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap; }
        .day-badge { background: #e2e8f0; padding: 2px 8px; border-radius: 20px; font-size: 0.6rem; }
        .points-section { background: white; margin: 12px; border-radius: 16px; padding: 12px; }
        .points-title { font-weight: 600; font-size: 0.85rem; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        .point-item { display: flex; align-items: center; gap: 12px; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .point-number { width: 24px; height: 24px; background: #10b981; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold; }
        .point-name { flex: 1; font-size: 0.85rem; }
        .point-location { font-size: 0.65rem; color: #94a3b8; }
        .notify-btn { background: #f59e0b; border: none; border-radius: 40px; padding: 10px; margin: 12px; color: white; font-weight: 600; width: calc(100% - 24px); }
        .custom-toast { position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%); background: #1e293b; color: white; padding: 8px 16px; border-radius: 40px; font-size: 0.8rem; z-index: 2000; white-space: nowrap; }
        .bottom-nav { display: flex; justify-content: space-around; align-items: center; background: white; position: fixed; bottom: 0; left: 0; right: 0; padding: 8px 12px 12px; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); z-index: 1000; }
        .nav-item { text-align: center; color: #94a3b8; text-decoration: none; font-size: 0.7rem; flex: 1; }
        .nav-item i { font-size: 1.3rem; display: block; margin-bottom: 3px; }
        .nav-item.active { color: #10b981; }
    </style>
</head>
<body>
    <div class="header-gradient p-4 text-white">
        <div class="d-flex align-items-center">
            <a href="index.html" class="text-white me-3"><i class="fas fa-arrow-left fs-5"></i></a>
            <div>
                <h1 class="h4 fw-bold mb-0"><i class="fas fa-trash-alt me-2"></i>Recolección de Basura</h1>
                <p class="small opacity-75 mb-0">Oxkutzcab, Yucatán - Rutas del camión</p>
            </div>
        </div>
    </div>
    
    <div class="map-container">
        <div id="map"></div>
        <button class="location-btn" onclick="centrarMiUbicacion()">
            <i class="fas fa-location-dot fa-lg"></i>
        </button>
    </div>
    
    <div id="selectedRouteCard" class="selected-route-card" style="display: none;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-success mb-1">Ruta activa</span>
                <div class="route-name fw-bold" id="selectedRouteName"></div>
                <div class="route-schedule" id="selectedRouteSchedule"></div>
            </div>
            <button class="btn btn-sm btn-outline-success" id="btnSuscribirse">
                <i class="fas fa-bell"></i> Suscribirme
            </button>
        </div>
        <div class="route-days" id="selectedRouteDays"></div>
    </div>
    
    <div class="routes-list" id="rutasList">
        <div class="text-center py-4">
            <div class="spinner-border text-success" role="status"></div>
            <p class="mt-2 text-muted">Cargando rutas...</p>
        </div>
    </div>
    
    <div id="puntosSection" class="points-section" style="display: none;">
        <div class="points-title">
            <i class="fas fa-list-ol text-success"></i>
            <span>Calles donde pasa el camión</span>
        </div>
        <div id="puntosLista"></div>
    </div>
    
    <button class="notify-btn" onclick="solicitarNotificaciones()">
        <i class="fas fa-bell"></i> Activar notificaciones del camión
    </button>
    
    <div class="bottom-nav">
        <a href="index.html" class="nav-item"><i class="fas fa-home"></i><span>Inicio</span></a>
        <a href="reportar.html" class="nav-item"><i class="fas fa-plus-circle"></i><span>Reportar</span></a>
        <a href="basura.php" class="nav-item active"><i class="fas fa-trash-alt"></i><span>Basura</span></a>
        <a href="mis-reportes.html" class="nav-item"><i class="fas fa-list"></i><span>Mis R.</span></a>
        <a href="perfil.html" class="nav-item"><i class="fas fa-user"></i><span>Perfil</span></a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
    let map;
    let rutasData = [];
    let rutaActualId = null;
    let rutaActualObjeto = null;
    let markersMapa = [];
    let userLocationMarker = null;
    let routingControl = null;
    
    const OXKUTZCAB = { lat: 20.3051, lng: -89.4179 };
    
    // ============================================
    // RECIBIR NOTIFICACIONES DEL ADMIN
    // ============================================
    function escucharNotificacionesAdmin() {
        // Escuchar cambios en localStorage
        window.addEventListener('storage', function(e) {
            if(e.key === 'notificacion_basura_admin' && e.newValue) {
                try {
                    const notificacion = JSON.parse(e.newValue);
                    console.log('📢 Notificación recibida:', notificacion);
                    
                    // Mostrar notificación push
                    if(Notification.permission === 'granted') {
                        new Notification('🚛 CityFix - Recolección de Basura', {
                            body: notificacion.mensaje,
                            icon: '/icon-192.png',
                            vibrate: [200, 100, 200]
                        });
                    }
                    
                    // Mostrar toast en la app
                    mostrarToast(`🔔 ${notificacion.mensaje}`);
                } catch(e) {
                    console.error('Error al procesar notificación:', e);
                }
            }
        });
        
        // Revisar si hay notificación pendiente al cargar la página
        const notificacionPendiente = localStorage.getItem('notificacion_basura_admin');
        if(notificacionPendiente) {
            try {
                const noti = JSON.parse(notificacionPendiente);
                mostrarToast(`🔔 ${noti.mensaje}`);
                localStorage.removeItem('notificacion_basura_admin');
            } catch(e) {}
        }
    }
    
    function solicitarNotificaciones() {
        if('Notification' in window) {
            if(Notification.permission === 'granted') {
                mostrarToast('✅ Notificaciones ya activadas');
            } else if(Notification.permission !== 'denied') {
                Notification.requestPermission().then(function(permission) {
                    if(permission === 'granted') {
                        mostrarToast('✅ Notificaciones activadas');
                        new Notification('🚛 CityFix - Recolección', {
                            body: 'Recibirás alertas cuando el camión de basura esté en tu zona'
                        });
                    } else {
                        mostrarToast('❌ No se activaron las notificaciones');
                    }
                });
            } else {
                mostrarToast('❌ Notificaciones bloqueadas. Ve a configuración');
            }
        } else {
            mostrarToast('❌ Tu navegador no soporta notificaciones');
        }
    }
    
    // ============================================
    // VERIFICAR SESIÓN
    // ============================================
    function verificarSesion() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '../api/get_usuario.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if(data.success) {
                        localStorage.setItem('usuarioId', data.usuario.id);
                        localStorage.setItem('usuarioNombre', data.usuario.nombre);
                        localStorage.setItem('usuarioEmail', data.usuario.email);
                        resolve(data.usuario);
                    } else {
                        window.location.href = 'login.php';
                        reject();
                    }
                },
                error: function() {
                    window.location.href = 'login.php';
                    reject();
                }
            });
        });
    }
    
    function initMap() {
        map = L.map('map').setView([OXKUTZCAB.lat, OXKUTZCAB.lng], 14);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '© CityFix - Recolección',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);
        cargarRutas();
        obtenerUbicacion();
    }
    
    function obtenerUbicacion() {
        if(navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    if(userLocationMarker) map.removeLayer(userLocationMarker);
                    const userIcon = L.divIcon({
                        html: `<div style="background-color: #3b82f6; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white;"></div>`,
                        iconSize: [18, 18]
                    });
                    userLocationMarker = L.marker([position.coords.latitude, position.coords.longitude], { icon: userIcon })
                        .addTo(map)
                        .bindPopup('<b>📍 Tu ubicación</b>');
                },
                function(error) { console.log('Error:', error); }
            );
        }
    }
    
    function centrarMiUbicacion() {
        if(navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    map.setView([position.coords.latitude, position.coords.longitude], 16);
                    mostrarToast('📍 Centrando en tu ubicación');
                },
                function() { mostrarToast('❌ No se pudo obtener ubicación'); }
            );
        }
    }
    
    function cargarRutas() {
        let rutasGuardadas = localStorage.getItem('basura_rutas_final');
        if(rutasGuardadas && rutasGuardadas !== '[]' && rutasGuardadas !== 'null') {
            rutasData = JSON.parse(rutasGuardadas);
        } else {
            let otrasRutas = localStorage.getItem('basura_rutas');
            if(otrasRutas && otrasRutas !== '[]') {
                rutasData = JSON.parse(otrasRutas);
            } else {
                rutasData = [];
            }
        }
        rutasData = rutasData.filter(r => r.activo !== false);
        mostrarRutasEnLista();
        if(rutasData.length > 0 && !rutaActualId) {
            seleccionarRuta(rutasData[0].id);
        }
    }
    
    function mostrarRutasEnLista() {
        const container = $('#rutasList');
        container.empty();
        if(rutasData.length === 0) {
            container.html(`<div class="text-center text-muted py-4"><i class="fas fa-trash-alt fa-3x mb-3 opacity-50"></i><p>No hay rutas disponibles</p><small>El administrador está configurando las rutas</small></div>`);
            return;
        }
        const diasMap = { 'lunes':'Lun','martes':'Mar','miercoles':'Mié','jueves':'Jue','viernes':'Vie','sabado':'Sáb','domingo':'Dom' };
        rutasData.forEach(ruta => {
            let diasHtml = '';
            if(ruta.dias) { ruta.dias.forEach(d => { diasHtml += `<span class="day-badge">${diasMap[d] || d.substring(0,3)}</span>`; }); }
            const puntosCount = ruta.puntos ? ruta.puntos.length : 0;
            const card = $(`<div class="route-item ${rutaActualId === ruta.id ? 'active' : ''}" data-id="${ruta.id}" style="border-left-color: ${ruta.color || '#10b981'}"><div class="route-name">${escapeHtml(ruta.nombre)}</div><div class="route-schedule"><i class="fas fa-clock"></i> ${ruta.hora_inicio || '--:--'} - ${ruta.hora_fin || '--:--'}</div><div class="route-days">${diasHtml}</div><div class="route-schedule mt-1"><i class="fas fa-map-pin"></i> ${puntosCount} puntos</div></div>`);
            card.on('click', () => seleccionarRuta(ruta.id));
            container.append(card);
        });
    }
    
    function seleccionarRuta(id) {
        rutaActualId = id;
        rutaActualObjeto = rutasData.find(r => r.id === id);
        if(!rutaActualObjeto) return;
        
        $('.route-item').removeClass('active');
        $(`.route-item[data-id="${id}"]`).addClass('active');
        
        const diasMap = { 'lunes':'Lunes','martes':'Martes','miercoles':'Miércoles','jueves':'Jueves','viernes':'Viernes','sabado':'Sábado','domingo':'Domingo' };
        let diasTexto = '';
        if(rutaActualObjeto.dias) { diasTexto = rutaActualObjeto.dias.map(d => diasMap[d] || d).join(', '); }
        
        $('#selectedRouteName').text(rutaActualObjeto.nombre);
        $('#selectedRouteSchedule').html(`<i class="fas fa-clock"></i> ${rutaActualObjeto.hora_inicio || '--:--'} - ${rutaActualObjeto.hora_fin || '--:--'} | ${diasTexto}`);
        
        let daysHtml = '';
        if(rutaActualObjeto.dias) { rutaActualObjeto.dias.forEach(d => { daysHtml += `<span class="day-badge">${diasMap[d] || d}</span>`; }); }
        $('#selectedRouteDays').html(daysHtml);
        $('#selectedRouteCard').show();
        $('#puntosSection').show();
        mostrarPuntos();
        
        if(routingControl) map.removeControl(routingControl);
        markersMapa.forEach(m => map.removeLayer(m));
        markersMapa = [];
        
        const puntos = rutaActualObjeto.puntos || [];
        puntos.sort((a,b) => (a.orden || 0) - (b.orden || 0));
        
        const puntosValidos = [];
        puntos.forEach((punto, idx) => {
            if(punto.lat && punto.lng) {
                puntosValidos.push([punto.lat, punto.lng]);
                const marker = L.marker([punto.lat, punto.lng], {
                    icon: L.divIcon({
                        html: `<div style="background-color: ${rutaActualObjeto.color || '#10b981'}; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.3);"><span style="position: absolute; top: -16px; left: 3px; font-size: 10px; background: white; padding: 2px 5px; border-radius: 10px;">${idx+1}</span></div>`,
                        iconSize: [22, 22]
                    })
                }).bindPopup(`<b>${escapeHtml(punto.nombre)}</b><br>📍 Punto de recolección`);
                marker.addTo(map);
                markersMapa.push(marker);
            }
        });
        
        if(puntosValidos.length >= 2) {
            trazarRutaConCalles(puntosValidos);
        } else if(puntosValidos.length === 1) {
            map.setView(puntosValidos[0], 15);
        }
    }
    
    async function trazarRutaConCalles(puntos) {
        if(puntos.length < 2) return;
        let coordinates = puntos.map(p => `${p[1]},${p[0]}`).join(';');
        const url = `https://router.project-osrm.org/route/v1/driving/${coordinates}?overview=full&geometries=geojson`;
        try {
            const response = await fetch(url);
            const data = await response.json();
            if(data.code === 'Ok' && data.routes && data.routes.length > 0) {
                const route = data.routes[0];
                const routeLine = L.geoJSON(route.geometry, { style: { color: rutaActualObjeto.color || '#10b981', weight: 5, opacity: 0.8 } }).addTo(map);
                markersMapa.push(routeLine);
                map.fitBounds(routeLine.getBounds(), { padding: [30, 30] });
            } else {
                const fallbackLine = L.polyline(puntos, { color: rutaActualObjeto.color || '#10b981', weight: 4, opacity: 0.6, dashArray: '5, 10' }).addTo(map);
                markersMapa.push(fallbackLine);
                map.fitBounds(fallbackLine.getBounds(), { padding: [30, 30] });
            }
        } catch(error) {
            console.error('Error trazando ruta:', error);
            const fallbackLine = L.polyline(puntos, { color: rutaActualObjeto.color || '#10b981', weight: 4, opacity: 0.6 }).addTo(map);
            markersMapa.push(fallbackLine);
        }
    }
    
    function mostrarPuntos() {
        const container = $('#puntosLista');
        container.empty();
        const puntos = rutaActualObjeto?.puntos || [];
        puntos.sort((a,b) => (a.orden || 0) - (b.orden || 0));
        if(puntos.length === 0) {
            container.html('<div class="text-center text-muted py-3">No hay calles registradas para esta ruta</div>');
            return;
        }
        puntos.forEach((punto, idx) => {
            const div = $(`<div class="point-item"><div class="point-number">${idx + 1}</div><div class="point-name">${escapeHtml(punto.nombre)}<div class="point-location">${punto.lat.toFixed(5)}, ${punto.lng.toFixed(5)}</div></div><button class="btn btn-sm btn-outline-primary" onclick="centrarEnPunto(${punto.lat}, ${punto.lng})"><i class="fas fa-crosshairs"></i></button></div>`);
            container.append(div);
        });
    }
    
    function centrarEnPunto(lat, lng) {
        map.setView([lat, lng], 18);
        mostrarToast('📍 Centrando en punto');
    }
    
    function suscribirseARuta() {
        if(!rutaActualObjeto) return;
        const usuarioId = localStorage.getItem('usuarioId');
        if(!usuarioId) {
            mostrarToast('⚠️ Inicia sesión para suscribirte');
            setTimeout(() => { window.location.href = 'login.php'; }, 1500);
            return;
        }
        
        const $btn = $('#btnSuscribirse');
        const textoOriginal = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Suscribiendo...');
        
        $.ajax({
            url: '../api/guardar_suscripcion.php',
            type: 'POST',
            data: { usuario_id: usuarioId, ruta_id: rutaActualId, ruta_nombre: rutaActualObjeto.nombre },
            dataType: 'json',
            success: function(data) {
                if(data.success) {
                    mostrarToast(`✅ Suscrito a "${rutaActualObjeto.nombre}"`);
                    $btn.html('<i class="fas fa-bell"></i> Suscrito').prop('disabled', true);
                } else if(data.already) {
                    mostrarToast(`ℹ️ Ya estás suscrito a "${rutaActualObjeto.nombre}"`);
                    $btn.html('<i class="fas fa-bell"></i> Suscrito').prop('disabled', true);
                } else {
                    mostrarToast(`❌ Error: ${data.error || 'No se pudo suscribir'}`);
                    $btn.prop('disabled', false).html(textoOriginal);
                }
            },
            error: function() {
                mostrarToast('❌ Error de conexión');
                $btn.prop('disabled', false).html(textoOriginal);
            }
        });
    }
    
    function mostrarToast(mensaje) {
        const toast = $(`<div class="custom-toast">${mensaje}</div>`);
        $('body').append(toast);
        setTimeout(() => toast.remove(), 2500);
    }
    
    function escapeHtml(text) {
        if(!text) return '';
        return $('<div>').text(text).html();
    }
    
    // ============================================
    // INICIALIZACIÓN
    // ============================================
    $(document).ready(function() {
        verificarSesion().then(() => {
            initMap();
            escucharNotificacionesAdmin(); // <-- ESCUCHA NOTIFICACIONES
            $('#btnSuscribirse').on('click', suscribirseARuta);
        });
    });
    </script>
</body>
</html>