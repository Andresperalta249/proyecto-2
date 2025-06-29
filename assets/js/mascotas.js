/**
 * Gestión de Mascotas
 * ==================
 * 
 * Archivo: assets/js/mascotas.js
 * 
 * Propósito:
 *   - Funcionalidades para la gestión de mascotas.
 *   - CRUD de mascotas (crear, leer, actualizar, eliminar).
 *   - Validación de formularios de mascotas.
 * 
 * Funciones principales:
 *   - inicializarMascotas(): Configura la gestión de mascotas.
 *   - cargarMascotas(): Carga la lista de mascotas.
 *   - guardarMascota(): Guarda una mascota nueva o existente.
 *   - eliminarMascota(): Elimina una mascota.
 * 
 * Uso:
 *   Este archivo se usa en las páginas de gestión de mascotas para
 *   manejar todas las operaciones relacionadas con mascotas.
 */

document.addEventListener('DOMContentLoaded', function() {
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

    // Obtenemos la instancia del modal UNA SOLA VEZ
    const mainModalEl = document.getElementById('mainModal');
    const mainModal = new bootstrap.Modal(mainModalEl);
    
    const tablaMascotas = new DataTable('#tablaMascotas', {
        responsive: true,
        ajax: {
            url: `${configElement.dataset.baseUrl || window.location.origin + '/proyecto-2/'}mascotas/listar`,
            dataSrc: 'data'
        },
        columns: [
            { data: 'id_mascota' },
            { data: 'nombre' },
            { data: 'especie' },
            { data: 'fecha_nacimiento' },
            { data: 'propietario_nombre' },
            {
                data: 'estado',
                render: function() { return ''; } // El switch se crea en createdRow
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    // Volvemos a usar data-attributes y clases para los listeners
                    return `
                        <button class="btn btn-sm btn-info btn-ver" data-id="${row.id_mascota}" title="Ver"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-primary btn-editar" data-id="${row.id_mascota}" title="Editar"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger btn-eliminar" data-id="${row.id_mascota}" title="Eliminar"><i class="fas fa-trash"></i></button>
                    `;
                }
            }
        ],
        createdRow: function(row, data) {
            // Columna de Estado (índice 5)
            const estadoCell = $('td', row).eq(5);
            const switchContainer = $('<div>').addClass('form-check form-switch');
            const switchInput = $('<input>').attr({
                type: 'checkbox',
                class: 'form-check-input',
                id: `switch-${data.id_mascota}`,
                checked: data.estado === 'activo'
            });
            estadoCell.html(switchContainer.append(switchInput));
        },
        language: {
            url: `${configElement.dataset.baseUrl || window.location.origin + '/proyecto-2/'}assets/js/i18n/Spanish.json`
        },
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        ordering: true,
        dom: 'frtip',
        buttons: []
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

    // Listener para el switch de estado
    $('#tablaMascotas tbody').on('change', '.form-check-input', function() {
        const id = this.id.split('-')[1];
        const estado = this.checked;

        $.ajax({
            url: `${configElement.dataset.baseUrl || window.location.origin + '/proyecto-2/'}mascotas/toggleEstado`,
            type: 'POST',
            data: { id, estado },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Éxito', text: response.message, timer: 1500, showConfirmButton: false });
                    tablaMascotas.ajax.reload(null, false);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'No se pudo comunicar con el servidor.' });
            }
        });
    });

    // Listeners para los botones de acción
    $('#tablaMascotas tbody').on('click', '.btn-ver', function() {
        editarMascota($(this).data('id'));
    });
    $('#tablaMascotas tbody').on('click', '.btn-editar', function() {
        editarMascota($(this).data('id'));
    });
    $('#tablaMascotas tbody').on('click', '.btn-eliminar', function() {
        eliminarMascota($(this).data('id'));
    });

    // Listener para guardar el formulario del modal
    $(document).on('submit', '#formMascota', function(e) {
        e.preventDefault();
        
        // Validación especial para el campo propietario si está presente
        const propietarioField = $(this).find('#usuario_id');
        if (propietarioField.length && propietarioField.attr('required') && !propietarioField.val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo Obligatorio',
                text: 'Debe seleccionar un propietario para la mascota.',
                confirmButtonText: 'Entendido'
            });
            propietarioField.focus();
            return false;
        }
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    mainModal.hide(); // Usamos la instancia para cerrar el modal
                    Swal.fire('Éxito', response.message, 'success');
                    tablaMascotas.ajax.reload();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                 Swal.fire('Error', 'No se pudo guardar la mascota.', 'error');
            }
        });
    });

    // Hacemos las funciones globales para que sean accesibles desde el HTML
    window.editarMascota = function(id) {
        const baseUrl = configElement.dataset.baseUrl || window.location.origin + '/proyecto-2/';
        const url = `${baseUrl}mascotas/cargarFormulario/${id || ''}`;
        const modalTitle = id ? 'Editar Mascota' : 'Nueva Mascota';
        
        // Actualizar título del modal
        $('#mainModalLabel').text(modalTitle);
        
        // Mostrar loading en el modal
        $('#mainModal .modal-body').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Cargando...</p></div>');
        mainModal.show();
        
        // Cargar contenido con AJAX mejorado
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#mainModal .modal-body').html(response);
                // Inicializar cualquier plugin necesario
                initializeModalPlugins();
            },
            error: function(xhr, status, error) {
                console.error('Error loading form:', error);
                mainModal.hide();
                Swal.fire('Error', 'No se pudo cargar el formulario.', 'error');
            }
        });
    }

    window.eliminarMascota = function(id) {
        Swal.fire({
            title: '¿Estás seguro?', text: "No podrás revertir esto.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡elimínalo!', cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const baseUrl = configElement.dataset.baseUrl || window.location.origin + '/proyecto-2/';
                $.ajax({
                    url: `${baseUrl}mascotas/eliminar/${id}`,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('¡Eliminado!', response.message, 'success');
                            tablaMascotas.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Ocurrió un error de red.', 'error');
                    }
                });
            }
        });
    }
});

// Función para inicializar plugins como Select2 dentro del modal
function initializeModalPlugins() {
    const modalElement = $('#mainModal');
    
    // Inicializar Select2 si existe
    if (modalElement.find('.select2').length) {
        modalElement.find('.select2').select2({
            dropdownParent: modalElement
        });
    }
    
    // Manejar mostrar/ocultar el campo propietario según permisos
    const propietarioField = modalElement.find('#usuario_id').closest('.mb-3');
    const puedeAsignarPropietario = modalElement.find('[data-puede-asignar-propietario]').data('puede-asignar-propietario');
    
    if (propietarioField.length) {
        if (puedeAsignarPropietario) {
            propietarioField.show();
            modalElement.find('#usuario_id').attr('required', true);
        } else {
            propietarioField.hide();
            modalElement.find('#usuario_id').removeAttr('required');
        }
    }
} 