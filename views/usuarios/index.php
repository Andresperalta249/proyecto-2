<?php
// DEPURACIÓN: Esto es una prueba de contenido visible desde la vista de usuarios
?>
<div class="contenedor-sistema">
    <div class="contenedor-sistema-header">
        <div class="header-content">
            <div class="header-title">
                <i class="fas fa-users"></i>
                Lista de Usuarios
            </div>
            <div class="header-search">
                <input type="text" id="buscar" class="form-control" placeholder="Buscar por nombre o email...">
            </div>
        </div>
    </div>
    <div class="contenedor-sistema-body">
    <?php 
    // Debug: Verificar si hay usuarios
    if (isset($usuarios)) {
        echo "<!-- Debug: Número de usuarios: " . count($usuarios) . " -->";
    } else {
        echo "<!-- Debug: Variable usuarios no está definida -->";
    }
    ?>
    
    <table class="tabla-sistema" id="tablaUsuarios">
        <thead>
            <tr>
                <th class="celda-id">ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th class="celda-estado">Estado</th>
                <th class="celda-acciones">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($usuarios)): ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td class="celda-id"><?= $usuario['id_usuario'] ?></td>
                        <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td>
                            <span class="badge-estado badge-<?= $usuario['rol_id'] == 3 ? 'inactivo' : ($usuario['rol_id'] == 1 ? 'pendiente' : 'activo') ?>">
                                <?= htmlspecialchars($usuario['rol_nombre'] ?? 'Sin rol') ?>
                            </span>
                        </td>
                        <td class="celda-estado">
                            <label class="switch-estado">
                                <input class="cambiar-estado-usuario" type="checkbox" 
                                       data-id="<?= $usuario['id_usuario'] ?>"
                                       <?= $usuario['estado'] === 'activo' ? 'checked' : '' ?>>
                                <span class="slider-estado"></span>
                            </label>
                        </td>
                        <td class="celda-acciones">
                            <button type="button" class="btn-accion btn-editar" 
                                    onclick="editarUsuario(<?= $usuario['id_usuario'] ?>)" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn-accion btn-eliminar" 
                                    onclick="eliminarUsuario(<?= $usuario['id_usuario'] ?>)" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="mensaje-vacio">
                        <i class="fas fa-users"></i>
                        <div>No se encontraron usuarios</div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Modal para usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="modalUsuarioBody">
      <!-- Aquí se cargará el formulario por AJAX -->
    </div>
  </div>
</div>

<!-- Asegúrate de que jQuery y Bootstrap JS estén cargados antes de este script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {

    // Búsqueda simple sin DataTables
    $('#buscar').on('input', function() {
        var searchTerm = this.value.toLowerCase();
        
        $('#tablaUsuarios tbody tr').each(function() {
            var rowText = $(this).text().toLowerCase();
            if (rowText.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });



    // Cambiar de página
    window.cambiarPagina = function(pagina) {
        const url = new URL(window.location.href);
        url.searchParams.set('pagina', pagina);
        window.location.href = url.toString();
    };

    // Manejar cambio de estado
    $(document).on('change', '.cambiar-estado-usuario', function() {
        const $checkbox = $(this);
        const id = $checkbox.data('id');
        const estado = $checkbox.prop('checked') ? 'activo' : 'inactivo';

        $.post('<?= APP_URL ?>/usuarios/cambiarEstado', { id_usuario: id, estado: estado }, function(response) {
            if (typeof response === 'string') {
                try { response = JSON.parse(response); } catch (e) { response = {}; }
            }
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Estado actualizado',
                    text: response.message || 'El estado del usuario se actualizó correctamente.'
                });
            } else if (response.needsConfirmation) {
                // Mostrar consecuencias y pedir confirmación
                let msg = response.message || 'Esta acción afectará elementos asociados.';
                if (response.data && response.data.mascotas) {
                    msg += `\n\nMascotas asociadas: ${response.data.mascotas.length}`;
                }
                Swal.fire({
                    icon: 'warning',
                    title: '¿Estás seguro?',
                    html: msg.replace(/\n/g, '<br>'),
                    showCancelButton: true,
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Confirmar cambio en cascada
                        $.post('<?= APP_URL ?>/usuarios/confirmarCambioEstado', { id_usuario: id, estado: estado }, function(resp2) {
                            if (typeof resp2 === 'string') {
                                try { resp2 = JSON.parse(resp2); } catch (e) { resp2 = {}; }
                            }
                            if (resp2.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Estado actualizado',
                                    text: resp2.message || 'El estado del usuario y sus asociados se actualizó correctamente.'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: resp2.error || 'Error al cambiar el estado en cascada.'
                                });
                                $checkbox.prop('checked', !($checkbox.prop('checked'))); // Revertir cambio
                            }
                        });
                    } else {
                        $checkbox.prop('checked', !($checkbox.prop('checked'))); // Revertir cambio
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.error || 'Error al cambiar el estado del usuario.'
                });
                $checkbox.prop('checked', !($checkbox.prop('checked'))); // Revertir cambio
            }
        });
    });
});

