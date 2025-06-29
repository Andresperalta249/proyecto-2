<?php
/**
 * Vista: errors/404.php
 * ---------------------
 * Página de error 404 - Página no encontrada.
 *
 * Variables recibidas:
 *   - $mensaje: Mensaje de error personalizado (opcional).
 *
 * Uso:
 *   Esta vista se muestra cuando un usuario intenta acceder a una página que no existe.
 *   Es llamada automáticamente por el sistema cuando se detecta un error 404.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Incluir los estilos de error-pages.css -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/pages/error-pages.css">
</head>
<body class="error-page-body">
    <div class="container error-container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="error-card">
                    <h1 class="error-title">404</h1>
                    <h2 class="error-subtitle">Página no encontrada</h2>
                    <p class="error-message">Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>
                    <a href="<?= APP_URL ?>/" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>
                        Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 