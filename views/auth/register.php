<!-- Registro Moderno PetMonitoring IoT -->
<div class="container d-flex align-items-center justify-content-center min-vh-100" style="background: #f6f8fc;">
  <div class="card shadow-lg border-0 rounded-4 p-4" style="max-width: 500px; width: 100%;">
    <div class="text-center mb-4">
      <img src="https://cdn-icons-png.flaticon.com/512/616/616408.png" alt="Logo mascota" style="width: 80px;">
      <h2 class="mt-3 mb-1" style="font-weight: 700; color: #0D47A1;">Crear Cuenta</h2>
      <p class="text-muted mb-0">Regístrate para comenzar a usar PetMonitoring IoT</p>
    </div>
    <form id="registerForm" method="POST" autocomplete="off" novalidate>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre completo" required minlength="3" pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{3,}$">
        <label for="nombre"><i class="fas fa-user me-2"></i>Nombre completo</label>
        <div class="invalid-feedback" id="nombreError">Nombre inválido (mínimo 3 letras, solo letras y espacios)</div>
      </div>
      <div class="form-floating mb-3">
        <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico" required>
        <label for="email"><i class="fas fa-envelope me-2"></i>Correo electrónico</label>
        <div class="invalid-feedback" id="emailError">Ingrese un correo válido</div>
      </div>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" required pattern="^[0-9]{7,15}$">
        <label for="telefono"><i class="fas fa-phone me-2"></i>Teléfono</label>
        <div class="invalid-feedback" id="telefonoError">Ingrese un teléfono válido (7-15 dígitos)</div>
      </div>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección" required minlength="5">
        <label for="direccion"><i class="fas fa-map-marker-alt me-2"></i>Dirección</label>
        <div class="invalid-feedback" id="direccionError">Ingrese una dirección válida (mínimo 5 caracteres)</div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <div class="form-floating mb-3 mb-md-0">
            <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required minlength="8">
            <label for="password"><i class="fas fa-lock me-2"></i>Contraseña</label>
            <div class="invalid-feedback" id="passwordError">La contraseña no cumple los requisitos</div>
            <ul class="list-unstyled mt-2 mb-0" id="passwordChecklist">
              <li id="chk-length" class="text-danger">Mínimo 8 caracteres</li>
              <li id="chk-mayus" class="text-danger">Al menos una mayúscula</li>
              <li id="chk-minus" class="text-danger">Al menos una minúscula</li>
              <li id="chk-num" class="text-danger">Al menos un número</li>
              <li id="chk-especial" class="text-danger">Al menos un carácter especial</li>
            </ul>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating mb-3 mb-md-0 position-relative">
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmar contraseña" required>
            <label for="confirm_password"><i class="fas fa-lock me-2"></i>Confirmar contraseña</label>
            <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;" id="togglePassword">
              <i class="fas fa-eye"></i>
            </span>
            <div class="invalid-feedback" id="confirmPasswordError">Las contraseñas no coinciden</div>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100 mb-2" style="font-weight:600;">Crear Cuenta</button>
      <div class="d-flex justify-content-between">
        <a href="<?= APP_URL ?>/auth/login" class="small">¿Ya tienes una cuenta? Inicia sesión</a>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mostrar/ocultar contraseña
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  const confirmPassword = document.getElementById('confirm_password');
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
  const checklist = {
    length: document.getElementById('chk-length'),
    mayus: document.getElementById('chk-mayus'),
    minus: document.getElementById('chk-minus'),
    num: document.getElementById('chk-num'),
    especial: document.getElementById('chk-especial')
  };
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
    const v1 = validarNombre();
    const v2 = validarEmail();
    const v3 = validarTelefono();
    const v4 = validarDireccion();
    const v5 = validarPassword();
    const v6 = validarConfirmPassword();
    document.querySelector('#registerForm button[type="submit"]').disabled = !(v1 && v2 && v3 && v4 && v5 && v6);
  }
  nombre.addEventListener('input', validarFormulario);
  email.addEventListener('input', validarFormulario);
  telefono.addEventListener('input', validarFormulario);
  direccion.addEventListener('input', validarFormulario);
  passwordInput.addEventListener('input', validarFormulario);
  confirmPassword.addEventListener('input', validarFormulario);
  // Envío por AJAX (usa la función global handleFormSubmit si existe)
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
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
#togglePassword i { color: #888; }
</style> 