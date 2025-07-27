// Constantes y utilidades
const API_ROLES = {
    list: '/roles/list',
    get: '/roles/get',
    save: '/roles/save',
    delete: '/roles/delete',
    cambiarEstado: '/roles/cambiarEstado'
};

// Funciones de utilidad
const utils = {
    mostrarError(mensaje) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: mensaje
        });
    },
    
    mostrarExito(mensaje) {
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: mensaje
        });
    },
    
    confirmarAccion(titulo, texto) {
        return Swal.fire({
            title: titulo,
            text: texto,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí',
            cancelButtonText: 'Cancelar'
        });
    },
    
    formatearEstado(estado) {
        return estado.charAt(0).toUpperCase() + estado.slice(1);
    }
};

// Variable global para almacenar los permisos
let permisos = [];

// Función para cargar los roles
function cargarRoles() {
    $.ajax({
        url: APP_URL + API_ROLES.list,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const roles = response.data;
                const tbody = document.getElementById('tbodyRoles');
                
                if (!tbody) {
                    console.error('No se encontró el elemento tbodyRoles');
                    return;
                }
                
                tbody.innerHTML = '';
                
                roles.forEach(rol => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="id-azul">${rol.id_rol}</td>
                        <td>${rol.nombre}</td>
                        <td>
                            ${rol.id_rol > 3 ? 
                                `<div class="form-check form-switch d-flex align-items-center mb-0">
                                    <input class="form-check-input cambiar-estado-rol" type="checkbox" 
                                           data-id="${rol.id_rol}" ${rol.estado === 'activo' ? 'checked' : ''}>
                                    <label class="form-check-label ms-2">
                                        ${utils.formatearEstado(rol.estado)}
                                    </label>
                                </div>` :
                                `<span class="badge bg-${rol.estado === 'activo' ? 'success' : 'danger'}">
                                    ${utils.formatearEstado(rol.estado)}
                                </span>`
                            }
                        </td>
                        <td>${rol.descripcion || 'Sin descripción'}</td>
                        <td>
                            <button class="btn-accion btn-primary ver-detalles" data-id="${rol.id_rol}" 
                                    data-bs-toggle="modal" data-bs-target="#modalDetalles">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" 
                                     stroke-width="1.5" stroke="currentColor" width="22" height="22">
                                    <path stroke-linecap="round" stroke-linejoin="round" 
                                          d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z" />
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" fill="none" />
                                </svg>
                            </button>
                        </td>
                        <td>
                            ${rol.id_rol > 3 ? 
                                `<button class="btn-accion btn-info editar-rol" data-id="${rol.id_rol}" 
                                         data-bs-toggle="modal" data-bs-target="#modalRol">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-accion btn-danger eliminar-rol" data-id="${rol.id_rol}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>` :
                                `<span class="text-muted" title="Rol protegido"><i class="fas fa-lock"></i> No editable</span>`
                            }
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                utils.mostrarError('Error al cargar los roles: ' + response.error);
            }
        },
        error: function(xhr, status, error) {
            utils.mostrarError('Error en la petición AJAX: ' + error);
        }
    });
}

// Función para verificar permisos
function tienePermiso(permiso, permisosRol) {
    if (!permisosRol || !Array.isArray(permisosRol)) {
        return false;
    }
    return permisosRol.includes(permiso);
}

// Función para mostrar éxito
function mostrarExito(mensaje) {
    Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: mensaje
    });
}

// Evento para cambiar el estado de un rol
$(document).on('change', '.cambiar-estado-rol', function() {
    const idRol = $(this).data('id');
    const nuevoEstado = $(this).prop('checked') ? 'activo' : 'inactivo';
    
    $.ajax({
        url: APP_URL + API_ROLES.cambiarEstado,
        method: 'POST',
        data: { id: idRol, estado: nuevoEstado },
        success: function(response) {
            if (response.success) {
                utils.mostrarExito('Estado actualizado correctamente');
                cargarRoles();
            } else {
                utils.mostrarError('Error al actualizar el estado: ' + response.error);
            }
        },
        error: function(xhr, status, error) {
            utils.mostrarError('Error en la petición AJAX: ' + error);
        }
    });
});

// Evento para ver detalles de un rol
$(document).on('click', '.ver-detalles', function() {
    const idRol = $(this).data('id');
    
    $.ajax({
        url: APP_URL + API_ROLES.get,
        method: 'GET',
        data: { id: idRol },
        success: function(response) {
            if (response.success) {
                const rol = response.data;
                $('#detallesNombre').text(rol.nombre);
                $('#detallesDescripcion').text(rol.descripcion || 'Sin descripción');
                $('#detallesEstado').text(utils.formatearEstado(rol.estado));
                $('#detallesPermisos').html(rol.permisos.map(p => `<li>${p}</li>`).join(''));
            } else {
                utils.mostrarError('Error al cargar los detalles: ' + response.error);
            }
        },
        error: function(xhr, status, error) {
            utils.mostrarError('Error en la petición AJAX: ' + error);
        }
    });
});

