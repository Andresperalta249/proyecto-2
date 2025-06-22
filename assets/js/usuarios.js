document.addEventListener('DOMContentLoaded', function() {
    // Obtenemos la instancia del modal UNA SOLA VEZ
    const mainModalEl = document.getElementById('mainModal');
    const mainModal = new bootstrap.Modal(mainModalEl);
    
    const tablaUsuarios = new DataTable('#tablaUsuarios', {
        responsive: true,
        ajax: {
            url: 'http://localhost/proyecto-2/usuarios/obtenerUsuarios',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'nombre' },
            { data: 'email' },
            { data: 'telefono' },
            { data: 'rol' },
            {
                data: 'estado',
                render: function() { return ''; } 
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let html = '';
                    if (row.puede_editar) {
                        html += `<button class="btn btn-sm btn-primary btn-editar" data-id="${row.id}" title="Editar"><i class="fas fa-edit"></i></button> `;
                    }
                    if (row.puede_eliminar) {
                        html += `<button class="btn btn-sm btn-danger btn-eliminar" data-id="${row.id}" title="Eliminar"><i class="fas fa-trash"></i></button>`;
                    }
                    return html;
                }
            }
        ],
        createdRow: function(row, data) {
            const estadoCell = $('td', row).eq(5);
            const switchContainer = $('<div>').addClass('form-check form-switch');
            const switchInput = $('<input>').attr({
                type: 'checkbox',
                class: 'form-check-input',
                id: `switch-${data.id}`,
                checked: data.estado === 'activo'
            });
            estadoCell.html(switchContainer.append(switchInput));
        },
        language: {
            url: 'http://localhost/proyecto-2/assets/js/i18n/Spanish.json'
        },
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        ordering: true,
        dom: 'frtip',
        buttons: []
    });

    // Listener para el switch de estado
    $('#tablaUsuarios tbody').on('change', '.form-check-input', function() {
        const id = this.id.split('-')[1];
        const estado = this.checked ? 'activo' : 'inactivo';

        $.ajax({
            url: `http://localhost/proyecto-2/usuarios/toggleEstado`,
            type: 'POST',
            data: { id, estado },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Éxito', text: response.message, timer: 1500, showConfirmButton: false });
                    tablaUsuarios.ajax.reload(null, false);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                    // Revertir el switch si la operación falla
                    $(`#switch-${id}`).prop('checked', !this.checked);
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'No se pudo comunicar con el servidor.' });
                $(`#switch-${id}`).prop('checked', !this.checked);
            }
        });
    });

    // Listeners para los botones de acción
    $('#tablaUsuarios tbody').on('click', '.btn-editar', function() {
        editarUsuario($(this).data('id'));
    });
    $('#tablaUsuarios tbody').on('click', '.btn-eliminar', function() {
        eliminarUsuario($(this).data('id'));
    });
    
    // Listener para el botón global de "Nuevo Usuario"
    $('#btnNuevoUsuario').on('click', function() {
        editarUsuario(null);
    });

    // Listener para guardar el formulario del modal
    $(document).on('submit', '#formUsuario', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    mainModal.hide();
                    Swal.fire('Éxito', response.message, 'success');
                    tablaUsuarios.ajax.reload();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                 Swal.fire('Error', 'No se pudo guardar el usuario.', 'error');
            }
        });
    });

    // Hacemos las funciones globales para que sean accesibles desde el HTML
    window.editarUsuario = function(id) {
        const url = `http://localhost/proyecto-2/usuarios/cargarFormulario/${id || ''}`;
        const modalTitle = id ? 'Editar Usuario' : 'Nuevo Usuario';
        $('#mainModalLabel').text(modalTitle);
        $('#mainModal .modal-body').load(url, function(response, status) {
            if (status === "error") {
                Swal.fire('Error', 'No se pudo cargar el formulario.', 'error');
            } else {
                mainModal.show();
            }
        });
    }

    window.eliminarUsuario = function(id) {
        Swal.fire({
            title: '¿Estás seguro?', text: "Esta acción no se puede revertir.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡elimínalo!', cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `http://localhost/proyecto-2/usuarios/eliminarUsuario/${id}`,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('¡Eliminado!', response.message, 'success');
                            tablaUsuarios.ajax.reload();
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