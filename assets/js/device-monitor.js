/**
 * Monitor de Dispositivos
 * ======================
 * 
 * Archivo: assets/js/device-monitor.js
 * 
 * Propósito:
 *   - Funcionalidades para el monitoreo en tiempo real de dispositivos.
 *   - Actualización automática de datos de sensores.
 *   - Visualización de gráficas y estadísticas.
 * 
 * Funciones principales:
 *   - inicializarMonitor(): Configura el monitor de dispositivos.
 *   - actualizarDatos(): Actualiza los datos de los dispositivos.
 *   - mostrarGrafica(): Muestra gráficas de datos de sensores.
 *   - configurarActualizacion(): Configura actualización automática.
 * 
 * Uso:
 *   Este archivo se usa en las páginas de monitoreo para mostrar datos
 *   en tiempo real de los dispositivos IoT.
 */

// Importar utilidades de fecha
import { formatDate, formatRelativeTime } from './date-utils.js';

// Variables globales
let mapa;
let marcador;
let graficas = {};
let intervaloActualizacion;
let circuloError = null;

// Configuración de Chart.js
const configuracionGraficas = {
    temperatura: {
        color: '#dc3545',
        unidad: '°C',
        min: 35,
        max: 42,
        ticks: {
            stepSize: 1
        }
    },
    ritmoCardiaco: {
        color: '#28a745',
        unidad: 'BPM',
        min: 60,
        max: 200,
        ticks: {
            stepSize: 20
        }
    },
    bateria: {
        color: '#007bff',
        unidad: '%',
        min: 0,
        max: 100,
        ticks: {
            stepSize: 10
        }
    }
};

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js no está disponible');
        return;
    }

    inicializarMapa();
    inicializarGraficas();
    cargarDatosIniciales();
    configurarEventos();
    iniciarActualizacionAutomatica();

    // Botón centrar en dispositivo
    const btnCentrar = document.getElementById('btnCentrarDispositivo');
    if (btnCentrar) {
        btnCentrar.addEventListener('click', function() {
            if (marcador) {
                mapa.setView(marcador.getLatLng(), 15);
            }
        });
    }

    // Menú rango de tiempo
    const fabDropdown = document.getElementById('fabRangoTiempo');
    const btnRangoTiempo = document.getElementById('btnRangoTiempo');
    if (btnRangoTiempo && fabDropdown) {
        btnRangoTiempo.addEventListener('click', function(e) {
            e.stopPropagation();
            fabDropdown.classList.toggle('open');
        });
        // Cerrar menú al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!fabDropdown.contains(e.target)) {
                fabDropdown.classList.remove('open');
            }
        });
        // Botones de rango
        fabDropdown.querySelectorAll('.btn-fab-option').forEach(btn => {
            btn.addEventListener('click', function() {
                const horas = parseInt(this.dataset.range);
                cargarDatosPorRango(horas);
                fabDropdown.classList.remove('open');
            });
        });
    }

    // Botón pantalla completa
    const btnMapaFull = document.getElementById('btnMapaFull');
    const mapaDispositivo = document.getElementById('mapaDispositivo');
    if (btnMapaFull && mapaDispositivo) {
        btnMapaFull.addEventListener('click', function() {
            if (mapaDispositivo.requestFullscreen) {
                mapaDispositivo.requestFullscreen();
            } else if (mapaDispositivo.webkitRequestFullscreen) { /* Safari */
                mapaDispositivo.webkitRequestFullscreen();
            } else if (mapaDispositivo.msRequestFullscreen) { /* IE11 */
                mapaDispositivo.msRequestFullscreen();
            }
        });
    }
});