// Funciones específicas para usuarios
function editarUsuario(id) {
    $.get('<?= APP_URL ?>/usuarios/get?id_usuario=' + id, function(response) {
        $('#modalUsuarioBody').html(response);
        $('#modalUsuario').modal('show');
    });
}

function eliminarUsuario(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= APP_URL ?>/usuarios/eliminar', { id_usuario: id }, function(response) {
                // Asegurar que response sea un objeto
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        response = { success: false, error: 'Error en la respuesta del servidor' };
                    }
                }
                
                if (response.success) {
                    Swal.fire('Eliminado', 'El usuario ha sido eliminado correctamente.', 'success')
                    .then(() => {
                        location.reload();
                    });
                } else if (response.needsConfirmation) {
                    // Mostrar confirmación adicional si hay elementos asociados
                    Swal.fire({
                        icon: 'warning',
                        title: '¿Estás seguro?',
                        html: response.message.replace(/\n/g, '<br>'),
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar todo',
                        cancelButtonText: 'Cancelar',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.post('<?= APP_URL ?>/usuarios/confirmarEliminacion', { id_usuario: id }, function(resp2) {
                                if (typeof resp2 === 'string') {
                                    try { resp2 = JSON.parse(resp2); } catch (e) { resp2 = {}; }
                                }
                                if (resp2.success) {
                                    Swal.fire('Eliminado', 'El usuario y sus elementos asociados han sido eliminados.', 'success')
                                    .then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', resp2.error || 'Error al eliminar en cascada.', 'error');
                                }
                            });
                        }
                    });
                } else {
                    Swal.fire('Error', response.error || 'Error al eliminar el usuario', 'error');
                }
            }).fail(function(xhr, status, error) {
                console.error('Error AJAX:', error);
                Swal.fire('Error', 'Error de conexión al eliminar el usuario', 'error');
            });
        }
    });
}

function agregarUsuario() {
    $.get('<?= APP_URL ?>/usuarios/get', function(response) {
        $('#modalUsuarioBody').html(response);
        $('#modalUsuario').modal('show');
    });
}

// Manejador de eventos para el formulario de usuario
$(document).on('submit', '#formUsuario', function(e) {
    e.preventDefault();
    
    var formData = $(this).serialize();
    var accion = $(this).data('accion');
    
    $.post('<?= APP_URL ?>/usuarios/' + accion, formData, function(response) {
        // Asegurar que response sea un objeto
        if (typeof response === 'string') {
            try {
                response = JSON.parse(response);
            } catch (e) {
                console.error('Error parsing JSON:', e);
                response = { success: false, error: 'Error en la respuesta del servidor' };
            }
        }
        
        if (response.success) {
            Swal.fire('Éxito', response.message || 'Usuario guardado correctamente', 'success')
            .then(() => {
                $('#modalUsuario').modal('hide');
                location.reload();
            });
        } else {
            Swal.fire('Error', response.error || 'Error al guardar el usuario', 'error');
        }
    }).fail(function(xhr, status, error) {
        console.error('Error AJAX:', error);
        Swal.fire('Error', 'Error de conexión al guardar el usuario', 'error');
    });
});
</script>

<!-- Botón flotante para agregar usuario -->
<button type="button" class="btn-flotante-sistema" onclick="agregarUsuario()" title="Agregar Usuario">
    <i class="fas fa-plus"></i>
</button> 