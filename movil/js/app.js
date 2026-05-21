// Variables globales
let usuarioActual = null;

// Inicializar app
$(document).ready(function() {
    // Verificar usuario
    if(!localStorage.getItem('usuarioId')) {
        const userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('usuarioId', userId);
        localStorage.setItem('usuarioNombre', 'Usuario');
        localStorage.setItem('usuarioEmail', '');
    }
    
    // Registrar Service Worker para PWA
    if('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js').then(reg => {
            console.log('Service Worker registrado:', reg);
        }).catch(err => {
            console.log('Error al registrar SW:', err);
        });
    }
});

// Función para mostrar notificaciones
function mostrarNotificacion(titulo, mensaje) {
    if(localStorage.getItem('notificaciones') === 'true') {
        if('Notification' in window && Notification.permission === 'granted') {
            new Notification(titulo, { body: mensaje, icon: '/icon-192.png' });
        }
    }
}

// Pedir permiso para notificaciones
function pedirPermisoNotificaciones() {
    if('Notification' in window && Notification.permission !== 'granted') {
        Notification.requestPermission();
    }
}

// Exportar funciones globales
window.mostrarNotificacion = mostrarNotificacion;
window.pedirPermisoNotificaciones = pedirPermisoNotificaciones;