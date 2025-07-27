<?php
$esEditar = isset($usuario);
$titulo = $esEditar ? 'Editar Usuario' : 'Nuevo Usuario';
?>

<div class="modal-header border-0">
    <h5 class="modal-title"><?= $titulo ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>

<div class="modal-body">
    <form id="formUsuario" data-accion="<?= $esEditar ? 'editar' : 'crear' ?>" class="needs-validation" novalidate>
        <?php if ($esEditar): ?>
            <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
        <?php endif; ?>

        <div class="row g-3">
            <!-- Nombre -->
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= $esEditar ? htmlspecialchars($usuario['nombre']) : '' ?>" 
                           required autocomplete="name">
                    <label for="nombre">Nombre</label>
                </div>
            </div>

            <!-- Email -->
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= $esEditar ? htmlspecialchars($usuario['email']) : '' ?>" 
                           <?= $esEditar ? 'readonly' : 'required' ?> autocomplete="email">
                    <label for="email">Email</label>
                </div>
            </div>

            <!-- Teléfono -->
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                           value="<?= $esEditar ? htmlspecialchars($usuario['telefono']) : '' ?>" autocomplete="tel">
                    <label for="telefono">Teléfono</label>
                </div>
            </div>

            <!-- Dirección -->
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control" id="direccion" name="direccion" 
                           value="<?= $esEditar ? htmlspecialchars($usuario['direccion']) : '' ?>" autocomplete="street-address">
                    <label for="direccion">Dirección</label>
                </div>
            </div>

            <!-- Rol -->
            <div class="col-md-6">
                <div class="form-floating">
                    <select class="form-select" id="rol_id" name="rol_id" required>
                        <option value="">Seleccione un rol</option>
                        <?php 
                        // Obtener el rol actual del usuario en sesión
                        $rolUsuarioActual = $_SESSION['user_role'] ?? 0;
                        $esSuperAdmin = $rolUsuarioActual == 1; // 1 = superadmin
                        
                        // Debug temporal
                        echo "<!-- Debug: Rol usuario actual: $rolUsuarioActual, EsSuperAdmin: " . ($esSuperAdmin ? 'true' : 'false') . " -->";
                        echo "<!-- Debug: Total roles: " . count($roles) . " -->";
                        
                        // Como super admin, mostrar todos los roles sin restricciones
                        foreach ($roles as $rol): 
                            echo "<!-- Debug: Rol {$rol['id_rol']} ({$rol['nombre']}) -->";
                        ?>
                            <option value="<?= $rol['id_rol'] ?>" 
                                <?= $esEditar && $usuario['rol_id'] == $rol['id_rol'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rol['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="rol_id">Rol</label>
                </div>
            </div>

            <!-- Estado -->
            <div class="col-md-6">
                <div class="form-floating">
                    <select class="form-select" id="estado" name="estado">
                        <option value="activo" <?= $esEditar && $usuario['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= $esEditar && $usuario['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                    <label for="estado">Estado</label>
                </div>
            </div>

            <!-- Contraseña -->
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" 
                           <?= $esEditar ? '' : 'required' ?> autocomplete="new-password">
                    <label for="password">
                        Contraseña<?= $esEditar ? ' (dejar vacío para no cambiar)' : '' ?>
                    </label>
                </div>
            </div>

            <!-- Confirmar Contraseña -->
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           <?= $esEditar ? '' : 'required' ?> autocomplete="new-password">
                    <label for="confirm_password">Confirmar Contraseña</label>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal-footer border-0">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="submit" form="formUsuario" class="btn btn-primary">Guardar</button>
</div> 