// Inicialización del mapa
function inicializarMapa() {
    mapa = L.map('mapaDispositivo').setView([0, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapa);
}

// Inicialización de gráficas
function inicializarGraficas() {
    Object.keys(configuracionGraficas).forEach(tipo => {
        const config = configuracionGraficas[tipo];
        const ctx = document.getElementById(`grafica${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`).getContext('2d');
        
        const chartConfig = {
            type: 'line',
            data: {
                datasets: [{
                    label: tipo.charAt(0).toUpperCase() + tipo.slice(1),
                    data: [],
                    borderColor: config.color,
                    backgroundColor: config.color + '20',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 0 // Desactivar animaciones para mejor rendimiento
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'minute',
                            displayFormats: {
                                minute: 'HH:mm'
                            },
                            tooltipFormat: 'HH:mm:ss'
                        },
                        title: {
                            display: true,
                            text: 'Hora'
                        },
                        grid: {
                            display: true
                        }
                    },
                    y: {
                        min: config.min,
                        max: config.max,
                        ticks: config.ticks,
                        title: {
                            display: true,
                            text: config.unidad
                        },
                        grid: {
                            display: true
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y}${config.unidad}`;
                            }
                        }
                    }
                }
            }
        };

        try {
            graficas[tipo] = new Chart(ctx, chartConfig);
        } catch (error) {
            console.error(`Error al inicializar la gráfica de ${tipo}:`, error);
        }
    });
}

// Carga de datos iniciales
async function cargarDatosIniciales() {
    try {
        if (!window.dispositivoId) {
            throw new Error('ID de dispositivo no definido');
        }

                        const response = await fetch(`${window.MONITOR_BASE_URL}monitor/getDatos/${window.dispositivoId}`);
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const json = await response.json();
        console.log('Respuesta del servidor:', json);

        if (!json || typeof json !== 'object') {
            throw new Error('Formato de respuesta inválido');
        }

        if (!json.success) {
            throw new Error(json.error || 'Error al obtener los datos');
        }

        if (!json.datos || !Array.isArray(json.datos)) {
            throw new Error('Formato de datos inválido');
        }

        // Actualizar la interfaz con los datos recibidos
        actualizarSensores(json.datos);
        actualizarTabla(json.datos);
        
        if (json.ubicacion) {
            actualizarUbicacion(json.ubicacion);
        }

    } catch (error) {
        console.error('Error al cargar datos iniciales:', error);
        mostrarError(error.message || 'Error al cargar los datos del dispositivo');
    }
}

// Actualización de tabla
function actualizarTabla(datos) {
    if (!Array.isArray(datos)) {
        console.error('actualizarTabla: El argumento no es un array', datos);
        return;
    }
    const tbody = document.querySelector('#tablaRegistros tbody');
    tbody.innerHTML = '';

    datos.forEach(dato => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${formatDate(dato.fecha, 'yyyy-MM-dd HH:mm:ss')}</td>
            <td>${dato.temperatura !== undefined ? dato.temperatura + '°C' : '--'}</td>
            <td>${(dato.ritmo_cardiaco !== undefined ? dato.ritmo_cardiaco : (dato.bpm !== undefined ? dato.bpm : '--')) + (dato.ritmo_cardiaco !== undefined || dato.bpm !== undefined ? ' BPM' : '')}</td>
            <td>${dato.bateria !== undefined ? dato.bateria + '%' : '--'}</td>
            <td>${(dato.latitud !== undefined && dato.longitud !== undefined) ? dato.latitud + ', ' + dato.longitud : '--'}</td>
        `;
        tbody.appendChild(tr);
    });
}

// Configuración de eventos
function configurarEventos() {
    // Botones de expandir gráfica
    document.querySelectorAll('[data-chart]').forEach(button => {
        button.addEventListener('click', function() {
            const tipo = this.dataset.chart;
            expandirGrafica(tipo);
        });
    });
}

// Carga de datos por rango de tiempo
async function cargarDatosPorRango(horas) {
    try {
                    const response = await fetch(`${window.MONITOR_BASE_URL}monitor/getUltimosDatos/${window.dispositivoId}?horas=${horas}`);
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const datos = await response.json();
        if (!datos || !Array.isArray(datos.sensores)) {
            throw new Error('Datos inválidos recibidos del servidor');
        }

        actualizarSensores(datos.sensores);
        actualizarTabla(datos.sensores);
        actualizarUbicacion(datos.ubicacion);

    } catch (error) {
        console.error('Error al cargar datos por rango:', error);
        mostrarError('Error al cargar los datos del dispositivo');
    }
}

// Expansión de gráfica
function expandirGrafica(tipo) {
    const modal = new bootstrap.Modal(document.getElementById('modalGrafica'));
    const canvas = document.getElementById('graficaExpandida');
    const ctx = canvas.getContext('2d');

    // Crear nueva gráfica expandida
    const graficaExpandida = new Chart(ctx, {
        type: 'line',
        data: graficas[tipo].data,
        options: {
            ...graficas[tipo].options,
            maintainAspectRatio: false,
            responsive: true
        }
    });

    modal.show();

    // Limpiar al cerrar
    document.getElementById('modalGrafica').addEventListener('hidden.bs.modal', function() {
        graficaExpandida.destroy();
    });
}

// Iniciar actualización automática
function iniciarActualizacionAutomatica() {
    // Limpiar intervalo existente si hay uno
    if (intervaloActualizacion) {
        clearInterval(intervaloActualizacion);
    }

    // Actualizar cada 5 segundos
    intervaloActualizacion = setInterval(cargarDatosIniciales, 5000);
}

// Manejo de errores
function mostrarError(mensaje) {
    const errorDiv = document.getElementById('error-message');
    errorDiv.textContent = mensaje;
    errorDiv.style.display = 'block';
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 5000);
}

// Limpieza al desmontar
window.addEventListener('beforeunload', function() {
    if (intervaloActualizacion) {
        clearInterval(intervaloActualizacion);
    }
});

// Actualización de sensores
function actualizarSensores(datos) {
    if (!Array.isArray(datos) || datos.length === 0) {
        console.warn('No hay datos de sensores para actualizar');
        return;
    }

    // Obtener el último dato
    const ultimoDato = datos[datos.length - 1];

    // Actualizar tarjetas de estado
    if (ultimoDato.temperatura !== undefined) {
        document.querySelector('#cardTemperatura .status-value').textContent = `${ultimoDato.temperatura}°C`;
    }
    if (ultimoDato.ritmo_cardiaco !== undefined || ultimoDato.bpm !== undefined) {
        const bpm = ultimoDato.ritmo_cardiaco || ultimoDato.bpm;
        document.querySelector('#cardRitmoCardiaco .status-value').textContent = `${bpm} BPM`;
    }
    if (ultimoDato.bateria !== undefined) {
        document.querySelector('#cardBateria .status-value').textContent = `${ultimoDato.bateria}%`;
    }

    // Actualizar gráficas
    Object.keys(graficas).forEach(tipo => {
        const grafica = graficas[tipo];
        if (!grafica) return;

        const datosGrafica = datos.map(dato => ({
            x: new Date(dato.fecha),
            y: dato[tipo] || dato[tipo === 'ritmoCardiaco' ? 'ritmo_cardiaco' : tipo]
        })).filter(punto => punto.y !== undefined);

        grafica.data.datasets[0].data = datosGrafica;
        grafica.update();
    });
}

// Actualización de ubicación
function actualizarUbicacion(ubicacion) {
    if (!ubicacion || !ubicacion.latitud || !ubicacion.longitud) {
        console.warn('Datos de ubicación inválidos:', ubicacion);
        return;
    }

    const lat = parseFloat(ubicacion.latitud);
    const lng = parseFloat(ubicacion.longitud);

    if (isNaN(lat) || isNaN(lng)) {
        console.warn('Coordenadas inválidas:', ubicacion);
        return;
    }

    if (marcador) {
        marcador.setLatLng([lat, lng]);
    } else {
        marcador = L.marker([lat, lng]).addTo(mapa);
    }

    mapa.setView([lat, lng], 15);
} 