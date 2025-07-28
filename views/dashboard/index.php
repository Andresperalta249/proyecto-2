<?php 
$titulo = isset($titulo) ? $titulo : 'Dashboard IoT';
$subtitulo = isset($subtitulo) ? $subtitulo : 'Resumen general del sistema IoT Pets.';
?>

<!-- Header del Dashboard -->
<div class="dashboard-header">
    <div class="dashboard-title">
        <h1><?= htmlspecialchars($titulo) ?></h1>
        <p><?= htmlspecialchars($subtitulo) ?></p>
    </div>
    <div class="dashboard-controls">
        <label for="rangoDias">Rango de días:</label>
        <select id="rangoDias" class="form-select">
            <option value="7">Últimos 7 días</option>
            <option value="15">Últimos 15 días</option>
            <option value="30">Últimos 30 días</option>
        </select>
    </div>
</div>

<!-- KPIs Section -->
<div class="dashboard-kpis">
    <div class="kpi-card">
        <div class="kpi-icon">
            <i class="fas fa-microchip"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-value" id="totalDispositivos">0</div>
            <div class="kpi-label">Dispositivos Conectados</div>
        </div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-icon">
            <i class="fas fa-paw"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-value" id="totalMascotas">0</div>
            <div class="kpi-label">Mascotas Registradas</div>
        </div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-value" id="usuariosRegistrados">0</div>
            <div class="kpi-label">Usuarios Registrados</div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="dashboard-charts">
    <div class="chart-card">
        <div class="chart-header">
            <h3>Distribución de Especies</h3>
        </div>
        <div class="chart-body">
            <canvas id="especiesChart"></canvas>
        </div>
    </div>
    
    <div class="chart-card">
        <div class="chart-header">
            <h3>Registros por Día</h3>
        </div>
        <div class="chart-body">
            <canvas id="usuariosChart"></canvas>
        </div>
    </div>
</div>

<script>
// Detectar la base del proyecto automáticamente
const BASE_URL = window.location.pathname.split('/dashboard')[0] + '/dashboard/';

// Variables globales para las gráficas
let especiesChart, usuariosChart;

// Inicializar las gráficas
function initCharts() {
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    boxWidth: 16,
                    padding: 12,
                    font: {
                        size: 12,
                        weight: '500'
                    },
                    color: '#2c3e50',
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            }
        }
    };

    // Inicializar gráfica de especies
    const especiesCtx = document.getElementById('especiesChart').getContext('2d');
    especiesChart = new Chart(especiesCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#3b82f6',
                    '#f59e0b',
                    '#22c55e',
                    '#ef4444',
                    '#8b5cf6'
                ]
            }]
        },
        options: {
            ...commonOptions,
            cutout: '60%',
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            return `${label}: ${value}`;
                        }
                    }
                }
            }
        }
    });

    // Inicializar gráfica de usuarios
    const usuariosCtx = document.getElementById('usuariosChart').getContext('2d');
    usuariosChart = new Chart(usuariosCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Usuarios',
                data: [],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4
            },
            {
                label: 'Mascotas',
                data: [],
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#22c55e',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        color: '#6c757d'
                    },
                    grid: {
                        color: '#e9ecef'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        color: '#6c757d'
                    },
                    grid: {
                        color: '#e9ecef'
                    }
                }
            }
        }
    });
}

