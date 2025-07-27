// Monitor Dashboard JavaScript
// Variables globales
let mapa;
let marcadores = new Map();
let intervaloActualizacion;
let paginaActual = 1;
let dispositivoSeleccionado = null;

// Configuración del mapa
const configuracionMapa = {
    centro: [4.5709, -74.2973], // Bogotá, Colombia
    zoom: 10,
    maxZoom: 18
};

// Iconos personalizados para marcadores
const iconosMarcadores = {
    perro: L.divIcon({
        className: 'marcador-mascota marcador-perro',
        html: '🐕',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    }),
    gato: L.divIcon({
        className: 'marcador-mascota marcador-gato',
        html: '🐱',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    }),
    default: L.divIcon({
        className: 'marcador-mascota marcador-default',
        html: '📍',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    })
};

// Inicialización del monitor
function inicializarMonitor() {
    console.log('Inicializando Monitor Dashboard...');
    
    inicializarMapa();
    cargarPropietarios();
    cargarTablaDatos();
    iniciarActualizacionAutomatica();
    
    console.log('Monitor Dashboard inicializado correctamente');
}

// Inicializar mapa Leaflet
function inicializarMapa() {
    if (mapa) {
        mapa.remove();
    }
    
    mapa = L.map('mapaMonitor').setView(configuracionMapa.centro, configuracionMapa.zoom);
    
    // Agregar capa de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: configuracionMapa.maxZoom
    }).addTo(mapa);
    
    // Evento para centrar mapa cuando se hace zoom
    mapa.on('zoomend', function() {
        actualizarMarcadoresVisibles();
    });
}

