<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../controllers/RoleController.php';

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
$controller = new UserController();

// Verificar permisos
if (!$controller->tienePermiso('usuarios') && !$controller->tienePermiso('gestionar_usuarios')) {
    header('Location: /Proyecto 2/views/errors/403.php');
    exit;
}

// Obtener los datos necesarios
try {
    $roleController = new RoleController();
    $roles = $roleController->getAllRoles();
    
    // Verificar permisos específicos
    $puede_crear = $controller->tienePermiso('gestionar_usuarios');
    $puede_editar = $controller->tienePermiso('gestionar_usuarios');
    $puede_eliminar = $controller->tienePermiso('gestionar_usuarios');
    $puede_cambiar_estado = $controller->tienePermiso('gestionar_usuarios');
    $es_superadmin = $controller->tienePermiso('superadministrador');
} catch (Exception $e) {
    error_log('Error en usuarios.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Ocurrió un error al cargar la página de usuarios.';
    header('Location: /Proyecto 2/views/dashboard/index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>   
    <title>Gestión de Usuarios - MascotasIoT</title>
    <link rel="stylesheet" href="../../public/assets/css/styles.css">
    <link rel="stylesheet" href="../../public/assets/css/table-styles.css">
    <!-- CSS -->
    <?= include_once __DIR__ . '/../../views/layouts/head.php'; ?>


    <style>
        :root {
            --primary-color: #2c3e50;
            --hover-color: #34495e;
            --text-color: #2c3e50;
            --border-color: #e9ecef;
            --background-color: #f8f9fa;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-size: 14px;
        }

        .content-wrapper {
            margin-left: 250px;
            padding: 1.5rem;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        .card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h5 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .btn-nuevo {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-nuevo:hover {
            background-color: var(--hover-color);
            color: white;
            transform: translateY(-1px);
        }

        .table-container {
            padding: 1rem;
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
            width: 100%;
            font-size: 0.9rem;
        }

        .table th {
            background-color: white;
            color: var(--text-color);
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .table td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .badge {
            padding: 0.4em 0.6em;
            font-size: 0.75em;
            font-weight: 500;
            border-radius: 4px;
        }

        .badge-success {
            background-color: #27ae60;
            color: white;
        }

        .badge-danger {
            background-color: #e74c3c;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .action-buttons button {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            background: white;
            color: var(--text-color);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .action-buttons button:hover {
            background-color: var(--background-color);
        }

        .modal-content {
            border-radius: 8px;
            border: none;
        }

        .modal-header {
            padding: 1rem 1.5rem;
            background-color: white;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .form-control, .form-select {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding: 1rem;
            }

            .card-header {
                flex-direction: column;
                gap: 1rem;
            }

            .action-buttons {
                justify-content: center;
            }
        }

        .sidebar-collapsed .content-wrapper {
            margin-left: 0;
        }

        .password-group .input-group-text {
            cursor: pointer;
        }

        .password-requirements div {
            margin-bottom: 0.25rem;
            color: #6c757d;
        }

        .password-requirements div.valid {
            color: #198754;
        }

        .password-requirements div.invalid {
            color: #dc3545;
        }

        .password-requirements .fas {
            width: 16px;
            text-align: center;
            margin-right: 0.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .input-group .btn-outline-secondary {
            border-color: #ced4da;
        }

        .input-group .btn-outline-secondary:hover {
            background-color: #e9ecef;
            border-color: #ced4da;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #dee2e6;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .invalid-feedback {
            font-size: 0.875rem;
        }

        .password-requirements.d-none {
            display: none !important;
        }

        .password-requirements.show {
            display: block !important;
        }

        .password-requirements {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
        }

        .password-requirements h6 {
            color: #495057;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .requirements-list {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .requirements-list i {
            font-size: 0.5rem;
            color: #6c757d;
            transition: all 0.2s;
        }

        .requirements-list .valid i {
            color: #198754;
        }

        .requirements-list .invalid i {
            color: #dc3545;
        }

        .requirements-list .valid span {
            color: #198754;
        }

        .requirements-list .invalid span {
            color: #dc3545;
        }

        .card {
            margin-bottom: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .btn-logout {
            position: fixed;
            bottom: 1rem;
            left: 1rem;
            width: calc(250px - 2rem);
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 0.25rem;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background-color: #bb2d3b;
        }

        .sidebar-collapsed .btn-logout {
            width: auto;
            left: 0.5rem;
        }

        .password-requirements {
            font-size: 0.875rem;
        }

        .requirements-list i {
            width: 20px;
            text-align: center;
            font-size: 0.875rem;
        }

        .requirements-list .valid i {
            color: #198754;
        }

        .requirements-list .invalid i {
            color: #dc3545;
        }

        .requirements-list .valid span {
            color: #198754;
        }

        .requirements-list .invalid span {
            color: #dc3545;
        }

        .requirements-list div {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid p-0">
            <div class="card">
                <div class="card-header">
                    <h5>Gestión de Usuarios</h5>
                    <?php if ($controller->tienePermiso('gestionar_usuarios')): ?>
                    <button type="button" class="btn-nuevo" onclick="showCreateUserModal()">
                        <i class="fas fa-plus"></i>
                        <span>Nuevo Usuario</span>
                    </button>
                    <?php endif; ?>
                </div>
                <div class="table-container">
                    <table id="usersTable" class="table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Mascotas</th>
                                <th>Último acceso</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Usuario -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm" class="needs-validation" novalidate>
                        <input type="hidden" id="userId" name="id">

                        <!-- Información del Usuario -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                            <div class="invalid-feedback">
                                Por favor ingrese el nombre del usuario
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="form-text email-help-text">El correo electrónico no se puede modificar una vez creado el usuario.</div>
                            <div class="invalid-feedback">
                                Por favor ingrese un correo electrónico válido
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rol_id" class="form-label">Rol</label>
                            <select class="form-select" id="rol_id" name="rol_id" required>
                                <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol['id']; ?>"><?php echo htmlspecialchars($rol['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione un rol
                            </div>
                        </div>

                        <div class="mb-3 password-group">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text password-help-text">
                                La contraseña solo es requerida para nuevos usuarios.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirmar Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Requisitos de contraseña -->
                        <div class="password-requirements mb-3">
                            <div class="requirements-list">
                                <div class="req-length d-flex align-items-center mb-2">
                                    <i class="fas fa-times text-danger me-2"></i>
                                    <span>Mínimo 8 caracteres</span>
                                </div>
                                <div class="req-uppercase d-flex align-items-center mb-2">
                                    <i class="fas fa-times text-danger me-2"></i>
                                    <span>Una mayúscula</span>
                                </div>
                                <div class="req-lowercase d-flex align-items-center mb-2">
                                    <i class="fas fa-times text-danger me-2"></i>
                                    <span>Una minúscula</span>
                                </div>
                                <div class="req-number d-flex align-items-center mb-2">
                                    <i class="fas fa-times text-danger me-2"></i>
                                    <span>Un número</span>
                                </div>
                                <div class="req-special d-flex align-items-center mb-2">
                                    <i class="fas fa-times text-danger me-2"></i>
                                    <span>Un carácter especial (!@#$%^&*.)</span>
                                </div>
                                <div class="req-space d-flex align-items-center">
                                    <i class="fas fa-times text-danger me-2"></i>
                                    <span>Sin espacios</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveUserBtn">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
  
    <script src="/Proyecto 2/public/assets/js/config.js"></script>
    <script src="/Proyecto 2/public/assets/js/usuarios.js"></script>
</body>
</html>

<!-- Función para detectar dispositivo móvil -->
<?php
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
?> 