<?php
$dispositivo = $dispositivo ?? null;
$isEdit = isset($dispositivo);
?>

<form id="formDispositivo" method="POST" action="<?= BASE_URL ?>dispositivos/<?= $isEdit ? 'guardar' : 'guardar' ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id_dispositivo" value="<?= $dispositivo['id_dispositivo'] ?>">
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Dispositivo *</label>
                <input type="text" class="form-control" id="nombre" name="nombre" 
                       value="<?= htmlspecialchars($dispositivo['nombre'] ?? '') ?>" 
                       required maxlength="100">
                <div class="invalid-feedback">El nombre es requerido</div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label for="mac" class="form-label">Dirección MAC *</label>
                <input type="text" class="form-control" id="mac" name="mac" 
                       value="<?= htmlspecialchars($dispositivo['mac'] ?? '') ?>" 
                       pattern="^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$" 
                       placeholder="00:11:22:33:44:55" required>
                <div class="invalid-feedback">Formato MAC inválido (ej: 00:11:22:33:44:55)</div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="estado" class="form-label">Estado *</label>
                <select class="form-select" id="estado" name="estado" required>
                    <option value="activo" <?= ($dispositivo['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= ($dispositivo['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label for="usuario_id" class="form-label">Usuario Asignado</label>
                <select class="form-select" id="usuario_id" name="usuario_id">
                    <option value="">Sin asignar</option>
                    <?php if (isset($usuarios) && is_array($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= $usuario['id_usuario'] ?>" 
                                    <?= ($dispositivo['usuario_id'] ?? '') == $usuario['id_usuario'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="mascota_id" class="form-label">Mascota Asignada</label>
                <select class="form-select" id="mascota_id" name="mascota_id">
                    <option value="">Sin asignar</option>
                    <!-- Las mascotas se cargarán dinámicamente según el usuario seleccionado -->
                </select>
                <div class="form-text">Selecciona primero un usuario para ver sus mascotas</div>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> 
            <?= $isEdit ? 'Actualizar' : 'Crear' ?> Dispositivo
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Validación del formato MAC
    $('#mac').on('input', function() {
        const mac = $(this).val();
        const macPattern = /^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/;
        
        if (mac && !macPattern.test(mac)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Cargar mascotas cuando se selecciona un usuario
    $('#usuario_id').on('change', function() {
        const usuarioId = $(this).val();
        const mascotaSelect = $('#mascota_id');
        
        if (usuarioId) {
            // Cargar mascotas del usuario seleccionado
            $.ajax({
                url: '<?= BASE_URL ?>dispositivos/obtenerMascotasSinDispositivo/' + usuarioId,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        mascotaSelect.empty().append('<option value="">Sin asignar</option>');
                        response.data.forEach(function(mascota) {
                            mascotaSelect.append(`<option value="${mascota.id_mascota}">${mascota.nombre} (${mascota.especie})</option>`);
                        });
                    } else {
                        mascotaSelect.empty().append('<option value="">Sin asignar</option>');
                    }
                },
                error: function() {
                    mascotaSelect.empty().append('<option value="">Sin asignar</option>');
                }
            });
        } else {
            // Limpiar mascotas si no hay usuario seleccionado
            mascotaSelect.empty().append('<option value="">Sin asignar</option>');
        }
    });
    
    // Cargar mascotas al inicializar si hay un usuario seleccionado
    const usuarioIdInicial = $('#usuario_id').val();
    if (usuarioIdInicial) {
        $('#usuario_id').trigger('change');
        
        // Si estamos editando y hay una mascota asignada, seleccionarla después de cargar las opciones
        <?php if (isset($dispositivo) && $dispositivo && $dispositivo['mascota_id']): ?>
        setTimeout(function() {
            $('#mascota_id').val('<?= $dispositivo['mascota_id'] ?>');
        }, 500);
        <?php endif; ?>
    }
});
</script> 