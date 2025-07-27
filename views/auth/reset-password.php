<!-- Restablecer Contraseña Moderno PetMonitoring IoT -->
<div class="container d-flex align-items-center justify-content-center min-vh-100" style="background: #f6f8fc;">
  <div class="card shadow-lg border-0 rounded-4 p-4" style="max-width: 400px; width: 100%;">
    <div class="text-center mb-4">
      <img src="https://cdn-icons-png.flaticon.com/512/616/616408.png" alt="Logo mascota" style="width: 80px;">
      <h2 class="mt-3 mb-1" style="font-weight: 700; color: #0D47A1;">Restablecer Contraseña</h2>
      <p class="text-muted mb-0">Ingresa tu nueva contraseña</p>
    </div>
    <form id="resetPasswordForm" method="POST" autocomplete="off" novalidate>
      <div class="form-floating mb-3 position-relative">
        <input type="password" class="form-control" id="password" name="password" placeholder="Nueva contraseña" required minlength="8">
        <label for="password"><i class="fas fa-lock me-2"></i>Nueva contraseña</label>
        <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;" id="togglePassword1">
          <i class="fas fa-eye"></i>
        </span>
        <div class="invalid-feedback" id="passwordError">La contraseña no cumple los requisitos</div>
      </div>
      <div class="form-floating mb-3 position-relative">
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmar contraseña" required>
        <label for="confirm_password"><i class="fas fa-lock me-2"></i>Confirmar contraseña</label>
        <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;" id="togglePassword2">
          <i class="fas fa-eye"></i>
        </span>
        <div class="invalid-feedback" id="confirmPasswordError">Las contraseñas no coinciden</div>
      </div>
      <ul class="list-unstyled mt-2 mb-3" id="passwordChecklist">
        <li id="chk-length" class="text-danger">Mínimo 8 caracteres</li>
        <li id="chk-mayus" class="text-danger">Al menos una mayúscula</li>
        <li id="chk-minus" class="text-danger">Al menos una minúscula</li>
        <li id="chk-num" class="text-danger">Al menos un número</li>
        <li id="chk-especial" class="text-danger">Al menos un carácter especial</li>
      </ul>
      <button type="submit" class="btn btn-primary w-100 mb-2" style="font-weight:600;">Restablecer contraseña</button>
      <div class="d-flex justify-content-between">
        <a href="<?= APP_URL ?>/auth/login" class="small">Volver al login</a>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mostrar/ocultar contraseña para ambos campos
  const togglePassword1 = document.getElementById('togglePassword1');
  const togglePassword2 = document.getElementById('togglePassword2');
  const passwordInput = document.getElementById('password');
  const confirmPassword = document.getElementById('confirm_password');
  togglePassword1.addEventListener('click', function() {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.querySelector('i').classList.toggle('fa-eye');
    this.querySelector('i').classList.toggle('fa-eye-slash');
  });
  togglePassword2.addEventListener('click', function() {
    const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
    confirmPassword.setAttribute('type', type);
    this.querySelector('i').classList.toggle('fa-eye');
    this.querySelector('i').classList.toggle('fa-eye-slash');
  });

  // Validaciones en tiempo real
  const passwordError = document.getElementById('passwordError');
  const confirmPasswordError = document.getElementById('confirmPasswordError');
  const checklist = {
    length: document.getElementById('chk-length'),
    mayus: document.getElementById('chk-mayus'),
    minus: document.getElementById('chk-minus'),
    num: document.getElementById('chk-num'),
    especial: document.getElementById('chk-especial')
  };
  function validarPassword() {
    const val = passwordInput.value;
    let valid = true;
    if (val.length >= 8) {
      checklist.length.classList.remove('text-danger');
      checklist.length.classList.add('text-success');
    } else {
      checklist.length.classList.add('text-danger');
      checklist.length.classList.remove('text-success');
      valid = false;
    }
    if (/[A-Z]/.test(val)) {
      checklist.mayus.classList.remove('text-danger');
      checklist.mayus.classList.add('text-success');
    } else {
      checklist.mayus.classList.add('text-danger');
      checklist.mayus.classList.remove('text-success');
      valid = false;
    }
    if (/[a-z]/.test(val)) {
      checklist.minus.classList.remove('text-danger');
      checklist.minus.classList.add('text-success');
    } else {
      checklist.minus.classList.add('text-danger');
      checklist.minus.classList.remove('text-success');
      valid = false;
    }
    if (/[0-9]/.test(val)) {
      checklist.num.classList.remove('text-danger');
      checklist.num.classList.add('text-success');
    } else {
      checklist.num.classList.add('text-danger');
      checklist.num.classList.remove('text-success');
      valid = false;
    }
    if (/[@$!%*?&]/.test(val)) {
      checklist.especial.classList.remove('text-danger');
      checklist.especial.classList.add('text-success');
    } else {
      checklist.especial.classList.add('text-danger');
      checklist.especial.classList.remove('text-success');
      valid = false;
    }
    if (!valid) {
      passwordInput.classList.add('is-invalid');
      passwordError.style.display = 'block';
    } else {
      passwordInput.classList.remove('is-invalid');
      passwordError.style.display = 'none';
    }
    return valid;
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
    const v1 = validarPassword();
    const v2 = validarConfirmPassword();
    document.querySelector('#resetPasswordForm button[type="submit"]').disabled = !(v1 && v2);
  }
  passwordInput.addEventListener('input', validarFormulario);
  confirmPassword.addEventListener('input', validarFormulario);
  // Envío por AJAX (usa la función global handleFormSubmit si existe)
  const resetForm = document.getElementById('resetPasswordForm');
  if (resetForm) {
    resetForm.addEventListener('submit', function(e) {
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
<style>
body { background: #f6f8fc !important; }
.card { border-radius: 1.5rem !important; transition: none !important; }
.card:hover, .card:focus, .card:active { box-shadow: 0 2px 12px rgba(13,71,161,0.07) !important; transform: none !important; }
.form-control:focus { border-color: #0D47A1; box-shadow: 0 0 0 0.2rem rgba(13,71,161,.15); }
.btn-primary { background: #0D47A1; border: none; }
.btn-primary:hover { background: #1565C0; }
#togglePassword1 i, #togglePassword2 i { color: #888; }
</style> 