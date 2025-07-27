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

        const response = await fetch(`${window.BASE_URL}monitor/getUltimosDatos/${window.dispositivoId}`);
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const datos = await response.json();
        if (!datos || !datos.ubicacion || !datos.sensores) {
            throw new Error('Datos inválidos recibidos del servidor');
        }

        actualizarSensores(datos.sensores);
        actualizarTabla(datos.sensores);
        actualizarUbicacion(datos.ubicacion);

    } catch (error) {
        console.error('Error al cargar datos iniciales:', error);
        mostrarError('Error al cargar los datos del dispositivo');
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
        const response = await fetch(`${window.BASE_URL}monitor/getUltimosDatos/${window.dispositivoId}?horas=${horas}`);
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

// Restaurar función para actualizar las gráficas y tarjetas de estado
function actualizarSensores(datos) {
    if (!datos || !Array.isArray(datos)) {
        return;
    }
    // Actualizar tarjetas de estado
    const ultimoDato = datos[datos.length - 1];
    if (ultimoDato) {
        const tempCard = document.querySelector('#cardTemperatura .status-value');
        if (tempCard) tempCard.textContent = (ultimoDato.temperatura !== undefined && ultimoDato.temperatura !== null) ? `${ultimoDato.temperatura}°C` : '--°C';
        const ritmo = (ultimoDato.ritmo_cardiaco !== undefined && ultimoDato.ritmo_cardiaco !== null)
            ? ultimoDato.ritmo_cardiaco
            : (ultimoDato.bpm !== undefined && ultimoDato.bpm !== null ? ultimoDato.bpm : undefined);
        const ritmoCard = document.querySelector('#cardRitmoCardiaco .status-value');
        if (ritmoCard) ritmoCard.textContent = (ritmo !== undefined) ? `${ritmo} BPM` : '-- BPM';
        const batCard = document.querySelector('#cardBateria .status-value');
        if (batCard) batCard.textContent = (ultimoDato.bateria !== undefined && ultimoDato.bateria !== null) ? `${ultimoDato.bateria}%` : '--%';
    }
    // Actualizar gráficas
    Object.keys(graficas).forEach(tipo => {
        let key = tipo === 'ritmoCardiaco' ? 'ritmo_cardiaco' : tipo;
        // Filtrar solo datos válidos y aceptar 'bpm' para ritmoCardiaco
        const datosGrafica = datos
            .map(dato => {
                const valor = Number(key === 'ritmo_cardiaco'
                    ? (dato.ritmo_cardiaco !== undefined && dato.ritmo_cardiaco !== null ? dato.ritmo_cardiaco : dato.bpm)
                    : dato[key]);
                const fechaValida = dato.fecha ? new Date(dato.fecha.replace(' ', 'T')) : null;
                if (typeof valor === 'number' && !isNaN(valor) && fechaValida instanceof Date && !isNaN(fechaValida)) {
                    return { x: fechaValida, y: valor };
                }
                return null;
            })
            .filter(d => d !== null);
        const datosLimitados = datosGrafica.slice(-60);
        graficas[tipo].data.datasets[0].data = datosLimitados;
        graficas[tipo].update('none');
    });
}

// Restaurar función para actualizar el marcador en el mapa
function actualizarUbicacion(ubicacion) {
    if (!ubicacion || !ubicacion.latitud || !ubicacion.longitud) {
        return;
    }
    const lat = parseFloat(ubicacion.latitud);
    const lng = parseFloat(ubicacion.longitud);

    // Usar el icono por defecto de Leaflet
    const customIcon = undefined;

    if (marcador) {
        marcador.setLatLng([lat, lng]);
        // No cambiar el icono, se mantiene el default
    } else {
        marcador = L.marker([lat, lng]).addTo(mapa);
    }
    // Ajustar el zoom automáticamente a 18
    mapa.setView([lat, lng], 18);

    // Círculo de margen de error (50 metros)
    if (circuloError) {
        circuloError.setLatLng([lat, lng]);
        circuloError.setRadius(50);
    } else {
        circuloError = L.circle([lat, lng], {
            color: '#2563eb',
            fillColor: '#2563eb',
            fillOpacity: 0.15,
            radius: 50
        }).addTo(mapa);
        console.log('Círculo de margen de error creado en:', lat, lng);
    }
} 