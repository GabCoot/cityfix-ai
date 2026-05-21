<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>CityFix AI - Reportar Problema</title>
    
    <!-- Bootstrap 5 CSS Mobile First -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet para mapa móvil -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            padding: 16px;
            padding-bottom: 80px;
        }
        
        /* Contenedor principal */
        .mobile-container {
            max-width: 500px;
            margin: 0 auto;
        }
        
        /* Header */
        .app-header {
            text-align: center;
            margin-bottom: 24px;
            animation: fadeInDown 0.6s ease;
        }
        
        .app-logo {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            backdrop-filter: blur(10px);
            font-size: 35px;
        }
        
        .app-title {
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin: 0;
        }
        
        .app-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 14px;
            margin-top: 4px;
        }
        
        /* Tarjetas */
        .card-mobile {
            background: white;
            border-radius: 24px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            animation: fadeInUp 0.6s ease;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-title i {
            color: #6366f1;
            font-size: 22px;
        }
        
        /* Campos de formulario móvil */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control-mobile,
        .form-select-mobile {
            width: 100%;
            padding: 14px 16px;
            font-size: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            transition: all 0.3s;
            background: white;
            font-family: inherit;
        }
        
        .form-control-mobile:focus,
        .form-select-mobile:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }
        
        textarea.form-control-mobile {
            resize: vertical;
            min-height: 100px;
        }
        
        /* Botones */
        .btn-mobile {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .btn-primary-mobile {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
        }
        
        .btn-primary-mobile:active {
            transform: scale(0.98);
        }
        
        .btn-secondary-mobile {
            background: #f1f5f9;
            color: #475569;
        }
        
        /* Mapa móvil */
        #map {
            height: 250px;
            border-radius: 16px;
            margin-top: 8px;
        }
        
        .location-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 12px;
            color: #64748b;
            padding: 8px;
            background: #f8fafc;
            border-radius: 12px;
        }
        
        /* Categorías */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .category-item {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .category-item.selected {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-color: #6366f1;
            color: white;
        }
        
        .category-item i {
            font-size: 28px;
            margin-bottom: 8px;
            display: block;
        }
        
        .category-item span {
            font-size: 12px;
            font-weight: 500;
        }
        
        /* Fotos */
        .photo-preview {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        
        .photo-item {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 12px;
            overflow: hidden;
            background: #f1f5f9;
        }
        
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .remove-photo {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(0,0,0,0.6);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            cursor: pointer;
        }
        
        /* Botón flotante */
        .fab-submit {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 10px 30px rgba(16,185,129,0.4);
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s;
            width: calc(100% - 32px);
            max-width: 300px;
        }
        
        .fab-submit:active {
            transform: translateX(-50%) scale(0.97);
        }
        
        /* Animaciones */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Toast notification */
        .toast-message {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: white;
            padding: 12px 24px;
            border-radius: 40px;
            font-size: 14px;
            z-index: 2000;
            animation: fadeInUp 0.3s ease;
            white-space: nowrap;
        }
        
        /* Loading */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            display: none;
        }
        
        .loading.active {
            display: flex;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 12px;
            }
            
            .categories-grid {
                gap: 8px;
            }
            
            .category-item {
                padding: 12px 4px;
            }
            
            .category-item i {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="loading" id="loading">
    <div class="spinner"></div>
</div>

<div class="mobile-container">
    <!-- Header -->
    <div class="app-header">
        <div class="app-logo">
            <i class="fas fa-city"></i>
        </div>
        <h1 class="app-title">CityFix AI</h1>
        <p class="app-subtitle">Reporta problemas en tu ciudad</p>
    </div>
    
    <!-- Formulario -->
    <form id="reportForm" onsubmit="return false;">
        <!-- Datos personales -->
        <div class="card-mobile">
            <div class="card-title">
                <i class="fas fa-user"></i>
                <span>Tus datos</span>
            </div>
            
            <div class="form-group">
                <label class="form-label">Tu nombre *</label>
                <input type="text" id="nombre" class="form-control-mobile" placeholder="Ej: Juan Pérez" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Correo electrónico</label>
                <input type="email" id="email" class="form-control-mobile" placeholder="ejemplo@correo.com">
            </div>
            
            <div class="form-group">
                <label class="form-label">Teléfono (opcional)</label>
                <input type="tel" id="telefono" class="form-control-mobile" placeholder="555-123-4567">
            </div>
        </div>
        
        <!-- Categoría -->
        <div class="card-mobile">
            <div class="card-title">
                <i class="fas fa-tags"></i>
                <span>¿Qué problema reportas?</span>
            </div>
            
            <div class="categories-grid">
                <div class="category-item" data-categoria="bache" onclick="selectCategoria(this, 'bache')">
                    <i class="fas fa-road"></i>
                    <span>Bache</span>
                </div>
                <div class="category-item" data-categoria="fuga" onclick="selectCategoria(this, 'fuga')">
                    <i class="fas fa-water"></i>
                    <span>Fuga de agua</span>
                </div>
                <div class="category-item" data-categoria="basura" onclick="selectCategoria(this, 'basura')">
                    <i class="fas fa-trash"></i>
                    <span>Basura</span>
                </div>
                <div class="category-item" data-categoria="luz" onclick="selectCategoria(this, 'luz')">
                    <i class="fas fa-lightbulb"></i>
                    <span>Luminaria</span>
                </div>
                <div class="category-item" data-categoria="senal" onclick="selectCategoria(this, 'senal')">
                    <i class="fas fa-sign"></i>
                    <span>Señalamiento</span>
                </div>
                <div class="category-item" data-categoria="otro" onclick="selectCategoria(this, 'otro')">
                    <i class="fas fa-ellipsis-h"></i>
                    <span>Otro</span>
                </div>
            </div>
            <input type="hidden" id="categoria" value="">
        </div>
        
        <!-- Descripción -->
        <div class="card-mobile">
            <div class="card-title">
                <i class="fas fa-pen"></i>
                <span>Describe el problema</span>
            </div>
            
            <div class="form-group">
                <textarea id="descripcion" class="form-control-mobile" placeholder="Describe detalladamente el problema... (Ej: Bache de 50cm en medio de la calle)" rows="4"></textarea>
            </div>
        </div>
        
        <!-- Ubicación -->
        <div class="card-mobile">
            <div class="card-title">
                <i class="fas fa-map-marker-alt"></i>
                <span>Ubicación del problema</span>
            </div>
            
            <div id="map"></div>
            
            <div class="location-info">
                <span><i class="fas fa-location-dot"></i> <span id="direccion">Obteniendo ubicación...</span></span>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="obtenerUbicacion()">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <input type="hidden" id="latitud" value="">
            <input type="hidden" id="longitud" value="">
            
            <div class="mt-2">
                <button type="button" class="btn-secondary-mobile btn-mobile" onclick="obtenerUbicacion()">
                    <i class="fas fa-crosshairs"></i> Usar mi ubicación actual
                </button>
            </div>
        </div>
        
        <!-- Fotos -->
        <div class="card-mobile">
            <div class="card-title">
                <i class="fas fa-camera"></i>
                <span>Fotos del problema</span>
            </div>
            
            <button type="button" class="btn-secondary-mobile btn-mobile" onclick="tomarFoto()">
                <i class="fas fa-camera"></i> Tomar foto
            </button>
            
            <div class="photo-preview" id="photoPreview"></div>
            <small class="text-muted">Las fotos ayudan a identificar mejor el problema</small>
        </div>
    </form>
</div>

<!-- Botón flotante para enviar -->
<button class="fab-submit" onclick="enviarReporte()">
    <i class="fas fa-paper-plane"></i> Enviar Reporte
</button>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map;
    let marker;
    let fotos = [];
    let categoriaSeleccionada = null;
    
    // Inicializar mapa
    function initMap() {
        map = L.map('map').setView([19.4326, -99.1332], 15);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '© CityFix AI'
        }).addTo(map);
        
        map.on('click', function(e) {
            if (marker) {
                map.removeLayer(marker);
            }
            marker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(map);
            document.getElementById('latitud').value = e.latlng.lat;
            document.getElementById('longitud').value = e.latlng.lng;
            reverseGeocode(e.latlng.lat, e.latlng.lng);
        });
    }
    
    // Obtener ubicación actual
    function obtenerUbicacion() {
        if (navigator.geolocation) {
            mostrarToast('📍 Obteniendo tu ubicación...', 'info');
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    map.setView([lat, lng], 17);
                    
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    marker = L.marker([lat, lng]).addTo(map);
                    
                    document.getElementById('latitud').value = lat;
                    document.getElementById('longitud').value = lng;
                    
                    reverseGeocode(lat, lng);
                    mostrarToast('✅ Ubicación obtenida', 'success');
                },
                function(error) {
                    mostrarToast('❌ Error al obtener ubicación', 'error');
                }
            );
        } else {
            mostrarToast('❌ Tu navegador no soporta geolocalización', 'error');
        }
    }
    
    // Reverse geocoding
    function reverseGeocode(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
            .then(response => response.json())
            .then(data => {
                const direccion = data.display_name || `${lat}, ${lng}`;
                document.getElementById('direccion').innerHTML = direccion.substring(0, 60) + '...';
            })
            .catch(() => {
                document.getElementById('direccion').innerHTML = `${lat}, ${lng}`;
            });
    }
    
    // Seleccionar categoría
    function selectCategoria(element, categoria) {
        document.querySelectorAll('.category-item').forEach(item => {
            item.classList.remove('selected');
        });
        element.classList.add('selected');
        categoriaSeleccionada = categoria;
        document.getElementById('categoria').value = categoria;
    }
    
    // Tomar foto
    function tomarFoto() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.capture = 'environment';
        
        input.onchange = function(e) {
            const file = e.target.files[0];
            if (file && fotos.length < 5) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    fotos.push({
                        file: file,
                        preview: event.target.result
                    });
                    mostrarFotos();
                };
                reader.readAsDataURL(file);
            } else if (fotos.length >= 5) {
                mostrarToast('❌ Máximo 5 fotos por reporte', 'error');
            }
        };
        
        input.click();
    }
    
    // Mostrar fotos
    function mostrarFotos() {
        const container = document.getElementById('photoPreview');
        container.innerHTML = '';
        
        fotos.forEach((foto, index) => {
            const div = document.createElement('div');
            div.className = 'photo-item';
            div.innerHTML = `
                <img src="${foto.preview}" alt="Foto">
                <div class="remove-photo" onclick="eliminarFoto(${index})">
                    <i class="fas fa-times"></i>
                </div>
            `;
            container.appendChild(div);
        });
    }
    
    function eliminarFoto(index) {
        fotos.splice(index, 1);
        mostrarFotos();
    }
    
    // Enviar reporte
    function enviarReporte() {
        const nombre = document.getElementById('nombre').value.trim();
        const categoria = document.getElementById('categoria').value;
        const descripcion = document.getElementById('descripcion').value.trim();
        const latitud = document.getElementById('latitud').value;
        const longitud = document.getElementById('longitud').value;
        
        // Validaciones
        if (!nombre) {
            mostrarToast('❌ Por favor ingresa tu nombre', 'error');
            return;
        }
        
        if (!categoria) {
            mostrarToast('❌ Selecciona una categoría', 'error');
            return;
        }
        
        if (!descripcion) {
            mostrarToast('❌ Describe el problema', 'error');
            return;
        }
        
        if (!latitud || !longitud) {
            mostrarToast('📍 Por activa la ubicación', 'error');
            return;
        }
        
        // Mostrar loading
        document.getElementById('loading').classList.add('active');
        
        // Simular envío (aquí conectarás con tu backend)
        setTimeout(() => {
            document.getElementById('loading').classList.remove('active');
            
            // Mostrar éxito
            mostrarToast('✅ ¡Reporte enviado con éxito!', 'success');
            
            // Limpiar formulario
            setTimeout(() => {
                resetFormulario();
                mostrarToast('🎉 Gracias por ayudar a mejorar tu ciudad', 'success');
            }, 1500);
        }, 2000);
    }
    
    // Resetear formulario
    function resetFormulario() {
        document.getElementById('nombre').value = '';
        document.getElementById('email').value = '';
        document.getElementById('telefono').value = '';
        document.getElementById('descripcion').value = '';
        document.getElementById('categoria').value = '';
        fotos = [];
        mostrarFotos();
        
        document.querySelectorAll('.category-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        if (marker) {
            map.removeLayer(marker);
        }
    }
    
    // Mostrar toast
    function mostrarToast(mensaje, tipo) {
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        if (tipo === 'error') {
            toast.style.background = '#ef4444';
        } else if (tipo === 'success') {
            toast.style.background = '#10b981';
        }
        toast.innerHTML = mensaje;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
    
    // Inicializar
    document.addEventListener('DOMContentLoaded', () => {
        initMap();
        setTimeout(() => {
            obtenerUbicacion();
        }, 500);
    });
</script>

</body>
</html>