<?php
/**
 * Vista: errors/500.php
 * ---------------------
 * Página de error 500 - Error interno del servidor.
 *
 * Variables recibidas:
 *   - $mensaje: Mensaje de error personalizado (opcional).
 *
 * Uso:
 *   Esta vista se muestra cuando ocurre un error interno en el servidor.
 *   Es llamada automáticamente por el sistema cuando se detecta un error 500.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del servidor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Incluir los estilos de error-pages.css -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/pages/error-pages.css">
</head>
<body class="error-page-body">
    <div class="container error-container">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                <div class="error-card">
                    <img src="https://cdn-icons-png.flaticon.com/512/5948/5948565.png" alt="Error 500" class="error-image">
                    <h1 class="error-title">500</h1>
                    <h2 class="error-subtitle">¡Ups! Error del servidor</h2>
                    <p class="error-message">Lo sentimos, ha ocurrido un error inesperado.<br>Por favor, intenta nuevamente más tarde.</p>
                    <a href="<?= APP_URL ?>/" class="btn btn-primary btn-lg px-4">
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