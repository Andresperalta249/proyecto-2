<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/errors.css">
</head>
<body class="error-body">
    <div class="error-container">
        <div class="error-page">
            <h1 class="error-code">404</h1>
            <h2 class="error-title">Página no encontrada</h2>
            <p class="error-message">Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>
            <a href="<?= APP_URL ?>/" class="btn btn-primary error-btn">
                <i class="fas fa-home"></i>
                Volver al inicio
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 