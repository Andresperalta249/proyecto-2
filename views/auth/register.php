<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - VitalPet Monitor</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= APP_URL ?>/assets/img/favico.ico">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/typography.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/auth.css">  
</head>
<body>
    <form id="registerForm" method="POST" autocomplete="off" novalidate style="max-width:450px;margin:30px auto 0 auto;" action="<?= APP_URL ?>/auth/register">
        <div style="text-align:center; margin-bottom: 15px;">
            <img src="<?= APP_URL ?>/assets/img/logo.png" alt="VitalPet Monitor Logo" style="width:70px;height:70px; margin-bottom: 10px;">
            <h1>VitalPet Monitor</h1>
            <p>Monitoreo inteligente para tus mascotas</p>
        </div>

        <h2 class="mb-2" style="text-align:center;">Crear Cuenta</h2>

        <div class="row mb-2">
            <div class="col-md-6">
                <div class="mb-2">
                    <label for="nombre" class="form-label">Nombre completo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre completo" required minlength="3" pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{3,}$">
                    <div class="invalid-feedback" id="nombreError">Nombre inválido</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-2">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico" required>
                    <div class="invalid-feedback" id="emailError">Ingrese un correo válido</div>
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-6">
                <div class="mb-2">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" required pattern="^[0-9]{7,15}$">
                    <div class="invalid-feedback" id="telefonoError">Teléfono inválido</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-2">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección" required minlength="5">
                    <div class="invalid-feedback" id="direccionError">Dirección inválida</div>
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-6">
                <div class="mb-2">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required minlength="8">
                    <div class="invalid-feedback" id="passwordError">La contraseña no cumple los requisitos</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-2 position-relative">
                    <label for="confirm_password" class="form-label">Confirmar contraseña</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmar contraseña" required>
                    <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </span>
                    <div class="invalid-feedback" id="confirmPasswordError">Las contraseñas no coinciden</div>
                </div>
            </div>
        </div>



        <button type="submit" class="btn btn-primary w-100" id="submitBtn">
            <span class="btn-text">Crear Cuenta</span>
            <span class="btn-loader" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </button>

        <div class="mt-2 d-flex justify-content-center">
            <a href="<?= APP_URL ?>/auth/login" style="color: #2563eb; text-decoration: none;">¿Ya tienes una cuenta? Inicia sesión</a>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar/ocultar contraseña
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoader = submitBtn.querySelector('.btn-loader');

        togglePassword.addEventListener('click', function() {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Validaciones en tiempo real
        const nombre = document.getElementById('nombre');
        const email = document.getElementById('email');
        const telefono = document.getElementById('telefono');
        const direccion = document.getElementById('direccion');
        const passwordError = document.getElementById('passwordError');
        const confirmPasswordError = document.getElementById('confirmPasswordError');
        const nombreError = document.getElementById('nombreError');
        const emailError = document.getElementById('emailError');
        const telefonoError = document.getElementById('telefonoError');
        const direccionError = document.getElementById('direccionError');


        function validarNombre() {
            const val = nombre.value.trim();
            if (val.length < 3 || /[^A-Za-zÁÉÍÓÚáéíóúÑñ ]/.test(val)) {
                nombre.classList.add('is-invalid');
                nombreError.style.display = 'block';
                return false;
            }
            nombre.classList.remove('is-invalid');
            nombreError.style.display = 'none';
            return true;
        }

        function validarEmail() {
            const val = email.value.trim();
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(val)) {
                email.classList.add('is-invalid');
                emailError.style.display = 'block';
                return false;
            }
            email.classList.remove('is-invalid');
            emailError.style.display = 'none';
            return true;
        }

        function validarTelefono() {
            const val = telefono.value.trim();
            if (!/^[0-9]{7,15}$/.test(val)) {
                telefono.classList.add('is-invalid');
                telefonoError.style.display = 'block';
                return false;
            }
            telefono.classList.remove('is-invalid');
            telefonoError.style.display = 'none';
            return true;
        }

        function validarDireccion() {
            const val = direccion.value.trim();
            if (val.length < 5) {
                direccion.classList.add('is-invalid');
                direccionError.style.display = 'block';
                return false;
            }
            direccion.classList.remove('is-invalid');
            direccionError.style.display = 'none';
            return true;
        }

        function validarPassword() {
            const val = passwordInput.value;
            if (val.length < 8) {
                passwordInput.classList.add('is-invalid');
                passwordError.style.display = 'block';
                return false;
            } else {
                passwordInput.classList.remove('is-invalid');
                passwordError.style.display = 'none';
                return true;
            }
        }

        function validarConfirmPassword() {
            if (confirmPassword.value !== passwordInput.value || !confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                confirmPasswordError.style.display = 'block';
                return false;
            }
            confirmPassword.classList.remove('is-invalid');
            confirmPasswordError.style.display = 'none';
            return true;
        }

        function validarFormulario() {
            const v1 = validarNombre();
            const v2 = validarEmail();
            const v3 = validarTelefono();
            const v4 = validarDireccion();
            const v5 = validarPassword();
            const v6 = validarConfirmPassword();
            submitBtn.disabled = !(v1 && v2 && v3 && v4 && v5 && v6);
        }

        nombre.addEventListener('input', validarFormulario);
        email.addEventListener('input', validarFormulario);
        telefono.addEventListener('input', validarFormulario);
        direccion.addEventListener('input', validarFormulario);
        passwordInput.addEventListener('input', validarFormulario);
        confirmPassword.addEventListener('input', validarFormulario);

        // Envío del formulario
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                e.preventDefault();
                let isValid = true;
                
                if (!validarNombre()) isValid = false;
                if (!validarEmail()) isValid = false;
                if (!validarTelefono()) isValid = false;
                if (!validarDireccion()) isValid = false;
                if (!validarPassword()) isValid = false;
                if (!validarConfirmPassword()) isValid = false;
                
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
        }
    });
    </script>
</body>
</html> 