<?php 
$titulo = isset($titulo) ? $titulo : 'Monitor IoT';
$subtitulo = isset($subtitulo) ? $subtitulo : 'Monitorea en tiempo real los dispositivos y mascotas asociados.';
?>
<h1 class="titulo-pagina"><?= htmlspecialchars($titulo) ?></h1>
<p class="subtitulo-pagina">
  <?= htmlspecialchars($subtitulo) ?>
</p>

<div class="contenedor-sistema">
    <div class="contenedor-sistema-header">
        <i class="fas fa-chart-line"></i>
        Dispositivos Disponibles
    </div>
    <div class="contenedor-sistema-body">
        <?php if (empty($dispositivos)): ?>
            <div class="alert alert-info text-center">
                No tienes dispositivos registrados. Por favor, agrega uno para comenzar a monitorear.
            </div>
        <?php endif; ?>
        
        <div class="dispositivos-grid">
            <?php foreach ($dispositivos as $dispositivo): ?>
            <div class="dispositivo-card">
                <div class="dispositivo-header">
                    <h5 class="dispositivo-title">
                        <?= $dispositivo['nombre'] ?>
                        <span class="badge bg-<?= $dispositivo['estado'] === 'activo' ? 'success' : 'danger' ?> float-end">
                            <?= ucfirst($dispositivo['estado']) ?>
                        </span>
                    </h5>
                </div>
                <div class="dispositivo-body">
                    <div class="dispositivo-info">
                        <p class="mb-2">
                            <strong>MAC:</strong> <?= $dispositivo['mac'] ?><br>
                            <strong>ID:</strong> <?= $dispositivo['id_dispositivo'] ?><br>
                            <strong>Mascota:</strong> <?= $dispositivo['nombre_mascota'] ?><br>
                            <strong>Especie:</strong> <?= $dispositivo['especie_mascota'] ?>
                        </p>
                    </div>
                    <div class="dispositivo-actions">
                        <a href="<?= BASE_URL ?>monitor/device/<?= $dispositivo['id_dispositivo'] ?>" class="btn btn-primary">
                            <i class="fas fa-chart-line"></i> Ver Monitor
                        </a>
                        <?php if (verificarPermiso('gestionar_alertas')): ?>
                            <button class="btn btn-warning btn-sm ms-2" onclick="abrirConfigAlerta(<?= $dispositivo['mascota_id'] ?>, <?= $dispositivo['id_dispositivo'] ?>)">
                                <i class="bi bi-bell"></i> Configurar alerta
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php if (verificarPermiso('gestionar_alertas')): ?>
<!-- Modal Configuración de Alerta Específica -->
<div class="modal fade" id="modalConfigAlertaEspecifica" tabindex="-1" aria-labelledby="modalConfigAlertaEspecificaLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalConfigAlertaEspecificaLabel">Configurar Alerta Específica</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formConfigAlertaEspecifica">
        <div class="modal-body">
          <input type="hidden" name="mascota_id" id="inputMascotaId">
          <input type="hidden" name="dispositivo_id" id="inputDispositivoId">
          <!-- Temperatura -->
          <div class="card mb-3">
            <div class="card-header bg-danger text-white">
              <h6 class="mb-0">Temperatura</h6>
            </div>
            <div class="card-body row">
              <div class="col-md-6 mb-2">
                <label class="form-label">Valor Mínimo (°C)</label>
                <input type="number" class="form-control" name="temperatura[min]" step="0.1" required>
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label">Valor Máximo (°C)</label>
                <input type="number" class="form-control" name="temperatura[max]" step="0.1" required>
              </div>
            </div>
          </div>
          <!-- Ritmo Cardíaco -->
          <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
              <h6 class="mb-0">Ritmo Cardíaco</h6>
            </div>
            <div class="card-body row">
              <div class="col-md-6 mb-2">
                <label class="form-label">Valor Mínimo (bpm)</label>
                <input type="number" class="form-control" name="ritmo_cardiaco[min]" required>
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label">Valor Máximo (bpm)</label>
                <input type="number" class="form-control" name="ritmo_cardiaco[max]" required>
              </div>
            </div>
          </div>
          <!-- Batería -->
          <div class="card mb-3">
            <div class="card-header bg-info text-white">
              <h6 class="mb-0">Batería</h6>
            </div>
            <div class="card-body row">
              <div class="col-md-6 mb-2">
                <label class="form-label">Valor Mínimo (%)</label>
                <input type="number" class="form-control" name="bateria[min]" min="0" max="100" required>
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label">Valor Máximo (%)</label>
                <input type="number" class="form-control" name="bateria[max]" min="0" max="100" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar Configuración</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function abrirConfigAlerta(mascotaId, dispositivoId = null) {
  document.getElementById('inputMascotaId').value = mascotaId;
  if (dispositivoId) document.getElementById('inputDispositivoId').value = dispositivoId;
  // Limpiar campos
  const campos = [
    ['temperatura[min]', ''], ['temperatura[max]', ''],
    ['ritmo_cardiaco[min]', ''], ['ritmo_cardiaco[max]', ''],
    ['bateria[min]', ''], ['bateria[max]', '']
  ];
  campos.forEach(([name, def]) => {
    const el = document.querySelector(`[name='${name}']`);
    if (el) el.value = def;
  });
  // Cargar configuración actual si hay dispositivoId
  if (dispositivoId) {
    fetch(`/proyecto-2/configuracion-alerta/obtener-especifica?dispositivo_id=${dispositivoId}`)
      .then(res => res.json())
      .then(data => {
        if (data.success && data.configuraciones) {
          for (const tipo in data.configuraciones) {
            const conf = data.configuraciones[tipo];
            if (conf.min !== undefined) document.querySelector(`[name='${tipo}[min]']`).value = conf.min;
            if (conf.max !== undefined) document.querySelector(`[name='${tipo}[max]']`).value = conf.max;
          }
        }
        var modal = new bootstrap.Modal(document.getElementById('modalConfigAlertaEspecifica'));
        modal.show();
      });
  } else {
    var modal = new bootstrap.Modal(document.getElementById('modalConfigAlertaEspecifica'));
    modal.show();
  }
}

document.getElementById('formConfigAlertaEspecifica').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = this;
  const formData = new FormData(form);
  // Validación simple de rangos
  const tempMin = parseFloat(form['temperatura[min]'].value);
  const tempMax = parseFloat(form['temperatura[max]'].value);
  const ritmoMin = parseFloat(form['ritmo_cardiaco[min]'].value);
  const ritmoMax = parseFloat(form['ritmo_cardiaco[max]'].value);
  const batMin = parseFloat(form['bateria[min]'].value);
  const batMax = parseFloat(form['bateria[max]'].value);
  if (tempMin >= tempMax) {
    alert('El valor mínimo de temperatura debe ser menor que el máximo.');
    return;
  }
  if (ritmoMin >= ritmoMax) {
    alert('El valor mínimo de ritmo cardíaco debe ser menor que el máximo.');
    return;
  }
  if (batMin >= batMax) {
    alert('El valor mínimo de batería debe ser menor que el máximo.');
    return;
  }
  fetch('/proyecto-2/configuracion-alerta/guardar-especifica', {
    method: 'POST',
    body: formData,
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Configuración guardada correctamente');
      const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfigAlertaEspecifica'));
      if (modal) modal.hide();
    } else {
      alert(data.error || 'Error al guardar la configuración');
    }
  })
  .catch(() => {
    alert('Error al guardar la configuración');
  });
});
</script>
<?php endif; ?> 