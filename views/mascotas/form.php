<?php
/**
 * Vista: mascotas/form.php
 * ------------------------
 * Formulario para crear o editar una mascota.
 *
 * Variables recibidas:
 *   - $mascota: Datos de la mascota (si se está editando).
 *   - $usuarios: Lista de usuarios para asignar propietario.
 *   - $permisos: Permisos del usuario actual.
 *
 * Uso:
 *   Esta vista es llamada desde MascotasController para mostrar el formulario de alta/edición.
 */
?>
<form id="formMascota" action="<?= BASE_URL ?>mascotas/guardar" method="post" autocomplete="off" data-puede-asignar-propietario="<?= $puedeAsignarPropietario ? 'true' : 'false' ?>">
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
            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($mascota['fecha_nacimiento'] ?? '') ?>">
            <div class="form-text">Opcional</div>
        </div>
    </div>

    <?php if ($puedeAsignarPropietario): ?>
    <div class="mb-3">
        <label for="usuario_id" class="form-label">Propietario <span class="text-danger">*</span></label>
        <select class="form-select select2" id="usuario_id" name="usuario_id" required>
            <option value="">Seleccione un propietario</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?= $usuario['id_usuario'] ?>" <?= ($mascota['usuario_id'] ?? '') == $usuario['id_usuario'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($usuario['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="form-text">Campo obligatorio: debe asignar un propietario a la mascota</div>
    </div>
    <?php endif; ?>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
</form>

<!-- Los scripts se manejan desde initializeModalPlugins() en mascotas.js --> 