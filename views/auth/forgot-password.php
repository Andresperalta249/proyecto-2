<?php
/**
 * Vista: auth/forgot-password.php
 * -------------------------------
 * Formulario para solicitar restablecimiento de contraseña.
 *
 * Variables recibidas:
 *   - $error: Mensaje de error (si existe).
 *   - $success: Mensaje de éxito (si existe).
 *
 * Uso:
 *   Esta vista es llamada desde AuthController para mostrar el formulario de recuperación.
 *   Los usuarios ingresan su email para recibir un enlace de restablecimiento.
 */
?>
<!-- Recuperar Contraseña Moderno PetMonitoring IoT -->
<div class="container d-flex align-items-center justify-content-center min-vh-100">
  <div class="card shadow-lg border-0 rounded-4 p-4 auth-card">
    <div class="text-center mb-4">
      <img src="https://cdn-icons-png.flaticon.com/512/616/616408.png" alt="Logo mascota" class="auth-logo">
      <h2 class="mt-3 mb-1 auth-title">¿Olvidaste tu contraseña?</h2>
      <p class="text-muted mb-0">Ingresa tu correo y te enviaremos instrucciones para restablecerla</p>
    </div>
    <form id="forgotForm" method="POST" autocomplete="off" novalidate>
      <div class="form-floating mb-3">
        <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico" required autofocus>
        <label for="email"><i class="fas fa-envelope me-2"></i>Correo electrónico</label>
        <div class="invalid-feedback" id="emailError">Ingrese un correo válido</div>
      </div>
      <button type="submit" class="btn btn-primary w-100 mb-2 font-weight-semibold">Enviar instrucciones</button>
      <div class="d-flex justify-content-between">
        <a href="<?= APP_URL ?>/auth/login" class="small">Volver al login</a>
        <a href="<?= APP_URL ?>/auth/register" class="small">Regístrate</a>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Validación en tiempo real
  const emailInput = document.getElementById('email');
  // const emailError = document.getElementById('emailError'); // Ya no es necesario manipular style.display
  emailInput.addEventListener('input', function() {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!re.test(this.value)) {
      this.classList.add('is-invalid');
    } else {
      this.classList.remove('is-invalid');
    }
  });
  // Envío por AJAX (usa la función global handleFormSubmit si existe)
  const forgotForm = document.getElementById('forgotForm');
  if (forgotForm) {
    forgotForm.addEventListener('submit', function(e) {
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