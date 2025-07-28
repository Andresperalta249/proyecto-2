<?php
// Formulario para crear/editar mascota
$mascota = $mascota ?? null;
$esEdicion = isset($mascota) && $mascota !== null;


?>

<form id="formMascota" method="POST" action="<?= BASE_URL ?>mascotas/<?= $esEdicion ? 'edit' : 'create' ?>">
    <?php if ($esEdicion && isset($mascota['id_mascota'])): ?>
        <input type="hidden" name="id_mascota" value="<?= $mascota['id_mascota'] ?>">
    <?php endif; ?>

    <!-- Nombre -->
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre *</label>
        <input type="text" class="form-control" id="nombre" name="nombre" 
               value="<?= htmlspecialchars($mascota['nombre'] ?? '') ?>" required>
    </div>

    <!-- Especie -->
    <div class="mb-3">
        <label for="especie" class="form-label">Especie *</label>
        <select class="form-select" id="especie" name="especie" required>
            <option value="">Seleccione una especie</option>
            <option value="perro" <?= ($mascota['especie'] ?? '') === 'perro' ? 'selected' : '' ?>>Perro</option>
            <option value="gato" <?= ($mascota['especie'] ?? '') === 'gato' ? 'selected' : '' ?>>Gato</option>
        </select>
    </div>

    <!-- Tamaño -->
    <div class="mb-3">
        <label for="tamano" class="form-label">Tamaño *</label>
        <select class="form-select" id="tamano" name="tamano" required>
            <option value="">Seleccione un tamaño</option>
            <option value="pequeño" <?= ($mascota['tamano'] ?? '') === 'pequeño' ? 'selected' : '' ?>>Pequeño</option>
            <option value="Mediano" <?= ($mascota['tamano'] ?? '') === 'Mediano' ? 'selected' : '' ?>>Mediano</option>
            <option value="Grande" <?= ($mascota['tamano'] ?? '') === 'Grande' ? 'selected' : '' ?>>Grande</option>
        </select>
    </div>

    <!-- Género -->
    <div class="mb-3">
        <label for="genero" class="form-label">Género *</label>
        <select class="form-select" id="genero" name="genero" required>
            <option value="">Seleccione un género</option>
            <option value="Macho" <?= ($mascota['genero'] ?? '') === 'Macho' ? 'selected' : '' ?>>Macho</option>
            <option value="Hembra" <?= ($mascota['genero'] ?? '') === 'Hembra' ? 'selected' : '' ?>>Hembra</option>
        </select>
    </div>

    <!-- Fecha de Nacimiento -->
    <div class="mb-3">
        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
               value="<?= $mascota['fecha_nacimiento'] ?? '' ?>" required>
    </div>

    <!-- Propietario (solo para administradores) -->
    <?php if (verificarPermiso('gestionar_mascotas')): ?>
    <div class="mb-3">
        <label for="usuario_id" class="form-label">Propietario</label>
        <select class="form-select" id="usuario_id" name="usuario_id">
            <option value="">Seleccione un propietario</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?= $usuario['id_usuario'] ?>" 
                        <?= ($mascota['usuario_id'] ?? '') == $usuario['id_usuario'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['email']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <!-- Estado -->
    <div class="mb-3">
        <label for="estado" class="form-label">Estado</label>
        <select class="form-select" id="estado" name="estado">
            <option value="activo" <?= ($mascota['estado'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
            <option value="inactivo" <?= ($mascota['estado'] ?? 'activo') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>
    </div>



    <!-- Botones -->
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-<?= $esEdicion ? 'save' : 'plus' ?>"></i>
            <?= $esEdicion ? 'Actualizar' : 'Crear' ?> Mascota
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Inicializar Select2 si está disponible
    if (typeof $.fn.select2 !== 'undefined') {
        $('#propietario_id').select2({
            placeholder: 'Seleccione un propietario',
            allowClear: true
        });
    }

    // Manejar envío del formulario
    $('#formMascota').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const url = $(this).attr('action');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message || 'Mascota <?= $esEdicion ? 'actualizada' : 'creada' ?> correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#modalMascota').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.error || 'Error al <?= $esEdicion ? 'actualizar' : 'crear' ?> la mascota'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión'
                });
            }
        });
    });
});
</script> 