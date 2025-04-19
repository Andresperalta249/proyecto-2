<?php
require_once __DIR__ . '/../../controllers/RoleController.php';
require_once __DIR__ . '/../../models/Role.php';
require_once __DIR__ . '/../../config/database.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Debe iniciar sesión para acceder a esta página";
    header('Location: /Proyecto 2/views/auth/login.php');
    exit;
}

// Crear una instancia del controlador
$controller = new RoleController();

// Verificar solo el permiso de acceso al módulo
if (!$controller->tienePermiso('roles')) {
    $_SESSION['error_message'] = "No tiene permiso para acceder a esta sección";
    header('Location: /Proyecto 2/views/dashboard/index.php');
    exit;
}

// Obtener los datos necesarios
try {
    $roles = $controller->getAllRoles();
    $permisos = $controller->getAllPermisos();
    // Verificar si tiene permisos de gestión
    $puede_gestionar = $controller->tienePermiso('gestionar_roles');
} catch (Exception $e) {
    error_log('Error en roles.php: ' . $e->getMessage());
    $_SESSION['error'] = 'Ocurrió un error al cargar la página de roles.';
    header('Location: /Proyecto 2/views/dashboard/index.php');
    exit;
}

// Si es una solicitud directa a la página
if (!isset($roles)) {
    $controller->index();
    exit;
}

