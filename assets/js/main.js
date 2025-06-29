/**
 * JavaScript Principal del Sistema
 * ===============================
 * 
 * Archivo: assets/js/main.js
 * 
 * Propósito:
 *   - Funciones JavaScript principales del sistema.
 *   - Inicialización de componentes comunes.
 *   - Funciones de utilidad reutilizables.
 * 
 * Funciones principales:
 *   - inicializarSistema(): Configuración inicial del sistema.
 *   - mostrarNotificacion(): Muestra notificaciones al usuario.
 *   - confirmarAccion(): Solicita confirmación antes de acciones críticas.
 *   - formatearFecha(): Formatea fechas para mostrar.
 * 
 * Uso:
 *   Este archivo se carga en todas las páginas del sistema y proporciona
 *   funcionalidades básicas de JavaScript.
 */

// Funciones de Utilidad
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('es-ES').format(number);
};

// Funciones de UI
const showLoader = () => {
    const loader = document.createElement('div');
    loader.className = 'loader';
    loader.id = 'global-loader';
    document.body.appendChild(loader);
};

const hideLoader = () => {
    const loader = document.getElementById('global-loader');
    if (loader) {
        loader.remove();
    }
};

// Funciones de Validación
const validateEmail = (email) => {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
};

const validatePassword = (password) => {
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    return re.test(password);
};

// Funciones de Formulario
const handleFormSubmit = async (form, url, method = 'POST') => {
    try {
        showLoader();
        const formData = new FormData(form);

        const response = await fetch(url, {
            method: method,
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const result = await response.json();

        if (response.ok) {
            showMessage('success', result.message || 'Operación exitosa');
            if (result.redirect) {
                window.location.href = result.redirect;
            }
        } else {
            showMessage('error', result.error || 'Error en la operación');
        }
    } catch (error) {
        showMessage('error', 'Error en la conexión');
    } finally {
        hideLoader();
    }
};

// Funciones de Tabla
// const initializeDataTable = (tableId, options = {}) => {
//     const defaultOptions = {
//         language: {
//             url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
//         },
//         responsive: false,
//         scrollX: true,
//         scrollCollapse: true,
//         fixedColumns: {
//             left: 2,
//             heightMatch: 'none'
//         },
//         dom: 'Bfrtip',
//         buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
//     };
    
//     return $(`#${tableId}`).DataTable({
//         ...defaultOptions,
//         ...options
//     });
// };

// Funciones de Mapa
const initializeMap = (elementId, center = [19.4326, -99.1332]) => {
    const map = L.map(elementId).setView(center, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    return map;
};

// Funciones de Gráficos
const createChart = (ctx, type, data, options = {}) => {
    return new Chart(ctx, {
        type: type,
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            ...options
        }
    });
};

// Funciones de Sesión
const checkSession = () => {
    const sessionTimeout = 3600000; // 1 hora
    const lastActivity = localStorage.getItem('lastActivity');
    const now = Date.now();
    
    if (lastActivity && now - lastActivity > sessionTimeout) {
        window.location.href = '/proyecto-2/auth/logout';
    }
    
    localStorage.setItem('lastActivity', now);
};

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Inicializar popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Verificar sesión cada minuto
    setInterval(checkSession, 60000);
    
    // Actualizar última actividad
    document.addEventListener('click', () => {
        localStorage.setItem('lastActivity', Date.now());
    });
});

function showMessage(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 5000);
} 