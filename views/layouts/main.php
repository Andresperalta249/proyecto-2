<?php
$content = $GLOBALS['content'] ?? '';
$title = $GLOBALS['title'] ?? '';
$loginError = $GLOBALS['loginError'] ?? '';
$menuActivo = $GLOBALS['menuActivo'] ?? null;

// Log para depuración del layout
$logMsg = '['.date('Y-m-d H:i:s')."] Layout: content=" . (empty($content) ? 'VACIO' : 'LLENO') . ", ruta=" . ($_SERVER['REQUEST_URI'] ?? '') . ", usuario=" . (isset($_SESSION['user_id']) ? 'AUTENTICADO' : 'NO AUTENTICADO') . "\n";
file_put_contents(__DIR__ . '/../../logs/error.log', $logMsg, FILE_APPEND);
?>
<?php require_once __DIR__ . '/../../includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - ' . APP_NAME : APP_NAME ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Chart.js -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
    <!-- Chart.js DataLabels -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script> -->
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/device-monitor.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/typography.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/sidebar.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/tables.css">
    
    <!-- Leaflet CSS para mapas -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <?php if (isset($extra_css)): ?>
        <?= $extra_css ?>
    <?php endif; ?>

  
</head>
<body>
<?php
if (!isset($rolNombre)) {
    $rolNombre = $_SESSION['rol_nombre'] ?? 'Usuario';
}
if (!isset($badgeColor)) {
    switch (strtolower($rolNombre)) {
        case 'superadministrador': $badgeColor = 'primary'; break;
        case 'administrador': $badgeColor = 'success'; break;
        case 'usuario': $badgeColor = 'info'; break;
        default: $badgeColor = 'secondary'; break;
    }
}
if (!isset($content)) {
    $content = '';
}
?>
    <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
        <!-- Sidebar Moderno -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header d-flex align-items-center p-3">
                <span class="sidebar-logo me-2"><i class="fas fa-dog"></i></span>
                <span class="sidebar-title">PetMonitoring IoT</span>
            </div>
            <div class="sidebar-menu">
                <div class="sidebar-section">
                    <a href="<?= APP_URL ?>/dashboard" class="sidebar-item<?= ($menuActivo === 'dashboard' ? ' active' : '') ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </div>
                <div class="sidebar-section">
                    <div class="sidebar-section-title px-3 py-2 text-muted small text-uppercase">
                        <i class="fas fa-folder me-2"></i> Administración
                    </div>
                    <a href="<?= APP_URL ?>/usuarios" class="sidebar-item<?= ($menuActivo === 'usuarios' ? ' active' : '') ?>">
                        <i class="fas fa-users"></i> Usuarios
                    </a>
                    <a href="<?= APP_URL ?>/roles" class="sidebar-item<?= ($menuActivo === 'roles' ? ' active' : '') ?>">
                        <i class="fas fa-user-tag"></i> Roles y Permisos
                    </a>
                </div>
                <div class="sidebar-section">
                    <div class="sidebar-section-title px-3 py-2 text-muted small text-uppercase">
                        <i class="fas fa-broadcast-tower me-2"></i> Monitoreo
                    </div>
                    <a href="<?= APP_URL ?>/monitor" class="sidebar-item<?= ($menuActivo === 'monitor' ? ' active' : '') ?>">
                        <i class="fas fa-desktop"></i> Monitor
                    </a>
                    <a href="<?= APP_URL ?>/mascotas" class="sidebar-item<?= ($menuActivo === 'mascotas' ? ' active' : '') ?>">
                        <i class="fas fa-paw"></i> Mascotas
                    </a>
                    <a href="<?= APP_URL ?>/dispositivos" class="sidebar-item<?= ($menuActivo === 'dispositivos' ? ' active' : '') ?>">
                        <i class="fas fa-microchip"></i> Dispositivos
                    </a>
                    <a href="<?= APP_URL ?>/alertas" class="sidebar-item<?= ($menuActivo === 'alertas' ? ' active' : '') ?>">
                        <i class="fas fa-bell"></i> Alertas
                        <span class="badge bg-danger rounded-pill ms-2">3</span>
                    </a>
                </div>
                <div class="sidebar-section">
                    <div class="sidebar-section-title px-3 py-2 text-muted small text-uppercase">
                        <i class="fas fa-chart-bar me-2"></i> Análisis
                    </div>
                    <a href="<?= APP_URL ?>/reportes" class="sidebar-item<?= ($menuActivo === 'reportes' ? ' active' : '') ?>">
                        <i class="fas fa-chart-line"></i> Reportes
                    </a>
                </div>
                <div class="sidebar-section">
                    <div class="sidebar-section-title px-3 py-2 text-muted small text-uppercase">
                        <i class="fas fa-cog me-2"></i> Configuración
                    </div>
                    <a href="<?= APP_URL ?>/configuracion" class="sidebar-item<?= ($menuActivo === 'configuracion' ? ' active' : '') ?>">
                        <i class="fas fa-cogs"></i> Preferencias
                    </a>
                </div>
            </div>
            <div class="sidebar-footer">
                <div class="d-flex align-items-center p-3">
                    <i class="fas fa-user-circle fs-4 me-2"></i>
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($_SESSION['user']['nombre_real'] ?? $_SESSION['user']['nombre'] ?? 'Usuario') ?></div>
                        <div class="small text-muted">
                            <?= htmlspecialchars($_SESSION['rol_nombre'] ?? $rolNombre) ?>
                        </div>
                    </div>
                </div>
                <a href="<?= APP_URL ?>/auth/logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                </a>
            </div>
        </nav>
        <!-- Overlay para móviles -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <!-- Fin Sidebar Moderno -->
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Content -->
            <div class="container-fluid dashboard-compact">
                <?php if (isset($title) && !(isset($_SERVER['REQUEST_URI']) && preg_match('#/monitor/device/#', $_SERVER['REQUEST_URI']))): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
                    <?php if (isset($header_buttons)): ?>
                        <?= $header_buttons ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?= $content ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Auth Content -->
        <?php if (!empty($content)): ?>
            <?= $content ?>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Scripts principales al final para asegurar disponibilidad -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <?php if (isset($extra_js)): ?>
        <?= $extra_js ?>
    <?php endif; ?>

    <script>
        // Sidebar colapsable y móvil mejorado
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        // Solo ejecutar el código del sidebar si los elementos existen
        if (sidebar && sidebarToggle && sidebarOverlay) {
            function openSidebarMobile() {
                sidebar.classList.add('open');
                sidebarOverlay.classList.add('open');
            }
            function closeSidebarMobile() {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('open');
            }
            
            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth <= 900) {
                    if (sidebar.classList.contains('open')) {
                        closeSidebarMobile();
                    } else {
                        openSidebarMobile();
                    }
                } else {
                    sidebar.classList.toggle('sidebar-collapsed');
                }
            });
            
            sidebarOverlay.addEventListener('click', closeSidebarMobile);
            
            window.addEventListener('resize', function() {
                if (window.innerWidth > 900) {
                    closeSidebarMobile();
                    sidebar.classList.remove('sidebar-collapsed');
                }
            });

            // Cerrar sidebar móvil al hacer clic en cualquier enlace del menú
            document.querySelectorAll('.sidebar-item').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 991) {
                        closeSidebarMobile();
                    }
                });
            });
        }

        // Función para mostrar mensajes con SweetAlert2 como modal centrado
        function showMessage(type, message) {
            Swal.fire({
                icon: type, // 'success', 'error', 'warning', 'info'
                title: type === 'success' ? '¡Éxito!' : 'Error',
                text: message,
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
        }

        // Manejar mensajes de sesión si existen
        <?php if (isset($_SESSION['message'])): ?>
            showMessage('<?= $_SESSION['message']['type'] ?>', '<?= $_SESSION['message']['text'] ?>');
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        // Manejar el envío del formulario de login
        window.handleFormSubmit = function(form, url) {
            event.preventDefault();
            
            $.ajax({
                url: url,
                type: 'POST',
                data: $(form).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.message);
                        if (response.redirect) {
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1500);
                        }
                    } else {
                        showMessage('error', response.error);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error al procesar la solicitud';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    showMessage('error', errorMessage);
                }
            });
            
            return false;
        };
    </script>
    <script src="<?= APP_URL ?>/assets/js/roles.js"></script>
</body>
</html> 