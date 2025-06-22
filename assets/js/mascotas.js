$(document).ready(function() {
    // Obtener configuración desde el DOM
    const configElement = document.getElementById('mascotas-config');
    if (!configElement) {
        console.error('Elemento de configuración no encontrado');
        return;
    }

    // Verificar que estamos en la página correcta
    if (!document.getElementById('tablaMascotas')) {
        return;
    }

    // Verificar que la tabla realmente existe y tiene contenido
    if (!document.getElementById('tablaMascotas').querySelector('thead') || !document.getElementById('tablaMascotas').querySelector('tbody')) {
        console.log('La tabla de mascotas no tiene la estructura correcta - saliendo');
        return;
    }

    // --- CONFIGURACIÓN Y VARIABLES ---
    const PERMISOS = {
        editar: configElement.dataset.permisoEditar === 'true',
        eliminar: configElement.dataset.permisoEliminar === 'true'
    };

    // --- FUNCIONES ---
    function getDynamicPageLength() {
        const tableWrapper = document.querySelector('.table-responsive');
        if (!tableWrapper) return 10;
        const topOffset = tableWrapper.getBoundingClientRect().top;
        const headerHeight = 56;
        const footerHeight = 50;
        const safetyMargin = 20;
        const availableHeight = window.innerHeight - topOffset - headerHeight - footerHeight - safetyMargin;
        const avgRowHeight = 48;
        const numRows = Math.floor(availableHeight / avgRowHeight);
        return Math.max(5, numRows);
    }

    // --- INICIALIZACIÓN DE DATATABLES ---
    const tablaElement = document.getElementById('tablaMascotas');
    if (!tablaElement) {
        console.error('La tabla #tablaMascotas no existe en el DOM');
        return;
    }

    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables no está disponible');
        return;
    }

    const tablaMascotas = $('#tablaMascotas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": `${APP_URL}/mascotas/obtenerMascotas`,
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "nombre" },
            { "data": "especie" },
            { "data": "propietario" },
            { 
                "data": "estado",
                "render": function(data, type, row) {
                    const isChecked = data === 'activo';
                    const canToggle = PERMISOS.editar;
                    
                    return `
                        <div class="form-check form-switch d-flex justify-content-center">
                            <input class="form-check-input switch-estado" type="checkbox" 
                                   role="switch"
                                   data-id="${row.id}" 
                                   ${isChecked ? 'checked' : ''} 
                                   ${canToggle ? '' : 'disabled'}
                                   title="${canToggle ? 'Cambiar estado' : 'No tienes permiso'}">
                        </div>
                    `;
                },
                "className": "text-center",
                "orderable": false
            },
            { "data": "dispositivo_estado" },
            { 
                "data": "id",
                "render": function(data, type, row) {
                    let botones = '';
                    // El chequeo de permisos se hace ahora en el controlador
                    botones += `<button class="btn btn-sm btn-info btn-editar" data-id="${data}" title="Editar"><i class="fas fa-edit"></i></button>`;
                    botones += ` <button class="btn btn-sm btn-danger btn-eliminar" data-id="${data}" title="Eliminar"><i class="fas fa-trash-alt"></i></button>`;
                    return botones;
                },
                "orderable": false,
                "searchable": false,
                "width": "100px"
            }
        ],
        "language": {
            "url": `${APP_URL}/assets/js/i18n/Spanish.json`
        },
        "responsive": false,
        "autoWidth": false,
        "ordering": true,
        "lengthChange": false,
        "dom": 'ftip'
    });

    // --- MANEJO DE EVENTOS ---
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if ($.fn.DataTable.isDataTable('#tablaMascotas')) {
                const newPageLength = getDynamicPageLength();
                $('#tablaMascotas').DataTable().page.len(newPageLength).draw();
            }
        }, 250);
    });

    // Cambiar estado de mascota con switch
    $('#tablaMascotas tbody').on('change', '.switch-estado', function() {
        if (!PERMISOS.editar) return;

        const id = $(this).data('id');
        const nuevoEstado = this.checked ? 'activo' : 'inactivo';

        fetch(`${APP_URL}/mascotas/toggleEstado`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&estado=${nuevoEstado}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // No es necesario mostrar un popup en cada cambio de estado exitoso
                // Simplemente se puede recargar la tabla para ver el cambio reflejado,
                // aunque el cambio visual del switch ya es una confirmación.
                // tablaMascotas.ajax.reload(null, false);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                $(this).prop('checked', !this.checked); // Revertir si falla
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación.' });
            $(this).prop('checked', !this.checked);
        });
    });

    // --- GESTIÓN DE MODALES (Crear y Editar) ---
    const modalElement = document.getElementById('modalMascota');
    const modal = new bootstrap.Modal(modalElement);
    const modalContent = modalElement.querySelector('.modal-content');

    function loadFormInModal(url) {
        fetch(url)
            .then(response => response.ok ? response.text() : Promise.reject('Error al cargar el formulario.'))
            .then(html => {
                modalContent.innerHTML = html;
                modal.show();
            })
            .catch(error => Swal.fire('Error', error.toString(), 'error'));
    }

    // Verificar que los elementos existen antes de agregar event listeners
    const btnNuevaMascota = document.getElementById('btnNuevaMascota');
    const btnNuevaMascotaFlotante = document.getElementById('btnNuevaMascotaFlotante');

    if (btnNuevaMascota) {
        btnNuevaMascota.addEventListener('click', () => {
            loadFormInModal(`${APP_URL}/mascotas/cargarFormulario`);
        });
    }

    if (btnNuevaMascotaFlotante) {
        btnNuevaMascotaFlotante.addEventListener('click', () => {
            loadFormInModal(`${APP_URL}/mascotas/cargarFormulario`);
        });
    }

    $('#tablaMascotas tbody').on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        loadFormInModal(`${APP_URL}/mascotas/cargarFormulario/${id}`);
    });

    modalElement.addEventListener('submit', function(e) {
        if (e.target && e.target.id === 'formMascota') {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            
            fetch(form.action, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modal.hide();
                        tablaMascotas.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Éxito', text: data.message, timer: 2000, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', html: data.message || 'Ocurrió un error.' });
                    }
                })
                .catch(() => Swal.fire('Error', 'No se pudo procesar la solicitud.', 'error'));
        }
    });
    
    // Eliminar Mascota
    $('#tablaMascotas tbody').on('click', '.btn-eliminar', function() {
        if (!PERMISOS.eliminar) return;

        const id = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡eliminar!',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`${APP_URL}/mascotas/eliminar/${id}`, { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            tablaMascotas.ajax.reload(null, false);
                            Swal.fire('¡Eliminado!', data.message, 'success');
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'No se pudo comunicar con el servidor.', 'error'));
            }
        });
    });

    // Función para inicializar plugins como Select2 dentro del modal
    function initializeModalPlugins() {
        if ($(modalElement).find('.select2').length) {
            $(modalElement).find('.select2').select2({
                dropdownParent: $(modalElement)
            });
        }
    }
}); 