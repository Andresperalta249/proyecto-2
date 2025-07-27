<?php
$rol = $data['rol'] ?? null;
$permisos = $data['permisos'] ?? [];
$esEdicion = !empty($rol);

// Agrupar permisos por tipo de gestión
$grupos = [
    'Gestión de Mascotas' => [],
    'Gestión de Roles' => [],
    'Gestión de Dispositivos' => [],
    'Gestión de Usuarios' => [],
    'Gestión de Configuración' => [],
    'Gestión de Reportes' => [],
    'Gestión de Alertas' => [],
    'Dashboard' => [],
    'Otros' => []
];
foreach ($permisos as $permiso) {
    $nombre = $permiso['nombre'];
    if (strpos($nombre, 'mascota') !== false) {
        $grupos['Gestión de Mascotas'][] = $permiso;
    } elseif (strpos($nombre, 'rol') !== false) {
        $grupos['Gestión de Roles'][] = $permiso;
    } elseif (strpos($nombre, 'dispositivo') !== false) {
        $grupos['Gestión de Dispositivos'][] = $permiso;
    } elseif (strpos($nombre, 'usuario') !== false) {
        $grupos['Gestión de Usuarios'][] = $permiso;
    } elseif (strpos($nombre, 'configuracion') !== false) {
        $grupos['Gestión de Configuración'][] = $permiso;
    } elseif (strpos($nombre, 'reporte') !== false) {
        $grupos['Gestión de Reportes'][] = $permiso;
    } elseif (strpos($nombre, 'alerta') !== false) {
        $grupos['Gestión de Alertas'][] = $permiso;
    } elseif (strpos($nombre, 'dashboard') !== false) {
        $grupos['Dashboard'][] = $permiso;
    } else {
        $grupos['Otros'][] = $permiso;
    }
}
?>

<div class="modal-header border-0 pb-0">
    <h4 class="modal-title fw-bold text-primary title-h4"><i class="fas fa-user-tag text-primary me-2"></i><?= $esEdicion ? 'Editar Rol' : 'Nuevo Rol' ?></h4>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body pt-2 pb-0">
    <form id="rolForm" class="needs-validation" novalidate autocomplete="off">
        <input type="hidden" name="id_rol" value="<?= $rol['id_rol'] ?? '' ?>">
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($rol['nombre'] ?? '') ?>" required autocomplete="off" placeholder="Ingrese el nombre del rol">
                    <label for="nombre"><i class="fas fa-id-badge me-1 text-primary"></i> Nombre del Rol</label>
                    <div class="invalid-feedback" id="nombreFeedback">Por favor ingrese un nombre único para el rol.</div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control" id="descripcion" name="descripcion" value="<?= htmlspecialchars($rol['descripcion'] ?? '') ?>" autocomplete="off" placeholder="Ingrese una descripción">
                    <label for="descripcion"><i class="fas fa-align-left me-1 text-secondary"></i> Descripción</label>
                </div>
            </div>
            <?php if ($esEdicion && $rol['id_rol'] > 3): ?>
            <div class="col-12 col-md-6">
                <label class="form-label mb-1"><i class="fas fa-toggle-on me-1 text-success"></i> Estado</label>
                <div class="d-flex gap-3 align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="estado" id="estado_activo" value="activo" <?= ($rol['estado'] ?? '') == 'activo' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="estado_activo">Activo</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="estado" id="estado_inactivo" value="inactivo" <?= ($rol['estado'] ?? '') == 'inactivo' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="estado_inactivo">Inactivo</label>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-bold mb-0"><i class="fas fa-key text-primary me-1"></i> Permisos</label>
            </div>
            <div class="accordion" id="acordeonPermisos">
                <?php $i = 0; foreach ($grupos as $titulo => $permisosGrupo): ?>
                    <?php if (count($permisosGrupo) > 0): ?>
                        <div class="accordion-item mb-1">
                            <h2 class="accordion-header" id="heading<?= $i ?>">
                                <button class="accordion-button collapsed fw-semibold text-primary text-md" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>" aria-expanded="false" aria-controls="collapse<?= $i ?>">
                                    <i class="fas fa-folder-open me-1"></i> <?= $titulo ?>
                                </button>
                            </h2>
                            <div id="collapse<?= $i ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $i ?>" data-bs-parent="#acordeonPermisos">
                                <div class="accordion-body py-2 px-2">
                                    <div class="row g-2">
                                        <?php foreach ($permisosGrupo as $permiso): ?>
                                        <div class="col-12 col-md-6">
                                            <div class="form-check permiso-item mb-1 small">
                                                <input class="form-check-input" type="checkbox" name="permisos[]" value="<?= $permiso['id_permiso'] ?>" id="permiso_<?= $permiso['id_permiso'] ?>" <?= in_array($permiso['id_permiso'], $rol['permiso_ids'] ?? []) ? 'checked' : '' ?>>
                                                <label class="form-check-label d-flex align-items-center gap-1" for="permiso_<?= $permiso['id_permiso'] ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($permiso['descripcion']) ?>">
                                                    <i class="fas fa-circle text-primary" style="font-size:0.7em;"></i>
                                                    <span class="text-sm"><?= htmlspecialchars($permiso['nombre']) ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; $i++; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </form>
