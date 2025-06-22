<?php
$subtitulo = isset($subtitulo) ? $subtitulo : 'Monitorea en tiempo real los dispositivos y mascotas. Consulta reportes históricos y ubicaciones.';
?>
<p class="subtitle text-md">
  <?= htmlspecialchars($subtitulo) ?>
</p>

<div class="container-fluid">
  <!-- Pestañas de navegación -->
  <ul class="nav nav-pills mb-4" id="monitor-tabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="dispositivos-tab" data-bs-toggle="pill" data-bs-target="#dispositivos-panel" type="button" role="tab">
        <i class="fas fa-desktop"></i> Mis Dispositivos
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="reportes-tab" data-bs-toggle="pill" data-bs-target="#reportes-panel" type="button" role="tab">
        <i class="fas fa-chart-line"></i> Reportes IoT
      </button>
    </li>
  </ul>

  <!-- Contenido de las pestañas -->
  <div class="tab-content" id="monitor-tab-content">
    
    <!-- Panel de Dispositivos (funcionalidad original) -->
    <div class="tab-pane fade show active" id="dispositivos-panel" role="tabpanel">
      <div class="row">
        <?php if (empty($dispositivos)): ?>
          <div class="col-12">
            <div class="alert alert-info text-center">
              No tienes dispositivos registrados. Por favor, agrega uno para comenzar a monitorear.
            </div>
          </div>
        <?php endif; ?>
        <?php foreach ($dispositivos as $dispositivo): ?>
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <?= $dispositivo['nombre'] ?>
                <span class="badge bg-<?= $dispositivo['estado'] === 'activo' ? 'success' : 'danger' ?> float-end">
                  <?= ucfirst($dispositivo['estado']) ?>
                </span>
              </h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <p class="mb-2">
                    <strong>MAC:</strong> <?= $dispositivo['mac'] ?><br>
                    <strong>ID:</strong> <?= $dispositivo['id_dispositivo'] ?><br>
                    <strong>Mascota:</strong> <?= $dispositivo['nombre_mascota'] ?><br>
                    <strong>Especie:</strong> <?= $dispositivo['especie_mascota'] ?>
                  </p>
                </div>
                <div class="col-md-6">
                  <div class="d-grid">
                    <a href="<?= BASE_URL ?>monitor/device/<?= $dispositivo['id_dispositivo'] ?>" class="btn btn-primary">
                      <i class="fas fa-chart-line"></i> Ver Monitor
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Panel de Reportes IoT (funcionalidad integrada) -->
    <div class="tab-pane fade" id="reportes-panel" role="tabpanel">
      <!-- Panel de filtros -->
      <div class="card shadow-sm mb-3 rounded-4 border-0">
        <div class="card-body pb-2">
          <form id="buscador-avanzado" class="row g-2 align-items-end flex-wrap">
            <div class="col-12 col-md-3">
              <label for="propietario" class="form-label"><i class="fas fa-user text-primary"></i> Dueño</label>
              <select id="propietario" class="form-select" data-placeholder="🔍 Buscar dueño..."></select>
            </div>
            <div class="col-12 col-md-3">
              <label for="mascota" class="form-label"><i class="fas fa-paw text-success"></i> Mascota</label>
              <select id="mascota" class="form-select" data-placeholder="🐕 Selecciona mascota..." disabled></select>
            </div>
            <div class="col-12 col-md-2">
              <label for="mac" class="form-label"><i class="fas fa-microchip text-info"></i> Dispositivo</label>
              <select id="mac" class="form-select" data-placeholder="📡 Buscar MAC..."></select>
            </div>
            <div class="col-12 col-md-3">
              <label for="rango-fechas" class="form-label"><i class="fas fa-calendar-alt text-warning"></i> Período</label>
              <input type="text" id="rango-fechas" class="form-control" placeholder="📅 Selecciona rango de fechas" autocomplete="off">
            </div>
            <div class="col-6 col-md-1 d-grid">
              <button type="button" id="mostrar-todo" class="btn btn-outline-primary" title="Mostrar todos los datos según permisos">
                <i class="fas fa-list"></i> Mostrar todas
              </button>
            </div>
            <div class="col-6 col-md-12 d-flex justify-content-md-end justify-content-start mt-2 mt-md-0">
              <button id="exportar-excel" class="btn btn-success ms-md-auto"><i class="fas fa-file-excel"></i> Exportar CSV</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Mapa principal compacto -->
      <div id="map" style="height:200px; min-height:120px; border-radius:18px; box-shadow:0 4px 16px rgba(44,62,80,0.10); margin-bottom: 1.5rem;"></div>

      <!-- Tabla de registros -->
      <div class="card shadow-sm rounded-4 border-0">
        <div class="card-body p-0">
          <div class="table-responsive" style="max-height: calc(100vh - 320px); overflow-y: auto;">
            <table id="tabla-registros" class="table table-bordered table-striped table-hover align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th><i class="fas fa-clock"></i> Fecha y hora</th>
                  <th><i class="fas fa-paw"></i> Mascota</th>
                  <th><i class="fas fa-user"></i> Dueño</th>
                  <th><i class="fas fa-microchip"></i> Dispositivo</th>
                  <th><i class="fas fa-thermometer-half"></i> Temperatura</th>
                  <th><i class="fas fa-heartbeat"></i> Ritmo cardíaco</th>
                  <th><i class="fas fa-map-marker-alt"></i> Ubicación</th>
                  <th><i class="fas fa-battery-half"></i> Batería</th>
                </tr>
              </thead>
              <tbody>
                <!-- Registros AJAX -->
              </tbody>
            </table>
          </div>
          <div id="paginador" class="mt-2"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Dependencias para reportes -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css">

<!-- Estilos personalizados -->
<style>
.card {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: box-shadow 0.3s ease;
}
.card:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
.table-responsive {
  border-radius: 12px;
  overflow: hidden;
}
.table th {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
}
.select2-container {
  width: 100% !important;
}
.btn {
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
}
.btn:hover {
  transform: translateY(-1px);
}
#map {
  border: 3px solid #e2e8f0;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.table-striped tbody tr:nth-of-type(odd) {
  background-color: rgba(103, 126, 234, 0.05);
}
.nav-pills .nav-link {
  border-radius: 25px;
  padding: 12px 24px;
  font-weight: 600;
  margin-right: 8px;
}
.nav-pills .nav-link.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>

<script>
// Definir BASE_URL globalmente
window.BASE_URL = '<?= BASE_URL ?>';
window.MONITOR_URL = '<?= BASE_URL ?>monitor/';
</script>

<script src="<?= BASE_URL ?>assets/js/monitor.js"></script> 