<?php
/**
 * Vista: monitor/device.php
 * -------------------------
 * Muestra los datos en tiempo real de un dispositivo y su historial.
 *
 * Variables recibidas:
 *   - $dispositivo: Información del dispositivo.
 *   - $datos: Datos recientes del sensor.
 *   - $ubicacion: Última ubicación conocida.
 *
 * Uso:
 *   Esta vista es llamada desde MonitorController para mostrar el detalle de un dispositivo.
 */
// Obtener el tipo de mascota para el icono
$tipoMascota = isset($dispositivo['tipo_mascota']) ? $dispositivo['tipo_mascota'] : 'perro';
$iconoMascota = $tipoMascota === 'gato' ? '🐱' : '🐕';
$dispositivoId = isset($dispositivo['id']) ? $dispositivo['id'] : 0;
$configuraciones = isset($configuraciones) ? $configuraciones : [];
?>

<div class="device-header">
    <h1>Monitor de Dispositivo</h1>
    <div class="device-info">
        <strong><?= htmlspecialchars($dispositivo['nombre']) ?></strong><br>
        <?php if (isset($dispositivo['nombre_mascota'])): ?>
            <span>Mascota: <?= htmlspecialchars($dispositivo['nombre_mascota']) ?></span> |
        <?php endif; ?>
        <span>Estado: <span class="status-badge <?= $dispositivo['estado'] ?>"><?= ucfirst($dispositivo['estado']) ?></span></span> |
        <span>Batería: <?= $dispositivo['bateria'] ?>%</span>
    </div>
</div>

<div class="device-monitor">
    <!-- Mensaje de error -->
    <div id="error-message" class="alert alert-danger d-none"></div>

    <!-- Mapa Interactivo -->
    <div class="map-container">
        <div id="mapaDispositivo" class="device-map"></div>
        <div class="map-fab-controls">
            <button class="btn-fab" id="btnCentrarDispositivo" title="Centrar en dispositivo">
                <i class="fas fa-location-arrow"></i>
            </button>
            <div class="fab-dropdown" id="fabRangoTiempo">
                <button class="btn-fab" id="btnRangoTiempo" title="Rango de tiempo">
                    <i class="fas fa-clock"></i>
                </button>
                <div class="fab-dropdown-menu">
                    <button class="btn-fab-option" data-range="2">2h</button>
                    <button class="btn-fab-option" data-range="6">6h</button>
                    <button class="btn-fab-option" data-range="12">12h</button>
                    <button class="btn-fab-option" data-range="24">24h</button>
                </div>
            </div>
            <button class="btn-fab" id="btnMapaFull" title="Pantalla completa">
                <i class="fas fa-expand"></i>
            </button>
        </div>
    </div>

    <!-- Tarjetas de Estado -->
    <div class="status-cards">
        <div class="card" id="cardTemperatura">
            <div class="card-body">
                <div class="status-icon">
                    <i class="fas fa-thermometer-half"></i>
                </div>
                <div class="status-info">
                    <h3 class="status-value">--°C</h3>
                    <p class="status-label">Temperatura</p>
                </div>
            </div>
        </div>

        <div class="card" id="cardRitmoCardiaco">
            <div class="card-body">
                <div class="status-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="status-info">
                    <h3 class="status-value">-- BPM</h3>
                    <p class="status-label">Ritmo Cardíaco</p>
                </div>
            </div>
        </div>

        <div class="card" id="cardBateria">
            <div class="card-body">
                <div class="status-icon">
                    <i class="fas fa-battery-three-quarters"></i>
                </div>
                <div class="status-info">
                    <h3 class="status-value">--%</h3>
                    <p class="status-label">Batería</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas -->
    <div class="charts-container">
        <div class="chart-card">
            <div class="chart-header">
                <h3>Temperatura</h3>
                <button class="btn btn-sm btn-outline-primary" data-chart="temperatura">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
            <canvas id="graficaTemperatura"></canvas>
            <div id="mensajeGraficaTemperatura" class="mensaje-grafica"></div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Ritmo Cardíaco</h3>
                <button class="btn btn-sm btn-outline-primary" data-chart="ritmoCardiaco">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
            <canvas id="graficaRitmoCardiaco"></canvas>
            <div id="mensajeGraficaRitmoCardiaco" class="mensaje-grafica"></div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Batería</h3>
                <button class="btn btn-sm btn-outline-primary" data-chart="bateria">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
            <canvas id="graficaBateria"></canvas>
            <div id="mensajeGraficaBateria" class="mensaje-grafica"></div>
        </div>
    </div>

    <!-- Tabla de Registros -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover" id="tablaRegistros">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Temperatura</th>
                        <th>Ritmo Cardíaco</th>
                        <th>Batería</th>
                        <th>Ubicación</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se cargarán dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para gráficas expandidas -->
<div class="modal fade" id="modalGrafica" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gráfica Detallada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <canvas id="graficaExpandida"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script type="module">
    // Configuración específica del monitor
    window.MONITOR_BASE_URL = '<?= BASE_URL ?>';
    window.dispositivoId = '<?= $dispositivo['id_dispositivo'] ?>';
    window.iconoMascota = '<?= $dispositivo['icono_mascota'] ?? '🐾' ?>';
    window.tipoMascota = '<?= strtolower($tipoMascota) ?>';
</script>

<!-- Dependencias principales -->
<script src="https://cdn.jsdelivr.net/npm/date-fns@2.30.0/index.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<!-- Estilos -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/device-monitor.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css">

<!-- Configuración global de Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.color = '#6c757d';
        Chart.defaults.scale.grid.color = 'rgba(0, 0, 0, 0.05)';
        Chart.defaults.scale.ticks.color = '#6c757d';
        
        // Asegurarse de que el adaptador de fechas se registre correctamente
        if (typeof ChartjsAdapterDateFns !== 'undefined') {
            Chart.register(ChartjsAdapterDateFns);
            console.log('Adaptador de fechas de Chart.js registrado.');
        } else {
            console.error('ChartjsAdapterDateFns no está definido. Asegúrate de que el script se cargue correctamente.');
        }
    }

    if (!window.dispositivoId) {
        console.error('ID de dispositivo no definido');
        document.getElementById('errorContainer').innerHTML = `
            <div class="alert alert-danger">
                Error: ID de dispositivo no definido
            </div>
        `;
    }
});
</script>

<!-- Scripts locales -->
<script src="<?= BASE_URL ?>assets/js/date-utils.js"></script>
<script src="<?= BASE_URL ?>assets/js/device-monitor.js"></script>