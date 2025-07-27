<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - PetMonitoring IoT</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/proyecto-2/assets/css/typography.css">
    <link rel="stylesheet" href="/proyecto-2/assets/css/auth.css">
    <link rel="icon" type="image/svg+xml" href="/proyecto-2/assets/img/favicon.svg">
</head>
<body>
    <div style="text-align:center; margin-top: 40px;">
        <img src="/proyecto-2/assets/img/paw-dog.svg" alt="PetMonitoring Logo" style="width:54px;height:54px;">
        <h1>PetMonitoring IoT</h1>
        <p>Monitoreo inteligente para tus mascotas</p>
    </div>

    <form id="forgotForm" method="POST" autocomplete="off" novalidate style="max-width:370px;margin:30px auto 0 auto;">
        <h2 class="mb-3" style="text-align:center;">¿Olvidaste tu contraseña?</h2>
        <p style="text-align:center;">Ingresa tu correo y te enviaremos instrucciones para restablecerla</p>
        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico" required autofocus>
            <div class="invalid-feedback" id="emailError">Ingrese un correo válido</div>
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-2 fw-semibold">Enviar instrucciones</button>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <a href="<?= APP_URL ?>/auth/login"><i class="fas fa-sign-in-alt me-1"></i>Volver al login</a>
            <a href="<?= APP_URL ?>/auth/register"><i class="fas fa-user-plus me-1"></i>Regístrate</a>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      const emailInput = document.getElementById('email');
      const emailError = document.getElementById('emailError');
      emailInput.addEventListener('input', function() {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!re.test(this.value)) {
          this.classList.add('is-invalid');
          emailError.style.display = 'block';
        } else {
          this.classList.remove('is-invalid');
          emailError.style.display = 'none';
        }
      });
      const forgotForm = document.getElementById('forgotForm');
      if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
          e.preventDefault();
          if (!emailInput.value || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
            emailInput.classList.add('is-invalid');
            emailError.style.display = 'block';
            return;
          }
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