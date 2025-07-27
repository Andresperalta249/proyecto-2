<?php
// Definir BASE_URL para el JavaScript
echo '<script>window.BASE_URL = "' . BASE_URL . '";</script>';
?>

<h2 class="mb-4">
    <i class="fas fa-desktop"></i> Monitor IoT de Mascotas
</h2>

<!-- Filtros Avanzados -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-2">
                <label for="filtroPropietario" class="form-label">Propietario</label>
                <select class="form-select" id="filtroPropietario">
                    <option value="">Todos los propietarios</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtroMascota" class="form-label">Mascota</label>
                <select class="form-select" id="filtroMascota">
                    <option value="">Todas las mascotas</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtroEspecie" class="form-label">Especie</label>
                <select class="form-select" id="filtroEspecie">
                    <option value="">Todas las especies</option>
                    <option value="perro">Perro</option>
                    <option value="gato">Gato</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtroBateria" class="form-label">Estado Batería</label>
                <select class="form-select" id="filtroBateria">
                    <option value="">Todos</option>
                    <option value="alta">Alta (>80%)</option>
                    <option value="media">Media (20-80%)</option>
                    <option value="baja">Baja (<20%)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtroMAC" class="form-label">MAC del Dispositivo</label>
                <input type="text" class="form-control" id="filtroMAC" placeholder="Buscar por MAC...">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="button" class="btn btn-primary" id="btnSoloActivos">
                        <i class="fas fa-filter"></i> Solo Activos
                    </button>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <label for="filtroFechaInicio" class="form-label">Fecha Inicio</label>
                <input type="date" class="form-control" id="filtroFechaInicio">
            </div>
            <div class="col-md-3">
                <label for="filtroFechaFin" class="form-label">Fecha Fin</label>
                <input type="date" class="form-control" id="filtroFechaFin">
            </div>
            <div class="col-md-3">
                <label for="filtroBusqueda" class="form-label">Búsqueda</label>
                <input type="text" class="form-control" id="filtroBusqueda" placeholder="Buscar por nombre...">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="button" class="btn btn-warning" id="btnExportar">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <button type="button" class="btn btn-secondary" id="btnLimpiarFiltros">
                    <i class="fas fa-times"></i> Limpiar Filtros
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mapa Interactivo -->
<div class="card mb-3">
    <div class="card-body">
        <div id="mapaMonitor" style="height: 300px; width: 100%; border-radius: 8px;"></div>
    </div>
</div>

<!-- Tabla de Datos de Sensores -->
<div class="card" style="margin: 0; border-radius: 0;">
    <div class="card-header d-flex justify-content-between align-items-center" style="border-radius: 0;">
        <h5 class="card-title mb-0">
            <i class="fas fa-table"></i> Historial de Datos de Sensores
        </h5>
        <div class="d-flex gap-2">
            <div class="input-group" style="width: 200px;">
                <input type="text" class="form-control form-control-sm" id="busquedaTabla" placeholder="Buscar en tabla...">
                <button class="btn btn-outline-secondary btn-sm" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnActualizarTabla">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive" style="margin: 0;">
            <table class="table table-striped table-hover mb-0" id="tablaDatos" style="margin: 0;">
                <thead class="table-dark">
                    <tr>
                        <th style="cursor: pointer;" onclick="ordenarTabla(0)">
                            ID <i class="fas fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="ordenarTabla(1)">
                            Dispositivo <i class="fas fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="ordenarTabla(2)">
                            Mascota <i class="fas fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="ordenarTabla(3)">
                            Propietario <i class="fas fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="ordenarTabla(4)">
                            Temperatura <i class="fas fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="ordenarTabla(5)">
                            BPM <i class="fas fa-sort"></i>
                        </th>
                        <th>Latitud</th>
                        <th>Longitud</th>
                        <th style="cursor: pointer;" onclick="ordenarTabla(8)">
                            Fecha <i class="fas fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="ordenarTabla(9)">
                            Batería <i class="fas fa-sort"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
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
        <!-- Paginación -->
        <nav aria-label="Paginación de datos">
            <ul class="pagination justify-content-center" id="paginacion">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Anterior</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item disabled">
                    <a class="page-link" href="#">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<!-- Scripts para el mapa -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script>
