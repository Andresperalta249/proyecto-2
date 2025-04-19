<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../config/database.php';

class RoleController extends Controller {
    private $roleModel;
    private $db;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->db = new Database();
        $this->roleModel = new Role($this->db);

        // Si es una solicitud directa al controlador
        if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
            $this->handleRequest();
            exit;
        }
    }

    private function handleRequest() {
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('No has iniciado sesión');
            }

            if (!isset($_POST['action']) && !isset($_GET['action'])) {
                throw new Exception('Acción no especificada');
            }

            $action = $_POST['action'] ?? $_GET['action'];

            switch ($action) {
                case 'create':
                    $this->createRole();
                    break;
                case 'update':
                    $this->updateRole();
                    break;
                case 'delete':
                    $this->deleteRole();
                    break;
                case 'getPermisos':
                    $this->getPermisos();
                    break;
                case 'updateEstado':
                    $this->updateEstado();
                    break;
                default:
                    throw new Exception('Acción no válida');
            }
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                // Si es una petición AJAX
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
            } else {
                // Si es una petición normal
                $_SESSION['error'] = $e->getMessage();
                header('Location: /Proyecto 2/views/roles/roles.php');
            }
            exit;
        }
    }

    private function createRole() {
        try {
            if (!$this->tienePermiso('gestionar_roles')) {
                $this->sendJsonResponse(['success' => false, 'message' => 'No tiene permiso para crear roles']);
                return;
            }

            if (empty($_POST['nombre']) || empty($_POST['descripcion'])) {
                $this->sendJsonResponse(['success' => false, 'message' => 'El nombre y la descripción son requeridos']);
                return;
            }

            $data = [
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'])
            ];

            $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : [];
            
            if (empty($permisos)) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Debe seleccionar al menos un permiso']);
                return;
            }

            $result = $this->roleModel->create($data, $permisos);
            
            if ($result) {
                $this->sendJsonResponse(['success' => true, 'message' => 'Rol creado exitosamente']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Error al crear el rol']);
            }
        } catch (Exception $e) {
            error_log('Error en createRole: ' . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al crear el rol: ' . $e->getMessage()]);
        }
    }

    private function updateRole() {
        try {
            if (!$this->tienePermiso('gestionar_roles')) {
                $this->sendJsonResponse(['success' => false, 'message' => 'No tiene permiso para actualizar roles']);
                return;
            }

            if (empty($_POST['id']) || empty($_POST['nombre']) || empty($_POST['descripcion'])) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Todos los campos son requeridos']);
                return;
            }

            // Verificar si es un rol predeterminado
            $rol = $this->roleModel->getById($_POST['id']);
            if ($rol['es_predeterminado']) {
                $this->sendJsonResponse(['success' => false, 'message' => 'No se pueden modificar los roles predeterminados']);
                return;
            }

            $data = [
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion']),
                'estado' => $_POST['estado'] ?? 'activo'
            ];

            $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : [];
            
            if (empty($permisos)) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Debe seleccionar al menos un permiso']);
                return;
            }

            $result = $this->roleModel->update($_POST['id'], $data, $permisos);
            
            if ($result) {
                $this->sendJsonResponse(['success' => true, 'message' => 'Rol actualizado exitosamente']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Error al actualizar el rol']);
            }
        } catch (Exception $e) {
            error_log('Error en updateRole: ' . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al actualizar el rol: ' . $e->getMessage()]);
        }
    }

    public function deleteRole() {
        try {
            // Verificar permisos
            if (!$this->tienePermiso('gestionar_roles')) {
                $this->sendJsonResponse(['success' => false, 'message' => 'No tiene permiso para eliminar roles']);
                return;
            }

            // Verificar que se recibió el ID
            if (empty($_POST['id'])) {
                $this->sendJsonResponse(['success' => false, 'message' => 'ID de rol no proporcionado']);
                return;
            }

            $id = $_POST['id'];
            $role = new Role(new Database());

            // Verificar si el rol existe
            $rolData = $role->getById($id);
            if (!$rolData) {
                $this->sendJsonResponse(['success' => false, 'message' => 'El rol no existe']);
                return;
            }

            // Verificar si es un rol predefinido
            if ($rolData['es_predeterminado']) {
                $this->sendJsonResponse(['success' => false, 'message' => 'No se pueden eliminar roles predefinidos']);
                return;
            }

            // Verificar si hay usuarios asignados
            $usersCount = $role->getUsersCount($id);
            if ($usersCount > 0) {
                $this->sendJsonResponse(['success' => false, 'message' => 'No se puede eliminar el rol porque tiene usuarios asignados']);
                return;
            }

            // Eliminar el rol
            if ($role->delete($id)) {
                $this->sendJsonResponse(['success' => true, 'message' => 'Rol eliminado exitosamente']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Error al eliminar el rol']);
            }
        } catch (Exception $e) {
            error_log('Error en deleteRole: ' . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al eliminar el rol: ' . $e->getMessage()]);
        }
    }

    private function getPermisos() {
        if (!isset($_GET['id'])) {
            throw new Exception('ID del rol no proporcionado');
        }

        $permisos = $this->roleModel->getRolePermisos($_GET['id']);
        
        header('Content-Type: application/json');
        echo json_encode($permisos);
        exit;
    }

    private function updateEstado() {
        try {
            if (!$this->tienePermiso('gestionar_roles')) {
                $this->sendJsonResponse(['success' => false, 'message' => 'No tiene permiso para realizar esta acción']);
                return;
            }

            if (empty($_POST['role_id']) || empty($_POST['estado'])) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Parámetros incompletos']);
                return;
            }

            $role_id = intval($_POST['role_id']);
            $estado = trim($_POST['estado']);

            if (!in_array($estado, ['activo', 'inactivo'])) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Estado no válido']);
                return;
            }

            $roleModel = new Role($this->db);
            $roleData = $roleModel->getById($role_id);

            if (!$roleData) {
                $this->sendJsonResponse(['success' => false, 'message' => 'El rol no existe']);
                return;
            }

            if ($roleData['es_predeterminado']) {
                $this->sendJsonResponse(['success' => false, 'message' => 'No se puede modificar un rol predeterminado']);
                return;
            }

            if ($estado === 'inactivo') {
                $usuariosVinculados = $roleModel->getUsuariosConRol($role_id);
                if (!empty($usuariosVinculados)) {
                    $this->sendJsonResponse([
                        'success' => false,
                        'message' => 'Este rol tiene usuarios vinculados. Al desactivarlo, estos usuarios no podrán iniciar sesión.',
                        'usuarios_vinculados' => count($usuariosVinculados)
                    ]);
                    return;
                }
            }

            if ($roleModel->updateEstado($role_id, $estado)) {
                $this->sendJsonResponse(['success' => true, 'message' => 'Estado actualizado correctamente']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Error al actualizar el estado']);
            }
        } catch (Exception $e) {
            error_log('Error en updateEstado: ' . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al actualizar el estado: ' . $e->getMessage()]);
        }
    }

    public function getAllRoles() {
        try {
            $roles = $this->roleModel->getAllRoles();
            
            // Si es una petición AJAX, devolver JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                $this->sendJsonResponse(['success' => true, 'data' => $roles]);
            }
            
            // Si es una petición normal, devolver el array
            return $roles;
        } catch (Exception $e) {
            error_log('Error en getAllRoles: ' . $e->getMessage());
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                $this->sendJsonResponse(['success' => false, 'message' => 'Error al obtener los roles']);
            }
            throw $e;
        }
    }

    public function getAllPermisos() {
        return $this->roleModel->getAllPermisos();
    }

    public function tienePermiso($permiso) {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado intentando acceder a: ' . $permiso);
            return false;
        }

        // Superadmin tiene todos los permisos
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
            return true;
        }

        // Verificar si el usuario tiene permisos asignados
        if (!isset($_SESSION['permisos']) || !is_array($_SESSION['permisos'])) {
            error_log('Usuario ' . $_SESSION['user_id'] . ' no tiene permisos configurados');
            return false;
        }

        return in_array($permiso, $_SESSION['permisos']);
    }

    public function sendJsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function getRolePermisos($roleId) {
        try {
            return $this->roleModel->getPermissions($roleId);
        } catch (Exception $e) {
            error_log('Error en getRolePermisos: ' . $e->getMessage());
            return [];
        }
    }
}

// Manejo directo de la solicitud
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    $controller = new RoleController();
} 