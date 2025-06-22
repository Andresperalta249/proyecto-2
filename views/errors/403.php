<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Prohibido</title>
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
                    <h1 class="error-title">403</h1>
                    <h2 class="error-subtitle">Acceso Prohibido</h2>
                    <p class="error-message">No tienes permisos para acceder a esta página. Contacta al administrador si crees que esto es un error.</p>
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