<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: /Proyecto 2/views/auth/login.php');
    exit;
}
?>

<div class="sidebar bg-primary">
    <div class="sidebar-header">
        <div class="app-brand text-center text-white">
            <i class="fas fa-paw fa-2x mb-2"></i>
            <h4 class="mb-0">MascotasIoT</h4>
            <small>Sistema de Monitoreo</small>
        </div>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link text-white" href="/Proyecto 2/views/dashboard/index.php">
                Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white" href="/Proyecto 2/views/users/users.php">
                Usuarios
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white" href="/Proyecto 2/views/pets/pets.php">
                Mascotas
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white" href="/Proyecto 2/views/reports/reports.php">
                Reportes
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white" href="/Proyecto 2/views/roles/roles.php">
                Roles
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white" href="/Proyecto 2/views/settings/settings.php">
                Configuración
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="/Proyecto 2/controllers/AuthController.php?action=logout" class="btn btn-light btn-block">
            Cerrar Sesión
        </a>
    </div>
</div>

<style>
.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
}

.sidebar-header {
    padding: 20px;
}

.app-brand i {
    display: block;
    margin: 0 auto;
}

.nav-link {
    padding: 10px 20px;
}

.nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar-footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    padding: 20px;
}

.btn-block {
    width: 100%;
}

.main-content {
    margin-left: 250px;
    padding: 20px;
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    .main-content {
        margin-left: 0;
    }
    .sidebar-footer {
        position: relative;
    }
}
</style> 