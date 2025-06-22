<form id="formMascota" action="<?= APP_URL ?>/mascotas/guardar" method="post" autocomplete="off">
    <div class="modal-header">
        <h5 class="modal-title" id="modalMascotaLabel"><?= isset($mascota['id_mascota']) ? 'Editar Mascota' : 'Nueva Mascota' ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <input type="hidden" name="id_mascota" value="<?= htmlspecialchars($mascota['id_mascota'] ?? '') ?>">
        
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($mascota['nombre'] ?? '') ?>" required>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="especie" class="form-label">Especie</label>
                <select class="form-select" id="especie" name="especie" required>
                    <option value="perro" <?= ($mascota['especie'] ?? '') === 'perro' ? 'selected' : '' ?>>Perro</option>
                    <option value="gato" <?= ($mascota['especie'] ?? '') === 'gato' ? 'selected' : '' ?>>Gato</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="genero" class="form-label">Género</label>
                <select class="form-select" id="genero" name="genero" required>
                    <option value="macho" <?= ($mascota['genero'] ?? '') === 'macho' ? 'selected' : '' ?>>Macho</option>
                    <option value="hembra" <?= ($mascota['genero'] ?? '') === 'hembra' ? 'selected' : '' ?>>Hembra</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="tamano" class="form-label">Tamaño</label>
                <select class="form-select" id="tamano" name="tamano" required>
                    <option value="Pequeño" <?= ($mascota['tamano'] ?? '') === 'Pequeño' ? 'selected' : '' ?>>Pequeño</option>
                    <option value="Mediano" <?= ($mascota['tamano'] ?? '') === 'Mediano' ? 'selected' : '' ?>>Mediano</option>
                    <option value="Grande" <?= ($mascota['tamano'] ?? '') === 'Grande' ? 'selected' : '' ?>>Grande</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($mascota['fecha_nacimiento'] ?? '') ?>" required>
            </div>
        </div>

        <?php if ($esAdmin): ?>
        <div class="mb-3">
            <label for="usuario_id" class="form-label">Propietario</label>
            <select class="form-select select2" id="usuario_id" name="usuario_id" required>
                <option value="">Seleccione un propietario</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['id_usuario'] ?>" <?= ($mascota['usuario_id'] ?? '') == $usuario['id_usuario'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($usuario['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
</form>

<script>
// Asegurarse de que jQuery y select2 estén disponibles
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        setTimeout(function() {
            if ($('.select2').length && $.fn.select2) {
                $('.select2').select2({
                    dropdownParent: $('#modalMascota')
                });
            }
        }, 100); // Pequeño retraso para asegurar que el DOM y select2 estén listos
    });
} else {
    console.error('jQuery no está disponible');
}
</script> 