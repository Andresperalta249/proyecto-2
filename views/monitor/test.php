<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Test Monitor - Sistema Funcionando</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h5>✅ Sistema de Monitor Funcionando Correctamente</h5>
                        <p>Esta es una página de prueba para verificar que el módulo monitor está operativo.</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Información de Sesión</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Usuario:</strong> <?= $_SESSION['user_name'] ?? 'No definido' ?></li>
                                        <li><strong>Rol:</strong> <?= $_SESSION['rol_nombre'] ?? 'No definido' ?></li>
                                        <li><strong>ID Usuario:</strong> <?= $_SESSION['user_id'] ?? 'No definido' ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Permisos</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>ver_monitor:</strong> <?= verificarPermiso('ver_monitor') ? '✅ Sí' : '❌ No' ?></li>
                                        <li><strong>ver_todos_dispositivos:</strong> <?= verificarPermiso('ver_todos_dispositivos') ? '✅ Sí' : '❌ No' ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6>Variables del Sistema</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>BASE_URL:</strong> <?= BASE_URL ?? 'No definido' ?></li>
                                        <li><strong>APP_URL:</strong> <?= APP_URL ?? 'No definido' ?></li>
                                        <li><strong>ROOT_PATH:</strong> <?= ROOT_PATH ?? 'No definido' ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <a href="<?= BASE_URL ?>monitor" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Ir al Monitor Principal
                            </a>
                            <a href="<?= BASE_URL ?>dashboard" class="btn btn-secondary">
                                <i class="fas fa-home"></i> Ir al Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 