// Los datos pasados desde el controlador
$roles = $roles ?? [];
$permisos = $permisos ?? [];
$es_superadmin = $es_superadmin ?? false;
$puede_gestionar = $puede_gestionar ?? false;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles</title>
    <?= include_once __DIR__ . '/../../views/layouts/head.php'; ?>
    <style>
        /* Contenedor principal - respetando el sidebar */
        .roles-container {
            margin-left: 250px; /* Ancho del sidebar */
            padding: 1.5rem;
            min-height: 100vh;
            background: #f8f9fa;
        }

        /* Tabla y contenedor */
        .table-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table {
            margin: 0;
            width: 100%;
            font-size: 14px;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 500;
            padding: 12px 16px;
            color: #444;
            border-bottom: 1px solid #e9ecef;
        }

        .table td {
            padding: 12px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        /* DataTables personalización */
        .dataTables_wrapper {
            padding: 16px;
        }

        .dataTables_length {
            margin-bottom: 16px;
        }

        .dataTables_length select {
            padding: 6px 24px 6px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background: #fff url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") no-repeat right 8px center/10px 10px;
            appearance: none;
        }

        .dataTables_filter input {
            padding: 6px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            width: 200px;
        }

        /* Permisos */
        .permisos-preview {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .permiso-icon {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 14px;
        }

        .permiso-icon[data-action="gestionar"] {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .permiso-icon[data-action="ver"] {
            background: #e3f2fd;
            color: #1976d2;
        }

        /* Botón más permisos */
        .more-permisos {
            padding: 4px 8px;
            background: #e9ecef;
            color: #495057;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .more-permisos:hover {
            background: #dee2e6;
        }

        /* Estados */
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-inactive {
            background: #ffebee;
            color: #c62828;
        }

        /* Botones de acción */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn-action {
            width: 28px;
            height: 28px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            border: none;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .btn-action.btn-primary {
            background: #e3f2fd;
            color: #1976d2;
        }

        .btn-action.btn-danger {
            background: #ffebee;
            color: #c62828;
        }

        .btn-action.btn-warning {
            background: #fff3e0;
            color: #f57c00;
        }

        .btn-action.btn-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        /* Badge predeterminado */
        .badge-predefinido {
            padding: 4px 8px;
            background: #e9ecef;
            color: #495057;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Botón nuevo rol */
        .btn-nuevo-rol {
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .roles-container {
                margin-left: 0;
                padding: 1rem;
            }
        }

        /* Estilos para tooltips */
        .tooltip {
            font-size: 12px;
        }

        .tooltip-inner {
            background-color: #333;
            color: #fff;
            border-radius: 4px;
            padding: 8px 12px;
            max-width: 200px;
            text-align: left;
        }

        .tooltip.bs-tooltip-top .tooltip-arrow::before {
            border-top-color: #333;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include __DIR__ . '/../../views/layouts/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <div class="roles-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
                <h5 class="m-0" style="font-size: 16px;">Gestión de Roles</h5>
                <?php if ($puede_gestionar && !isMobile()): ?>
                <button type="button" class="btn-nuevo-rol" data-bs-toggle="modal" data-bs-target="#modalRole">
                    <i class="fas fa-plus"></i>
                    <span>Nuevo Rol</span>
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="rolesTable" class="table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Permisos</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $rol): 
                                $permisos_rol = $controller->getRolePermisos($rol['id']);
                            ?>
                            <tr data-id="<?php echo $rol['id']; ?>" class="<?php echo $rol['es_predeterminado'] ? 'rol-predeterminado' : ''; ?>">
                                <td data-label="Nombre" class="role-name">
                                    <?php echo htmlspecialchars($rol['nombre']); ?>
                                </td>
                                <td data-label="Descripción" class="role-description">
                                    <?php echo htmlspecialchars($rol['descripcion']); ?>
                                </td>
                                <td data-label="Permisos" class="permisos-column">
                                    <div class="permisos-preview">
                                        <?php
                                        $permisos_mostrados = 0;
                                        $total_permisos = count($permisos_rol);
                                        $tooltip_content = [];
                                        
                                        foreach ($permisos_rol as $index => $permiso):
                                            $permiso_nombre = strtolower($permiso['nombre']);
                                            $icono_info = null;
                                            
                                            // Determinar el icono basado en el tipo de permiso
                                            if (strpos($permiso_nombre, 'gestionar') !== false) {
                                                $icono_info = ['<i class="fas fa-user-cog"></i>', 'gestionar'];
                                            } elseif (strpos($permiso_nombre, 'ver') !== false) {
                                                $icono_info = ['<i class="fas fa-eye"></i>', 'ver'];
                                            } else {
                                                $icono_info = ['<i class="fas fa-shield-alt"></i>', 'otros'];
                                            }
                                            
                                            // Agregar al array de tooltips
                                            $tooltip_content[] = ucfirst(str_replace('_', ' ', $permiso['nombre']));
                                            
                                            // Mostrar solo los primeros 2 permisos
                                            if ($permisos_mostrados < 2):
                                        ?>
                                            <span class="permiso-icon" 
                                                  data-action="<?php echo $icono_info[1]; ?>"
                                                  data-bs-toggle="tooltip"
                                                  data-bs-title="<?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $permiso['nombre']))); ?>">
                                                <?php echo $icono_info[0]; ?>
                                            </span>
                                        <?php
                                            $permisos_mostrados++;
                                            endif;
                                        endforeach;
                                        
                                        // Mostrar botón +X si hay más de 2 permisos
                                        if ($total_permisos > 2):
                                            $restantes = array_slice($tooltip_content, 2);
                                        ?>
                                            <button type="button" 
                                                    class="more-permisos"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-html="true"
                                                    data-bs-title="<?php echo htmlspecialchars(implode('<br>', $restantes)); ?>">
                                                +<?php echo ($total_permisos - 2); ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td data-label="Estado">
                                    <span class="status-badge <?php echo $rol['estado'] === 'activo' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($rol['estado']); ?>
                                    </span>
                                </td>
                                <td data-label="Acciones">
                                    <?php if ($puede_gestionar): ?>
                                        <?php if (!$rol['es_predeterminado']): ?>
                                            <div class="action-buttons">
                                                <button type="button" class="btn-action btn-primary" 
                                                        onclick="editarRol(<?php echo $rol['id']; ?>)"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-title="Editar rol">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn-action btn-danger" 
                                                        onclick="eliminarRol(<?php echo $rol['id']; ?>)"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-title="Eliminar rol">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn-action <?php echo $rol['estado'] === 'activo' ? 'btn-warning' : 'btn-success'; ?>" 
                                                        onclick="cambiarEstado(<?php echo $rol['id']; ?>, '<?php echo $rol['estado'] === 'activo' ? 'inactivo' : 'activo'; ?>')"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-title="<?php echo $rol['estado'] === 'activo' ? 'Desactivar rol' : 'Activar rol'; ?>">
                                                    <i class="fas fa-<?php echo $rol['estado'] === 'activo' ? 'ban' : 'check'; ?>"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge-predefinido">
                                                <i class="fas fa-shield-alt"></i>
                                                Sistema
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Modal de permisos -->
                            <div class="modal fade" id="permisosModal<?php echo $rol['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-shield-alt me-2"></i>
                                                Permisos de <?php echo htmlspecialchars($rol['nombre']); ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="permisos-grid">
                                                <?php foreach ($permisos_rol as $permiso):
                                                    $tipo_permiso = '';
                                                    $icono = '';
                                                    
                                                    foreach ($iconos_permisos as $key => $value) {
                                                        if (strpos(strtolower($permiso['nombre']), $key) !== false) {
                                                            $tipo_permiso = $key;
                                                            $icono = $value;
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                    <div class="permiso-card permiso-<?php echo $tipo_permiso; ?>">
                                                        <?php echo $icono; ?>
                                                        <span><?php echo ucfirst(str_replace('_', ' ', $permiso['nombre'])); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Botón flotante para agregar (visible solo en móviles) -->
        <?php if ($puede_gestionar): ?>
        <button type="button" 
                class="floating-action-button d-md-none" 
                style="width: 48px; height: 48px;"
                data-bs-toggle="modal" 
                data-bs-target="#modalRole">
            <i class="fas fa-plus" style="font-size: var(--font-size-base);"></i>
        </button>
        <?php endif; ?>
    </div>

    <!-- Modal para crear/editar rol -->
    <div class="modal fade" id="modalRole" tabindex="-1" aria-labelledby="modalRoleLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRoleLabel">Nuevo Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rolForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="id" id="rolId">
                        
                        <!-- Información básica -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Información Básica</h6>
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Rol</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                       placeholder="Ingrese el nombre del rol">
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required
                                          placeholder="Describa el propósito y funciones del rol"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <!-- Permisos -->
                        <div class="permissions-section">
                            <h6 class="fw-bold mb-3">Permisos del Rol</h6>
                            <div class="row g-4">
                                <!-- Módulo Usuarios -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Usuarios</h6>
                                        </div>
                                        <div class="card-body">
                                            <?php foreach ($permisos as $permiso): 
                                                if (strpos(strtolower($permiso['nombre']), 'usuario') !== false): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permisos[]" 
                                                           value="<?php echo $permiso['id']; ?>" 
                                                           id="permiso_<?php echo $permiso['id']; ?>">
                                                    <label class="form-check-label" for="permiso_<?php echo $permiso['id']; ?>">
                                                        <?php echo htmlspecialchars($permiso['nombre']); ?>
                                                    </label>
                                                </div>
                                            <?php endif; endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Mascotas -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Mascotas</h6>
                                        </div>
                                        <div class="card-body">
                                            <?php foreach ($permisos as $permiso): 
                                                if (strpos(strtolower($permiso['nombre']), 'mascota') !== false): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permisos[]" 
                                                           value="<?php echo $permiso['id']; ?>" 
                                                           id="permiso_<?php echo $permiso['id']; ?>">
                                                    <label class="form-check-label" for="permiso_<?php echo $permiso['id']; ?>">
                                                        <?php echo htmlspecialchars($permiso['nombre']); ?>
                                                    </label>
                                                </div>
                                            <?php endif; endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Dispositivos -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Dispositivos</h6>
                                        </div>
                                        <div class="card-body">
                                            <?php foreach ($permisos as $permiso): 
                                                if (strpos(strtolower($permiso['nombre']), 'dispositivo') !== false): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permisos[]" 
                                                           value="<?php echo $permiso['id']; ?>" 
                                                           id="permiso_<?php echo $permiso['id']; ?>">
                                                    <label class="form-check-label" for="permiso_<?php echo $permiso['id']; ?>">
                                                        <?php echo htmlspecialchars($permiso['nombre']); ?>
                                                    </label>
                                                </div>
                                            <?php endif; endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Reportes -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Reportes</h6>
                                        </div>
                                        <div class="card-body">
                                            <?php foreach ($permisos as $permiso): 
                                                if (strpos(strtolower($permiso['nombre']), 'reporte') !== false): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permisos[]" 
                                                           value="<?php echo $permiso['id']; ?>" 
                                                           id="permiso_<?php echo $permiso['id']; ?>">
                                                    <label class="form-check-label" for="permiso_<?php echo $permiso['id']; ?>">
                                                        <?php echo htmlspecialchars($permiso['nombre']); ?>
                                                    </label>
                                                </div>
                                            <?php endif; endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Configuración -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Configuración</h6>
                                        </div>
                                        <div class="card-body">
                                            <?php foreach ($permisos as $permiso): 
                                                if (strpos(strtolower($permiso['nombre']), 'config') !== false): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permisos[]" 
                                                           value="<?php echo $permiso['id']; ?>" 
                                                           id="permiso_<?php echo $permiso['id']; ?>">
                                                    <label class="form-check-label" for="permiso_<?php echo $permiso['id']; ?>">
                                                        <?php echo htmlspecialchars($permiso['nombre']); ?>
                                                    </label>
                                                </div>
                                            <?php endif; endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo Roles -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Roles</h6>
                                        </div>
                                        <div class="card-body">
                                            <?php foreach ($permisos as $permiso): 
                                                if (strpos(strtolower($permiso['nombre']), 'rol') !== false): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permisos[]" 
                                                           value="<?php echo $permiso['id']; ?>" 
                                                           id="permiso_<?php echo $permiso['id']; ?>">
                                                    <label class="form-check-label" for="permiso_<?php echo $permiso['id']; ?>">
                                                        <?php echo htmlspecialchars($permiso['nombre']); ?>
                                                    </label>
                                                </div>
                                            <?php endif; endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        $(document).ready(function() {
            // Configuración de DataTables
            const table = $('#rolesTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                order: [[0, 'asc']],
                columnDefs: [
                    {
                        targets: -1,
                        orderable: false
                    }
                ]
            });

            // Inicializar tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

            // Destruir tooltips al cerrar modales
            $('.modal').on('hidden.bs.modal', function () {
                tooltipList.forEach(tooltip => tooltip.dispose());
            });

            // Función para editar rol
            window.editarRol = function(id) {
                // Limpiar todos los checkboxes antes de cargar los nuevos
                $('input[name="permisos[]"]').prop('checked', false);
                
                $.ajax({
                    url: '/Proyecto 2/controllers/RoleController.php',
                    type: 'GET',
                    data: {
                        action: 'get',
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            const rol = response.data;
                            
                            // Establecer los valores en el modal
                            $('#modalRoleLabel').text('Editar Rol');
                            $('#rolId').val(id);
                            $('#nombre').val(rol.nombre);
                            $('#descripcion').val(rol.descripcion);
                            $('#estado').val(rol.estado);
                            $('input[name="action"]').val('update');
                            
                            // Marcar los permisos del rol
                            if (rol.permisos && Array.isArray(rol.permisos)) {
                                rol.permisos.forEach(function(permiso) {
                                    $(`input[name="permisos[]"][value="${permiso.id}"]`).prop('checked', true);
                                });
                            }
                            
                            $('#modalRole').modal('show');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error al cargar los datos del rol'
                            });
                        }
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
            };

            // Función para eliminar rol
            window.eliminarRol = function(id) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: '¿Deseas eliminar el rol?',
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
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: '¡Eliminado!',
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: response.message,
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Ocurrió un error al procesar la solicitud',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            };

            // Manejar el envío del formulario de crear/editar
            $('#rolForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const action = $('input[name="action"]').val();
                const title = action === 'create' ? 'crear' : 'actualizar';

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Deseas ${title} este rol?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: `Sí, ${title}`,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../../controllers/RoleController.php',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: '¡Éxito!',
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: response.message,
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Ocurrió un error al procesar la solicitud',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });

            // Configuración mejorada de tooltips
            tippy('[data-tippy-content]', {
                allowHTML: true,
                interactive: true,
                animation: 'scale',
                duration: 200,
                theme: 'permisos',
                appendTo: document.body
            });

            // Asegurar altura consistente de las filas
            document.addEventListener('DOMContentLoaded', function() {
                const rows = document.querySelectorAll('.table tr');
                const maxHeight = Math.max(...Array.from(rows).map(row => row.offsetHeight));
                rows.forEach(row => row.style.height = maxHeight + 'px');
            });
        });
    </script>

    <!-- Función para detectar dispositivo móvil -->
    <?php
    function isMobile() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }
    ?>

    <!-- Agregar Tippy.js para tooltips mejorados -->
    <?= include_once __DIR__ . '/../../views/layouts/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar todos los tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    placement: 'top',
                    trigger: 'hover',
                    html: true
                });
            });

            // Limpiar tooltips al cerrar modales
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('hidden.bs.modal', function() {
                    tooltipList.forEach(tooltip => tooltip.hide());
                });
            });
        });
    </script>
</body>
</html> 