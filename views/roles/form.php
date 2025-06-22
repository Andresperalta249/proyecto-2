<?php
// Define valores por defecto para el modo de creación
$esEdicion = isset($rol) && $rol;
$titulo = $esEdicion ? 'Editar Rol' : 'Nuevo Rol';

// Asegurarse de que $rol sea un array para evitar errores, incluso si es nuevo
$rolData = $esEdicion ? $rol : ['id_rol' => null, 'nombre' => '', 'descripcion' => ''];
$permisosAsignados = $esEdicion ? ($rol['permiso_ids'] ?? []) : [];
?>

<form id="formRol" action="<?= htmlspecialchars($esEdicion ? APP_URL . '/roles/guardar/' . $rolData['id_rol'] : APP_URL . '/roles/guardar') ?>" method="POST">
    <?php if ($esEdicion) : ?>
        <input type="hidden" name="id_rol" value="<?= htmlspecialchars($rolData['id_rol']) ?>">
    <?php endif; ?>
        <div class="form-group mb-3">
            <label for="nombre" class="form-label">Nombre del Rol</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required 
                   value="<?= htmlspecialchars($rolData['nombre']) ?>">
        </div>

        <div class="form-group mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($rolData['descripcion']) ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Permisos</label>
            <div class="permisos-container p-2 border rounded" style="max-height: 250px; overflow-y: auto;">
                <?php if (empty($permisos)) : ?>
                    <p class="text-muted">No hay permisos para asignar.</p>
                <?php else : ?>
                    <?php foreach ($permisos as $permiso) : ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permisos[]" 
                                   value="<?= $permiso['id_permiso'] ?>" 
                                   id="permiso_<?= $permiso['id_permiso'] ?>"
                                   <?= in_array($permiso['id_permiso'], $permisosAsignados) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="permiso_<?= $permiso['id_permiso'] ?>">
                                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $permiso['nombre']))) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    
    <div class="mt-3 text-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar Rol</button>
    </div>
</form> 