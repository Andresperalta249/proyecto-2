<?php 
$titulo = 'Test Monitor';
$subtitulo = 'Página de prueba para verificar si el problema es de JavaScript';
?>

<div class="contenedor-sistema">
    <!-- Header con título -->
    <div class="contenedor-sistema-header">
        <div class="header-content">
            <div class="header-title">
                <i class="fas fa-chart-line"></i>
                <?= htmlspecialchars($titulo) ?>
            </div>
        </div>
        <p class="header-subtitle"><?= htmlspecialchars($subtitulo) ?></p>
    </div>

    <!-- Contenido de prueba -->
    <div class="alert alert-info">
        <h4>✅ Página de Prueba Funcionando</h4>
        <p>Si puedes ver este mensaje, significa que:</p>
        <ul>
            <li>✅ La vista se está cargando correctamente</li>
            <li>✅ Los permisos están funcionando</li>
            <li>✅ El controlador está ejecutándose</li>
        </ul>
    </div>

    <!-- Información de debug -->
    <div class="card">
        <div class="card-header">
            <h5>Información de Debug</h5>
        </div>
        <div class="card-body">
            <p><strong>Usuario:</strong> <?= $_SESSION['user_name'] ?? 'No definido' ?></p>
            <p><strong>Rol:</strong> <?= $_SESSION['rol_nombre'] ?? 'No definido' ?></p>
            <p><strong>Permiso ver_monitor:</strong> <?= verificarPermiso('ver_monitor') ? '✅ Sí' : '❌ No' ?></p>
            <p><strong>BASE_URL:</strong> <?= BASE_URL ?? 'No definido' ?></p>
            <p><strong>APP_URL:</strong> <?= APP_URL ?? 'No definido' ?></p>
        </div>
    </div>

    <!-- Botón para volver -->
    <div class="mt-3">
        <a href="<?= BASE_URL ?>monitor" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Volver al Monitor
        </a>
    </div>
</div>

<script>
console.log('Test page loaded successfully');
console.log('BASE_URL:', '<?= BASE_URL ?>');
console.log('APP_URL:', '<?= APP_URL ?>');
</script> 