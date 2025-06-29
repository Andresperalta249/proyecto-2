/**
 * Gestión de Roles
 * ===============
 * 
 * Archivo: assets/js/roles.js
 * 
 * Propósito:
 *   - Funcionalidades para la gestión de roles de usuario.
 *   - CRUD de roles (crear, leer, actualizar, eliminar).
 *   - Gestión de permisos asociados a roles.
 * 
 * Funciones principales:
 *   - inicializarRoles(): Configura la gestión de roles.
 *   - cargarRoles(): Carga la lista de roles.
 *   - guardarRol(): Guarda un rol nuevo o existente.
 *   - eliminarRol(): Elimina un rol.
 * 
 * Uso:
 *   Este archivo se usa en las páginas de gestión de roles para
 *   manejar todas las operaciones relacionadas con roles y permisos.
 */

$(function () {
    let tablaRoles;
    const configElement = $('#roles-config');
    const PERMISOS = {
        editar: configElement.data('permiso-editar'),
        eliminar: configElement.data('permiso-eliminar')
    };

    // --- CONFIGURACIÓN DE DATATABLES ---
    tablaRoles = $('#tablaRoles').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": `${APP_URL}/roles/listar`,
            "type": "POST"
        },
        "columns": [
            { "data": "id_rol" },
            { "data": "nombre" },
            { "data": "descripcion" },
            { "data": "usuarios_count", "className": "text-center" },
            { 
                "data": "permisos_count",
                "title": "PERMISOS",
                "className": "text-center",
                "render": function(data, type, row) {
                    const permisosLista = row.permisos_lista ? row.permisos_lista.replace(/,/g, ',<br>') : 'Sin permisos';
                    return `<span class="badge bg-info" data-bs-toggle="tooltip" data-bs-html="true" title="${permisosLista}">${data}</span>`;
                }
            },
            {
                "data": "estado",
                "title": "ESTADO",
                "orderable": false,
                "className": "text-center",
                "render": function(data, type, row) {
                    const isChecked = data === 'activo';
                    const isDisabled = row.is_protected;
                    return `
                        <div class="form-check form-switch d-flex justify-content-center">
                            <input class="form-check-input toggle-estado" type="checkbox" 
                                   role="switch" id="toggle-${row.id_rol}" 
                                   data-id="${row.id_rol}" 
                                   ${isChecked ? 'checked' : ''}
                                   ${isDisabled ? 'disabled' : ''}
                                   title="${isDisabled ? 'Rol protegido' : 'Cambiar estado'}">
                        </div>`;
                }
            },
            {
                data: null,
                title: 'ACCIONES',
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    if (row.is_protected) {
                        return `
                            <button class="btn btn-sm btn-secondary" disabled title="Rol protegido"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-secondary" disabled title="Rol protegido"><i class="fas fa-lock"></i></button>
                        `;
                    }

                    const canDelete = row.usuarios_count == 0;
                    const deleteButton = canDelete 
                        ? `<button class="btn btn-sm btn-danger btn-eliminar" data-id="${row.id_rol}" title="Eliminar Rol"><i class="fas fa-trash-alt"></i></button>`
                        : `<button class="btn btn-sm btn-secondary" disabled title="Rol asignado a usuarios"><i class="fas fa-lock"></i></button>`;

                    return `
                        <button class="btn btn-sm btn-info btn-editar" data-id="${row.id_rol}" title="Editar Rol"><i class="fas fa-edit"></i></button>
                        ${deleteButton}
                    `;
                }
            }
        ],
        "language": {
            "url": `${APP_URL}/assets/js/i18n/Spanish.json`
        },
        "responsive": false,
        "autoWidth": false,
        "ordering": true,
        "lengthChange": false,
        "dom": 'ftip',
        "drawCallback": function(settings) {
            // Habilitar tooltips de Bootstrap en la tabla
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('#tablaRoles [data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });

    // --- MANEJO DE MODALES ---

    function cargarFormularioEnModal(rolId = null) {
        const url = rolId ? `${APP_URL}/roles/editar/${rolId}` : `${APP_URL}/roles/crear`;
        const modalTitle = rolId ? 'Editar Rol' : 'Nuevo Rol';

        $('#modalRol .modal-title').text(modalTitle);
        $('#modalRol .modal-body').load(url, function(response, status, xhr) {
            if (status === "error") {
                Swal.fire('Error', 'No se pudo cargar el formulario: ' + xhr.statusText, 'error');
                return;
            }
            $('#modalRol').modal('show');
            inicializarEventosFormulario();
        });
    }

    // Abrir modal para NUEVO rol
    $('#btnNuevoRol').on('click', function() {
        cargarFormularioEnModal();
    });

    // Abrir modal para EDITAR rol
    $('#tablaRoles tbody').on('click', '.btn-editar', function() {
        const rolId = $(this).data('id');
        cargarFormularioEnModal(rolId);
    });

    // --- MANEJO DEL FORMULARIO DEL MODAL ---

    function inicializarEventosFormulario() {
        $('#formRol').on('submit', function(e) {
            const nombre = $('#nombre').val().trim();
            const permisos = $("input[name='permisos[]']:checked");
            const descripcion = $('#descripcion').val().trim();
            let errores = [];

            if (!nombre) {
                errores.push('El nombre del rol es obligatorio.');
            }
            if (permisos.length === 0) {
                errores.push('Debes seleccionar al menos un permiso.');
            }
            if (!descripcion) {
                errores.push('La descripción es obligatoria.');
            }

            if (errores.length > 0) {
                Swal.fire('Validación', errores.join('<br>'), 'warning');
                e.preventDefault();
                return false;
            }

            // Validación de nombre único por AJAX (sincrónica para bloquear el submit)
            let esEdicion = $(this).find('input[name="id_rol"]').length > 0;
            let idRol = esEdicion ? $(this).find('input[name="id_rol"]').val() : '';
            let esUnico = true;
            $.ajax({
                url: APP_URL + '/roles/validar-nombre',
                type: 'POST',
                data: { nombre: nombre, id_rol: idRol },
                async: false,
                dataType: 'json',
                success: function(resp) {
                    if (!resp.success) {
                        let mensaje = '';
                        if (resp.errores && Array.isArray(resp.errores)) {
                            mensaje = '<ul style="text-align:left">';
                            resp.errores.forEach(function(err) { mensaje += '<li>' + err + '</li>'; });
                            mensaje += '</ul>';
                        } else if (resp.error) {
                            mensaje = resp.error;
                        } else {
                            mensaje = 'Error desconocido.';
                        }
                        Swal.fire({ icon: 'error', title: 'Validación', html: mensaje });
                        esUnico = false;
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo validar el nombre del rol.', 'error');
                    esUnico = false;
                }
            });
            if (!esUnico) {
                e.preventDefault();
                return false;
            }

            e.preventDefault();

            const btnSubmit = $(this).find('button[type="submit"]');
            btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            const formData = new FormData(this);

            $.ajax({
                url: `${APP_URL}/roles/guardar`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(function(response) {
                if (response.success) {
                    $('#modalRol').modal('hide');
                    Swal.fire('Éxito', response.message, 'success');
                    tablaRoles.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Ocurrió un problema con la solicitud.', 'error');
            }).always(function() {
                btnSubmit.prop('disabled', false).html('Guardar');
            });
        });
    }

    // --- MANEJO DE ELIMINACIÓN ---

    $('#tablaRoles tbody').on('click', '.btn-eliminar', function() {
        const rolId = $(this).data('id');

        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡eliminar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${APP_URL}/roles/eliminar`,
                    type: 'POST',
                    data: { id_rol: rolId },
                    dataType: 'json'
                }).done(function(response) {
                    if (response.success) {
                        Swal.fire('Eliminado', response.message, 'success');
                        tablaRoles.ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Ocurrió un problema con la solicitud.', 'error');
                });
            }
        });
    });

    // --- MANEJO DE CAMBIO DE ESTADO ---
    $('#tablaRoles tbody').on('change', '.toggle-estado', function() {
        const rolId = $(this).data('id');
        const nuevoEstado = this.checked ? 'activo' : 'inactivo';

        $.ajax({
            url: `${APP_URL}/roles/toggleEstado`,
            type: 'POST',
            data: { id: rolId, estado: nuevoEstado },
            dataType: 'json'
        }).done(function(response) {
            if (!response.success) {
                Swal.fire('Error', response.message, 'error');
                $(this).prop('checked', !this.checked); // Revertir
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message || 'Estado actualizado correctamente.',
                    timer: 1200,
                    showConfirmButton: false
                });
            }
        }.bind(this)).fail(function() {
            Swal.fire('Error', 'Ocurrió un problema con la solicitud.', 'error');
            $(this).prop('checked', !this.checked); // Revertir
        }.bind(this));
    });

    // --- LIMPIAR MODAL AL CERRAR ---
    $('#modalRol').on('hidden.bs.modal', function () {
        $(this).find('.modal-body').empty();
        $(this).find('.modal-title').empty();
    });

    // Destruir tooltips antes de recargar la tabla para evitar duplicados
    $('#tablaRoles').on('preDraw.dt', function () {
        $('[data-bs-toggle="tooltip"]').tooltip('dispose');
    });

    // Validación personalizada para el formulario de rol
    $(document).on('submit', '#formRol', function(e) {
        const nombre = $('#nombre').val().trim();
        const permisos = $("input[name='permisos[]']:checked");
        const descripcion = $('#descripcion').val().trim();
        let errores = [];

        if (!nombre) {
            errores.push('El nombre del rol es obligatorio.');
        }
        if (permisos.length === 0) {
            errores.push('Debes seleccionar al menos un permiso.');
        }
        if (!descripcion) {
            errores.push('La descripción es obligatoria.');
        }

        if (errores.length > 0) {
            Swal.fire('Validación', errores.join('<br>'), 'warning');
            e.preventDefault();
            return false;
        }

        // Validación de nombre único por AJAX (sincrónica para bloquear el submit)
        let esEdicion = $(this).find('input[name="id_rol"]').length > 0;
        let idRol = esEdicion ? $(this).find('input[name="id_rol"]').val() : '';
        let esUnico = true;
        $.ajax({
            url: APP_URL + '/roles/validar-nombre',
            type: 'POST',
            data: { nombre: nombre, id_rol: idRol },
            async: false,
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    let mensaje = '';
                    if (resp.errores && Array.isArray(resp.errores)) {
                        mensaje = '<ul style="text-align:left">';
                        resp.errores.forEach(function(err) { mensaje += '<li>' + err + '</li>'; });
                        mensaje += '</ul>';
                    } else if (resp.error) {
                        mensaje = resp.error;
                    } else {
                        mensaje = 'Error desconocido.';
                    }
                    Swal.fire({ icon: 'error', title: 'Validación', html: mensaje });
                    esUnico = false;
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo validar el nombre del rol.', 'error');
                esUnico = false;
            }
        });
        if (!esUnico) {
            e.preventDefault();
            return false;
        }
    });
}); 