// Función para manejar errores de fetch
async function handleFetchError(error) {
    console.error('Error en la petición:', error);
    
    // Remover mensajes de error anteriores
    const existingAlerts = document.querySelectorAll('.alert-danger');
    existingAlerts.forEach(alert => alert.remove());
    
    // Mostrar mensaje de error al usuario
    const errorMessage = document.createElement('div');
    errorMessage.className = 'alert alert-danger alert-dismissible fade show';
    errorMessage.innerHTML = `
        <strong>Error!</strong> ${error.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.querySelector('.dashboard-charts').prepend(errorMessage);
    
    // Actualizar valores por defecto
    document.getElementById('totalDispositivos').textContent = '0/0';
    document.getElementById('totalMascotas').textContent = '0';
    document.getElementById('usuariosRegistrados').textContent = '0';
}

// Función para actualizar los KPI
async function updateKPIs() {
    try {
        const response = await fetch(BASE_URL + 'getKPIData');
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Error al obtener datos KPI');
        }
        
        const data = result.data;
        if (!data) {
            throw new Error('No se recibieron datos del servidor');
        }

        // Validar y actualizar cada KPI
        const dispositivosConectados = data.dispositivos?.conectados ?? 0;
        const dispositivosTotal = data.dispositivos?.total ?? 0;
        const mascotasTotal = data.mascotas ?? 0;
        const usuariosRegistrados = data.usuarios_registrados ?? 0;

        document.getElementById('totalDispositivos').textContent = 
            `${dispositivosConectados}/${dispositivosTotal}`;
        document.getElementById('totalMascotas').textContent = mascotasTotal;
        document.getElementById('usuariosRegistrados').textContent = usuariosRegistrados;
    } catch (error) {
        console.error('Error en updateKPIs:', error);
        handleFetchError(error);
    }
}

// Obtener el rango de días seleccionado
function getDiasSeleccionados() {
    return parseInt(document.getElementById('rangoDias').value, 10);
}

// Función para formatear fecha (solo día)
function formatearFecha(fecha) {
    if (!fecha) return '';
    const d = new Date(fecha);
    if (isNaN(d)) return fecha;
    return d.getDate(); // Solo retorna el día
}

// Función para actualizar la gráfica de especies
async function updateEspeciesChart() {
    if (!especiesChart) {
        console.error('Gráfica de especies no inicializada');
        return;
    }

    try {
        const response = await fetch(BASE_URL + 'getDistribucionEspecies');
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'Error al obtener distribución de especies');
        
        const data = result.data;
        if (!data || !Array.isArray(data)) throw new Error('Datos de especies inválidos');

        const labels = data.map(item => `${item.especie} (${item.porcentaje}%)`);
        const values = data.map(item => item.total);

        especiesChart.data.labels = labels;
        especiesChart.data.datasets[0].data = values;
        especiesChart.update();
    } catch (error) {
        console.error('Error en updateEspeciesChart:', error);
        handleFetchError(error);
    }
}

// Función para actualizar la gráfica de usuarios
async function updateUsuariosChart() {
    if (!usuariosChart) {
        console.error('Gráfica de usuarios no inicializada');
        return;
    }

    try {
        const dias = getDiasSeleccionados();
        const response = await fetch(`${BASE_URL}getHistorialUsuarios?dias=${dias}`);
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'Error al obtener historial de usuarios');
        
        const data = result.data;
        if (!data || !Array.isArray(data)) throw new Error('Datos de usuarios inválidos');

        const labels = data.map(item => formatearFecha(item.fecha));
        const usuariosData = data.map(item => item.usuarios);
        const mascotasData = data.map(item => item.mascotas);

        usuariosChart.data.labels = labels;
        usuariosChart.data.datasets[0].data = usuariosData;
        usuariosChart.data.datasets[1].data = mascotasData;
        usuariosChart.update();
    } catch (error) {
        console.error('Error en updateUsuariosChart:', error);
        handleFetchError(error);
    }
}

// Función para actualizar todos los datos
async function updateAllData() {
    try {
        await Promise.all([
            updateKPIs(),
            updateEspeciesChart(),
            updateUsuariosChart()
        ]);
    } catch (error) {
        handleFetchError(error);
    }
}

// Inicializar las gráficas cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    initCharts();
    // Actualización inicial
    updateAllData();
    // Actualización periódica cada 30 segundos
    setInterval(updateAllData, 30000);
});

// Event listener para el selector de rango de días
document.getElementById('rangoDias').addEventListener('change', () => {
    updateUsuariosChart();
});
</script>

<!-- Cargar Chart.js antes de nuestro código -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Cargar jQuery antes de cualquier plugin o JS que lo requiera -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Si usas Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Si usas DataTables y plugins -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>