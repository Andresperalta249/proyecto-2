<?php
if (!isset($dispositivo)) {
    echo '<div class="alert alert-danger">Dispositivo no encontrado</div>';
    return;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-microchip"></i> 
                        Monitor de Dispositivo: <?= htmlspecialchars($dispositivo['nombre_dispositivo'] ?? 'Sin nombre') ?>
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Información del Dispositivo -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Información del Dispositivo</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>MAC:</strong></td>
                                            <td><?= htmlspecialchars($dispositivo['mac_address'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Estado:</strong></td>
                                            <td>
                                                <span class="badge bg-<?= ($dispositivo['estado'] === 'activo') ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($dispositivo['estado'] ?? 'desconocido') ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Última Conexión:</strong></td>
                                            <td><?= $dispositivo['ultima_conexion'] ?? 'N/A' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Información de la Mascota</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Mascota:</strong></td>
                                            <td><?= htmlspecialchars($dispositivo['mascota_nombre'] ?? 'Sin asignar') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Especie:</strong></td>
                                            <td><?= htmlspecialchars($dispositivo['especie'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Propietario:</strong></td>
                                            <td><?= htmlspecialchars($dispositivo['usuario_nombre'] ?? 'N/A') ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos y Datos -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title">Datos de Sensores</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="graficaSensores" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title">Últimos Datos</h6>
                                </div>
                                <div class="card-body">
                                    <div id="ultimosDatos">
                                        <p class="text-muted">Cargando datos...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mapa de Ubicación -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title">Ubicación Actual</h6>
                                </div>
                                <div class="card-body">
                                    <div id="mapaDispositivo" style="height: 300px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Variables globales
const dispositivoId = <?= $dispositivo['id_dispositivo'] ?? 'null' ?>;
const BASE_URL = '<?= BASE_URL ?>';

// Inicializar mapa
let mapa = L.map('mapaDispositivo').setView([0, 0], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(mapa);

// Función para cargar datos
function cargarDatosDispositivo() {
    if (!dispositivoId) return;

    fetch(`${BASE_URL}monitor/getDatos/${dispositivoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarUltimosDatos(data.data);
                actualizarGrafica(data.data);
                actualizarMapa(data.data);
            }
        })
        .catch(error => {
            console.error('Error al cargar datos:', error);
        });
}

// Actualizar últimos datos
function actualizarUltimosDatos(datos) {
    const container = document.getElementById('ultimosDatos');
    if (!datos || datos.length === 0) {
        container.innerHTML = '<p class="text-muted">No hay datos disponibles</p>';
        return;
    }

    const ultimo = datos[0];
    container.innerHTML = `
        <div class="row">
            <div class="col-6">
                <small class="text-muted">Temperatura</small>
                <div class="h5">${ultimo.temperatura}°C</div>
            </div>
            <div class="col-6">
                <small class="text-muted">Humedad</small>
                <div class="h5">${ultimo.humedad}%</div>
            </div>
        </div>
        <div class="mt-3">
            <small class="text-muted">Última actualización</small>
            <div>${new Date(ultimo.fecha_registro).toLocaleString()}</div>
        </div>
    `;
}

// Actualizar gráfica
function actualizarGrafica(datos) {
    const ctx = document.getElementById('graficaSensores').getContext('2d');
    
    if (window.graficaSensores) {
        window.graficaSensores.destroy();
    }

    const labels = datos.slice(0, 10).reverse().map(d => 
        new Date(d.fecha_registro).toLocaleTimeString()
    );
    const temperaturas = datos.slice(0, 10).reverse().map(d => d.temperatura);
    const humedades = datos.slice(0, 10).reverse().map(d => d.humedad);

    window.graficaSensores = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Temperatura (°C)',
                data: temperaturas,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }, {
                label: 'Humedad (%)',
                data: humedades,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Actualizar mapa
function actualizarMapa(datos) {
    if (!datos || datos.length === 0) return;

    const ultimo = datos[0];
    if (ultimo.latitud && ultimo.longitud) {
        const lat = parseFloat(ultimo.latitud);
        const lng = parseFloat(ultimo.longitud);
        
        mapa.setView([lat, lng], 15);
        
        // Limpiar marcadores existentes
        mapa.eachLayer((layer) => {
            if (layer instanceof L.Marker) {
                mapa.removeLayer(layer);
            }
        });

        // Agregar nuevo marcador
        L.marker([lat, lng]).addTo(mapa)
            .bindPopup(`
                <strong>${<?= json_encode($dispositivo['mascota_nombre'] ?? 'Sin nombre') ?>}</strong><br>
                Temperatura: ${ultimo.temperatura}°C<br>
                Humedad: ${ultimo.humedad}%<br>
                Fecha: ${new Date(ultimo.fecha_registro).toLocaleString()}
            `);
    }
}

// Cargar datos iniciales
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosDispositivo();
    
    // Actualizar cada 30 segundos
    setInterval(cargarDatosDispositivo, 30000);
});
</script> 