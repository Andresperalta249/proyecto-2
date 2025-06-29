<?php
/**
 * Vista: roles/index.php
 * ----------------------
 * Muestra la lista de roles registrados y permite acciones como editar o eliminar.
 *
 * Variables recibidas:
 *   - $roles: Lista de roles a mostrar.
 *   - $permisos: Lista de permisos disponibles.
 *
 * Uso:
 *   Esta vista es llamada desde RolesController para mostrar el listado general de roles.
 */
$titulo = "Gestión de Roles";
$subtitulo = "Administración de roles y permisos del sistema.";
?>

<div class="container-fluid pt-3">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Gestión de Roles</h3>
                <?php if (verificarPermiso('crear_roles')): ?>
                    <button id="btnNuevoRol" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Rol
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaRoles" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Usuarios</th>
                            <th>Permisos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- El contenido se cargará vía DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Contenedor para la configuración que necesita el JS -->
<div id="roles-config" 
     data-app-url="<?= APP_URL ?>"
     data-permiso-editar="<?= verificarPermiso('editar_roles') ? 'true' : 'false' ?>"
     data-permiso-eliminar="<?= verificarPermiso('eliminar_roles') ? 'true' : 'false' ?>">
</div>

<!-- Modal para el formulario de Rol -->
<div class="modal fade" id="modalRol" tabindex="-1" role="dialog" aria-labelledby="modalRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRolLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- El contenido del formulario se cargará aquí vía AJAX -->
            </div>
        </div>
    </div>
</div>

<script src="<?= APP_URL ?>/assets/js/roles.js"></script>