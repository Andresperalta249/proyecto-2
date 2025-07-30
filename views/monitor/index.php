<?php
// Definir BASE_URL para el JavaScript
echo '<script>window.BASE_URL = "' . BASE_URL . '";</script>';
?>

<style>
/* Estilos para optimizar la tabla */
#tablaDatos {
    width: 100% !important;
    table-layout: fixed;
    font-size: 12px;
}

#tablaDatos th,
#tablaDatos td {
    padding: 6px 3px;
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#tablaDatos .text-truncate {
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.table-responsive {
    width: 100% !important;
    overflow-x: auto;
    max-height: 35vh;
    overflow-y: auto;
}

/* Responsive para diferentes pantallas */
@media (max-width: 768px) {
    .table-responsive {
        max-height: 30vh;
    }
}

@media (min-width: 1400px) {
    .table-responsive {
        max-height: 45vh;
    }
}

.card {
    width: 100% !important;
    margin: 0 !important;
}

/* Optimizar espacio de filtros */
.card-body {
    padding: 15px !important;
}

/* Hacer la tabla más compacta */
.table-striped > tbody > tr:nth-of-type(odd) > td {
    background-color: rgba(0,0,0,.02);
}

.fila-dato:hover {
    background-color: rgba(0,123,255,.05) !important;
    cursor: pointer;
}

.fila-seleccionada {
    background-color: rgba(0,123,255,.15) !important;
    border-left: 4px solid #007bff !important;
}

.fila-seleccionada:hover {
    background-color: rgba(0,123,255,.2) !important;
}

/* Mejorar legibilidad de badges */
.badge {
    font-weight: 500;
    padding: 4px 6px;
    border-radius: 4px;
}

/* Scroll personalizado para la tabla */
.table-responsive::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

/* Estilos específicos para los filtros del header */
.card-header .d-flex {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.card-header .form-control-sm {
    height: 28px !important;
    font-size: 11px !important;
    padding: 4px 8px !important;
    border: 1px solid #ced4da !important;
    margin: 0 !important;
    border-radius: 4px !important;
}

.card-header .btn-sm {
    height: 28px !important;
    width: 32px !important;
    padding: 0 !important;
    font-size: 11px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
    border: none !important;
    margin: 0 !important;
    border-radius: 4px !important;
}

.card-header .btn-sm i {
    font-size: 11px !important;
    margin: 0 !important;
}

/* Forzar alineación vertical */
.card-header .d-flex > * {
    vertical-align: middle !important;
    display: inline-flex !important;
    align-items: center !important;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Estilos para filtros simplificados - Override Bootstrap */
.card-header .form-control-sm {
    border-radius: 4px !important;
    border: 1px solid #ced4da !important;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
    font-size: 11px !important;
    height: 28px !important;
    padding: 4px 8px !important;
}

.card-header .form-control-sm:focus {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    outline: none !important;
}

.card-header .btn-sm {
    border-radius: 4px !important;
    font-weight: 500 !important;
    transition: all 0.15s ease-in-out !important;
    height: 28px !important;
    width: 32px !important;
    padding: 0 !important;
    font-size: 11px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
    vertical-align: middle !important;
}

.card-header .btn-sm:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

/* Asegurar alineación correcta */
.card-header .d-flex {
    align-items: center !important;
    gap: 8px !important;
}

.card-header .d-flex > * {
    vertical-align: middle !important;
    display: inline-flex !important;
    align-items: center !important;
}

.card-header .btn-sm i {
    font-size: 11px !important;
    line-height: 1 !important;
}

/* Responsive para móviles */
@media (max-width: 768px) {
    #mapaMonitor {
        height: 20vh !important;
        min-height: 150px !important;
    }
}

@media (max-width: 576px) {
    #mapaMonitor {
        height: 20vh !important;
        min-height: 180px !important;
    }
}

@media (min-width: 1400px) {
    #mapaMonitor {
        height: 40vh !important;
        max-height: 500px !important;
    }
}

/* Asegurar que el mapa se renderice correctamente */
#mapaMonitor {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}

#mapaMonitor .leaflet-container {
    height: 100% !important;
    width: 100% !important;
}

/* Estilos para marcadores personalizados del mapa */
.custom-marker {
    background: transparent !important;
    border: none !important;
}

.custom-marker div {
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
}

.custom-marker div:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
}


</style>



