/**
 * Formulario de Dispositivos
 * =========================
 * 
 * Archivo: assets/js/form-dispositivo.js
 * 
 * Propósito:
 *   - Manejo específico del formulario de dispositivos.
 *   - Validación de campos del formulario.
 *   - Carga dinámica de mascotas según usuario seleccionado.
 * 
 * Funciones principales:
 *   - inicializarFormulario(): Configura el formulario de dispositivos.
 *   - validarFormulario(): Valida los campos del formulario.
 *   - cargarMascotas(): Carga mascotas según el usuario seleccionado.
 *   - guardarDispositivo(): Envía el formulario para guardar.
 * 
 * Uso:
 *   Este archivo se usa específicamente en el formulario de dispositivos
 *   para manejar la validación y envío de datos.
 */

window.inicializarFormularioDispositivo = function() {
    // Validación del formato MAC
    const macInput = document.getElementById('mac');
    if (macInput) {
        macInput.addEventListener('input', function() {
            const mac = this.value;
            const macPattern = /^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/;
            if (mac && !macPattern.test(mac)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    // Inicialización de selects
    const usuarioSelect = document.getElementById('usuario_id');
    const mascotaSelect = document.getElementById('mascota_id');
    if (!usuarioSelect || !mascotaSelect) return;

    // Cargar mascotas para el usuario seleccionado
    function cargarMascotas(usuarioId) {
        const dispositivoId = document.querySelector('input[name="id_dispositivo"]')?.value;
        mascotaSelect.innerHTML = '<option value="">Sin asignar</option>';
        const url = dispositivoId
            ? `${window.BASE_URL}dispositivos/obtenerMascotasDisponibles/${usuarioId}/${dispositivoId}`
            : `${window.BASE_URL}dispositivos/obtenerMascotasSinDispositivo/${usuarioId}`;
        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(mascota => {
                        const option = document.createElement('option');
                        option.value = mascota.id_mascota;
                        option.textContent = `${mascota.nombre} (${mascota.especie.charAt(0).toUpperCase() + mascota.especie.slice(1)})`;
                        mascotaSelect.appendChild(option);
                    });
                    // Seleccionar la mascota actual si existe
                    if (window.MASCOTA_ACTUAL && mascotaSelect.querySelector(`option[value="${window.MASCOTA_ACTUAL}"]`)) {
                        mascotaSelect.value = window.MASCOTA_ACTUAL;
                    } else {
                        mascotaSelect.value = "";
                    }
                    mascotaSelect.dispatchEvent(new Event('change'));
                }
            });
    }

    // Al abrir el modal, si hay usuario seleccionado, cargar mascotas
    if (usuarioSelect.value) {
        cargarMascotas(usuarioSelect.value);
    }
    usuarioSelect.addEventListener('change', function() {
        cargarMascotas(this.value);
    });
}; 