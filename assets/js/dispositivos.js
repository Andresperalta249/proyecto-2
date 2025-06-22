$(document).ready(function() {
    // Obtener configuración desde el DOM
    const configElement = document.getElementById('dispositivos-config');
    if (!configElement) {
        console.error('Elemento de configuración no encontrado');
        return;
    }

    // Verificar que estamos en la página correcta
    if (!document.getElementById('tablaDispositivos')) {
        return;
    }

    // Verificar que la tabla realmente existe y tiene contenido
    if (!document.getElementById('tablaDispositivos').querySelector('thead') || !document.getElementById('tablaDispositivos').querySelector('tbody')) {
        console.log('La tabla de dispositivos no tiene la estructura correcta - saliendo');
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
    const tablaElement = document.getElementById('tablaDispositivos');
    if (!tablaElement) {
        console.error('La tabla #tablaDispositivos no existe en el DOM');
        return;
    }

    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables no está disponible');
        return;
    }

    const tablaDispositivos = $('#tablaDispositivos').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/proyecto-2/dispositivos/obtenerDispositivos",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "nombre" },
            { "data": "mac" },
            { "data": "usuario" },
            { 
                "data": "disponible",
                "render": function(data, type, row) {
                    return data === 'Disponible' ? 
                        '<span class="badge bg-success">Sí</span>' : 
                        '<span class="badge bg-secondary">No</span>';
                }
            },
            { 
                "data": "estado",
                "render": function() { return ''; } // El switch se crea en createdRow
            },
            { "data": "mascota" },
            { 
                "data": "id",
                "render": function(data, type, row) {
                    let botones = '';
                    if (row.puede_editar) {
                        botones += `<button class="btn btn-sm btn-info btn-editar" data-id="${data}" title="Editar"><i class="fas fa-edit"></i></button>`;
                    }
                    if (row.puede_eliminar) {
                        botones += ` <button class="btn btn-sm btn-danger btn-eliminar" data-id="${data}" title="Eliminar"><i class="fas fa-trash-alt"></i></button>`;
                    }
                    return botones || '<span class="text-muted">Sin permisos</span>';
                },
                "orderable": false,
                "searchable": false,
                "width": "100px"
            }
        ],
        "createdRow": function(row, data) {
            // Columna de Estado (índice 5)
            const estadoCell = $('td', row).eq(5);
            const switchContainer = $('<div>').addClass('form-check form-switch');
            const switchInput = $('<input>').attr({
                type: 'checkbox',
                class: 'form-check-input',
                id: `switch-dispositivo-${data.id}`,
                checked: data.estado === 'activo'
            });
            estadoCell.html(switchContainer.append(switchInput));
        },
        "language": {
            "url": "/proyecto-2/assets/js/i18n/Spanish.json"
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
            if ($.fn.DataTable.isDataTable('#tablaDispositivos')) {
                const newPageLength = getDynamicPageLength();
                $('#tablaDispositivos').DataTable().page.len(newPageLength).draw();
            }
        }, 250);
    });

    // --- GESTIÓN DE MODALES (Crear y Editar) ---
    const modalElement = document.getElementById('modalDispositivo');
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

    // Listener para el switch de estado
    $('#tablaDispositivos tbody').on('change', '.form-check-input', function() {
        const id = this.id.split('-')[2]; // switch-dispositivo-{id}
        const estado = this.checked;

        $.ajax({
            url: '/proyecto-2/dispositivos/toggleEstado',
            type: 'POST',
            data: { id, estado },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Éxito', text: response.message, timer: 1500, showConfirmButton: false });
                    tablaDispositivos.ajax.reload(null, false);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'No se pudo comunicar con el servidor.' });
            }
        });
    });

    // Verificar que los elementos existen antes de agregar event listeners
    const btnNuevoDispositivo = document.getElementById('btnNuevoDispositivo');

    if (btnNuevoDispositivo) {
        btnNuevoDispositivo.addEventListener('click', () => {
            loadFormInModal('/proyecto-2/dispositivos/cargarFormulario');
        });
    }

    $('#tablaDispositivos tbody').on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        loadFormInModal(`/proyecto-2/dispositivos/cargarFormulario/${id}`);
    });

    // Eliminar dispositivo
    $('#tablaDispositivos tbody').on('click', '.btn-eliminar', function() {
        const id = $(this).data('id');
        const nombre = $(this).closest('tr').find('td:eq(1)').text();
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Realmente quieres eliminar el dispositivo "${nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/proyecto-2/dispositivos/eliminar', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        tablaDispositivos.ajax.reload(null, false);
                        Swal.fire('¡Eliminado!', 'El dispositivo ha sido eliminado correctamente.', 'success');
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo eliminar el dispositivo', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'No se pudo eliminar el dispositivo', 'error');
                });
            }
        });
    });

    // Enviar formulario de crear/editar
    modalElement.addEventListener('submit', function(e) {
        if (e.target && e.target.id === 'formDispositivo') {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            
            fetch(form.action, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modal.hide();
                        tablaDispositivos.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Éxito', text: data.message, timer: 2000, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error de Validación', html: data.message || 'Ocurrió un error.' });
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    Swal.fire('Error', 'No se pudo procesar la solicitud.', 'error');
                });
        }
    });

    // Cerrar modal y limpiar formulario
    modalElement.addEventListener('hidden.bs.modal', function() {
        const form = this.querySelector('form');
        if (form) {
            form.reset();
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        }
    });
}); 