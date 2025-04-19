<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: /Proyecto 2/views/login.php');
    exit;
}

// Obtener los permisos del usuario desde la sesión
$permisos_usuario = isset($_SESSION['user_permissions']) ? $_SESSION['user_permissions'] : [];
$nombre_usuario = $_SESSION['user_name'];
$rol_usuario = $_SESSION['user_role'];

// Definir permisos según rol
$permisos = [
    1 => ['dashboard', 'usuarios', 'mascotas', 'reportes', 'roles', 'configuracion'], // Admin
    2 => ['dashboard', 'mascotas', 'reportes'], // Veterinario
    3 => ['dashboard', 'mascotas'] // Usuario normal
];

$rol_actual = $_SESSION['user_role'];
$permisos_usuario = $permisos[$rol_actual] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MascotasIoT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/Proyecto 2/public/css/sidebar.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #198754;
            --accent-color: #fd7e14;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .top-bar {
            background: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .top-bar h4 {
            font-size: 1.25rem;
            margin-bottom: 0;
        }

        .top-bar p {
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .alert-status {
            position: relative;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 12px;
            background: rgba(253, 126, 20, 0.1);
            border-left: 4px solid var(--accent-color);
        }

        .alert-status i {
            color: var(--accent-color);
        }

        .card-dashboard {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }

        .card-dashboard .card-body {
            padding: 15px;
        }

        .stat-icon {
            font-size: 1.75rem;
            margin-bottom: 10px;
            background: rgba(13, 110, 253, 0.1);
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: var(--primary-color);
        }

        .card-dashboard h3.card-title {
            font-size: 0.875rem;
            margin-bottom: 8px;
            color: #6c757d;
        }

        .card-dashboard h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .card-dashboard p {
            font-size: 0.75rem;
            margin-bottom: 0;
        }

        .activity-timeline {
            padding-left: 25px;
        }

        .activity-item {
            padding-bottom: 15px;
        }

        .activity-item h6 {
            font-size: 0.875rem;
            margin-bottom: 4px;
        }

        .activity-item p {
            font-size: 0.75rem;
        }

        .device-status {
            padding: 8px;
            margin-bottom: 8px;
            font-size: 0.875rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.25em 0.75em;
        }

        /* Módulos del Sistema */
        .module-card {
            height: 100%;
            min-height: 180px;
        }

        .module-card .card-title {
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .module-card .card-text {
            font-size: 0.875rem;
            margin-bottom: 15px;
            color: #6c757d;
        }

        .module-card .btn {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }

        .module-card .stat-icon {
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .card-dashboard {
                margin-bottom: 12px;
            }

            .top-bar {
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Incluir el sidebar del layout -->
    <?php include '../layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar d-flex justify-content-between align-items-center">
            <div>
                <h4>Dashboard</h4>
                <p class="text-muted">Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?></p>
            </div>
            <div class="d-flex align-items-center">
                <div class="position-relative me-3">
                    <i class="fas fa-bell fa-lg text-muted"></i>
                    <span class="notification-badge">3</span>
                </div>
                <div class="user-info d-flex align-items-center">
                    <span class="me-2 d-none d-md-block"><?php echo htmlspecialchars($nombre_usuario); ?></span>
                    <i class="fas fa-user-circle fa-lg text-primary"></i>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['warning_message'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php 
            echo $_SESSION['warning_message'];
            unset($_SESSION['warning_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card card-dashboard">
                    <div class="card-body">
                        <div class="stat-icon">
                            <i class="fas fa-dog"></i>
                        </div>
                        <h3 class="card-title">Mascotas Activas</h3>
                        <h2>3</h2>
                        <p class="text-muted">↑ 2 nuevas esta semana</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-dashboard">
                    <div class="card-body">
                        <div class="stat-icon" style="color: var(--secondary-color); background: rgba(25, 135, 84, 0.1);">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h3 class="card-title">Signos Vitales</h3>
                        <h2>Normal</h2>
                        <p class="text-muted">Todas las mascotas estables</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-dashboard">
                    <div class="card-body">
                        <div class="stat-icon" style="color: var(--accent-color); background: rgba(253, 126, 20, 0.1);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="card-title">Alertas</h3>
                        <h2>2</h2>
                        <p class="text-muted">Requieren atención</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-dashboard">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #6f42c1; background: rgba(111, 66, 193, 0.1);">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <h3 class="card-title">Dispositivos</h3>
                        <h2>3/3</h2>
                        <p class="text-muted">Todos conectados</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Módulos del Sistema -->
        <h5 class="mb-3">Módulos del Sistema</h5>
        <div class="row g-3">
            <?php if (in_array('usuarios', $permisos_usuario)): ?>
            <div class="col-md-4 col-sm-6">
                <div class="card card-dashboard module-card">
                    <div class="card-body">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="card-title">Gestión de Usuarios</h5>
                        <p class="card-text">Administra usuarios, roles y permisos del sistema.</p>
                        <a href="/Proyecto 2/views/usuarios/usuarios.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i> Acceder
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (in_array('mascotas', $permisos_usuario)): ?>
            <div class="col-md-4 col-sm-6">
                <div class="card card-dashboard module-card">
                    <div class="card-body">
                        <div class="stat-icon" style="color: var(--secondary-color); background: rgba(25, 135, 84, 0.1);">
                            <i class="fas fa-paw"></i>
                        </div>
                        <h5 class="card-title">Gestión de Mascotas</h5>
                        <p class="card-text">Administra mascotas y sus dispositivos IoT.</p>
                        <a href="/Proyecto 2/views/mascotas/mascotas.php" class="btn btn-success">
                            <i class="fas fa-arrow-right"></i> Acceder
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (in_array('reportes', $permisos_usuario)): ?>
            <div class="col-md-4 col-sm-6">
                <div class="card card-dashboard module-card">
                    <div class="card-body">
                        <div class="stat-icon" style="color: var(--accent-color); background: rgba(253, 126, 20, 0.1);">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h5 class="card-title">Reportes y Estadísticas</h5>
                        <p class="card-text">Visualiza reportes y análisis de datos.</p>
                        <a href="/Proyecto 2/views/reportes/reportes.php" class="btn btn-warning">
                            <i class="fas fa-arrow-right"></i> Acceder
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (in_array('roles', $permisos_usuario)): ?>
            <div class="col-md-4 col-sm-6">
                <div class="card card-dashboard module-card">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #6f42c1; background: rgba(111, 66, 193, 0.1);">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h5 class="card-title">Gestión de Roles</h5>
                        <p class="card-text">Configura roles y permisos del sistema.</p>
                        <a href="/Proyecto 2/views/roles/roles.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i> Acceder
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (in_array('configuracion', $permisos_usuario)): ?>
            <div class="col-md-4 col-sm-6">
                <div class="card card-dashboard module-card">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #0dcaf0; background: rgba(13, 202, 240, 0.1);">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h5 class="card-title">Configuración del Sistema</h5>
                        <p class="card-text">Ajusta los parámetros y configuraciones.</p>
                        <a href="/Proyecto 2/views/configuracion/configuracion.php" class="btn btn-info">
                            <i class="fas fa-arrow-right"></i> Acceder
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Activity and Status -->
        <div class="row g-3 mt-3">
            <div class="col-md-8">
                <div class="card card-dashboard">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Actividad Reciente</h5>
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" id="timelineDropdown" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#">Ver todo</a></li>
                                    <li><a class="dropdown-item" href="#">Exportar</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="activity-timeline">
                            <div class="activity-item">
                                <h6 class="mb-1">Max - Temperatura Elevada</h6>
                                <p class="text-muted mb-0 small">39.2°C - Hace 5 minutos</p>
                            </div>
                            <div class="activity-item">
                                <h6 class="mb-1">Luna - Actividad Normal</h6>
                                <p class="text-muted mb-0 small">Ritmo cardíaco: 85 bpm - Hace 15 minutos</p>
                            </div>
                            <div class="activity-item">
                                <h6 class="mb-1">Rocky - Fuera de Zona Segura</h6>
                                <p class="text-muted mb-0 small">500m de distancia - Hace 30 minutos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-dashboard">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Estado del Sistema</h5>
                        
                        <div class="alert-status mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle fa-lg me-2"></i>
                                <div>
                                    <h6 class="mb-1">Alerta de Temperatura</h6>
                                    <p class="mb-0 small">Max presenta temperatura elevada</p>
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3">Dispositivos Conectados</h6>
                        <div class="device-status">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Collar Max</span>
                                <span class="badge bg-success">Conectado</span>
                            </div>
                        </div>
                        <div class="device-status">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Collar Luna</span>
                                <span class="badge bg-success">Conectado</span>
                            </div>
                        </div>
                        <div class="device-status">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Collar Rocky</span>
                                <span class="badge bg-success">Conectado</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para manejar el scroll de manera eficiente
        function throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            }
        }

        $(document).ready(function() {
            // Toggle sidebar en móviles
            $('.sidebar-toggle').click(function(e) {
                e.preventDefault();
                $('.sidebar').toggleClass('active');
            });

            // Manejar scroll de manera eficiente
            $(window).on('scroll', throttle(function() {
                // Actualizar estado de la interfaz si es necesario
                updateUIOnScroll();
            }, 100));

            // Simulación de actualizaciones en tiempo real
            let updateInterval = setInterval(function() {
                updateTimestamps();
            }, 60000);

            // Limpiar intervalo cuando la página se oculta
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    clearInterval(updateInterval);
                } else {
                    updateInterval = setInterval(function() {
                        updateTimestamps();
                    }, 60000);
                }
            });
        });

        function updateUIOnScroll() {
            // Aquí puedes agregar lógica para actualizar la UI durante el scroll
            // Por ejemplo, mostrar/ocultar elementos, cargar más contenido, etc.
        }

        function updateTimestamps() {
            // Implementar actualización de timestamps
            const timestamps = document.querySelectorAll('.activity-item p');
            timestamps.forEach(timestamp => {
                // Aquí iría la lógica para actualizar los timestamps
            });
        }

        // Manejador global de errores
        window.addEventListener('error', function(e) {
            console.warn('Error capturado:', e.error);
            return false;
        }, true);

        // Manejador de promesas rechazadas
        window.addEventListener('unhandledrejection', function(e) {
            console.warn('Promesa rechazada:', e.reason);
            return false;
        });

        // Optimizar la carga de recursos
        document.addEventListener('DOMContentLoaded', function() {
            // Diferir la carga de recursos no críticos
            setTimeout(function() {
                // Aquí puedes cargar recursos adicionales si es necesario
            }, 1000);
        });
    </script>
</body>
</html> 