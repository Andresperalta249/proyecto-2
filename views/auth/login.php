<?php
/**
 * Vista: auth/login.php
 * ---------------------
 * Formulario de inicio de sesión para usuarios del sistema.
 *
 * Variables recibidas:
 *   - $error: Mensaje de error (si existe).
 *   - $success: Mensaje de éxito (si existe).
 *
 * Uso:
 *   Esta vista es llamada desde AuthController para mostrar el formulario de login.
 *   Los usuarios ingresan su email y contraseña para acceder al sistema.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - PetMonitoring IoT</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Base CSS (Variables, Reset) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/base/variables.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/base/reset.css">

    <!-- Componentes CSS (Forms, Buttons) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/components/forms.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/components/buttons.css">

    <!-- Páginas CSS (Auth) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/pages/auth-pages.css">
</head>
<body>
    <!-- Login Moderno PetMonitoring IoT -->
    <div class="card shadow-lg border-0 rounded-4 p-4 auth-card">
        <div class="text-center mb-4">
            <img src="https://cdn-icons-png.flaticon.com/512/616/616408.png" alt="Logo mascota" class="auth-logo">
            <h2 class="mt-3 mb-1 auth-title">PetMonitoring IoT</h2>
            <p class="text-muted mb-0">¡Bienvenido! Ingresa para continuar</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center py-2 mb-3"><?php echo $error; ?></div>
        <?php endif; ?>
        <form id="loginForm" method="POST" autocomplete="off" novalidate>
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico" required autofocus autocomplete="username">
                <label for="email"><i class="fas fa-envelope me-2"></i>Correo electrónico</label>
                <div class="invalid-feedback" id="emailError">Ingrese un correo válido</div>
            </div>
            <div class="form-floating mb-3 position-relative">
                <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required autocomplete="current-password">
                <label for="password"><i class="fas fa-lock me-2"></i>Contraseña</label>
                <span class="position-absolute top-50 end-0 translate-middle-y me-3 toggle-password-icon" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </span>
                <div class="invalid-feedback" id="passwordError">Ingrese su contraseña</div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-2 font-weight-semibold">Iniciar sesión</button>
            <div class="d-flex justify-content-between">
                <a href="<?= APP_URL ?>/auth/forgot-password" class="small">¿Olvidaste tu contraseña?</a>
                <a href="<?= APP_URL ?>/auth/register" class="small">Regístrate</a>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar/ocultar contraseña
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Validación en tiempo real
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('input', function() {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(this.value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        const passwordInput = document.getElementById('password');
        passwordInput.addEventListener('input', function() {
            if (!this.value) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        // Envío por AJAX (usa la función global handleFormSubmit si existe)
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (typeof window.handleFormSubmit === 'function') {
                    window.handleFormSubmit(this, this.action || window.location.href);
                } else {
                    this.submit();
                }
            });
        }
    });
    </script>
</body>
</html> 