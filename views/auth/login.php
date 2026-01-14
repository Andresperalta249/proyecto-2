<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - VitalPet Monitor</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= APP_URL ?>/assets/img/favico.ico">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/typography.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/auth.css">  
</head>
<body>
    <form id="loginForm" method="POST" autocomplete="off" novalidate style="max-width:370px;margin:30px auto 0 auto;" action="<?= APP_URL ?>/auth/login">
        <div style="text-align:center; margin-bottom: 20px;">
            <img src="<?= APP_URL ?>/assets/img/logo.png" alt="VitalPet Monitor Logo" style="width:80px;height:80px; margin-bottom: 15px;">
            <h1>VitalPet Monitor</h1>
            <p>Monitoreo inteligente para tus mascotas</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($register_success)): ?>
            <div style="background: #dbeafe; border: 1px solid #2563eb; color: #1e40af; padding: 8px 12px; border-radius: 6px; margin-bottom: 15px; display: flex; align-items: center; gap: 7px; font-size: 14px;">
                <i class="fas fa-info-circle" style="color: #2563eb;"></i>
                <?php echo htmlspecialchars($register_success); ?>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico" required autofocus autocomplete="username">
            <div class="invalid-feedback" id="emailError">
                Por favor, ingresa un correo electrónico válido
            </div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required autocomplete="current-password">
            <div class="invalid-feedback" id="passwordError">
                Por favor, ingresa tu contraseña
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100" id="loginBtn">
            <span class="btn-text">Iniciar sesión</span>
            <span class="btn-loader" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </button>
        <div class="mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <a href="<?= APP_URL ?>/auth/forgot-password"><i class="fas fa-key me-1"></i>¿Olvidaste tu contraseña?</a>
            <a href="<?= APP_URL ?>/auth/register"><i class="fas fa-user-plus me-1"></i>Crear cuenta</a>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = loginBtn.querySelector('.btn-text');
        const btnLoader = loginBtn.querySelector('.btn-loader');



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

        // Solo validar email en tiempo real si el usuario ha escrito algo
        emailInput.addEventListener('blur', function() {
            if (this.value && !validateEmail(this.value)) {
                showError(this, 'Por favor, ingresa un correo electrónico válido');
            } else {
                clearError(this);
            }
        });

        // Solo validar contraseña cuando el usuario sale del campo
        passwordInput.addEventListener('blur', function() {
            if (this.value.length > 0 && !this.value) {
                showError(this, 'Por favor, ingresa tu contraseña');
            } else {
                clearError(this);
            }
        });

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;
            if (!emailInput.value || !validateEmail(emailInput.value)) {
                showError(emailInput, 'Por favor, ingresa un correo electrónico válido');
                isValid = false;
            }
            if (!passwordInput.value) {
                showError(passwordInput, 'Por favor, ingresa tu contraseña');
                isValid = false;
            }
            if (!isValid) {
                return;
            }
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-block';
            loginBtn.disabled = true;
            setTimeout(() => {
                this.submit();
            }, 500);
        });
    });
    </script>
</body>
</html> 