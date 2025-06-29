<?php
/**
 * Layout: layouts/auth.php
 * ------------------------
 * Plantilla para páginas de autenticación (login, registro, recuperación de contraseña).
 *
 * Variables recibidas:
 *   - $contenido: Contenido principal de la página de autenticación.
 *   - $titulo: Título de la página.
 *
 * Uso:
 *   Este layout es utilizado por las vistas de autenticación para mantener una estructura uniforme.
 *   Incluye estilos y scripts específicos para formularios de autenticación.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Autenticación' ?> - <?= APP_NAME ?></title>
    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>assets/img/favicon.svg" type="image/svg+xml">
    <!-- Estilos Base -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/base/reset.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/base/variables.css">
    <!-- Estilos de Componentes -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/components/forms.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/components/buttons.css">
    <!-- Estilos de Páginas de Autenticación -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/pages/auth-pages.css">
</head>
<body>
    <main class="auth-container">
        <div class="auth-card">
            <?= $content ?>
        </div>
    </main>
</body>
</html> 