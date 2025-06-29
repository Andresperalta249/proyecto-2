<?php
/**
 * Vista: dispositivos/index.php
 * -----------------------------
 * Muestra la lista de dispositivos registrados y permite acciones como editar o eliminar.
 *
 * Variables recibidas:
 *   - $dispositivos: Lista de dispositivos a mostrar.
 *   - $mascotas: Lista de mascotas (para mostrar asignaciones).
 *   - $usuarios: Lista de usuarios (si aplica).
 *   - $permisos: Permisos del usuario actual.
 *
 * Uso:
 *   Esta vista es llamada desde DispositivosController para mostrar el listado general de dispositivos.
 */
$titulo = "Gestión de Dispositivos";
$subtitulo = "Administración de dispositivos IoT para monitoreo de mascotas.";
?>

<div class="container-fluid pt-3">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Gestión de Dispositivos</h3>
                <?php if (verificarPermiso('crear_dispositivos')): ?>
                    <button id="btnNuevoDispositivo" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Dispositivo
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaDispositivos" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>MAC</th>
                            <th>Dueño</th>
                            <th>Disponible</th>
                            <th>Estado</th>
                            <th>Mascota</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Principal para Formularios -->
<div class="modal fade" id="modalDispositivo" tabindex="-1" aria-labelledby="modalDispositivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDispositivoLabel">Dispositivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- El contenido del formulario se cargará aquí dinámicamente -->
            </div>
        </div>
    </div>
</div>

<!-- Configuración para JS -->
<div id="dispositivos-config" 
     data-app-url="<?php echo rtrim(BASE_URL, '/'); ?>"
     data-permiso-editar="<?php echo verificarPermiso('editar_dispositivos') ? 'true' : 'false'; ?>"
     data-permiso-eliminar="<?php echo verificarPermiso('eliminar_dispositivos') ? 'true' : 'false'; ?>"
     data-user-id="<?php echo $_SESSION['user_id'] ?? '0'; ?>">
</div>

<script>
// Definir APP_URL globalmente para compatibilidad
window.APP_URL = '<?= rtrim(BASE_URL, '/') ?>';
</script>

<!-- Incluir modales adicionales (solo detalles/asignar, NO edición) -->
<?php include 'views/dispositivos/modals.php'; ?>

<!-- Inclusión del script JS centralizado para la página de dispositivos -->
<script src="<?= BASE_URL ?>assets/js/dispositivos.js"></script> 