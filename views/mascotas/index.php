<?php
// Test: Esto es un comentario de prueba
$titulo = "Gestión de Mascotas";
$subtitulo = "Administración de mascotas y sus dispositivos de monitoreo.";
?>

<div class="container-fluid pt-3">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><?= $subtitulo ?? 'Listado' ?></h5>
            <button id="btnNuevaMascota" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Mascota
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaMascotas" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Fecha de Nacimiento</th>
                            <th>Propietario</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Contenedor para la configuración que necesita el JS -->
<div id="mascotas-config" 
     data-base-url="<?php echo rtrim(BASE_URL, '/') . '/'; ?>"
     data-app-url="<?php echo rtrim(BASE_URL, '/'); ?>"
     data-permiso-editar="<?php echo verificarPermiso('editar_mascotas') ? 'true' : 'false'; ?>"
     data-permiso-eliminar="<?php echo verificarPermiso('eliminar_mascotas') ? 'true' : 'false'; ?>">
</div>

<!-- Modal genérico para el formulario de Mascota -->
<div class="modal fade" id="mainModal" tabindex="-1" role="dialog" aria-labelledby="mainModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mainModalLabel">Formulario de Mascota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- El contenido del formulario se cargará aquí vía AJAX -->
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/mascotas.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnNuevaMascota').addEventListener('click', function() {
        editarMascota(null);
    });
});
</script>