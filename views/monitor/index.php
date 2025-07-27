<?php 
$titulo = isset($titulo) ? $titulo : 'Monitor IoT Avanzado';
$subtitulo = isset($subtitulo) ? $subtitulo : 'Monitorea en tiempo real todos los dispositivos y mascotas con filtros avanzados.';
?>

<div class="contenedor-sistema">
    <!-- Header con título -->
    <div class="contenedor-sistema-header">
        <div class="header-content">
            <div class="header-title">
                <i class="fas fa-chart-line"></i>
                <?= htmlspecialchars($titulo) ?>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline-primary btn-sm" onclick="actualizarDatos()">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="limpiarFiltros()">
                    <i class="fas fa-filter"></i> Limpiar Filtros
                </button>
            </div>
        </div>
        <p class="header-subtitle"><?= htmlspecialchars($subtitulo) ?></p>
    </div>

    <!-- Filtros Avanzados -->
    <div class="filtros-avanzados">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="filtroPropietario" class="form-label">Propietario</label>
                <select class="form-select" id="filtroPropietario" onchange="cargarMascotas()">
                    <option value="">Todos los propietarios</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filtroMascota" class="form-label">Mascota</label>
                <select class="form-select" id="filtroMascota" onchange="aplicarFiltros()">
                    <option value="">Todas las mascotas</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filtroMAC" class="form-label">Dirección MAC</label>
                <input type="text" class="form-control" id="filtroMAC" 
                       placeholder="Buscar por MAC..." onkeyup="aplicarFiltros()">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="filtroSoloActivos" onchange="aplicarFiltros()">
                    <label class="form-check-label" for="filtroSoloActivos">
                        Solo dispositivos activos
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Mapa Interactivo -->
    <div class="mapa-container">
        <div class="mapa-header">
            <h5><i class="fas fa-map-marked-alt"></i> Mapa de Dispositivos Activos</h5>
            <div class="mapa-controls">
                <button class="btn btn-sm btn-outline-primary" onclick="centrarMapa()">
                    <i class="fas fa-crosshairs"></i> Centrar
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleFullscreen()">
                    <i class="fas fa-expand"></i> Pantalla Completa
                </button>
            </div>
        </div>
        <div id="mapaMonitor" class="mapa-monitor"></div>
    </div>

    <!-- Tabla de Datos en Tiempo Real -->
    <div class="tabla-datos-container">
        <div class="tabla-header">
            <h5><i class="fas fa-table"></i> Datos de Sensores en Tiempo Real</h5>
            <div class="tabla-controls">
                <select class="form-select form-select-sm" id="limiteDatos" onchange="cargarTablaDatos()">
                    <option value="25">25 registros</option>
                    <option value="50" selected>50 registros</option>
                    <option value="100">100 registros</option>
                </select>
                <button class="btn btn-sm btn-outline-primary" onclick="cargarTablaDatos()">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="tabla-sistema" id="tablaDatos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Dispositivo</th>
                        <th>Mascota</th>
                        <th>Fecha</th>
                        <th>Ubicación</th>
                        <th>Altitud</th>
                        <th>Velocidad</th>
                        <th>BPM</th>
                        <th>Temperatura</th>
                        <th>Batería</th>
                    </tr>
                </thead>
                <tbody id="tablaDatosBody">
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            Cargando datos...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="tabla-paginacion" id="paginacionTabla"></div>
    </div>
</div>

<!-- Estilos específicos para el monitor -->
<style>
.filtros-avanzados {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.mapa-container {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.mapa-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: var(--surface-elevated);
}

.mapa-monitor {
    height: 500px;
    width: 100%;
}

.tabla-datos-container {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.tabla-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: var(--surface-elevated);
}

.tabla-controls {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.tabla-paginacion {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
}

.header-subtitle {
    margin: 0.5rem 0 0 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .mapa-monitor {
        height: 300px;
    }
    
    .mapa-header, .tabla-header {
        flex-direction: column;
        gap: 0.5rem;
        align-items: stretch;
    }
    
    .tabla-controls {
        justify-content: center;
    }
}
</style>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Definir BASE_URL para el JavaScript
window.BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>assets/js/monitor-dashboard.js"></script>

<script>
// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarMonitor();
});
</script> 