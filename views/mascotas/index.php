<?php
// Test: Esto es un comentario de prueba
$titulo = "Gestión de Mascotas";
$subtitulo = "Administración de mascotas y sus dispositivos de monitoreo.";
?>

<div class="container-fluid pt-3">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Gestión de Mascotas</h3>
                <?php if (verificarPermiso('crear_mascotas')): ?>
                    <button id="btnNuevaMascota" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Mascota
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaMascotas" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Propietario</th>
                            <th>Estado</th>
                            <th>Dispositivo</th>
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
     data-app-url="<?php echo rtrim(BASE_URL, '/'); ?>"
     data-permiso-editar="<?php echo verificarPermiso('editar_mascotas') ? 'true' : 'false'; ?>"
     data-permiso-eliminar="<?php echo verificarPermiso('eliminar_mascotas') ? 'true' : 'false'; ?>">
</div>

<!-- Modal genérico para el formulario de Mascota -->
<div class="modal fade" id="modalMascota" tabindex="-1" role="dialog" aria-labelledby="modalMascotaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- El contenido del formulario se cargará aquí vía AJAX -->
        </div>
    </div>
</div>

<script src="<?= APP_URL ?>/assets/js/mascotas.js"></script>