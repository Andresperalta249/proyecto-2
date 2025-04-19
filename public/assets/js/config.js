// Configuración global de Toastr
const toastrConfig = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    preventDuplicates: true,
    timeOut: "3000",
    extendedTimeOut: "1000",
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut",
    tapToDismiss: false
};

// Configuración global de SweetAlert2
const swalConfig = {
    customClass: {
        confirmButton: 'btn btn-success mx-2',
        cancelButton: 'btn btn-danger mx-2'
    },
    buttonsStyling: false,
    reverseButtons: true
};

// Configuración global de DataTables
const dataTablesConfig = {
    language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
        loadingRecords: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>',
        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Procesando...</span></div>',
        zeroRecords: '<div class="text-center p-4"><i class="fas fa-slash fa-3x text-muted mb-3"></i><p class="text-muted">No se encontraron registros</p></div>',
        emptyTable: '<div class="text-center p-4"><i class="fas fa-slash fa-3x text-muted mb-3"></i><p class="text-muted">No hay datos disponibles</p></div>'
    },
    responsive: true
};

// Función para inicializar tooltips
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
}

// Función para validar email
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Función para mostrar mensajes de error
function showError(message) {
    toastr.error(message);
}

// Función para mostrar mensajes de éxito
function showSuccess(message) {
    toastr.success(message);
}

// Función para confirmar acciones
function confirmAction(title, text, icon = 'warning') {
    return Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        ...swalConfig
    });
}

// Aplicar configuraciones globales
$(document).ready(function() {
    // Configurar Toastr
    toastr.options = toastrConfig;
    
    // Inicializar tooltips
    initTooltips();
}); 