<!-- Modal de Detalles -->
<div class="modal fade" id="modalDetallesDispositivo" tabindex="-1" aria-labelledby="modalDetallesDispositivoLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetallesDispositivoLabel">Detalles del Dispositivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Información General</h6>
                                <p class="mb-1"><strong>ID:</strong> <span id="detalleId"></span></p>
                                <p class="mb-1"><strong>Nombre:</strong> <span id="detalleNombre"></span></p>
                                <p class="mb-1"><strong>MAC:</strong> <span id="detalleMac"></span></p>
                                <p class="mb-1"><strong>Estado:</strong> <span id="detalleEstado"></span></p>
                                <p class="mb-1"><strong>Batería:</strong> <span id="detalleBateria"></span></p>
                                <p class="mb-1"><strong>Última Lectura:</strong> <span id="detalleUltimaLectura"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Asignación</h6>
                                <p class="mb-1"><strong>Usuario:</strong> <span id="detalleUsuario"></span></p>
                                <p class="mb-1"><strong>Mascota:</strong> <span id="detalleMascota"></span></p>
                                <p class="mb-1"><strong>Fecha de Asignación:</strong> <span id="detalleFechaAsignacion"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edición -->
<div class="modal fade" id="modalEditarDispositivo" tabindex="-1" aria-labelledby="modalEditarDispositivoLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarDispositivoLabel">Editar Dispositivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarDispositivo">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="mb-3">
                        <label for="edit_nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_mac" class="form-label">MAC</label>
                        <input type="text" class="form-control" id="edit_mac" name="mac" pattern="^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$" required>
                        <div id="edit_macError" class="text-danger" style="display:none;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_estado" class="form-label">Estado</label>
                        <select class="form-select" id="edit_estado" name="estado" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Asignar/Reasignar -->
<div class="modal fade" id="modalAsignarDispositivo" tabindex="-1" aria-labelledby="modalAsignarDispositivoLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAsignarDispositivoLabel">Asignar/Reasignar Dispositivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAsignarDispositivo">
                    <input type="hidden" id="asignar_dispositivo_id" name="dispositivo_id">
                    <?php if (verificarPermiso('ver_todos_dispositivo')): ?>
                    <div class="mb-3">
                        <label for="usuario_id_asignar" class="form-label">Usuario</label>
                        <select class="form-select" id="usuario_id_asignar" name="usuario_id" required>
                            <option value="">Seleccione un usuario...</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="mascota_id_asignar" class="form-label">Mascota</label>
                        <select class="form-select" id="mascota_id_asignar" name="mascota_id" required>
                            <option value="">Seleccione una mascota...</option>
                        </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Asignar Dispositivo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 