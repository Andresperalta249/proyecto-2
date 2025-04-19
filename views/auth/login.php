<!DOCTYPE html>
<html lang="es">
<head>
   
    <title>Iniciar sesión - MascotasIoT</title>
    <?= include_once __DIR__ . '/../../views/layouts/head.php'; ?>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .contenedor-de-inicio-de-sesion {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .encabezado-de-inicio-de-sesion {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 3rem;
            color: #0d6efd;
        }
        .enlaces-adicionales {
            text-align: center;
            margin-top: 15px;
        }
        .enlaces-adicionales a {
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
            display: block;
            margin: 10px 0;
        }
        .enlaces-adicionales a:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="contenedor-de-inicio-de-sesion">
            <div class="encabezado-de-inicio-de-sesion">
                <i class="fas fa-paw"></i>
                <h2>Mascotas IoT</h2>
                <p class="text-muted">Sistema de Monitoreo de Mascotas</p>
            </div>

            <div id="Contenedor de alerta"></div>

            <form id="loginForm" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>

                <div class="enlaces-adicionales">
                    <a href="/Proyecto%202/views/auth/forgot-password.php">¿Olvidaste tu contraseña?</a>
                    <a href="/Proyecto%202/views/auth/register.php">¿No tienes cuenta? Regístrate</a>
                </div>
            </form>
        </div>
    </div>

   
    <script>
        $(document).ready(function() {
            // Configuración de Toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            // Toggle password visibility
            $('#togglePassword').click(function() {
                const password = $('#password');
                const icon = $(this).find('i');
                
                if (password.attr('type') === 'password') {
                    password.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    password.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Form submission handling
            $('#loginForm').submit(function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                $.ajax({
                    url: '/Proyecto%202/controllers/AuthController.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        console.log('Respuesta del servidor:', response);
                        if (response.success) {
                            toastr.success(response.message);
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            }
                        } else {
                            toastr.error(response.message || 'Error al iniciar sesión');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Estado:', status);
                        console.log('Error:', error);
                        console.log('Respuesta completa:', xhr.responseText);
                        try {
                            const response = JSON.parse(xhr.responseText);
                            toastr.error(response.message || 'Error al iniciar sesión');
                        } catch (e) {
                            toastr.error('Error al conectar con el servidor. Por favor, intente nuevamente.');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html> 