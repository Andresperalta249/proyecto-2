<?php
$titulo = "Monitor IoT";
$subtitulo = "Monitor IoT - Consulta datos históricos, ubicaciones y filtra por dispositivos y mascotas.";
?>

<div class="container-fluid pt-3">
    <!-- Card de filtros -->
    <div class="card mb-3">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Filtros de Búsqueda</h3>
                <button type="button" id="mostrar-todo" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-list"></i> Mostrar Todas
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="buscador-avanzado" class="row g-3">
                <div class="col-md-3">
                    <label for="propietario" class="form-label">Dueño</label>
                    <select id="propietario" class="form-select" data-placeholder="Buscar dueño..."></select>
                </div>
                <div class="col-md-3">
                    <label for="mascota" class="form-label">Mascota</label>
                    <select id="mascota" class="form-select" data-placeholder="Selecciona mascota..." disabled></select>
                </div>
                <div class="col-md-3">
                    <label for="mac" class="form-label">Dispositivo</label>
                    <select id="mac" class="form-select" data-placeholder="Buscar MAC..."></select>
                </div>
                <div class="col-md-3">
                    <label for="rango-fechas" class="form-label">Período</label>
                    <input type="text" id="rango-fechas" class="form-control" placeholder="Selecciona rango de fechas" autocomplete="off">
                </div>
            </form>
        </div>
    </div>

    <!-- Card del mapa -->
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Ubicaciones</h3>
        </div>
        <div class="card-body">
            <div id="map" style="height:250px; width:100%;"></div>
        </div>
    </div>

    <!-- Card principal de la tabla -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Datos de Sensores IoT</h3>
                <button id="exportar-excel" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Exportar CSV
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-registros" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Mascota</th>
                            <th>Dueño</th>
                            <th>Dispositivo</th>
                            <th>Temperatura</th>
                            <th>Ritmo Cardíaco</th>
                            <th>Ubicación</th>
                            <th>Batería</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargan vía AJAX -->
                    </tbody>
                </table>
            </div>
            
            <!-- Información y paginación -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span id="total-registros" class="text-muted small">0 registros</span>
                <div id="paginador" class="d-flex"></div>
            </div>
        </div>
    </div>
</div>

<!-- Configuración para JS -->
<div id="monitor-config" 
     data-app-url="<?= rtrim(BASE_URL, '/'); ?>"
     data-puede-ver-todas="<?= $puedeVerTodasMascotas ? 'true' : 'false'; ?>"
     data-puede-ver-mascotas="<?= $puedeVerMascotas ? 'true' : 'false'; ?>"
     data-user-id="<?= $usuarioActual; ?>">
</div>

<!-- Dependencias CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css">

<!-- Dependencias JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
// Definir URLs globalmente
window.BASE_URL = '<?= BASE_URL ?>';
window.MONITOR_URL = '<?= BASE_URL ?>monitor/';
</script>

<!-- Script principal -->
<script src="<?= BASE_URL ?>assets/js/monitor.js"></script> 