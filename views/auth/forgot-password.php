<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - VitalPet Monitor</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= APP_URL ?>/assets/img/favico.ico">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/typography.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/auth.css">  
</head>
<body>
    <form id="forgotForm" method="POST" autocomplete="off" novalidate style="max-width:370px;margin:30px auto 0 auto;">
        <div style="text-align:center; margin-bottom: 20px;">
            <img src="<?= APP_URL ?>/assets/img/logo.png" alt="VitalPet Monitor Logo" style="width:80px;height:80px; margin-bottom: 15px;">
            <h1>VitalPet Monitor</h1>
            <p>Monitoreo inteligente para tus mascotas</p>
        </div>

        <h2 class="mb-3" style="text-align:center;">¿Olvidaste tu contraseña?</h2>

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico" required autofocus>
            <div class="invalid-feedback" id="emailError">Ingrese un correo válido</div>
        </div>
        <button type="submit" class="btn btn-primary w-100" id="submitBtn">
            <span class="btn-text">Enviar instrucciones</span>
            <span class="btn-loader" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </button>
        <div class="mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <a href="<?= APP_URL ?>/auth/login"><i class="fas fa-sign-in-alt me-1"></i>Volver al login</a>
            <a href="<?= APP_URL ?>/auth/register"><i class="fas fa-user-plus me-1"></i>Regístrate</a>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const forgotForm = document.getElementById('forgotForm');
        const emailInput = document.getElementById('email');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoader = submitBtn.querySelector('.btn-loader');

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function showError(input, message) {
            input.classList.add('is-invalid');
            const errorElement = document.getElementById(input.id + 'Error');
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            }
        }

        function clearError(input) {
            input.classList.remove('is-invalid');
            const errorElement = document.getElementById(input.id + 'Error');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        }

        emailInput.addEventListener('blur', function() {
            if (this.value && !validateEmail(this.value)) {
                showError(this, 'Por favor, ingresa un correo electrónico válido');
            } else {
                clearError(this);
            }
        });

        forgotForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;
            
            if (!emailInput.value || !validateEmail(emailInput.value)) {
                showError(emailInput, 'Por favor, ingresa un correo electrónico válido');
                isValid = false;
            }
            
            if (!isValid) {
                return;
            }
            
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-block';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                this.submit();
            }, 500);
        });
    });
    </script>
</body>
</html> 