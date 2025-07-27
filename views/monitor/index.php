<?php
// Definir BASE_URL para el JavaScript
echo '<script>window.BASE_URL = "' . BASE_URL . '";</script>';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Monitor IoT de Mascotas</h4>
                </div>
                <div class="card-body">
                    <!-- Filtros Avanzados -->
                    <div class="filtros-avanzados mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="filtroPropietario" class="form-label">Propietario</label>
                                <select class="form-select" id="filtroPropietario">
                                    <option value="">Todos los propietarios</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroMascota" class="form-label">Mascota</label>
                                <select class="form-select" id="filtroMascota">
                                    <option value="">Todas las mascotas</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroMAC" class="form-label">MAC del Dispositivo</label>
                                <input type="text" class="form-control" id="filtroMAC" placeholder="Buscar por MAC...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="button" class="btn btn-primary" id="btnSoloActivos">
                                        <i class="fas fa-filter"></i> Solo Activos
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <button type="button" class="btn btn-success" id="btnAplicarFiltros">
                                    <i class="fas fa-search"></i> Aplicar Filtros
                                </button>
                                <button type="button" class="btn btn-secondary" id="btnLimpiarFiltros">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                                <button type="button" class="btn btn-info" id="btnCentrarMapa">
                                    <i class="fas fa-crosshairs"></i> Centrar Mapa
                                </button>
                                <button type="button" class="btn btn-warning" id="btnPantallaCompleta">
                                    <i class="fas fa-expand"></i> Pantalla Completa
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mapa Interactivo -->
                    <div class="mapa-container mb-4">
                        <div id="mapaMonitor" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                    </div>

                    <!-- Tabla de Datos -->
                    <div class="tabla-datos-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Datos de Sensores en Tiempo Real</h5>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnActualizarTabla">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaDatos">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Dispositivo</th>
                                        <th>Mascota</th>
                                        <th>Propietario</th>
                                        <th>Temperatura</th>
                                        <th>Humedad</th>
                                        <th>Latitud</th>
                                        <th>Longitud</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="10" class="text-center">Cargando datos...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos CSS -->
<style>
.filtros-avanzados {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.mapa-container {
    position: relative;
}

.tabla-datos-container {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#mapaMonitor {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.btn {
    margin-right: 5px;
}

.form-label {
    font-weight: 600;
    color: #495057;
}
</style>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script src="<?= BASE_URL ?>assets/js/monitor-dashboard.js"></script> 