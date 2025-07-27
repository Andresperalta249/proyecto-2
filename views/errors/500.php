<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del servidor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/errors.css">
</head>
<body class="error-body">
    <div class="error-container">
        <div class="error-page">
            <img src="https://cdn-icons-png.flaticon.com/512/5948/5948565.png" alt="Error 500" style="width:120px; margin-bottom:1.5rem; opacity:0.85;">
            <h1 class="error-code">500</h1>
            <h2 class="error-title">¡Ups! Error del servidor</h2>
            <p class="error-message">Lo sentimos, ha ocurrido un error inesperado.<br>Por favor, intenta nuevamente más tarde.</p>
            <a href="<?= APP_URL ?>/" class="btn btn-primary error-btn">
                <i class="fas fa-home me-2"></i>
                Volver al inicio
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 