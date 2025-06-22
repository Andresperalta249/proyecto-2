<?php
// Vista: Reporte moderno de monitoreo IoT de mascotas
?>
<div class="container-fluid py-4 bg-light min-vh-100">
  <!-- Panel de filtros -->
  <div class="card shadow-sm mb-3 rounded-4 border-0">
    <div class="card-body pb-2">
      <form id="buscador-avanzado" class="row g-2 align-items-end flex-wrap">
        <div class="col-12 col-md-3">
          <label for="propietario" class="form-label">Dueño</label>
          <select id="propietario" class="form-select" data-placeholder="Buscar dueño..."></select>
        </div>
        <div class="col-12 col-md-3">
          <label for="mascota" class="form-label">Mascota</label>
          <select id="mascota" class="form-select" data-placeholder="Selecciona mascota..." disabled></select>
        </div>
        <div class="col-12 col-md-2">
          <label for="mac" class="form-label">MAC</label>
          <select id="mac" class="form-select" data-placeholder="Buscar MAC..."></select>
        </div>
        <div class="col-12 col-md-3">
          <label for="rango-fechas" class="form-label">Rango de fechas</label>
          <input type="text" id="rango-fechas" class="form-control" placeholder="Selecciona rango" autocomplete="off">
        </div>
        <div class="col-6 col-md-1 d-grid">
          <button type="button" id="mostrar-todo" class="btn btn-outline-primary">Mostrar todas</button>
        </div>
        <div class="col-6 col-md-12 d-flex justify-content-md-end justify-content-start mt-2 mt-md-0">
          <button id="exportar-excel" class="btn btn-success ms-md-auto"><i class="fa fa-file-excel"></i> Exportar a Excel</button>
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
          <thead class="table-light sticky-top">
            <tr>
              <th>Fecha y hora</th>
              <th>Mascota</th>
              <th>Dueño</th>
              <th>MAC</th>
              <th>Temperatura</th>
              <th>Ritmo cardíaco</th>
              <th>Ubicación</th>
              <th>Batería</th>
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

<script src="<?= APP_URL ?>/assets/js/reportes.js"></script>
<script>
// Aquí irá la lógica JS para cargar propietarios, mascotas, MACs, mapa y tabla
// Se recomienda usar AJAX y reutilizar DataTables/Leaflet si ya están en el sistema
</script> 