<?php
/**
 * Vista: dispositivos/form.php
 * ----------------------------
 * Formulario para crear o editar un dispositivo.
 *
 * Variables recibidas:
 *   - $dispositivo: Datos del dispositivo (si se está editando).
 *   - $mascotas: Lista de mascotas disponibles para asignar.
 *   - $usuarios: Lista de usuarios (si aplica).
 *   - $permisos: Permisos del usuario actual.
 *
 * Uso:
 *   Esta vista es llamada desde DispositivosController para mostrar el formulario de alta/edición.
 */
$dispositivo = $dispositivo ?? null;
$isEdit = isset($dispositivo) && !empty($dispositivo);
$titulo = $isEdit ? 'Editar Dispositivo' : 'Nuevo Dispositivo';
?>

<div class="modal-header">
    <h5 class="modal-title"><?= $titulo ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="formDispositivo" method="POST" action="<?= BASE_URL ?>dispositivos/guardar">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id_dispositivo" value="<?= $dispositivo['id_dispositivo'] ?>">
    <?php endif; ?>
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-6 form-floating">
                <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="100" value="<?= htmlspecialchars($dispositivo['nombre'] ?? '') ?>">
                <label for="nombre">Nombre del Dispositivo *</label>
            </div>
            <div class="col-md-6 form-floating">
                <input type="text" class="form-control" id="mac" name="mac" required pattern="^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$" placeholder="00:11:22:33:44:55" value="<?= htmlspecialchars($dispositivo['mac'] ?? '') ?>">
                <label for="mac">Dirección MAC *</label>
            </div>
            <div class="col-md-6 form-floating">
                <select class="form-select" id="estado" name="estado" required>
                    <option value="activo" <?= ($dispositivo['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= ($dispositivo['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
                <label for="estado">Estado *</label>
            </div>
            <div class="col-md-6 form-floating">
                <select class="form-select" id="usuario_id" name="usuario_id">
                    <option value="">Sin asignar</option>
                    <?php if (isset($usuarios) && is_array($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= $usuario['id_usuario'] ?>" <?= ($dispositivo['usuario_id'] ?? '') == $usuario['id_usuario'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <label for="usuario_id">Usuario Asignado</label>
            </div>
            <div class="col-md-6 form-floating">
                <select class="form-select" id="mascota_id" name="mascota_id">
                    <option value="">Sin asignar</option>
                </select>
                <label for="mascota_id">Mascota Asignada</label>
                <div class="form-text">Selecciona primero un usuario para ver sus mascotas</div>
            </div>
        </div>
    </div>
    <div class="modal-footer d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Crear' ?> Dispositivo
        </button>
    </div>
</form>

<?php if ($isEdit && isset($dispositivo['mascota_id']) && $dispositivo['mascota_id']): ?>
<script>window.MASCOTA_ACTUAL = "<?= strval($dispositivo['mascota_id']) ?>"; console.log('DEBUG window.MASCOTA_ACTUAL:', window.MASCOTA_ACTUAL);</script>
<?php else: ?>
<script>window.MASCOTA_ACTUAL = ""; console.log('DEBUG window.MASCOTA_ACTUAL: vacía');</script>
<?php endif; ?> 