// Evento para editar un rol
$(document).on('click', '.editar-rol', function() {
    const idRol = $(this).data('id');
    
    $.ajax({
        url: APP_URL + API_ROLES.get,
        method: 'GET',
        data: { id: idRol },
        success: function(response) {
            if (response.success) {
                const rol = response.data;
                $('#id_rol').val(rol.id_rol);
                $('#nombre').val(rol.nombre);
                $('#descripcion').val(rol.descripcion);
                $('#estado').val(rol.estado);
                
                // Marcar los permisos seleccionados
                $('.permiso-checkbox').prop('checked', false);
                if (Array.isArray(rol.permiso_ids)) {
                    rol.permiso_ids.forEach(id => {
                        $(`#permiso_${id}`).prop('checked', true);
                    });
                }
            } else {
                utils.mostrarError('Error al cargar los datos del rol: ' + response.error);
            }
        },
        error: function(xhr, status, error) {
            utils.mostrarError('Error en la petición AJAX: ' + error);
        }
    });
});

// Evento para eliminar un rol
$(document).on('click', '.eliminar-rol', function() {
    const idRol = $(this).data('id');
    
    utils.confirmarAccion('¿Estás seguro?', 'Esta acción no se puede deshacer')
        .then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: APP_URL + API_ROLES.delete,
                    method: 'POST',
                    data: { id: idRol },
                    success: function(response) {
                        if (response.success) {
                            utils.mostrarExito('Rol eliminado correctamente');
                            cargarRoles();
                        } else {
                            utils.mostrarError('Error al eliminar el rol: ' + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        utils.mostrarError('Error en la petición AJAX: ' + error);
                    }
                });
            }
        });
});

// Evento para crear/editar rol
$('#formRol').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: APP_URL + API_ROLES.save,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                utils.mostrarExito('Rol guardado correctamente');
                const modalRol = bootstrap.Modal.getInstance(document.getElementById('modalRol'));
                if (modalRol) {
                    modalRol.hide();
                }
                cargarRoles();
            } else {
                utils.mostrarError('Error al guardar el rol: ' + response.error);
            }
        },
        error: function(xhr, status, error) {
            utils.mostrarError('Error en la petición AJAX: ' + error);
        }
    });
});

// Función para cargar los permisos
function cargarPermisos() {
    $.ajax({
        url: APP_URL + '/roles/getPermisos',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                permisos = response.data;
            } else {
                utils.mostrarError('Error al cargar los permisos: ' + response.error);
            }
        },
        error: function(xhr, status, error) {
            utils.mostrarError('Error en la petición AJAX: ' + error);
        }
    });
}

// Cargar los roles al iniciar
$(document).ready(function() {
    // Verificar que estamos en la página correcta
    if (document.getElementById('tbodyRoles')) {
        cargarRoles();
        
        // Inicializar los modales de Bootstrap
        const modals = ['modalRol', 'modalDetalles'];
        modals.forEach(modalId => {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
            }
        });
    }
});

// Función global para guardar el rol (copiada desde la vista)
function guardarRol() {
    const form = document.getElementById('rolForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        Swal.fire({
            icon: 'warning',
            title: 'Permisos requeridos',
            text: 'Selecciona al menos un permiso.'
        });
        return;
    }
    const formData = new FormData(form);
    const idRol = formData.get('id_rol');
    const nombre = formData.get('nombre');
    const descripcion = formData.get('descripcion');
    const url = idRol ? 'roles/update' : 'roles/create';
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: `El rol "${nombre}" (${descripcion}) se creó con éxito`,
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Ocurrió un error al crear el rol.'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ha ocurrido un error al procesar la solicitud'
        });
    });
}

window.verPermisos = function(id) {
    $.ajax({
        url: APP_URL + '/roles/getPermisos',
        type: 'GET',
        data: { id },
        success: function(response) {
            if (response.success) {
                let permisosHtml = '<ul class="list-group">';
                response.permisos.forEach(permiso => {
                    permisosHtml += `<li class="list-group-item">
                        <strong>${permiso.nombre}</strong>
                        <br>
                        <small class="text-muted">${permiso.descripcion}</small>
                    </li>`;
                });
                permisosHtml += '</ul>';
                Swal.fire({
                    title: 'Permisos del Rol',
                    html: permisosHtml,
                    width: '600px',
                    confirmButtonText: 'Cerrar'
                });
            } else {
                utils.mostrarError(response.message || 'Error al obtener los permisos');
            }
        },
        error: function() {
            utils.mostrarError('Error al obtener los permisos');
        }
    });
} 