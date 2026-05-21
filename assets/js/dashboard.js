// ============================================
// CityFix AI - Mobile JavaScript
// Funciones para la app móvil
// ============================================

// Variables globales móviles
let fotosArray = [];

// Función para tomar foto
function tomarFotoMovil() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.capture = 'environment';
    
    input.onchange = function(e) {
        const file = e.target.files[0];
        if(file && fotosArray.length < 5) {
            const reader = new FileReader();
            reader.onload = function(event) {
                fotosArray.push({
                    file: file,
                    preview: event.target.result
                });
                mostrarFotosMovil();
            };
            reader.readAsDataURL(file);
        } else if(fotosArray.length >= 5) {
            alert('Máximo 5 fotos por reporte');
        }
    };
    
    input.click();
}

// Función para seleccionar de galería
function seleccionarFotoMovil() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.multiple = true;
    
    input.onchange = function(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            if(file.type.startsWith('image/') && fotosArray.length < 5) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    fotosArray.push({
                        file: file,
                        preview: event.target.result
                    });
                    mostrarFotosMovil();
                };
                reader.readAsDataURL(file);
            }
        });
    };
    
    input.click();
}

// Mostrar fotos en preview
function mostrarFotosMovil() {
    const container = document.getElementById('photoPreview');
    if(!container) return;
    
    container.innerHTML = '';
    fotosArray.forEach((foto, index) => {
        const div = document.createElement('div');
        div.className = 'photo-preview-item';
        div.innerHTML = `
            <img src="${foto.preview}" alt="Foto">
            <button class="remove-photo" onclick="eliminarFotoMovil(${index})">✕</button>
        `;
        container.appendChild(div);
    });
}

// Eliminar foto
function eliminarFotoMovil(index) {
    fotosArray.splice(index, 1);
    mostrarFotosMovil();
}

// Seleccionar categoría
function selectCategoriaMovil(element, categoria) {
    document.querySelectorAll('.category-item').forEach(item => {
        item.classList.remove('selected');
    });
    element.classList.add('selected');
    document.getElementById('categoria').value = categoria;
}

// Enviar reporte móvil
function enviarReporteMovil() {
    const nombre = document.getElementById('nombre')?.value.trim();
    const titulo = document.getElementById('titulo')?.value.trim();
    const descripcion = document.getElementById('descripcion')?.value.trim();
    const categoria = document.getElementById('categoria')?.value;
    const latitud = document.getElementById('latitud')?.value;
    const longitud = document.getElementById('longitud')?.value;
    
    if(!nombre) { alert('Ingresa tu nombre'); return; }
    if(!titulo) { alert('Ingresa un título'); return; }
    if(!descripcion) { alert('Describe el problema'); return; }
    if(!categoria) { alert('Selecciona una categoría'); return; }
    if(!latitud || !longitud) { alert('Selecciona la ubicación en el mapa'); return; }
    
    const formData = new FormData();
    formData.append('nombre', nombre);
    formData.append('email', document.getElementById('email')?.value || '');
    formData.append('telefono', document.getElementById('telefono')?.value || '');
    formData.append('titulo', titulo);
    formData.append('descripcion', descripcion);
    formData.append('categoria', categoria);
    formData.append('latitud', latitud);
    formData.append('longitud', longitud);
    formData.append('usuario_id', localStorage.getItem('usuarioId') || 'guest');
    
    fotosArray.forEach((foto, index) => {
        formData.append(`fotos[${index}]`, foto.file);
    });
    
    const loading = document.getElementById('loadingOverlay');
    if(loading) loading.classList.add('active');
    
    fetch('../api/guardar_reporte_movil.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(loading) loading.classList.remove('active');
        if(data.success) {
            alert('✅ Reporte enviado con éxito');
            window.location.href = 'mis-reportes.html';
        } else {
            alert('❌ Error: ' + (data.error || 'Desconocido'));
        }
    })
    .catch(error => {
        if(loading) loading.classList.remove('active');
        alert('❌ Error de conexión');
    });
}

// Función para mostrar toast
function mostrarToastMovil(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    toast.className = 'toast-message';
    if(tipo === 'error') toast.style.background = '#ef4444';
    else if(tipo === 'success') toast.style.background = '#10b981';
    toast.innerHTML = mensaje;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}