<?php 
$titulo = isset($titulo) ? $titulo : 'Dashboard IoT';
$subtitulo = isset($subtitulo) ? $subtitulo : 'Resumen general del sistema IoT Pets.';
?>
<h1 class="titulo-pagina"><?= htmlspecialchars($titulo) ?></h1>
<p class="subtitulo-pagina">
  <?= htmlspecialchars($subtitulo) ?>
</p>

<div class="contenedor-sistema">
    <div class="contenedor-sistema-header">
        <i class="fas fa-chart-bar"></i>
        Resumen del Sistema
    </div>
    <div class="contenedor-sistema-body">
        <!-- KPI Cards -->
        <div class="dashboard-kpi-grid">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-value text-lg fw-bold" id="totalDispositivos">0</div>
                        <div class="kpi-label text-sm">Dispositivos Conectados</div>
                    </div>
                    <i class="fas fa-microchip kpi-icon"></i>
                </div>
            </div>
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-value text-lg fw-bold" id="totalMascotas">0</div>
                        <div class="kpi-label text-sm">Mascotas Registradas</div>
                    </div>
                    <i class="fas fa-paw kpi-icon"></i>
                </div>
            </div>
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-value text-lg fw-bold" id="usuariosRegistrados">0</div>
                        <div class="kpi-label text-sm">Usuarios Registrados</div>
                    </div>
                    <i class="fas fa-users kpi-icon"></i>
                </div>
            </div>
        </div>

        <!-- Selector de rango de días -->
        <div class="dashboard-controls">
            <label for="rangoDias" class="form-label mb-0 small">Rango de días:</label>
            <select id="rangoDias" class="form-select form-select-sm">
                <option value="7">Últimos 7 días</option>
                <option value="15">Últimos 15 días</option>
                <option value="30">Últimos 30 días</option>
            </select>
        </div>

        <!-- Charts -->
        <div class="dashboard-charts-grid">
            <div class="chart-container">
                <div class="chart-title text-md fw-bold">Distribución de especies</div>
                <canvas id="especiesChart"></canvas>
            </div>
            <div class="chart-container">
                <div class="chart-title text-md fw-bold">Registros por día</div>
                <canvas id="usuariosChart"></canvas>
            </div>
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
                    boxWidth: 12,
                    padding: 8,
                    font: {
                        size: 10
                    }
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
                    '#004AAD',
                    '#FFC107',
                    '#28a745',
                    '#dc3545',
                    '#17a2b8'
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
                borderColor: '#004AAD',
                backgroundColor: 'rgba(0, 74, 173, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Mascotas',
                data: [],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
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
                            size: 10
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 10
                        }
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
    document.querySelector('.contenedor-sistema-body').prepend(errorMessage);
    
    // Actualizar valores por defecto
    document.getElementById('totalDispositivos').textContent = '0/0';
    document.getElementById('totalMascotas').textContent = '0';
    document.getElementById('totalAlertas').textContent = '0/0';
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

// Función para obtener el rango de fechas mostrado
function getRangoFechas(data) {
    if (!data || data.length === 0) return '';
    const desde = data[0]?.fecha || '';
    const hasta = data[data.length - 1]?.fecha || '';
    if (!desde || !hasta) return '';
    return `Datos del ${formatearFecha(desde)} al ${formatearFecha(hasta)}`;
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

</body>
</html>