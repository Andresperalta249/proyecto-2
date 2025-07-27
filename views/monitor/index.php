<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-desktop"></i> Monitor de Dispositivos
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Debug info -->
                    <div class="alert alert-info">
                        <strong>Debug:</strong> Dispositivos encontrados: <?= count($dispositivos ?? []) ?>
                        <br>
                        <strong>Datos:</strong> <pre><?= print_r($dispositivos ?? [], true) ?></pre>
                    </div>
                    
                    <?php if (empty($dispositivos)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay dispositivos disponibles para mostrar.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($dispositivos as $dispositivo): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-microchip"></i> 
                                                <?= htmlspecialchars($dispositivo['nombre_dispositivo'] ?? 'Sin nombre') ?>
                                            </h6>
                                            <span class="badge bg-<?= ($dispositivo['estado'] === 'activo') ? 'success' : 'danger' ?>">
                                                <?= ucfirst($dispositivo['estado'] ?? 'desconocido') ?>
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted">MAC Address</small>
                                                    <div class="fw-bold"><?= htmlspecialchars($dispositivo['mac_address'] ?? 'N/A') ?></div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Mascota</small>
                                                    <div class="fw-bold"><?= htmlspecialchars($dispositivo['mascota_nombre'] ?? 'Sin asignar') ?></div>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-6">
                                                    <small class="text-muted">Propietario</small>
                                                    <div class="fw-bold"><?= htmlspecialchars($dispositivo['usuario_nombre'] ?? 'N/A') ?></div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Última Conexión</small>
                                                    <div class="fw-bold"><?= $dispositivo['ultima_conexion'] ?? 'N/A' ?></div>
                                                </div>
                                            </div>
                                            <?php if ($dispositivo['latitud'] && $dispositivo['longitud']): ?>
                                                <div class="row mt-2">
                                                    <div class="col-12">
                                                        <small class="text-muted">Ubicación</small>
                                                        <div class="fw-bold">
                                                            <?= number_format($dispositivo['latitud'], 6) ?>, 
                                                            <?= number_format($dispositivo['longitud'], 6) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer">
                                            <div class="d-flex justify-content-between">
                                                <button class="btn btn-sm btn-outline-primary" onclick="verDetalles(<?= $dispositivo['id_dispositivo'] ?>)">
                                                    <i class="fas fa-eye"></i> Ver Detalles
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" onclick="verUbicacion(<?= $dispositivo['id_dispositivo'] ?>, <?= $dispositivo['latitud'] ?? 'null' ?>, <?= $dispositivo['longitud'] ?? 'null' ?>)">
                                                    <i class="fas fa-map-marker-alt"></i> Ubicación
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function verDetalles(id) {
    // Por ahora solo muestra un alert, se puede expandir después
    Swal.fire({
        title: 'Detalles del Dispositivo',
        text: 'Funcionalidad en desarrollo para el dispositivo ID: ' + id,
        icon: 'info'
    });
}

function verUbicacion(id, lat, lng) {
    if (!lat || !lng) {
        Swal.fire({
            title: 'Sin Ubicación',
            text: 'Este dispositivo no tiene datos de ubicación disponibles.',
            icon: 'warning'
        });
        return;
    }
    
    // Abrir mapa en nueva ventana
    const url = `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}&zoom=15`;
    window.open(url, '_blank');
}
</script> 