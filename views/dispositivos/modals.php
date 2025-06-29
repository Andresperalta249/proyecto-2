<?php
/**
 * Vista: dispositivos/modals.php
 * =============================
 * 
 * ARCHIVO: views/dispositivos/modals.php
 * VERSIÓN: 1.0
 * ÚLTIMA ACTUALIZACIÓN: 2024
 * 
 * DESCRIPCIÓN:
 *   Contiene los modales HTML para la gestión de dispositivos IoT.
 *   Proporciona interfaces de usuario para crear, editar, eliminar y asignar dispositivos.
 * 
 * COMPONENTES INCLUIDOS:
 *   - Modal de Detalles: Muestra información completa del dispositivo
 *   - Modal de Asignación: Permite asignar/reasignar dispositivos a mascotas
 * 
 * FLUJO DE INTERACCIÓN:
 *   1. Usuario hace clic en botón "Ver detalles" → Se abre modalDetallesDispositivo
 *   2. Usuario hace clic en "Asignar" → Se abre modalAsignarDispositivo
 *   3. JavaScript (dispositivos.js) maneja la lógica de interacción
 *   4. Formularios envían datos a DispositivosController::asignarDispositivo()
 *   5. Controlador usa DispositivoModel::actualizarAsignacion() para persistir
 * 
 * DEPENDENCIAS:
 *   - JavaScript: assets/js/dispositivos.js (manejo de eventos)
 *   - Controlador: DispositivosController (procesamiento de formularios)
 *   - Modelo: DispositivoModel (persistencia de datos)
 *   - CSS: assets/css/modals.css (estilos de modales)
 * 
 * VARIABLES RECIBIDAS:
 *   - $dispositivo: array - Datos del dispositivo (si se está editando)
 *   - $mascotas: array - Lista de mascotas disponibles para asignar
 *   - $usuarios: array - Lista de usuarios (si aplica permisos)
 *   - $permisos: array - Permisos del usuario actual
 * 
 * MÉTODOS INVOCADOS:
 *   - verificarPermiso(): Verifica permisos del usuario
 *   - DispositivosController::asignarDispositivo(): Procesa asignación
 *   - DispositivoModel::obtenerPorId(): Obtiene datos del dispositivo
 *   - DispositivoModel::actualizarAsignacion(): Actualiza asignación
 * 
 * EJEMPLO DE USO:
 *   <?php include 'views/dispositivos/modals.php'; ?>
 * 
 * NOTAS TÉCNICAS:
 *   - Los modales usan Bootstrap 5 para el diseño
 *   - Los IDs de elementos deben coincidir con los referenciados en JavaScript
 *   - Los formularios incluyen validación tanto del lado cliente como servidor
 * 
 * AUTOR: Sistema PetMonitoring IoT
 * LICENCIA: Propietaria
 */
?>
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