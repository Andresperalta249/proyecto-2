<?php
/**
 * Vista: usuarios/form.php
 * ------------------------
 * Formulario para crear o editar un usuario.
 *
 * Variables recibidas:
 *   - $usuario: Datos del usuario (si se está editando).
 *   - $roles: Lista de roles disponibles.
 *   - $permisos: Lista de permisos disponibles.
 *
 * Uso:
 *   Esta vista es llamada desde UsuariosController para mostrar el formulario de alta/edición de usuarios.
 */
$esEditar = isset($usuario) && !empty($usuario);
$url_action = BASE_URL . 'usuarios/guardar';
?>

<form id="formUsuario" action="<?php echo $url_action; ?>" method="post">
    <?php if ($esEditar): ?>
        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario'] ?? ''; ?>">
    <?php endif; ?>

    <?php if ($esEditar): // --- VISTA PARA EDITAR USUARIO (CON PESTAÑAS) --- ?>
        <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="datos-tab" data-bs-toggle="tab" data-bs-target="#datos" type="button" role="tab" aria-controls="datos" aria-selected="true">Datos del Usuario</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-pane" type="button" role="tab" aria-controls="password" aria-selected="false">Cambiar Contraseña</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="datos" role="tabpanel" aria-labelledby="datos-tab">
                <div class="row g-3">
                    <div class="col-md-6 form-floating">
                        <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                        <label>Nombre</label>
                    </div>
                    <div class="col-md-6 form-floating">
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                        <label>Email</label>
                    </div>
                    <div class="col-md-6 form-floating">
                        <input type="tel" class="form-control" name="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                        <label>Teléfono</label>
                    </div>
                    <div class="col-md-6 form-floating">
                        <input type="text" class="form-control" name="direccion" value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>">
                        <label>Dirección</label>
                    </div>
                    <div class="col-md-6 form-floating">
                        <select class="form-select" name="rol_id" required>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol['id_rol'] ?>" <?= $usuario['rol_id'] == $rol['id_rol'] ? 'selected' : '' ?>><?= htmlspecialchars($rol['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Rol</label>
                    </div>
                    <div class="col-md-6 form-floating">
                        <select class="form-select" name="estado" required>
                            <option value="activo" <?= $usuario['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $usuario['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                        <label>Estado</label>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="password-pane" role="tabpanel" aria-labelledby="password-tab">
                <div class="row g-3">
                    <div class="col-md-6 form-floating">
                        <input type="password" class="form-control" name="password" autocomplete="new-password">
                        <label>Nueva Contraseña</label>
                    </div>
                    <div class="col-md-6 form-floating">
                        <input type="password" class="form-control" name="confirm_password" autocomplete="new-password">
                        <label>Confirmar Contraseña</label>
                    </div>
                    <div class="col-12"><small class="form-text text-muted">Dejar en blanco para no cambiar la contraseña.</small></div>
                </div>
            </div>
        </div>
    <?php else: // --- VISTA PARA CREAR USUARIO (FORMULARIO ÚNICO) --- ?>
        <div class="row g-3">
            <div class="col-md-6 form-floating">
                <input type="text" class="form-control" id="nombre" name="nombre" required autocomplete="name">
                <label for="nombre">Nombre</label>
            </div>
            <div class="col-md-6 form-floating">
                <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                <label for="email">Email</label>
            </div>
            <div class="col-md-6 form-floating">
                <input type="tel" class="form-control" id="telefono" name="telefono" autocomplete="tel">
                <label for="telefono">Teléfono</label>
            </div>
             <div class="col-md-6 form-floating">
                <input type="text" class="form-control" id="direccion" name="direccion" autocomplete="street-address">
                <label for="direccion">Dirección</label>
            </div>
            <div class="col-md-6 form-floating">
                <select class="form-select" id="rol_id" name="rol_id" required>
                    <option value="" disabled selected>Seleccione un rol</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= $rol['id_rol'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="rol_id">Rol</label>
            </div>
            <div class="col-md-6 form-floating">
                <select class="form-select" id="estado" name="estado" required>
                    <option value="activo" selected>Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
                <label for="estado">Estado</label>
            </div>
            <hr class="my-3">
            <div class="col-md-6 form-floating">
                <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                <label for="password">Contraseña</label>
            </div>
            <div class="col-md-6 form-floating">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                <label for="confirm_password">Confirmar Contraseña</label>
            </div>
            <div class="col-12">
                <small class="form-text text-muted">
                    La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, una minúscula y un número.
                </small>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-3 d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
</form> 