// Cargar lista de propietarios
async function cargarPropietarios() {
    try {
        const response = await fetch(`${window.BASE_URL}monitor/getPropietarios`);
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('filtroPropietario');
            select.innerHTML = '<option value="">Todos los propietarios</option>';
            
            data.propietarios.forEach(propietario => {
                const option = document.createElement('option');
                option.value = propietario.id_usuario;
                option.textContent = propietario.nombre;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar propietarios:', error);
        mostrarError('Error al cargar la lista de propietarios');
    }
}

// Cargar mascotas por propietario
async function cargarMascotas() {
    const propietarioId = document.getElementById('filtroPropietario').value;
    const select = document.getElementById('filtroMascota');
    
    try {
        let url = `${window.BASE_URL}monitor/getMascotasPorPropietario`;
        if (propietarioId) {
            url += `?propietario_id=${propietarioId}`;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        select.innerHTML = '<option value="">Todas las mascotas</option>';
        
        if (data.success) {
            data.mascotas.forEach(mascota => {
                const option = document.createElement('option');
                option.value = mascota.id_mascota;
                option.textContent = `${mascota.nombre} (${mascota.especie})`;
                select.appendChild(option);
            });
        }
        
        aplicarFiltros();
    } catch (error) {
        console.error('Error al cargar mascotas:', error);
        mostrarError('Error al cargar la lista de mascotas');
    }
}

// Aplicar filtros y actualizar mapa
async function aplicarFiltros() {
    const propietarioId = document.getElementById('filtroPropietario').value;
    const mascotaId = document.getElementById('filtroMascota').value;
    const mac = document.getElementById('filtroMAC').value;
    const soloActivos = document.getElementById('filtroSoloActivos').checked;
    
    try {
        const params = new URLSearchParams();
        if (propietarioId) params.append('propietario_id', propietarioId);
        if (mascotaId) params.append('mascota_id', mascotaId);
        if (mac) params.append('mac', mac);
        if (soloActivos) params.append('solo_activos', 'true');
        
        const response = await fetch(`${window.BASE_URL}monitor/getDatosFiltrados?${params}`);
        const data = await response.json();
        
        if (data.success) {
            actualizarMapaConDispositivos(data.dispositivos);
            dispositivoSeleccionado = mascotaId ? mascotaId : null;
        }
    } catch (error) {
        console.error('Error al aplicar filtros:', error);
        mostrarError('Error al aplicar los filtros');
    }
}

// Actualizar mapa con dispositivos filtrados
function actualizarMapaConDispositivos(dispositivos) {
    // Limpiar marcadores existentes
    marcadores.forEach(marcador => mapa.removeLayer(marcador));
    marcadores.clear();
    
    if (dispositivos.length === 0) {
        mostrarMensaje('No se encontraron dispositivos con los filtros aplicados');
        return;
    }
    
    const bounds = L.latLngBounds();
    let dispositivosConUbicacion = 0;
    
    dispositivos.forEach(dispositivo => {
        if (dispositivo.latitude && dispositivo.longitude) {
            const lat = parseFloat(dispositivo.latitude);
            const lng = parseFloat(dispositivo.longitude);
            
            // Determinar icono según especie
            const especie = dispositivo.mascota_especie?.toLowerCase() || 'default';
            const icono = iconosMarcadores[especie] || iconosMarcadores.default;
            
            // Crear marcador
            const marcador = L.marker([lat, lng], { icon: icono })
                .addTo(mapa)
                .bindPopup(crearPopupDispositivo(dispositivo));
            
            marcadores.set(dispositivo.id_dispositivo, marcador);
            bounds.extend([lat, lng]);
            dispositivosConUbicacion++;
        }
    });
    
    // Ajustar vista del mapa
    if (dispositivosConUbicacion > 0) {
        if (dispositivosConUbicacion === 1) {
            mapa.setView(bounds.getCenter(), 15);
        } else {
            mapa.fitBounds(bounds, { padding: [20, 20] });
        }
    }
    
    // Si hay un dispositivo seleccionado, centrar en él
    if (dispositivoSeleccionado) {
        const marcador = marcadores.get(parseInt(dispositivoSeleccionado));
        if (marcador) {
            mapa.setView(marcador.getLatLng(), 15);
            marcador.openPopup();
        }
    }
}

// Crear contenido del popup del dispositivo
function crearPopupDispositivo(dispositivo) {
    const estadoClass = dispositivo.estado === 'activo' ? 'text-success' : 'text-danger';
    const bateriaClass = dispositivo.bateria_sensor > 20 ? 'text-success' : 'text-warning';
    
    return `
        <div class="popup-dispositivo">
            <h6><strong>${dispositivo.nombre}</strong></h6>
            <p><strong>MAC:</strong> ${dispositivo.mac}</p>
            <p><strong>Mascota:</strong> ${dispositivo.mascota_nombre || 'Sin mascota'}</p>
            <p><strong>Propietario:</strong> ${dispositivo.usuario_nombre}</p>
            <p><strong>Estado:</strong> <span class="${estadoClass}">${dispositivo.estado}</span></p>
            ${dispositivo.temperatura ? `<p><strong>Temperatura:</strong> ${dispositivo.temperatura}°C</p>` : ''}
            ${dispositivo.bpm ? `<p><strong>BPM:</strong> ${dispositivo.bpm}</p>` : ''}
            ${dispositivo.bateria_sensor ? `<p><strong>Batería:</strong> <span class="${bateriaClass}">${dispositivo.bateria_sensor}%</span></p>` : ''}
            ${dispositivo.ultima_fecha ? `<p><strong>Última actualización:</strong> ${formatearFecha(dispositivo.ultima_fecha)}</p>` : ''}
            <div class="popup-actions">
                <button class="btn btn-sm btn-primary" onclick="verDispositivo(${dispositivo.id_dispositivo})">
                    <i class="fas fa-chart-line"></i> Ver Detalles
                </button>
            </div>
        </div>
    `;
}

// Cargar tabla de datos
async function cargarTablaDatos() {
    const limite = document.getElementById('limiteDatos').value;
    const tbody = document.getElementById('tablaDatosBody');
    
    try {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    Cargando datos...
                </td>
            </tr>
        `;
        
        const params = new URLSearchParams({
            limite: limite,
            pagina: paginaActual
        });
        
        if (dispositivoSeleccionado) {
            params.append('dispositivo_id', dispositivoSeleccionado);
        }
        
        const response = await fetch(`${window.BASE_URL}monitor/getDatosTabla?${params}`);
        const data = await response.json();
        
        if (data.success) {
            renderizarTablaDatos(data.datos);
        } else {
            mostrarError(data.error || 'Error al cargar los datos');
        }
    } catch (error) {
        console.error('Error al cargar tabla de datos:', error);
        mostrarError('Error al cargar los datos de la tabla');
    }
}

// Renderizar tabla de datos
function renderizarTablaDatos(datos) {
    const tbody = document.getElementById('tablaDatosBody');
    
    if (datos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center text-muted">
                    <i class="fas fa-inbox"></i>
                    No se encontraron datos
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = datos.map(dato => `
        <tr>
            <td>${dato.id}</td>
            <td>
                <strong>${dato.dispositivo_nombre}</strong><br>
                <small class="text-muted">${dato.mac}</small>
            </td>
            <td>
                ${dato.mascota_nombre ? `
                    <strong>${dato.mascota_nombre}</strong><br>
                    <small class="text-muted">${dato.mascota_especie}</small>
                ` : '<span class="text-muted">Sin mascota</span>'}
            </td>
            <td>${formatearFecha(dato.fecha)}</td>
            <td>
                ${dato.latitude && dato.longitude ? 
                    `${dato.latitude.toFixed(6)}, ${dato.longitude.toFixed(6)}` : 
                    '<span class="text-muted">No disponible</span>'}
            </td>
            <td>${dato.altitude ? `${dato.altitude}m` : '-'}</td>
            <td>${dato.speed ? `${dato.speed} km/h` : '-'}</td>
            <td>
                <span class="badge bg-${dato.bpm > 100 ? 'danger' : dato.bpm > 80 ? 'warning' : 'success'}">
                    ${dato.bpm} BPM
                </span>
            </td>
            <td>
                <span class="badge bg-${dato.temperatura > 39 ? 'danger' : 'info'}">
                    ${dato.temperatura}°C
                </span>
            </td>
            <td>
                <span class="badge bg-${dato.bateria > 20 ? 'success' : 'warning'}">
                    ${dato.bateria}%
                </span>
            </td>
        </tr>
    `).join('');
}

// Funciones de utilidad
function formatearFecha(fecha) {
    return new Date(fecha).toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function mostrarError(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        confirmButtonText: 'Aceptar'
    });
}

function mostrarMensaje(mensaje) {
    Swal.fire({
        icon: 'info',
        title: 'Información',
        text: mensaje,
        confirmButtonText: 'Aceptar'
    });
}

// Funciones de control
function actualizarDatos() {
    aplicarFiltros();
    cargarTablaDatos();
}

function limpiarFiltros() {
    document.getElementById('filtroPropietario').value = '';
    document.getElementById('filtroMascota').value = '';
    document.getElementById('filtroMAC').value = '';
    document.getElementById('filtroSoloActivos').checked = false;
    
    cargarMascotas();
}

function centrarMapa() {
    if (marcadores.size > 0) {
        const bounds = L.latLngBounds();
        marcadores.forEach(marcador => bounds.extend(marcador.getLatLng()));
        mapa.fitBounds(bounds, { padding: [20, 20] });
    } else {
        mapa.setView(configuracionMapa.centro, configuracionMapa.zoom);
    }
}

function toggleFullscreen() {
    const mapaContainer = document.getElementById('mapaMonitor');
    if (!document.fullscreenElement) {
        mapaContainer.requestFullscreen().then(() => {
            setTimeout(() => mapa.invalidateSize(), 100);
        });
    } else {
        document.exitFullscreen().then(() => {
            setTimeout(() => mapa.invalidateSize(), 100);
        });
    }
}

function verDispositivo(dispositivoId) {
    window.location.href = `${window.BASE_URL}monitor/device/${dispositivoId}`;
}

// Actualización automática
function iniciarActualizacionAutomatica() {
    if (intervaloActualizacion) {
        clearInterval(intervaloActualizacion);
    }
    
    // Actualizar cada 30 segundos
    intervaloActualizacion = setInterval(() => {
        actualizarDatos();
    }, 30000);
}

// Limpiar al salir de la página
window.addEventListener('beforeunload', () => {
    if (intervaloActualizacion) {
        clearInterval(intervaloActualizacion);
    }
});

// Estilos para marcadores
const estilosMarcadores = `
<style>
.marcador-mascota {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    border-radius: 50%;
    background: white;
    border: 2px solid #007bff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.marcador-mascota:hover {
    transform: scale(1.2);
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.marcador-perro {
    border-color: #28a745;
}

.marcador-gato {
    border-color: #ffc107;
}

.popup-dispositivo {
    min-width: 200px;
}

.popup-dispositivo h6 {
    margin-bottom: 0.5rem;
    color: #007bff;
}

.popup-dispositivo p {
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.popup-actions {
    margin-top: 0.5rem;
    text-align: center;
}
</style>
`;

// Insertar estilos en el head
document.head.insertAdjacentHTML('beforeend', estilosMarcadores); 