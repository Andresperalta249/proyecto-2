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
            mostrarError('Error al cargar propietarios');
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

// Aplicar filtros y actualizar mapa
function aplicarFiltros() {
    const propietario = document.getElementById('filtroPropietario').value;
    const mascota = document.getElementById('filtroMascota').value;
    const mac = document.getElementById('filtroMAC').value;
    
    const params = new URLSearchParams({
        propietario: propietario,
        mascota: mascota,
        mac: mac,
        soloActivos: soloActivos
    });
    
    fetch(`${window.BASE_URL}monitor/getDatosFiltrados?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarMapaConDispositivos(data.data);
            } else {
                mostrarError('Error al obtener dispositivos filtrados');
            }
        })
        .catch(error => {
            console.error('Error al aplicar filtros:', error);
            mostrarError('Error al aplicar filtros');
        });
}

// Actualizar mapa con dispositivos
function actualizarMapaConDispositivos(dispositivos) {
    // Limpiar marcadores existentes
    marcadores.forEach(marker => mapa.removeLayer(marker));
    marcadores = [];
    
    if (!dispositivos || dispositivos.length === 0) {
        mostrarInfo('No se encontraron dispositivos con los filtros aplicados');
        return;
    }
    
    const bounds = L.latLngBounds();
    
    dispositivos.forEach(dispositivo => {
        if (dispositivo.latitud && dispositivo.longitud) {
            const lat = parseFloat(dispositivo.latitud);
            const lng = parseFloat(dispositivo.longitud);
            
            // Crear icono personalizado según la especie
            let iconUrl = `${window.BASE_URL}assets/img/paw-default.svg`;
            if (dispositivo.especie === 'perro') {
                iconUrl = `${window.BASE_URL}assets/img/paw-dog.svg`;
            } else if (dispositivo.especie === 'gato') {
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
                    <p><strong>Dispositivo:</strong> ${dispositivo.nombre_dispositivo}</p>
                    <p><strong>MAC:</strong> ${dispositivo.mac_address}</p>
                    <p><strong>Estado:</strong> 
                        <span class="badge bg-${dispositivo.estado === 'activo' ? 'success' : 'danger'}">
                            ${dispositivo.estado}
                        </span>
                    </p>
                    <p><strong>Última conexión:</strong> ${dispositivo.ultima_conexion || 'N/A'}</p>
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
            } else {
                mostrarError('Error al cargar datos de la tabla');
            }
        })
        .catch(error => {
            console.error('Error al cargar tabla:', error);
            mostrarError('Error al cargar datos de la tabla');
        });
}

// Renderizar datos en la tabla
function renderizarTablaDatos(datos) {
    const tbody = document.querySelector('#tablaDatos tbody');
    
    if (!datos || datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center">No hay datos disponibles</td></tr>';
        return;
    }
    
    tbody.innerHTML = datos.map(dato => `
        <tr>
            <td>${dato.id_dato}</td>
            <td>${dato.nombre_dispositivo || 'N/A'}</td>
            <td>${dato.mascota_nombre || 'Sin asignar'}</td>
            <td>${dato.usuario_nombre || 'N/A'}</td>
            <td>${dato.temperatura}°C</td>
            <td>${dato.humedad}%</td>
            <td>${dato.latitud || 'N/A'}</td>
            <td>${dato.longitud || 'N/A'}</td>
            <td>${formatearFecha(dato.fecha_registro)}</td>
            <td>
                <span class="badge bg-${dato.estado === 'activo' ? 'success' : 'danger'}">
                    ${dato.estado}
                </span>
            </td>
        </tr>
    `).join('');
}

// Configurar eventos de los botones
function configurarEventos() {
    // Filtro de propietario
    document.getElementById('filtroPropietario').addEventListener('change', function() {
        cargarMascotas(this.value);
    });
    
    // Botón aplicar filtros
    document.getElementById('btnAplicarFiltros').addEventListener('click', aplicarFiltros);
    
    // Botón limpiar filtros
    document.getElementById('btnLimpiarFiltros').addEventListener('click', function() {
        document.getElementById('filtroPropietario').value = '';
        document.getElementById('filtroMascota').value = '';
        document.getElementById('filtroMAC').value = '';
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
    
    // Botón centrar mapa
    document.getElementById('btnCentrarMapa').addEventListener('click', function() {
        if (marcadores.length > 0) {
            const bounds = L.latLngBounds();
            marcadores.forEach(marker => bounds.extend(marker.getLatLng()));
            mapa.fitBounds(bounds, { padding: [20, 20] });
        }
    });
    
    // Botón pantalla completa
    document.getElementById('btnPantallaCompleta').addEventListener('click', function() {
        const mapaContainer = document.getElementById('mapaMonitor');
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            mapaContainer.requestFullscreen();
        }
    });
    
    // Botón actualizar tabla
    document.getElementById('btnActualizarTabla').addEventListener('click', cargarTablaDatos);
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

// Funciones de utilidad
function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleString('es-ES');
}

function mostrarError(mensaje) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: mensaje,
            timer: 3000
        });
    } else {
        console.error(mensaje);
    }
}

function mostrarInfo(mensaje) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'Información',
            text: mensaje,
            timer: 3000
        });
    } else {
        console.log(mensaje);
    }
} 