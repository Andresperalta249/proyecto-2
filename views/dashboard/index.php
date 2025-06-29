<?php
/**
 * Vista: dashboard/index.php
 * --------------------------
 * Panel principal del dashboard con estadísticas y resumen del sistema.
 *
 * Variables recibidas:
 *   - $estadisticas: Estadísticas generales del sistema.
 *   - $dispositivos: Lista de dispositivos recientes.
 *   - $mascotas: Lista de mascotas recientes.
 *   - $usuarios: Lista de usuarios recientes.
 *
 * Uso:
 *   Esta vista es llamada desde DashboardController para mostrar el panel principal.
 *   Muestra un resumen de la actividad del sistema y acceso rápido a funciones.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', 'Inter', Arial, sans-serif;
            background: #f8f9fc;
            overflow-y: hidden;
        }
        .dashboard-row {
            margin-bottom: 1.5rem;
        }
        .dashboard-card {
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 2px 12px rgba(44,62,80,0.07);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 180px;
            height: 100%;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .dashboard-card:hover {
            box-shadow: 0 6px 24px rgba(44,62,80,0.13);
            transform: translateY(-4px) scale(1.02);
        }
        .kpi-icon {
            font-size: 2.2rem;
            color: #1976d2;
        }
        .kpi-value {
            font-size: 2.1rem;
            font-weight: 700;
            color: #222;
        }
        .kpi-label {
            font-size: 1rem;
            color: #6c757d;
        }
        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1976d2;
            margin-bottom: 0.5rem;
        }
        .module-description {
            font-size: 0.98rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        .chart-container {
            width: 100%;
            height: 180px;
            max-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 991px) {
            .dashboard-row {
                margin-bottom: 1rem;
            }
            .chart-container {
                height: 160px;
            }
        }
        @media (max-width: 767px) {
            .dashboard-row {
                margin-bottom: 0.5rem;
            }
            .dashboard-card {
                min-height: 120px;
            }
            .chart-container {
                height: 120px;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid py-3" style="height: 100vh;">
        <div class="row dashboard-row align-items-stretch" style="height: 38%;">
            <div class="col-12 col-md-4 d-flex">
                <div class="dashboard-card w-100 h-100">
                    <div class="kpi-icon mb-1"><i class="fas fa-paw"></i></div>
                    <div class="kpi-value" id="totalMascotas">0</div>
                    <div class="kpi-label">Mascotas Registradas</div>
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex">
                <div class="dashboard-card w-100 h-100">
                    <div class="kpi-icon mb-1"><i class="fas fa-microchip"></i></div>
                    <div class="kpi-value" id="totalDispositivos">0</div>
                    <div class="kpi-label">Dispositivos Activos</div>
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex">
                <div class="dashboard-card w-100 h-100">
                    <div class="kpi-icon mb-1"><i class="fas fa-users"></i></div>
                    <div class="kpi-value" id="totalUsuarios">0</div>
                    <div class="kpi-label">Usuarios Activos</div>
                </div>
            </div>
        </div>
        <div class="row dashboard-row align-items-stretch" style="height: 58%;">
            <div class="col-12 col-md-6 d-flex">
                <div class="dashboard-card w-100 h-100">
                    <div class="chart-title">Mascotas por Especie</div>
                    <div class="module-description">Distribución de mascotas registradas por especie.</div>
                    <div class="chart-container">
                        <canvas id="especiesChart" style="height:100%!important;width:100%!important;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 d-flex">
                <div class="dashboard-card w-100 h-100">
                    <div class="chart-title">Usuarios y Mascotas por Mes</div>
                    <div class="module-description">Evolución mensual de usuarios y mascotas registrados en la plataforma.</div>
                    <div class="d-flex align-items-center gap-2 mb-2 justify-content-end w-100" style="max-width: 90%;">
                        <label for="rangoDias" class="form-label mb-0">Rango de días:</label>
                        <select id="rangoDias" class="form-select form-select-sm form-select-auto-width">
                            <option value="7">Últimos 7 días</option>
                            <option value="15">Últimos 15 días</option>
                            <option value="30">Últimos 30 días</option>
                        </select>
                    </div>
                    <div class="chart-container">
                        <canvas id="usuariosChart" style="height:100%!important;width:100%!important;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous"></script>
    <script>
    // Usar la configuración global BASE_URL en lugar de redeclarar
    const DASHBOARD_BASE_URL = window.location.pathname.split('/dashboard')[0] + '/dashboard/';
    let especiesChart, usuariosChart;
    function initCharts() {
        const especiesCtx = document.getElementById('especiesChart').getContext('2d');
        especiesChart = new Chart(especiesCtx, {
            type: 'doughnut',
            data: { labels: [], datasets: [{ data: [], backgroundColor: ['#004AAD','#FFC107','#28a745','#dc3545','#17a2b8','#6f42c1','#fd7e14'] }] },
            options: { plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 14, font: { size: 12 } } } }, cutout: '65%', responsive: true, maintainAspectRatio: false }
        });
        const usuariosCtx = document.getElementById('usuariosChart').getContext('2d');
        usuariosChart = new Chart(usuariosCtx, {
            type: 'line',
            data: { labels: [], datasets: [
                { label: 'Usuarios', data: [], borderColor: '#004AAD', backgroundColor: 'rgba(0, 74, 173, 0.1)', tension: 0.4, fill: true },
                { label: 'Mascotas', data: [], borderColor: '#28a745', backgroundColor: 'rgba(40, 167, 69, 0.1)', tension: 0.4, fill: true }
            ] },
            options: { plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 14, font: { size: 12 } } } }, responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { font: { size: 11 } } }, x: { ticks: { font: { size: 11 } } } } }
        });
    }
    async function updateKPIs() {
        try {
            const response = await fetch(DASHBOARD_BASE_URL + 'getKPIData');
            if (!response.ok) throw new Error('Error al obtener KPIs');
            const result = await response.json();
            if (!result.success) throw new Error(result.error || 'Error en KPIs');
            const data = result.data;
            document.getElementById('totalMascotas').textContent = data.totalMascotas ?? 0;
            document.getElementById('totalDispositivos').textContent = data.totalDispositivos?.total ?? 0;
            document.getElementById('totalUsuarios').textContent = data.totalUsuarios ?? 0;
        } catch (e) {
            document.getElementById('totalMascotas').textContent = '0';
            document.getElementById('totalDispositivos').textContent = '0';
            document.getElementById('totalUsuarios').textContent = '0';
        }
    }
    async function updateEspeciesChart() {
        try {
            const response = await fetch(DASHBOARD_BASE_URL + 'getDistribucionEspecies');
            if (!response.ok) throw new Error('Error al obtener especies');
            const result = await response.json();
            if (!result.success) throw new Error(result.error || 'Error en especies');
            const data = result.data;
            const labels = data.map(item => item.especie);
            const values = data.map(item => item.total);
            especiesChart.data.labels = labels;
            especiesChart.data.datasets[0].data = values;
            especiesChart.update();
        } catch (e) {
            especiesChart.data.labels = [];
            especiesChart.data.datasets[0].data = [];
            especiesChart.update();
        }
    }
    async function updateUsuariosChart() {
        try {
            const dias = parseInt(document.getElementById('rangoDias').value, 10);
            const response = await fetch(`${DASHBOARD_BASE_URL}getHistorialUsuarios?dias=${dias}`);
            if (!response.ok) throw new Error('Error al obtener usuarios');
            const result = await response.json();
            if (!result.success) throw new Error(result.error || 'Error en usuarios');
            const data = result.data;
            const labels = data.map(item => item.fecha);
            const usuarios = data.map(item => item.usuarios);
            const mascotas = data.map(item => item.mascotas);
            usuariosChart.data.labels = labels;
            usuariosChart.data.datasets[0].data = usuarios;
            usuariosChart.data.datasets[1].data = mascotas;
            usuariosChart.update();
        } catch (e) {
            usuariosChart.data.labels = [];
            usuariosChart.data.datasets[0].data = [];
            usuariosChart.data.datasets[1].data = [];
            usuariosChart.update();
        }
    }
    async function updateAll() {
        await Promise.all([
            updateKPIs(),
            updateEspeciesChart(),
            updateUsuariosChart()
        ]);
    }
    document.addEventListener('DOMContentLoaded', () => {
        initCharts();
        updateAll();
        document.getElementById('rangoDias').addEventListener('change', updateUsuariosChart);
        window.onresize = () => {
            especiesChart.resize();
            usuariosChart.resize();
        };
    });
    </script>
</body>
</html>