// Variables globales
let mapa;
let marcadores = [];
let soloActivos = false;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarMapa();
    cargarPropietarios();
    cargarTablaDatos();
    configurarEventos();
    iniciarActualizacionAutomatica();
});

// Inicializar mapa de Leaflet
function inicializarMapa() {
    mapa = L.map('mapaMonitor').setView([4.5709, -74.2973], 6); // Colombia
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapa);
}

// Cargar propietarios en el filtro
function cargarPropietarios() {
    fetch(`${window.BASE_URL}monitor/getPropietarios`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('filtroPropietario');
                select.innerHTML = '<option value="">Todos los propietarios</option>';
                
                data.data.forEach(propietario => {
                    const option = document.createElement('option');
                    option.value = propietario.usuario_id;
                    option.textContent = propietario.usuario_nombre;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar propietarios:', error);
        });
}

// Cargar mascotas por propietario
function cargarMascotas(propietarioId = '') {
    const select = document.getElementById('filtroMascota');
    select.innerHTML = '<option value="">Todas las mascotas</option>';
    
    if (!propietarioId) return;
    
    fetch(`${window.BASE_URL}monitor/getMascotasPorPropietario?propietario=${propietarioId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.data.forEach(mascota => {
                    const option = document.createElement('option');
                    option.value = mascota.id_mascota;
                    option.textContent = mascota.nombre;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar mascotas:', error);
        });
}

// Variables globales para filtros
let filtrosActuales = {
    propietario: '',
    mascota: '',
    especie: '',
    bateria: '',
    mac: '',
    fechaInicio: '',
    fechaFin: '',
    busqueda: '',
    soloActivos: false
};

// Aplicar filtros y actualizar mapa
function aplicarFiltros() {
    // Recoger todos los filtros
    filtrosActuales = {
        propietario: document.getElementById('filtroPropietario').value,
        mascota: document.getElementById('filtroMascota').value,
        especie: document.getElementById('filtroEspecie').value,
        bateria: document.getElementById('filtroBateria').value,
        mac: document.getElementById('filtroMAC').value,
        fechaInicio: document.getElementById('filtroFechaInicio').value,
        fechaFin: document.getElementById('filtroFechaFin').value,
        busqueda: document.getElementById('filtroBusqueda').value,
        soloActivos: soloActivos
    };
    
    const params = new URLSearchParams(filtrosActuales);
    
    fetch(`${window.BASE_URL}monitor/getDatosFiltrados?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarMapaConDispositivos(data.data);
            }
        })
        .catch(error => {
            console.error('Error al aplicar filtros:', error);
        });
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
    
    dispositivos.forEach(dispositivo => {
        if (dispositivo.latitude && dispositivo.longitude) {
            const lat = parseFloat(dispositivo.latitude);
            const lng = parseFloat(dispositivo.longitude);
            
            // Crear icono personalizado según la especie
            let iconUrl = `${window.BASE_URL}assets/img/paw-default.svg`;
            if (dispositivo.mascota_especie === 'perro') {
                iconUrl = `${window.BASE_URL}assets/img/paw-dog.svg`;
            } else if (dispositivo.mascota_especie === 'gato') {
                iconUrl = `${window.BASE_URL}assets/img/paw-cat.svg`;
            }
            
            const icon = L.icon({
                iconUrl: iconUrl,
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });
            
            const marker = L.marker([lat, lng], { icon: icon }).addTo(mapa);
            
            // Crear popup con información
            const popupContent = `
                <div style="min-width: 200px;">
                    <h6><strong>${dispositivo.mascota_nombre || 'Sin nombre'}</strong></h6>
                    <p><strong>Propietario:</strong> ${dispositivo.usuario_nombre}</p>
                    <p><strong>Dispositivo:</strong> ${dispositivo.nombre}</p>
                    <p><strong>MAC:</strong> ${dispositivo.mac}</p>
                    <p><strong>Estado:</strong> 
                        <span class="badge bg-${dispositivo.estado === 'activo' ? 'success' : 'danger'}">
                            ${dispositivo.estado}
                        </span>
                    </p>
                    <p><strong>Última fecha:</strong> ${dispositivo.ultima_fecha || 'N/A'}</p>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            marcadores.push(marker);
            bounds.extend([lat, lng]);
        }
    });
    
    // Ajustar vista del mapa
    if (marcadores.length > 0) {
        mapa.fitBounds(bounds, { padding: [20, 20] });
    }
}

// Cargar datos de la tabla
function cargarTablaDatos() {
    fetch(`${window.BASE_URL}monitor/getDatosTabla`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarTablaDatos(data.data);
            }
        })
        .catch(error => {
            console.error('Error al cargar tabla:', error);
        });
}

// Renderizar datos en la tabla
function renderizarTablaDatos(datos) {
    const tbody = document.querySelector('#tablaDatos tbody');
    
    // Guardar datos para ordenamiento y búsqueda
    datosTabla = datos;
    
    if (!datos || datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">No hay datos disponibles</td></tr>';
        return;
    }
    
    tbody.innerHTML = datos.map(dato => `
        <tr class="fila-dato" data-id="${dato.id}">
            <td class="text-center">
                <span class="badge bg-secondary">${dato.id || 'N/A'}</span>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <i class="fas fa-microchip text-primary me-2"></i>
                    <span class="fw-medium">${dato.dispositivo_nombre || 'N/A'}</span>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <i class="fas fa-paw text-warning me-2"></i>
                    <span>${dato.mascota_nombre || 'Sin asignar'}</span>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <i class="fas fa-user text-info me-2"></i>
                    <span>${dato.usuario_nombre || 'N/A'}</span>
                </div>
            </td>
            <td class="text-center">
                <span class="badge bg-${dato.temperatura > 30 ? 'danger' : dato.temperatura > 25 ? 'warning' : 'success'}">
                    ${dato.temperatura ? dato.temperatura + '°C' : 'N/A'}
                </span>
            </td>
            <td class="text-center">
                <span class="badge bg-info">
                    ${dato.bpm ? dato.bpm + ' BPM' : 'N/A'}
                </span>
            </td>
            <td class="text-muted small">${dato.latitude ? parseFloat(dato.latitude).toFixed(6) : 'N/A'}</td>
            <td class="text-muted small">${dato.longitude ? parseFloat(dato.longitude).toFixed(6) : 'N/A'}</td>
            <td class="text-muted small">${formatearFecha(dato.fecha)}</td>
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="progress me-2" style="width: 40px; height: 6px;">
                        <div class="progress-bar bg-${dato.bateria > 80 ? 'success' : dato.bateria > 20 ? 'warning' : 'danger'}" 
                             style="width: ${dato.bateria || 0}%"></div>
                    </div>
                    <span class="badge bg-${dato.bateria > 80 ? 'success' : dato.bateria > 20 ? 'warning' : 'danger'}">
                        ${dato.bateria ? dato.bateria + '%' : 'N/A'}
                    </span>
                </div>
            </td>
        </tr>
    `).join('');
    
    // Agregar eventos a las filas
    document.querySelectorAll('.fila-dato').forEach(fila => {
        fila.addEventListener('click', function() {
            const id = this.dataset.id;
            mostrarDetallesDato(id);
        });
    });
}

// Configurar eventos de los botones
function configurarEventos() {
    // Filtro de propietario
    document.getElementById('filtroPropietario').addEventListener('change', function() {
        cargarMascotas(this.value);
    });
    
    // Botón limpiar filtros
    document.getElementById('btnLimpiarFiltros').addEventListener('click', function() {
        // Limpiar todos los filtros
        document.getElementById('filtroPropietario').value = '';
        document.getElementById('filtroMascota').value = '';
        document.getElementById('filtroEspecie').value = '';
        document.getElementById('filtroBateria').value = '';
        document.getElementById('filtroMAC').value = '';
        document.getElementById('filtroFechaInicio').value = '';
        document.getElementById('filtroFechaFin').value = '';
        document.getElementById('filtroBusqueda').value = '';
        soloActivos = false;
        document.getElementById('btnSoloActivos').classList.remove('btn-success');
        document.getElementById('btnSoloActivos').classList.add('btn-primary');
        aplicarFiltros();
    });
    
    // Botón solo activos
    document.getElementById('btnSoloActivos').addEventListener('click', function() {
        soloActivos = !soloActivos;
        if (soloActivos) {
            this.classList.remove('btn-primary');
            this.classList.add('btn-success');
        } else {
            this.classList.remove('btn-success');
            this.classList.add('btn-primary');
        }
        aplicarFiltros();
    });
    
    // Botón actualizar tabla
    document.getElementById('btnActualizarTabla').addEventListener('click', cargarTablaDatos);
    
    // Botón exportar
    document.getElementById('btnExportar').addEventListener('click', exportarDatos);
    
    // Búsqueda en tabla
    document.getElementById('busquedaTabla').addEventListener('input', function() {
        buscarEnTabla(this.value);
    });
    
    // Filtros automáticos (se aplican automáticamente)
    ['filtroEspecie', 'filtroBateria', 'filtroMAC', 'filtroFechaInicio', 'filtroFechaFin', 'filtroBusqueda'].forEach(id => {
        document.getElementById(id).addEventListener('change', aplicarFiltros);
    });
}

// Iniciar actualización automática
function iniciarActualizacionAutomatica() {
    // Actualizar mapa y filtros cada 30 segundos
    setInterval(() => {
        aplicarFiltros();
    }, 30000);
    
    // Actualizar tabla cada 10 segundos
    setInterval(() => {
        cargarTablaDatos();
    }, 10000);
}

// Variables para ordenamiento
let ordenActual = { columna: -1, ascendente: true };
let datosTabla = [];

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

// Buscar en tabla
function buscarEnTabla(termino) {
    if (!termino) {
        renderizarTablaDatos(datosTabla);
        return;
    }
    
    const filtrados = datosTabla.filter(dato => {
        const texto = `${dato.dispositivo_nombre} ${dato.mascota_nombre} ${dato.usuario_nombre}`.toLowerCase();
        return texto.includes(termino.toLowerCase());
    });
    
    renderizarTablaDatos(filtrados);
}

// Mostrar detalles de un dato
function mostrarDetallesDato(id) {
    const dato = datosTabla.find(d => d.id == id);
    if (!dato) return;
    
    Swal.fire({
        title: 'Detalles del Registro',
        html: `
            <div class="text-start">
                <p><strong>ID:</strong> ${dato.id}</p>
                <p><strong>Dispositivo:</strong> ${dato.dispositivo_nombre}</p>
                <p><strong>Mascota:</strong> ${dato.mascota_nombre || 'Sin asignar'}</p>
                <p><strong>Propietario:</strong> ${dato.usuario_nombre}</p>
                <p><strong>Temperatura:</strong> ${dato.temperatura ? dato.temperatura + '°C' : 'N/A'}</p>
                <p><strong>BPM:</strong> ${dato.bpm ? dato.bpm + ' BPM' : 'N/A'}</p>
                <p><strong>Batería:</strong> ${dato.bateria ? dato.bateria + '%' : 'N/A'}</p>
                <p><strong>Ubicación:</strong> ${dato.latitude && dato.longitude ? 
                    `${parseFloat(dato.latitude).toFixed(6)}, ${parseFloat(dato.longitude).toFixed(6)}` : 'N/A'}</p>
                <p><strong>Fecha:</strong> ${formatearFecha(dato.fecha)}</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Cerrar',
        showCancelButton: true,
        cancelButtonText: 'Ver en Mapa'
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.cancel) {
            // Centrar mapa en la ubicación
            if (dato.latitude && dato.longitude) {
                mapa.setView([parseFloat(dato.latitude), parseFloat(dato.longitude)], 15);
            }
        }
    });
}

// Exportar datos
function exportarDatos() {
    if (!datosTabla || datosTabla.length === 0) {
        Swal.fire('Error', 'No hay datos para exportar', 'warning');
        return;
    }
    
    // Crear CSV
    const headers = ['ID', 'Dispositivo', 'Mascota', 'Propietario', 'Temperatura', 'BPM', 'Latitud', 'Longitud', 'Fecha', 'Batería'];
    const csvContent = [
        headers.join(','),
        ...datosTabla.map(dato => [
            dato.id || '',
            dato.dispositivo_nombre || '',
            dato.mascota_nombre || '',
            dato.usuario_nombre || '',
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
    link.setAttribute('download', `datos_sensores_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    Swal.fire('Éxito', 'Datos exportados correctamente', 'success');
}
</script> 