<!-- Mapa Interactivo -->
<div class="card mb-1">
    <div class="card-body py-1">
        <div id="mapaMonitor" style="height: 25vh; min-height: 180px; max-height: 250px; width: 100%; border-radius: 8px; position: relative; z-index: 1;"></div>
    </div>
</div>



<!-- Tabla de Datos de Sensores -->
<div class="contenedor-sistema">
    <div class="contenedor-sistema-header">
        <div class="header-content">
            <div class="header-title">
                <i class="fas fa-table"></i>
                Historial de Datos de Sensores
            </div>
            <div class="header-search">
                <input type="text" class="form-control" id="busquedaGeneral" placeholder="Buscar por ID, dispositivo, mascota, propietario...">
                <input type="date" class="form-control" id="filtroFechaInicio" style="width: 120px; margin-left: 8px;">
                <input type="date" class="form-control" id="filtroFechaFin" style="width: 120px; margin-left: 8px;">
                <button type="button" class="btn-accion btn-ver" id="btnExportar" title="Exportar datos" style="margin-left: 8px;">
                    <i class="fas fa-download"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="contenedor-sistema-body">
        <table class="tabla-sistema" id="tablaDatos">
            <thead>
                <tr>
                    <th class="celda-id" onclick="ordenarTabla(0)">
                        ID <i class="fas fa-sort"></i>
                    </th>
                    <th onclick="ordenarTabla(1)">
                        Dispositivo <i class="fas fa-sort"></i>
                    </th>
                    <th onclick="ordenarTabla(2)">
                        Mascota <i class="fas fa-sort"></i>
                    </th>
                    <th onclick="ordenarTabla(3)">
                        Propietario <i class="fas fa-sort"></i>
                    </th>
                    <th class="texto-centrado" onclick="ordenarTabla(4)">
                        Temp. <i class="fas fa-sort"></i>
                    </th>
                    <th class="texto-centrado" onclick="ordenarTabla(5)">
                        BPM <i class="fas fa-sort"></i>
                    </th>
                    <th class="texto-centrado">Latitud</th>
                    <th class="texto-centrado">Longitud</th>
                    <th class="celda-fecha" onclick="ordenarTabla(8)">
                        Fecha <i class="fas fa-sort"></i>
                    </th>
                    <th class="texto-centrado" onclick="ordenarTabla(9)">
                        Bat. <i class="fas fa-sort"></i>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="10" class="texto-centrado">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        Cargando datos...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Scripts para el mapa -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script>
// Variables globales
let mapa;
let marcadores = [];


// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Pequeño retraso para asegurar que el contenedor del mapa esté listo
    setTimeout(() => {
        inicializarMapa();
        cargarTablaDatos();
        configurarEventos();
        iniciarActualizacionAutomatica();
        
        // Forzar actualización de iconos después de un retraso
        setTimeout(() => {
            if (mapa) {
                mapa.invalidateSize();
                // Limpiar y recargar marcadores
                marcadores.forEach(marker => mapa.removeLayer(marker));
                marcadores = [];
                actualizarMapaConDispositivos(datosTabla);
            }
        }, 500);
    }, 100);
});

// Inicializar mapa de Leaflet
function inicializarMapa() {
    const mapaContainer = document.getElementById('mapaMonitor');
    if (!mapaContainer) {
        console.error('Contenedor del mapa no encontrado');
        return;
    }
    
    try {
        mapa = L.map('mapaMonitor').setView([4.5709, -74.2973], 6); // Colombia
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(mapa);
        
        // Forzar el refresh del mapa después de un pequeño retraso
        setTimeout(() => {
            if (mapa) {
                mapa.invalidateSize();
            }
        }, 200);
        
        console.log('Mapa inicializado correctamente');
    } catch (error) {
        console.error('Error al inicializar el mapa:', error);
    }
}




