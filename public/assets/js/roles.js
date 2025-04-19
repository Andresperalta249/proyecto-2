$(document).ready(function() {
    // Inicializar DataTable
    $('#rolesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        order: [[0, 'asc']],
        pageLength: 10,
        responsive: true
    });

    // Función para editar rol
    window.editarRol = function(id) {
        $.ajax({
            url: '/Proyecto 2/controllers/RoleController.php',
            type: 'GET',
            data: {
                action: 'getPermisos',
                id: id
            },
            success: function(response) {
                $('#rolId').val(id);
                $('#nombre').val(response.nombre);
                $('#descripcion').val(response.descripcion);
                $('#estado').val(response.estado);
                
                // Marcar los permisos del rol
                $('input[name="permisos[]"]').prop('checked', false);
                response.permisos.forEach(function(permiso) {
                    $(`input[name="permisos[]"][value="${permiso.id}"]`).prop('checked', true);
                });
                
                $('#modalRole').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Error al cargar los datos del rol'
                });
            }
        });
    };

    // Función para eliminar rol
    window.eliminarRol = function(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar este rol?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/Proyecto 2/controllers/RoleController.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Error al eliminar el rol'
                        });
                    }
                });
            }
        });
    };

    // Función para cambiar estado
    window.cambiarEstado = function(id, nuevoEstado) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas ${nuevoEstado === 'activo' ? 'activar' : 'desactivar'} este rol?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: nuevoEstado === 'activo' ? '#28a745' : '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/Proyecto 2/controllers/RoleController.php',
                    type: 'POST',
                    data: {
                        action: 'updateEstado',
                        role_id: id,
                        estado: nuevoEstado
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            if (response.usuarios_vinculados) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Advertencia',
                                    text: response.message,
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, continuar',
                                    cancelButtonText: 'Cancelar'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        cambiarEstadoConfirmado(id, nuevoEstado);
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Error al cambiar el estado del rol'
                        });
                    }
                });
            }
        });
    };

    // Función para confirmar cambio de estado
    function cambiarEstadoConfirmado(id, nuevoEstado) {
        $.ajax({
            url: '/Proyecto 2/controllers/RoleController.php',
            type: 'POST',
            data: {
                action: 'updateEstado',
                role_id: id,
                estado: nuevoEstado,
                confirmar: true
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Error al cambiar el estado del rol'
                });
            }
        });
    }
}); 