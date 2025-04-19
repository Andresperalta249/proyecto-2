$(document).ready(function() {
    // Verificar que las dependencias estén cargadas
    if (typeof toastr === 'undefined') {
        console.error('Error: Toastr no está cargado');
        return;
    }

    if (typeof Swal === 'undefined') {
        console.error('Error: SweetAlert2 no está cargado');
        return;
    }

    // Configuración de toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "timeOut": "3000"
    };

    // Inicializar DataTable
    const table = $('#usersTable').DataTable({
        serverSide: true,
        processing: false,
        ajax: {
            url: '/Proyecto 2/controllers/UserController.php',
            type: 'POST',
            data: function(d) {
                d.action = 'index';
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('Error en la solicitud AJAX:', error);
                toastr.error('Error al cargar los datos de usuarios');
            }
        },
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-id', data.id);
        },
        columns: [
            {
                data: null,
                render: function(data, type, row) {
                    return `<div>
                        <strong class="d-block">${row.nombre}</strong>
                        <small class="text-muted">${row.email}</small>
                    </div>`;
                }
            },
            {
                data: 'rol_nombre',
                render: function(data) {
                    let badgeClass = 'bg-info';
                    if (data === 'Superadministrador') badgeClass = 'bg-danger';
                    else if (data === 'Administrador') badgeClass = 'bg-warning';
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            {
                data: 'mascotas_count',
                className: 'text-center',
                render: function(data) {
                    return `<span class="badge bg-secondary">${data}</span>`;
                }
            },
            {
                data: 'ultimo_acceso',
                render: function(data) {
                    return data || '<span class="text-muted">Nunca</span>';
                }
            },
            {
                data: 'estado',
                className: 'text-center',
                render: function(data) {
                    return data === 1 
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: function(data, type, row) {
                    const buttons = [];
                    
                    // Botón de editar
                    if (row.puede_editar) {
                        buttons.push(`
                            <button type="button" class="btn btn-sm btn-light" onclick="editUser(${row.id})" data-bs-toggle="tooltip" title="Editar">
                                <i class="fas fa-edit text-primary"></i>
                            </button>
                        `);
                    }

                    // Botón de eliminar
                    if (row.puede_eliminar) {
                        buttons.push(`
                            <button type="button" class="btn btn-sm btn-light" onclick="deleteUser(${row.id})" data-bs-toggle="tooltip" title="Eliminar">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                        `);
                    }

                    // Botón de cambiar estado
                    if (row.puede_cambiar_estado) {
                        const toggleIcon = row.estado === 1 ? 'fa-ban' : 'fa-check';
                        const toggleColor = row.estado === 1 ? 'text-danger' : 'text-success';
                        const toggleTitle = row.estado === 1 ? 'Desactivar' : 'Activar';
                        buttons.push(`
                            <button type="button" class="btn btn-sm btn-light" onclick="toggleUserStatus(${row.id})" data-bs-toggle="tooltip" title="${toggleTitle}">
                                <i class="fas ${toggleIcon} ${toggleColor}"></i>
                            </button>
                        `);
                    }

                    return buttons.length ? `<div class="action-buttons">${buttons.join('')}</div>` : '';
                }
            }
        ],
        language: {
            processing: '',
            emptyTable: 'No hay usuarios registrados',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ usuarios',
            infoEmpty: 'Mostrando 0 a 0 de 0 usuarios',
            infoFiltered: '(filtrado de _MAX_ usuarios totales)',
            lengthMenu: 'Mostrar _MENU_ usuarios',
            loadingRecords: '',
            search: 'Buscar:',
            zeroRecords: 'No se encontraron usuarios coincidentes',
            paginate: {
                first: 'Primero',
                last: 'Último',
                next: 'Siguiente',
                previous: 'Anterior'
            }
        },
        order: [[0, 'asc']],
        pageLength: 10,
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Manejar cambios en los filtros
    $('#roleFilter, #statusFilter').on('change', function() {
        table.ajax.reload();
    });

    // Función para validar la fortaleza de la contraseña
    function checkPasswordStrength(password) {
        if (!password) return 0;
        
        let strength = 0;
        
        // Longitud mínima
        if (password.length >= 8) strength += 1;
        
        // Contiene números
        if (/\d/.test(password)) strength += 1;
        
        // Contiene letras minúsculas y mayúsculas
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
        
        // Contiene caracteres especiales
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
        
        return strength;
    }

    // Función para actualizar el indicador de fortaleza de la contraseña
    function updatePasswordFeedback(strength) {
        const passwordInput = $('#password');
        const feedback = $('.password-feedback');
        
        switch(strength) {
            case 0:
                feedback.text('Contraseña muy débil').removeClass('text-warning text-success').addClass('text-danger');
                break;
            case 1:
                feedback.text('Contraseña débil').removeClass('text-success text-danger').addClass('text-warning');
                break;
            case 2:
                feedback.text('Contraseña moderada').removeClass('text-success text-danger').addClass('text-warning');
                break;
            case 3:
                feedback.text('Contraseña fuerte').removeClass('text-warning text-danger').addClass('text-success');
                break;
            case 4:
                feedback.text('Contraseña muy fuerte').removeClass('text-warning text-danger').addClass('text-success');
                break;
        }
    }

    // Toggle password visibility
    $('#togglePassword').on('click', function() {
        const passwordInput = $('#password');
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Validación de email en tiempo real
    $('#email').on('blur', function() {
        const email = $(this).val();
        const userId = $('#userId').val();
        
        if (!email) return;
        
        $.ajax({
            url: '/Proyecto 2/controllers/UserController.php',
            type: 'POST',
            data: {
                action: 'checkEmail',
                email: email,
                userId: userId
            },
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    $('#email').addClass('is-invalid');
                    $('.email-feedback').remove();
                    $('#email').after(`<div class="invalid-feedback email-feedback">${response.message}</div>`);
                } else {
                    $('#email').removeClass('is-invalid').addClass('is-valid');
                    $('.email-feedback').remove();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la validación de email:', error);
                $('#email').addClass('is-invalid');
                $('.email-feedback').remove();
                $('#email').after('<div class="invalid-feedback email-feedback">Error al validar el email</div>');
            }
        });
    });

    // Validación de contraseña en tiempo real
    $('#password').on('input', function() {
        const password = $(this).val();
        
        // Validar cada requerimiento
        const validations = {
            'length': password.length >= 8,
            'uppercase': /[A-Z]/.test(password),
            'lowercase': /[a-z]/.test(password),
            'number': /[0-9]/.test(password),
            'special': /[!@#$%^&*.]/.test(password),
            'space': !/\s/.test(password)
        };

        // Actualizar clases y estilos
        Object.keys(validations).forEach(key => {
            const element = $(`.req-${key}`);
            if (validations[key]) {
                element.removeClass('invalid').addClass('valid');
                element.find('i')
                    .removeClass('fa-times text-danger')
                    .addClass('fa-check text-success');
            } else {
                element.removeClass('valid').addClass('invalid');
                element.find('i')
                    .removeClass('fa-check text-success')
                    .addClass('fa-times text-danger');
            }
        });
        
        // Validar el campo completo
        const isValid = Object.values(validations).every(v => v);
        if (password && isValid) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else if (password) {
            $(this).removeClass('is-valid').addClass('is-invalid');
        } else {
            $(this).removeClass('is-valid is-invalid');
        }

        // Validar confirmación si existe
        const confirmPassword = $('#confirmPassword').val();
        if (confirmPassword) {
            validateConfirmPassword();
        }
    });

    // Función para validar la confirmación de contraseña
    function validateConfirmPassword() {
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();
        const confirmInput = $('#confirmPassword');
        
        if (confirmPassword) {
            if (password === confirmPassword) {
                confirmInput.removeClass('is-invalid').addClass('is-valid');
            } else {
                confirmInput.removeClass('is-valid').addClass('is-invalid');
            }
        } else {
            confirmInput.removeClass('is-valid is-invalid');
        }
    }

    // Validación de confirmación de contraseña en tiempo real
    $('#confirmPassword').on('input', validateConfirmPassword);

    // Mostrar modal de creación
    window.showCreateUserModal = function() {
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('#modalTitle').text('Nuevo Usuario');
        
        // Habilitar campos para nuevo usuario
        $('#email').prop('readonly', false);
        $('#password, #confirmPassword').prop('required', true);
        
        // Mostrar pestaña de información
        $('#info-tab').tab('show');
        
        // Limpiar validaciones
        $('.password-requirements').remove();
        $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        
        $('#userModal').modal('show');
    };

    // Validación del formulario y guardado
    $('#saveUserBtn').on('click', function() {
        const form = $('#userForm')[0];
        const userId = $('#userId').val();
        
        // Validación básica del formulario
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Validación de contraseñas solo si se está creando un usuario o si se ingresó una contraseña
        if (!userId || $('#password').val()) {
            const password = $('#password').val();
            const confirmPassword = $('#confirmPassword').val();

            if (!userId && !password) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La contraseña es requerida para nuevos usuarios'
                });
                $('#password-tab').tab('show');
                return;
            }

            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Las contraseñas no coinciden'
                });
                $('#password-tab').tab('show');
                return;
            }

            if (password && (
                password.length < 8 || 
                !/[A-Z]/.test(password) || 
                !/[a-z]/.test(password) || 
                !/[0-9]/.test(password) || 
                !/[!@#$%^&*]/.test(password) || 
                /\s/.test(password)
            )) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La contraseña no cumple con los requisitos mínimos'
                });
                $('#password-tab').tab('show');
                return;
            }
        }

        const formData = new FormData(form);
        formData.append('action', userId ? 'update' : 'create');

        $.ajax({
            url: '/Proyecto 2/controllers/UserController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#userModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message || 'Usuario guardado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al guardar el usuario'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Error al guardar el usuario'
                });
            }
        });
    });

    // Limpiar modal al cerrar
    $('#userModal').on('hidden.bs.modal', function() {
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('#modalTitle').text('Nuevo Usuario');
        $('.password-feedback').text('');
    });

    // Función para editar usuario
    window.editUser = function(userId) {
        $.ajax({
            url: '/Proyecto 2/controllers/UserController.php',
            type: 'POST',
            data: {
                action: 'get',
                id: userId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const user = response.data;
                    
                    // Llenar datos del usuario
                    $('#userId').val(user.id);
                    $('#nombre').val(user.nombre);
                    $('#email').val(user.email);
                    $('#rol_id').val(user.rol_id);
                    
                    // Bloquear email y quitar requerimiento de contraseña
                    $('#email').prop('readonly', true);
                    $('#password, #confirmPassword').prop('required', false);
                    
                    // Mostrar pestaña de información
                    $('#info-tab').tab('show');
                    
                    // Limpiar campos de contraseña y validaciones
                    $('#password, #confirmPassword').val('');
                    $('.password-requirements').remove();
                    $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
                    
                    $('#modalTitle').text('Editar Usuario');
                    $('#userModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al cargar los datos del usuario'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Error al cargar los datos del usuario'
                });
            }
        });
    };

    // Función para cambiar estado del usuario (definida globalmente)
    window.toggleUserStatus = function(userId) {
        if (!userId) {
            toastr.error('ID de usuario no válido');
            return;
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esto cambiará el estado del usuario',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-primary me-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Deshabilitar la fila mientras se procesa
                const row = $(`#usersTable tr[data-id="${userId}"]`);
                if (row.length) {
                    row.find('button').prop('disabled', true);
                }

                $.ajax({
                    url: '/Proyecto 2/controllers/UserController.php',
                    type: 'POST',
                    data: {
                        action: 'toggleStatus',
                        id: userId
                    },
                    beforeSend: function() {
                        console.log('Enviando solicitud para cambiar estado del usuario:', userId);
                    },
                    success: function(response) {
                        console.log('Respuesta recibida:', response);
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                // Recargar la página para actualizar todos los datos
                                location.reload();
                            });
                        } else {
                            toastr.error(response.message || 'Error al actualizar el estado');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', {
                            status: status,
                            error: error,
                            response: xhr.responseText
                        });
                        toastr.error('Error al comunicarse con el servidor');
                    },
                    complete: function() {
                        // Rehabilitar la fila
                        if (row.length) {
                            row.find('button').prop('disabled', false);
                        }
                        console.log('Solicitud completada');
                    }
                });
            }
        });
    };

    // Función para eliminar usuario
    window.deleteUser = function(userId) {
        Swal.fire({
            title: '¿Eliminar usuario?',
            text: '¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Deshabilitar la fila mientras se procesa
                const row = $(`#usersTable tr[data-id="${userId}"]`);
                if (row.length) {
                    row.find('button').prop('disabled', true);
                }

                $.ajax({
                    url: '/Proyecto 2/controllers/UserController.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        id: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message || 'Usuario eliminado correctamente',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error al eliminar el usuario'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Error al eliminar el usuario'
                        });
                    },
                    complete: function() {
                        // Rehabilitar la fila
                        if (row.length) {
                            row.find('button').prop('disabled', false);
                        }
                    }
                });
            }
        });
    };

    // Manejar el colapso del sidebar
    $('.sidebar-toggle').on('click', function() {
        $('.content-wrapper').toggleClass('sidebar-collapsed');
    });
}); 