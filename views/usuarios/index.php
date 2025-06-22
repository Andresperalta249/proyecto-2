<?php
$titulo = "Gestión de Usuarios";
$subtitulo = "Administración de usuarios y sus roles en el sistema.";
?>

<div class="container-fluid pt-3">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Gestión de Usuarios</h3>
                <?php if (verificarPermiso('crear_usuarios')): ?>
                    <button id="btnNuevoUsuario" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaUsuarios" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
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

<!-- Configuración para JS -->
<div id="usuarios-config" 
     data-app-url="<?php echo rtrim(BASE_URL, '/'); ?>"
     data-permiso-editar="<?php echo verificarPermiso('editar_usuarios') ? 'true' : 'false'; ?>"
     data-permiso-eliminar="<?php echo verificarPermiso('eliminar_usuarios') ? 'true' : 'false'; ?>"
     data-user-id="<?php echo $_SESSION['user_id'] ?? '0'; ?>">
</div>

<!-- Inclusión del script JS centralizado para la página de usuarios -->
<script src="<?= APP_URL ?>/assets/js/usuarios.js"></script> 