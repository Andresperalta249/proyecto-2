<?php
// Permisos del usuario
$puedeCrear = true; // Permitimos que todos los usuarios puedan crear mascotas
$puedeEditar = in_array('editar_mascotas', $_SESSION['permissions'] ?? []);
$puedeEliminar = in_array('eliminar_mascotas', $_SESSION['permissions'] ?? []);
$esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]); // 1: Superadmin, 2: Admin

// Determinar el título según permisos
$tituloMascotas = (function_exists('verificarPermiso') && verificarPermiso('ver_todas_mascotas')) ? 'Todas las Mascotas' : 'Mis Mascotas';

// Paginación clásica
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$totalMascotas = isset(
    $totalMascotas) ? $totalMascotas : count($mascotas);
$totalPages = ceil($totalMascotas / $perPage);
$start = ($page - 1) * $perPage;
$mascotasPagina = array_slice($mascotas, $start, $perPage);
?>

<div class="contenedor-sistema">
    <div class="contenedor-sistema-header">
        <div class="header-content">
            <div class="header-title">
                <i class="fas fa-paw"></i>
                Lista de Mascotas
            </div>
            <div class="header-search">
                <input type="text" class="form-control" id="inputBusquedaMascota" placeholder="Buscar por nombre, especie, propietario o estado...">
            </div>
        </div>
    </div>
    <div class="contenedor-sistema-body">
        <table class="tabla-sistema" id="tablaMascotas">
            <thead>
                <tr>
                    <th class="celda-id">ID</th>
                    <th>Nombre</th>
                    <th>Especie</th>
                    <th>Tamaño</th>
                    <th>Género</th>
                    <th>Propietario</th>
                    <th class="texto-centrado">Edad</th>
                    <th class="celda-estado">Estado</th>
                    <th class="celda-acciones">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyMascotas">
                <?php foreach ($mascotasPagina as $mascota): ?>
                <tr data-id="<?= $mascota['id_mascota'] ?>">
                    <td class="celda-id"><?= $mascota['id_mascota'] ?></td>
                    <td><?= $mascota['nombre'] ?></td>
                    <td><?= $mascota['especie'] ?></td>
                    <td><?= $mascota['tamano'] ?? '-' ?></td>
                    <td><?= $mascota['genero'] ?? '-' ?></td>
                    <td><?= htmlspecialchars($mascota['propietario_nombre'] ?? 'Sin propietario') ?></td>
                    <td class="texto-centrado">
                        <?php
                        if ($mascota['fecha_nacimiento']) {
                            $nacimiento = new DateTime($mascota['fecha_nacimiento']);
                            $hoy = new DateTime();
                            $edad = $hoy->diff($nacimiento);
                            echo $edad->y . ' año' . ($edad->y !== 1 ? 's' : '');
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td class="celda-estado">
                        <label class="switch-estado">
                            <input class="cambiar-estado-mascota" type="checkbox" data-id="<?= $mascota['id_mascota'] ?>" <?= $mascota['estado'] === 'activo' ? 'checked' : '' ?>>
                            <span class="slider-estado"></span>
                        </label>
                    </td>
                    <td class="celda-acciones">
                        <?php if ($puedeEditar || $esAdmin): ?>
                        <button class="btn-accion btn-editar btnEditarMascota" data-id="<?= $mascota['id_mascota'] ?>" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php endif; ?>
                        <?php if ($puedeEliminar || $esAdmin): ?>
                        <button class="btn-accion btn-eliminar btnEliminarMascota" data-id="<?= $mascota['id_mascota'] ?>" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Mascota (para crear y editar) -->
<div class="modal fade" id="modalMascota" tabindex="-1" aria-labelledby="modalMascotaLabel" aria-hidden="true" data-bs-backdrop="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalMascotaLabel">Nueva Mascota</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php include __DIR__ . '/edit_modal.php'; ?>
      </div>
    </div>
  </div>
</div>

<!-- Botón flotante para agregar mascota -->
<?php if ($puedeCrear ?? true): ?>
<button class="btn-flotante-sistema" id="btnNuevaMascotaFlotante" title="Agregar Mascota">
    <i class="fas fa-plus"></i>
</button>
<?php endif; ?>

<script>
    var BASE_URL = "<?= rtrim(BASE_URL ?? APP_URL ?? '/', '/') . '/' ?>";
</script>

<script>
$(document).ready(function() {

    // Búsqueda simple sin DataTables
    $('#inputBusquedaMascota').on('input', function() {
        var searchTerm = this.value.toLowerCase();
        
        $('#tablaMascotas tbody tr').each(function() {
            var rowText = $(this).text().toLowerCase();
            if (rowText.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Manejar clic en fila de mascota
    $('#tablaMascotas tbody').on('click', 'tr', function(e) {
        // Si el clic fue en un botón dentro de la fila, no hacer nada
        if ($(e.target).closest('button').length > 0) return;
        var id = $(this).data('id');
        if (id) {
            editarMascota(id);
        }
    });

    // Manejar cambio de estado
    $(document).on('change', '.cambiar-estado-mascota', function(e) {
        e.stopPropagation(); // Evitar que el clic se propague a la fila
        var id = $(this).data('id');
        var nuevoEstado = $(this).prop('checked') ? 'activo' : 'inactivo';
        var label = $(this).next('label');

        $.ajax({
            url: BASE_URL + 'mascotas/cambiarEstado/' + id,
            type: 'POST',
            data: { estado: nuevoEstado },
            success: function(response) {
                if (response.success) {
                    label.text(nuevoEstado.charAt(0).toUpperCase() + nuevoEstado.slice(1));
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Estado actualizado correctamente'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.error
                    });
                    // Revertir el cambio
                    $(this).prop('checked', !$(this).prop('checked'));
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cambiar el estado'
                });
                // Revertir el cambio
                $(this).prop('checked', !$(this).prop('checked'));
            }
        });
    });

    // Función para editar mascota
    function editarMascota(id) {
        $.ajax({
            url: BASE_URL + 'mascotas/editarModal/' + id,
            type: 'GET',
            success: function(response) {
                $('#modalMascota .modal-body').html(response);
                $('#modalMascota').modal('show');
                initializeSelect2();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la información de la mascota'
                });
            }
        });
    }

    // Manejar envío del formulario
    $(document).on('submit', '#formMascota', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var id = formData.get('id_mascota');

        $.ajax({
            url: BASE_URL + 'mascotas/' + (id ? 'edit/' + id : 'create'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#modalMascota').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.error
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al procesar la solicitud'
                });
            }
        });
    });

    // Inicializar Select2
    function initializeSelect2() {
        if ($.fn.select2) {
            $('.select2').select2({
                dropdownParent: $('#modalMascota'),
                width: '100%'
            });
        }
    }

    // Manejar clic en botón de nueva mascota
    $('#btnNuevaMascotaFlotante').click(function() {
        $.ajax({
            url: BASE_URL + 'mascotas/editarModal',
            type: 'GET',
            success: function(response) {
                $('#modalMascota .modal-body').html(response);
                $('#modalMascota').modal('show');
                initializeSelect2();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el formulario'
                });
            }
        });
    });

    // Manejar clic en botón de monitor
    $(document).on('click', '.btnMonitorMascota', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Evitar que el clic se propague a la fila
        var id = $(this).data('id');
        window.location.href = BASE_URL + 'monitor/device/' + id;
    });
});
</script> 