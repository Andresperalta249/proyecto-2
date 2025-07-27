<?php
class RolesController {
    private $model;
    
    public function __construct() {
        $this->model = new Rol();
    }
    
    /**
     * Verifica si el usuario actual puede gestionar roles
     * @return bool True si puede gestionar roles
     */
    private function puedeGestionarRoles() {
        // Verificar si el usuario tiene el rol del sistema
        if (!isset($_SESSION['user_role'])) {
            return false;
        }
        
        return $this->model->puedeGestionarRoles($_SESSION['user_role']);
    }
    
    public function indexAction() {
        // Verificar permisos
        if (!verificarPermiso('ver_roles')) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
        
        $title = 'Administrador de roles';
        ob_start();
        require 'views/roles/index.php';
        $GLOBALS['content'] = ob_get_clean();
        $GLOBALS['title'] = $title;
        $GLOBALS['menuActivo'] = 'roles';
        require_once 'views/layouts/main.php';
    }
    
    public function getAction() {
        // Verificar permisos
        if (!verificarPermiso('editar_roles')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para editar roles']);
            exit;
        }
        
        // Verificar si puede gestionar roles
        if (!$this->puedeGestionarRoles()) {
            echo json_encode(['success' => false, 'error' => 'Solo los administradores pueden gestionar roles']);
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        if ($id) {
            $rol = $this->model->getById($id);
            if ($rol) {
                echo json_encode(['success' => true, 'data' => $rol]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Rol no encontrado']);
            }
        } else {
            // Cargar formulario para nuevo rol, pasando permisos
            $permisos = $this->model->getPermisos();
            $data = [
                'rol' => null,
                'permisos' => $permisos
            ];
            require_once 'views/roles/form.php';
        }
    }
    
    public function createAction() {
        // Verificar permisos
        if (!verificarPermiso('crear_roles')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para crear roles']);
            exit;
        }
        
        // Verificar si puede gestionar roles
        if (!$this->puedeGestionarRoles()) {
            echo json_encode(['success' => false, 'error' => 'Solo los administradores pueden crear roles']);
            exit;
        }
        
        $data = [
            'nombre' => $_POST['nombre'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'estado' => $_POST['estado'] ?? 'activo',
            'permisos' => $_POST['permisos'] ?? []
        ];
        // Validar que al menos un permiso esté seleccionado
        if (empty($data['permisos'])) {
            echo json_encode(['success' => false, 'error' => 'Debes asignar al menos un permiso al rol.']);
            exit;
        }
        if ($this->model->create($data)) {
            echo json_encode(['success' => true, 'message' => 'Rol creado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al crear el rol']);
        }
    }
    
    public function updateAction() {
        // Verificar permisos
        if (!verificarPermiso('editar_roles')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para editar roles']);
            exit;
        }
        
        // Verificar si puede gestionar roles
        if (!$this->puedeGestionarRoles()) {
            echo json_encode(['success' => false, 'error' => 'Solo los administradores pueden editar roles']);
            exit;
        }
        
        $id = $_POST['id_rol'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID de rol no proporcionado']);
            exit;
        }
        
        // Verificar que no esté intentando editar un rol por defecto
        if ($this->model->esRolPorDefecto($id)) {
            echo json_encode(['success' => false, 'error' => 'No se puede editar un rol por defecto del sistema']);
            exit;
        }
        
        $data = [
            'nombre' => $_POST['nombre'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'estado' => $_POST['estado'] ?? 'activo',
        ];
        // Si se envían permisos, usarlos. Si no, mantener los actuales.
        if (isset($_POST['permisos'])) {
            $data['permisos'] = $_POST['permisos'];
            // Si explícitamente no hay ninguno seleccionado, mostrar error
            if (empty($data['permisos'])) {
                echo json_encode(['success' => false, 'error' => 'Debes asignar al menos un permiso al rol.']);
                exit;
            }
        } else {
            // Mantener los permisos actuales
            $rolActual = $this->model->getById($id);
            $data['permisos'] = $rolActual['permiso_ids'] ?? [];
        }
        if ($this->model->update($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Rol actualizado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar el rol']);
        }
    }
    
    public function deleteAction() {
        // Verificar permisos
        if (!verificarPermiso('eliminar_roles')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para eliminar roles']);
            exit;
        }
        
        // Verificar si puede gestionar roles
        if (!$this->puedeGestionarRoles()) {
            echo json_encode(['success' => false, 'error' => 'Solo los administradores pueden eliminar roles']);
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID de rol no proporcionado']);
            exit;
        }
        
        // Verificar que no esté intentando eliminar un rol por defecto
        if ($this->model->esRolPorDefecto($id)) {
            echo json_encode(['success' => false, 'error' => 'No se puede eliminar un rol por defecto del sistema']);
            exit;
        }
        
        // Verificar si el usuario actual puede eliminar este rol específico
        if (!$this->model->puedeEliminarRol($_SESSION['user_role'], $id)) {
            echo json_encode(['success' => false, 'error' => 'No tienes permisos para eliminar este rol']);
            exit;
        }
        
        if ($this->model->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Rol eliminado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al eliminar el rol']);
        }
    }
    
    public function cambiarEstadoAction() {
        // Verificar permisos
        if (!verificarPermiso('editar_roles')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para editar roles']);
            exit;
        }
        
        // Verificar si puede gestionar roles
        if (!$this->puedeGestionarRoles()) {
            echo json_encode(['success' => false, 'error' => 'Solo los administradores pueden cambiar el estado de roles']);
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        $estado = $_POST['estado'] ?? null;
        
        if (!$id || !$estado) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit;
        }
        
        // Verificar que no esté intentando cambiar el estado de un rol por defecto
        if ($this->model->esRolPorDefecto($id)) {
            echo json_encode(['success' => false, 'error' => 'No se puede cambiar el estado de un rol por defecto del sistema']);
            exit;
        }
        
        if ($this->model->cambiarEstado($id, $estado)) {
            echo json_encode(['success' => true, 'message' => 'Estado actualizado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al cambiar el estado']);
        }
    }
    
    public function getPermisosAction() {
        // Verificar permisos
        if (!verificarPermiso('ver_roles')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para ver roles']);
            exit;
        }
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID de rol no proporcionado']);
            exit;
        }
        $permisos = $this->model->getPermisosPorRol($id);
        if ($permisos !== false) {
            echo json_encode(['success' => true, 'permisos' => $permisos]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al obtener los permisos']);
        }
    }
    
    public function tablaAction() {
        // Verificar permisos
        if (!verificarPermiso('ver_roles')) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
        
        // Obtener parámetros de búsqueda
        $search = $_GET['search'] ?? '';
        $estado = $_GET['estado'] ?? '';
        
        // Obtener roles desde el modelo
        $roles = $this->model->getAllWithSearch($search, $estado);
        
        // Generar tabla HTML
        echo '<div class="table-responsive">';
        echo '<table class="tabla-sistema">';
        echo '<thead><tr>';
        echo '<th class="celda-id">ID</th>';
        echo '<th>Nombre</th>';
        echo '<th>Descripción</th>';
        echo '<th class="celda-estado">Estado</th>';
        echo '<th class="celda-acciones">Acciones</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        if (empty($roles)) {
            echo '<tr><td colspan="5" class="mensaje-vacio">';
            echo '<i class="fas fa-user-tag"></i>';
            echo '<div>No se encontraron roles</div>';
            echo '</td></tr>';
        } else {
            foreach ($roles as $rol) {
                echo '<tr>';
                echo '<td class="celda-id">' . htmlspecialchars($rol['id_rol']) . '</td>';
                echo '<td>' . htmlspecialchars($rol['nombre']) . '</td>';
                echo '<td>' . htmlspecialchars($rol['descripcion']) . '</td>';
                echo '<td class="celda-estado">';
                
                // Mostrar switch solo si tiene permisos y no es rol por defecto
                if (verificarPermiso('editar_roles') && $this->puedeGestionarRoles() && !$rol['es_rol_por_defecto']) {
                    echo '<div class="form-check form-switch d-flex align-items-center mb-0">';
                    echo '<input class="form-check-input cambiar-estado-rol" type="checkbox" data-id="' . $rol['id_rol'] . '" ' . ($rol['estado'] === 'activo' ? 'checked' : '') . '>';
                    echo '<label class="form-check-label ms-2">' . ucfirst($rol['estado']) . '</label>';
                    echo '</div>';
                } else {
                    echo '<span class="badge-estado badge-' . ($rol['estado'] === 'activo' ? 'activo' : 'inactivo') . '">' . ucfirst($rol['estado']) . '</span>';
                }
                
                echo '</td>';
                echo '<td class="celda-acciones">';
                
                // Solo mostrar botones de edición/eliminación si puede gestionar roles
                if (verificarPermiso('editar_roles') && $this->puedeGestionarRoles() && !$rol['es_rol_por_defecto']) {
                    echo '<button type="button" class="btn-accion btn-editar" data-id="' . $rol['id_rol'] . '" title="Editar"><i class="fas fa-edit"></i></button>';
                }
                
                if (verificarPermiso('eliminar_roles') && $this->puedeGestionarRoles() && !$rol['es_rol_por_defecto']) {
                    // Verificar si puede eliminar este rol específico
                    if ($this->model->puedeEliminarRol($_SESSION['user_role'], $rol['id_rol'])) {
                        echo '<button type="button" class="btn-accion btn-eliminar" data-id="' . $rol['id_rol'] . '" title="Eliminar"><i class="fas fa-trash"></i></button>';
                    }
                }
                
                // Mostrar indicador de rol por defecto
                if ($rol['es_rol_por_defecto']) {
                    echo '<span class="text-muted" title="Rol por defecto del sistema"><i class="fas fa-shield-alt"></i> Sistema</span>';
                }
                
                echo '</td>';
                echo '</tr>';
            }
        }
        
        echo '</tbody></table>';
        echo '</div>';
    }
    
    public function formAction() {
        // Verificar permisos
        if (!verificarPermiso('editar_roles')) {
            header('Location: ' . APP_URL . '/error/403');
            exit;
        }
        
        // Verificar si puede gestionar roles
        if (!$this->puedeGestionarRoles()) {
            header('Location: ' . APP_URL . '/error/403');
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        $rol = null;
        if ($id) {
            $rol = $this->model->getById($id);
            // Verificar que no esté intentando editar un rol por defecto
            if ($rol && $rol['es_rol_por_defecto']) {
                header('Location: ' . APP_URL . '/error/403');
                exit;
            }
        }
        $permisos = $this->model->getPermisos();
        $data = [
            'rol' => $rol,
            'permisos' => $permisos
        ];
        require 'views/roles/form.php';
    }
    
    public function listAction() {
        // Verificar permisos
        if (!verificarPermiso('ver_roles')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para ver roles']);
            exit;
        }
        $roles = $this->model->getAll();
        echo json_encode(['success' => true, 'data' => $roles]);
        exit;
    }
} 