</div>

<div class="modal-footer border-0 bg-white sticky-bottom d-flex justify-content-end gap-2 py-3" style="z-index:1056;">
    <button type="button" class="btn btn-light border" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancelar</button>
    <button type="button" class="btn btn-success" id="btnGuardarRol" onclick="guardarRol()"><i class="fas fa-save me-1"></i> Guardar</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Validación nombre único en tiempo real
    const form = document.getElementById('rolForm');
    const nombreInput = document.getElementById('nombre');
    const nombreFeedback = document.getElementById('nombreFeedback');
    let nombreValido = true;

    nombreInput.addEventListener('input', function() {
        const nombre = nombreInput.value.trim();
        if (!nombre) return;
        fetch('roles/validarNombre?nombre=' + encodeURIComponent(nombre) + '&id_rol=' + (form.id_rol?.value || ''))
            .then(r => r.json())
            .then(data => {
                if (data.exists) {
                    nombreInput.classList.add('is-invalid');
                    nombreFeedback.textContent = 'Ya existe un rol con ese nombre.';
                    nombreValido = false;
                } else {
                    nombreInput.classList.remove('is-invalid');
                    nombreFeedback.textContent = 'Por favor ingrese un nombre único para el rol.';
                    nombreValido = true;
                }
                actualizarEstadoBotonGuardar();
            });
    });

    // Gestión de permisos
    const checkboxes = document.querySelectorAll('.permiso-item input[type="checkbox"]');
    const btnGuardar = document.getElementById('btnGuardarRol');

    function actualizarEstadoBotonGuardar() {
        const totalPermisos = parseInt(contadorTotal.textContent);
        btnGuardar.disabled = totalPermisos === 0 || !nombreValido;
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', actualizarContadores);
    });
    actualizarContadores();

    // Validación antes de guardar
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity() || !nombreValido || parseInt(contadorTotal.textContent) === 0) {
            event.preventDefault();
            event.stopPropagation();
            if (parseInt(contadorTotal.textContent) === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Permisos requeridos',
                    text: 'Selecciona al menos un permiso.'
                });
            }
        }
        form.classList.add('was-validated');
    });
});

function guardarRol() {
    const form = document.getElementById('rolForm');
    if (!form.checkValidity() || document.getElementById('contadorTotal').textContent === '0') {
        form.classList.add('was-validated');
        Swal.fire({
            icon: 'warning',
            title: 'Permisos requeridos',
            text: 'Selecciona al menos un permiso.'
        });
        return;
    }
    const formData = new FormData(form);
    const idRol = formData.get('id_rol');
    const nombre = formData.get('nombre');
    const descripcion = formData.get('descripcion');
    const esEdicion = idRol && idRol !== '0' && idRol !== '';
    const url = esEdicion ? 'roles/update' : 'roles/create';
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: esEdicion ? `El rol "${nombre}" se actualizó con éxito` : `El rol "${nombre}" (${descripcion}) se creó con éxito`,
                showConfirmButton: false,
                timer: 1800
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error || data.message || 'Ocurrió un error.'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ha ocurrido un error al procesar la solicitud'
        });
    });
}
</script>

<style>
.modal-header .modal-title { font-size: 1.45rem; }
.accordion-button { background: #f8fafc; font-size: 1.08rem; padding: 0.7rem 1rem; }
.accordion-button:not(.collapsed) { background: #eaf1fb; color: #2563eb; }
.accordion-item { border: none; border-radius: 0.5rem !important; overflow: hidden; margin-bottom: 0.2rem; }
.accordion-body { background: #fafdff; padding: 0.7rem 0.5rem; }
#listaPermisos .form-check-label, .permiso-item .form-check-label { cursor: pointer; transition: all 0.2s; font-size: 0.97em; }
#listaPermisos .form-check-label:hover, .permiso-item .form-check-label:hover { color: #2563eb; }
.permiso-item { font-size: 0.97em; }
.sticky-bottom { position: sticky; bottom: 0; background: #fff; z-index: 1056; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); }
.btn-success { background: #22c55e; border-color: #22c55e; padding: 0.5rem 1.2rem; font-size: 1em; }
.btn-success:hover { background: #16a34a; border-color: #16a34a; }
.btn-light { background: #f3f4f6; color: #222; border-color: #e5e7eb; padding: 0.5rem 1.2rem; font-size: 1em; }
.btn-light:hover { background: #e5e7eb; color: #111; }
.tooltip { font-size: 0.9em; }
@media (max-width: 600px) {
    .modal-header .modal-title { font-size: 1.1rem; }
    .modal-body { padding: 0.7rem 0.2rem 0.2rem 0.2rem !important; }
    .accordion-body { padding: 0.7rem 0.2rem !important; }
    .modal-footer { flex-direction: column; gap: 0.7rem; }
    .btn { width: 100%; font-size: 1em; }
    .permiso-item { font-size: 0.97em; }
}
</style> 