// Actualizar mapa con dispositivos
function actualizarMapaConDispositivos(dispositivos) {
    // Limpiar marcadores existentes
    marcadores.forEach(marker => mapa.removeLayer(marker));
    marcadores = [];
    
    if (!dispositivos || dispositivos.length === 0) {
        return;
    }
    
    const bounds = L.latLngBounds();
    let hayMarcadores = false;
    
    dispositivos.forEach(dispositivo => {
        if (dispositivo.latitude && dispositivo.longitude) {
            const lat = parseFloat(dispositivo.latitude);
            const lng = parseFloat(dispositivo.longitude);
            
            // Crear icono personalizado según la especie usando HTML
            let iconHtml, iconColor;
            
            console.log('Creando marcador para:', dispositivo.mascota_nombre, 'Especie:', dispositivo.mascota_especie);
            
            if (dispositivo.mascota_especie === 'perro') {
                iconHtml = '<div style="background-color: #FF6B35; border: 2px solid #E55A2B; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: bold;">🐕</div>';
                iconColor = '#FF6B35';
                console.log('Icono de perro creado');
            } else if (dispositivo.mascota_especie === 'gato') {
                iconHtml = '<div style="background-color: #9B59B6; border: 2px solid #8E44AD; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: bold;">🐱</div>';
                iconColor = '#9B59B6';
                console.log('Icono de gato creado');
            } else {
                // Si no es perro ni gato, usar icono de perro por defecto
                iconHtml = '<div style="background-color: #FF6B35; border: 2px solid #E55A2B; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: bold;">🐕</div>';
                iconColor = '#FF6B35';
                console.log('Icono por defecto creado');
            }
            
            const icon = L.divIcon({
                html: iconHtml,
                className: 'custom-marker',
                iconSize: [40, 40],
                iconAnchor: [20, 40]
            });
            
            const marker = L.marker([lat, lng], { icon: icon }).addTo(mapa);
            
            // Crear popup con información
            const popupContent = `
                <div style="min-width: 250px;">
                    <h6><strong>${dispositivo.mascota_nombre || 'Sin nombre'}</strong></h6>
                    <p><strong>Propietario:</strong> ${dispositivo.usuario_nombre}</p>
                    <p><strong>Dispositivo:</strong> ${dispositivo.dispositivo_nombre}</p>
                    <p><strong>MAC:</strong> ${dispositivo.dispositivo_mac}</p>
                    <p><strong>Zona:</strong> ${dispositivo.zona || 'Cali'}</p>
                    <p><strong>Temp:</strong> ${dispositivo.temperatura}°C | <strong>BPM:</strong> ${dispositivo.bpm}</p>
                    <p><strong>Estado:</strong> 
                        <span class="badge bg-${dispositivo.bateria > 20 ? 'success' : 'danger'}">
                            ${dispositivo.bateria > 20 ? 'Activo' : 'Inactivo'} (${dispositivo.bateria}%)
                        </span>
                    </p>
                    <p><strong>Última fecha:</strong> ${formatearFecha(dispositivo.fecha)}</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="mostrarDetallesDato(${dispositivo.id})">
                        Ver Detalles Completos
                    </button>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            
            // Agregar evento de click para mostrar detalles completos
            marker.on('click', function() {
                // El popup se abre automáticamente, pero también podemos mostrar detalles
                setTimeout(() => {
                    mostrarDetallesDato(dispositivo.id);
                }, 100);
            });
            marcadores.push(marker);
            bounds.extend([lat, lng]);
            hayMarcadores = true;
        }
    });
    
    // Solo ajustar vista si hay marcadores y no es la primera carga
    if (hayMarcadores && marcadores.length > 0) {
        // Si el usuario está viendo una mascota específica, mantener esa vista
        if (vistaMapaPersonalizada && coordenadasSeleccionadas) {
            mapa.setView([coordenadasSeleccionadas.lat, coordenadasSeleccionadas.lng], 15);
            console.log('Manteniendo vista personalizada para:', coordenadasSeleccionadas.mascota);
        } else {
            // Mantener el zoom actual si ya hay marcadores, solo ajustar si es necesario
            if (marcadores.length === 1) {
                mapa.setView(bounds.getCenter(), Math.max(mapa.getZoom(), 10));
            } else {
                mapa.fitBounds(bounds, { padding: [20, 20] });
            }
        }
    }
}

// Cargar datos de la tabla
function cargarTablaDatos() {
    fetch(`${window.BASE_URL}monitor/getDatosTabla`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarTablaDatos(data.data);
            } else {
                console.error('Error al cargar datos:', data.message);
            }
        })
        .catch(error => {
            console.error('Error al cargar tabla:', error);
            // Mostrar mensaje de error en la tabla
            const tbody = document.querySelector('#tablaDatos tbody');
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error al cargar datos</td></tr>';
        });
}



// Renderizar datos en la tabla
function renderizarTablaDatos(datos) {
    const tbody = document.querySelector('#tablaDatos tbody');
    
    // Guardar datos para ordenamiento y búsqueda
    datosTabla = datos;
    
    if (!datos || datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="texto-centrado">No hay datos disponibles</td></tr>';
        return;
    }
    
    tbody.innerHTML = datos.map(dato => `
        <tr class="fila-dato ${filaSeleccionada === dato.id ? 'fila-seleccionada' : ''}" data-id="${dato.id}">
            <td class="celda-id">
                <span class="badge-estado badge-secondary">${dato.id || 'N/A'}</span>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <i class="fas fa-microchip" style="color: var(--primary); margin-right: 8px;"></i>
                    <span class="texto-truncado" title="${dato.dispositivo_nombre || 'N/A'}">${dato.dispositivo_nombre || 'N/A'}</span>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <i class="fas fa-paw" style="color: var(--warning); margin-right: 8px;"></i>
                    <span class="texto-truncado" title="${dato.mascota_nombre || 'Sin asignar'}">${dato.mascota_nombre || 'Sin asignar'}</span>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <i class="fas fa-user" style="color: var(--info); margin-right: 8px;"></i>
                    <span class="texto-truncado" title="${dato.usuario_nombre || 'N/A'}">${dato.usuario_nombre || 'N/A'}</span>
                </div>
            </td>
            <td class="texto-centrado">
                <span class="badge-estado badge-${dato.temperatura > 30 ? 'danger' : dato.temperatura > 25 ? 'warning' : 'success'}">
                    ${dato.temperatura ? dato.temperatura + '°' : 'N/A'}
                </span>
            </td>
            <td class="texto-centrado">
                <span class="badge-estado badge-info">
                    ${dato.bpm ? dato.bpm : 'N/A'}
                </span>
            </td>
            <td class="texto-centrado">
                <span class="badge-estado badge-success" style="font-size: 11px;">
                    ${dato.latitude ? parseFloat(dato.latitude).toFixed(4) : 'N/A'}
                </span>
            </td>
            <td class="texto-centrado">
                <span class="badge-estado badge-success" style="font-size: 11px;">
                    ${dato.longitude ? parseFloat(dato.longitude).toFixed(4) : 'N/A'}
                </span>
            </td>
            <td class="celda-fecha">
                <span style="font-size: 11px; color: var(--text-secondary);">
                    ${formatearFecha(dato.fecha)}
                </span>
            </td>
            <td class="texto-centrado">
                <span class="badge-estado badge-${dato.bateria > 80 ? 'success' : dato.bateria > 20 ? 'warning' : 'danger'}">
                    ${dato.bateria ? dato.bateria + '%' : 'N/A'}
                </span>
            </td>
        </tr>
    `).join('');
    
    // Agregar eventos a las filas
    document.querySelectorAll('.fila-dato').forEach(fila => {
        fila.addEventListener('click', function() {
            const id = this.dataset.id;
            
            // Marcar fila como seleccionada
            document.querySelectorAll('.fila-dato').forEach(f => f.classList.remove('fila-seleccionada'));
            this.classList.add('fila-seleccionada');
            filaSeleccionada = id;
            
            ubicarEnMapa(id);
        });
    });
    
    // Restaurar selección si existe
    if (filaSeleccionada) {
        const filaSeleccionadaElement = document.querySelector(`.fila-dato[data-id="${filaSeleccionada}"]`);
        if (filaSeleccionadaElement) {
            filaSeleccionadaElement.classList.add('fila-seleccionada');
        }
    }
}

// Configurar eventos de los botones
function configurarEventos() {
    // Botón exportar
    document.getElementById('btnExportar').addEventListener('click', exportarDatos);
    
    // Búsqueda en tiempo real
    const busquedaInput = document.getElementById('busquedaGeneral');
    busquedaInput.addEventListener('input', aplicarFiltrosSimplificados);
    
    // Limpiar filtros cuando se borre completamente
    busquedaInput.addEventListener('keyup', function(e) {
        if (e.key === 'Backspace' || e.key === 'Delete') {
            if (this.value === '') {
                aplicarFiltrosSimplificados();
            }
        }
    });
    
    // También detectar cuando se borre el contenido de otras formas
    busquedaInput.addEventListener('paste', function() {
        setTimeout(() => {
            if (this.value === '') {
                aplicarFiltrosSimplificados();
            }
        }, 10);
    });
    
    busquedaInput.addEventListener('cut', function() {
        setTimeout(() => {
            if (this.value === '') {
                aplicarFiltrosSimplificados();
            }
        }, 10);
    });
    
    // Filtros de fecha
    document.getElementById('filtroFechaInicio').addEventListener('change', aplicarFiltrosSimplificados);
    document.getElementById('filtroFechaFin').addEventListener('change', aplicarFiltrosSimplificados);
}

// Aplicar filtros simplificados
function aplicarFiltrosSimplificados() {
    const busqueda = document.getElementById('busquedaGeneral').value.toLowerCase().trim();
    const fechaInicio = document.getElementById('filtroFechaInicio').value;
    const fechaFin = document.getElementById('filtroFechaFin').value;
    
    // Si no hay filtros activos, mostrar todos los datos
    if (!busqueda && !fechaInicio && !fechaFin) {
        renderizarTablaDatos(datosTabla);
        actualizarMapaConDispositivos(datosTabla);
        return;
    }
    
    // Filtrar datos
    const datosFiltrados = datosTabla.filter(dato => {
        // Filtro de búsqueda general - buscar en ID, dispositivo, mascota, propietario
        if (busqueda) {
            const textoBusqueda = `${dato.id || ''} ${dato.dispositivo_nombre || ''} ${dato.mascota_nombre || ''} ${dato.usuario_nombre || ''}`.toLowerCase();
            if (!textoBusqueda.includes(busqueda)) {
                return false;
            }
        }
        
        // Filtro por fecha inicio
        if (fechaInicio && dato.fecha) {
            const fechaDato = new Date(dato.fecha);
            const fechaInicioDate = new Date(fechaInicio);
            if (fechaDato < fechaInicioDate) {
                return false;
            }
        }
        
        // Filtro por fecha fin
        if (fechaFin && dato.fecha) {
            const fechaDato = new Date(dato.fecha);
            const fechaFinDate = new Date(fechaFin + 'T23:59:59');
            if (fechaDato > fechaFinDate) {
                return false;
            }
        }
        
        return true;
    });
    
    // Actualizar tabla y mapa
    renderizarTablaDatos(datosFiltrados);
    actualizarMapaConDispositivos(datosFiltrados);
}



// Iniciar actualización automática
function iniciarActualizacionAutomatica() {
    // Actualizar tabla cada 10 segundos
    setInterval(() => {
        cargarTablaDatos();
    }, 10000);
    
    // Listener para resize de ventana
    window.addEventListener('resize', () => {
        if (mapa) {
            setTimeout(() => {
                mapa.invalidateSize();
            }, 100);
        }
    });
}

// Variables para ordenamiento
let ordenActual = { columna: -1, ascendente: true };
let datosTabla = [];
let filaSeleccionada = null; // Para mantener la fila seleccionada
let vistaMapaPersonalizada = false; // Para saber si el usuario está viendo una mascota específica
let coordenadasSeleccionadas = null; // Para mantener las coordenadas de la mascota seleccionada

// Funciones de utilidad
function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleString('es-ES');
}

// Ordenar tabla
function ordenarTabla(columna) {
    if (ordenActual.columna === columna) {
        ordenActual.ascendente = !ordenActual.ascendente;
    } else {
        ordenActual.columna = columna;
        ordenActual.ascendente = true;
    }
    
    // Actualizar iconos de ordenamiento
    document.querySelectorAll('th i.fas').forEach(icon => {
        icon.className = 'fas fa-sort';
    });
    
    const th = document.querySelector(`th:nth-child(${columna + 1}) i`);
    if (th) {
        th.className = ordenActual.ascendente ? 'fas fa-sort-up' : 'fas fa-sort-down';
    }
    
    // Ordenar datos
    datosTabla.sort((a, b) => {
        let valorA, valorB;
        
        switch(columna) {
            case 0: // ID
                valorA = parseInt(a.id) || 0;
                valorB = parseInt(b.id) || 0;
                break;
            case 1: // Dispositivo
                valorA = (a.dispositivo_nombre || '').toLowerCase();
                valorB = (b.dispositivo_nombre || '').toLowerCase();
                break;
            case 2: // Mascota
                valorA = (a.mascota_nombre || '').toLowerCase();
                valorB = (b.mascota_nombre || '').toLowerCase();
                break;
            case 3: // Propietario
                valorA = (a.usuario_nombre || '').toLowerCase();
                valorB = (b.usuario_nombre || '').toLowerCase();
                break;
            case 4: // Temperatura
                valorA = parseFloat(a.temperatura) || 0;
                valorB = parseFloat(b.temperatura) || 0;
                break;
            case 5: // BPM
                valorA = parseFloat(a.bpm) || 0;
                valorB = parseFloat(b.bpm) || 0;
                break;
            case 8: // Fecha
                valorA = new Date(a.fecha || 0);
                valorB = new Date(b.fecha || 0);
                break;
            case 9: // Batería
                valorA = parseFloat(a.bateria) || 0;
                valorB = parseFloat(b.bateria) || 0;
                break;
            default:
                return 0;
        }
        
        if (valorA < valorB) return ordenActual.ascendente ? -1 : 1;
        if (valorA > valorB) return ordenActual.ascendente ? 1 : -1;
        return 0;
    });
    
    renderizarTablaDatos(datosTabla);
}



// Ubicar en mapa al hacer click en fila
function ubicarEnMapa(id) {
    const dato = datosTabla.find(d => d.id == id);
    if (!dato || !dato.latitude || !dato.longitude) {
        Swal.fire('Error', 'No hay ubicación disponible para este registro', 'warning');
        return;
    }
    
    // Guardar estado de vista personalizada
    vistaMapaPersonalizada = true;
    coordenadasSeleccionadas = {
        lat: parseFloat(dato.latitude),
        lng: parseFloat(dato.longitude),
        mascota: dato.mascota_nombre || 'Sin asignar'
    };
    
    // Centrar mapa en la ubicación
    mapa.setView([coordenadasSeleccionadas.lat, coordenadasSeleccionadas.lng], 15);
    
    // Forzar actualización del mapa
    setTimeout(() => {
        mapa.invalidateSize();
    }, 100);
    
    // Mostrar notificación
    Swal.fire({
        title: 'Ubicación Centrada',
        text: `Mascota: ${coordenadasSeleccionadas.mascota}`,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });
}

// Mostrar detalles de un dato (usado para marcadores del mapa)
function mostrarDetallesDato(id) {
    const dato = datosTabla.find(d => d.id == id);
    if (!dato) return;
    
    Swal.fire({
        title: 'Detalles del Registro',
        html: `
            <div class="text-start">
                <p><strong>ID:</strong> ${dato.id}</p>
                <p><strong>Dispositivo:</strong> ${dato.dispositivo_nombre}</p>
                <p><strong>MAC:</strong> ${dato.dispositivo_mac}</p>
                <p><strong>Mascota:</strong> ${dato.mascota_nombre || 'Sin asignar'}</p>
                <p><strong>Especie:</strong> ${dato.mascota_especie}</p>
                <p><strong>Propietario:</strong> ${dato.usuario_nombre}</p>
                <p><strong>Zona:</strong> ${dato.zona || 'Cali'}</p>
                <p><strong>Temperatura:</strong> ${dato.temperatura ? dato.temperatura + '°C' : 'N/A'}</p>
                <p><strong>BPM:</strong> ${dato.bpm ? dato.bpm + ' BPM' : 'N/A'}</p>
                <p><strong>Batería:</strong> ${dato.bateria ? dato.bateria + '%' : 'N/A'}</p>
                <p><strong>Ubicación:</strong> ${dato.latitude && dato.longitude ? 
                    `${parseFloat(dato.latitude).toFixed(6)}, ${parseFloat(dato.longitude).toFixed(6)}` : 'N/A'}</p>
                <p><strong>Fecha:</strong> ${formatearFecha(dato.fecha)}</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Cerrar'
    });
}

// Exportar datos
function exportarDatos() {
    if (!datosTabla || datosTabla.length === 0) {
        Swal.fire('Error', 'No hay datos para exportar', 'warning');
        return;
    }
    
    // Crear CSV
    const headers = ['ID', 'Dispositivo', 'MAC', 'Mascota', 'Especie', 'Propietario', 'Zona', 'Temperatura', 'BPM', 'Latitud', 'Longitud', 'Fecha', 'Batería'];
    const csvContent = [
        headers.join(','),
        ...datosTabla.map(dato => [
            dato.id || '',
            dato.dispositivo_nombre || '',
            dato.dispositivo_mac || '',
            dato.mascota_nombre || '',
            dato.mascota_especie || '',
            dato.usuario_nombre || '',
            dato.zona || 'Cali',
            dato.temperatura || '',
            dato.bpm || '',
            dato.latitude || '',
            dato.longitude || '',
            dato.fecha || '',
            dato.bateria || ''
        ].join(','))
    ].join('\n');
    
    // Descargar archivo
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `datos_sensores_cali_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    Swal.fire('Éxito', 'Datos exportados correctamente', 'success');
}
</script> 