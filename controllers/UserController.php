<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../models/Pet.php';

class UserController {
    private $db;
    private $userModel;
    private $roleModel;
    private $petModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
            header('Location: /Proyecto%202/views/auth/login.php');
            exit;
        }

        try {
            $this->db = new Database();
            $this->userModel = new User($this->db);
            $this->roleModel = new Role($this->db);
            $this->petModel = new Pet($this->db);

            // Cargar permisos del usuario si no están cargados
            if (!isset($_SESSION['user_permissions']) && isset($_SESSION['user_id'])) {
                $this->loadUserPermissions($_SESSION['user_id']);
            }
        } catch (Exception $e) {
            error_log("Error en UserController::__construct: " . $e->getMessage());
            // Redirigir a una página de error o mostrar un mensaje
            header('Location: /Proyecto%202/views/auth/error.php');
            exit;
        }
    }

    private function loadUserPermissions($userId) {
        try {
            if (empty($userId)) {
                throw new Exception("ID de usuario no proporcionado");
            }

            // Obtener el rol del usuario
            $userRole = $this->roleModel->getUserRole($userId);
            if (!$userRole) {
                throw new Exception("No se pudo obtener el rol del usuario");
            }

            // Guardar información del rol en la sesión
            $_SESSION['user_role_id'] = $userRole['id'];
            $_SESSION['user_role'] = $userRole['nombre'];
            
            // Obtener los permisos asociados al rol
            $permisos = $this->roleModel->getRolePermisos($userRole['id']);
            if (!is_array($permisos)) {
                throw new Exception("Error al obtener los permisos del rol");
            }

            // Si es superadmin, obtener todos los permisos
            if ($userRole['id'] == 1) {
                $permisos = $this->roleModel->getAllPermisos();
            }

            // Guardar los permisos en la sesión
            $_SESSION['user_permissions'] = array_column($permisos, 'nombre');
            
            // Registrar en el log
            error_log("Permisos cargados para usuario ID: " . $userId . 
                     ", Rol: " . $userRole['nombre'] . 
                     ", Permisos: " . implode(', ', $_SESSION['user_permissions']));

        } catch (Exception $e) {
            error_log("Error cargando permisos del usuario: " . $e->getMessage());
            // Inicializar permisos vacíos en caso de error
            $_SESSION['user_permissions'] = [];
            throw $e;
        }
    }

    private function hasPermission($permission) {
        // Si es superadministrador, tiene todos los permisos
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'superadministrador') {
            return true;
        }

        // Verificar si tiene el permiso específico
        return isset($_SESSION['user_permissions']) && 
               is_array($_SESSION['user_permissions']) && 
               (in_array($permission, $_SESSION['user_permissions']) || 
                in_array('gestionar_' . $permission, $_SESSION['user_permissions']));
    }

    public function tienePermiso($permission) {
        return $this->hasPermission($permission);
    }

    public function index() {
        try {
            // Verificar permisos
            if (!$this->hasPermission('usuarios') && !$this->hasPermission('gestionar_usuarios')) {
                $this->jsonResponse(false, "No tienes permisos para acceder a la gestión de usuarios");
                return;
            }

            // Obtener parámetros de DataTables
            $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
            $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
            $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
            
            // Filtros
            $filters = [
                'rol' => isset($_GET['rol']) ? $_GET['rol'] : '',
                'estado' => isset($_GET['estado']) ? $_GET['estado'] : '',
                'search' => isset($_GET['search']) ? $_GET['search'] : ''
            ];

            // Obtener total de registros sin filtrar
            $totalRecords = $this->userModel->getTotalUsers();

            // Obtener usuarios filtrados
            $users = $this->userModel->getAllUsers($filters, $start, $length);
            
            // Obtener total de registros filtrados
            $filteredRecords = $this->userModel->getTotalFilteredUsers($filters);

            // Procesar datos para la vista
            $data = [];
            foreach ($users as $user) {
                // Obtener conteo de mascotas
                $petCount = $this->petModel->countPetsByUser($user['id']);
                
                // Obtener último acceso
                $lastAccess = $this->userModel->getLastAccess($user['id']);

                $data[] = [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'],
                    'email' => $user['email'],
                    'rol_id' => $user['rol_id'],
                    'rol_nombre' => $user['rol_nombre'],
                    'estado' => $user['estado'] === 'activo' ? 1 : 0,
                    'mascotas_count' => $petCount,
                    'ultimo_acceso' => $lastAccess ?: 'Nunca',
                    'puede_editar' => $this->canEditUser($user),
                    'puede_eliminar' => $this->canDeleteUser($user),
                    'puede_cambiar_estado' => $this->canToggleUserStatus($user)
                ];
            }

            // Respuesta para DataTables
            $response = [
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
                'error' => null
            ];

            header('Content-Type: application/json');
            echo json_encode($response);
            exit;

        } catch (Exception $e) {
            error_log("Error en UserController::index: " . $e->getMessage());
            $this->jsonResponse(false, "Error al obtener usuarios: " . $e->getMessage());
        }
    }

    public function create() {
        if (!$this->hasPermission('gestionar_usuarios')) {
            $this->jsonResponse(false, 'No tienes permiso para crear usuarios');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
            exit;
        }

        try {
            $data = $this->validateUserData($_POST);
            
            // Verificar si el correo ya existe
            if ($this->userModel->findByEmail($data['email'])) {
                throw new Exception('El correo electrónico ya está registrado');
            }

            // Crear el usuario
            if ($this->userModel->create($data)) {
                $this->jsonResponse(true, 'Usuario creado exitosamente');
            } else {
                throw new Exception('Error al crear el usuario');
            }

        } catch (Exception $e) {
            $this->jsonResponse(false, $e->getMessage());
        }
    }

    public function update() {
        if (!$this->hasPermission('gestionar_usuarios')) {
            $this->jsonResponse(false, 'No tienes permiso para editar usuarios');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
            exit;
        }

        try {
            $userId = $_POST['id'] ?? null;
            if (!$userId) {
                throw new Exception('ID de usuario no proporcionado');
            }

            $targetUser = $this->userModel->getById($userId);
            if (!$targetUser) {
                throw new Exception('Usuario no encontrado');
            }

            // Verificar jerarquía de roles
            if (!$this->canModifyUser($targetUser)) {
                throw new Exception('No tienes permiso para modificar este usuario');
            }

            $data = $this->validateUserData($_POST, true);
            
            if ($this->userModel->update($userId, $data)) {
                $this->jsonResponse(true, 'Usuario actualizado exitosamente');
            } else {
                throw new Exception('Error al actualizar el usuario');
            }

        } catch (Exception $e) {
            $this->jsonResponse(false, $e->getMessage());
        }
    }

    public function delete() {
        if (!$this->hasPermission('gestionar_usuarios')) {
            $this->jsonResponse(false, 'No tienes permiso para eliminar usuarios');
            exit;
        }

        try {
            $userId = $_POST['id'] ?? null;
            if (!$userId) {
                throw new Exception('ID de usuario no proporcionado');
            }

            $targetUser = $this->userModel->getById($userId);
            if (!$targetUser) {
                throw new Exception('Usuario no encontrado');
            }

            // Verificar si es el último superadmin
            if ($targetUser['rol_id'] == 1 && $this->userModel->countSuperadmins() <= 1) {
                throw new Exception('No se puede eliminar el último superadministrador');
            }

            // Verificar jerarquía de roles
            if (!$this->canModifyUser($targetUser)) {
                throw new Exception('No tienes permiso para eliminar este usuario');
            }

            // Verificar si tiene mascotas o dispositivos
            $hasPets = $this->petModel->getUserPetsCount($userId) > 0;
            if ($hasPets) {
                $this->jsonResponse(false, 'El usuario tiene mascotas asociadas', ['requireConfirmation' => true]);
                exit;
            }

            if ($this->userModel->delete($userId)) {
                $this->jsonResponse(true, 'Usuario eliminado exitosamente');
            } else {
                throw new Exception('Error al eliminar el usuario');
            }

        } catch (Exception $e) {
            $this->jsonResponse(false, $e->getMessage());
        }
    }

    public function toggleStatus() {
        try {
            // Verificar si hay output previo
            if (ob_get_length()) ob_clean();
            
            // Establecer headers
            header('Content-Type: application/json');
            header('Cache-Control: no-cache, must-revalidate');
            
            // Verificar permisos
            if (!$this->hasPermission('gestionar_usuarios')) {
                throw new Exception('No tienes permiso para cambiar el estado de usuarios');
            }

            // Validar ID
            $userId = $_POST['id'] ?? null;
            if (!$userId) {
                throw new Exception('ID de usuario no proporcionado');
            }

            // Obtener usuario
            $targetUser = $this->userModel->getById($userId);
            if (!$targetUser) {
                throw new Exception('Usuario no encontrado');
            }

            // Verificar si es el último superadmin activo
            if ($targetUser['rol_id'] == 1 && $targetUser['estado'] === 'activo' && $this->userModel->countActiveSuperadmins() <= 1) {
                throw new Exception('No se puede desactivar el último superadministrador activo');
            }

            // Verificar jerarquía de roles
            if (!$this->canModifyUser($targetUser)) {
                throw new Exception('No tienes permiso para modificar este usuario');
            }

            // Cambiar estado
            $newStatus = $targetUser['estado'] === 'activo' ? 'inactivo' : 'activo';
            $result = $this->userModel->updateStatus($userId, $newStatus);

            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Estado del usuario actualizado exitosamente',
                    'newStatus' => $newStatus
                ];
            } else {
                throw new Exception('Error al actualizar el estado del usuario');
            }

            echo json_encode($response);
            exit;

        } catch (Exception $e) {
            error_log("Error en toggleStatus: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    // Obtener un usuario por ID
    public function get() {
        try {
            if (!isset($_POST['id'])) {
                $this->jsonResponse(false, 'ID de usuario no proporcionado');
                return;
            }

            $userId = $_POST['id'];
            $user = $this->userModel->getById($userId);

            if (!$user) {
                $this->jsonResponse(false, 'Usuario no encontrado');
                return;
            }

            // Asegurarse que el estado sea un booleano para la vista
            $user['estado'] = $user['estado'] === 'activo' ? 1 : 0;

            $this->jsonResponse(true, '', $user);
            
        } catch (Exception $e) {
            error_log('Error en UserController::get: ' . $e->getMessage());
            $this->jsonResponse(false, 'Error al obtener los datos del usuario');
        }
    }

    public function checkEmail() {
        try {
            $email = $_POST['email'] ?? null;
            $userId = $_POST['userId'] ?? null;
            
            if (!$email) {
                throw new Exception('El correo electrónico es requerido');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('El formato del correo electrónico no es válido');
            }
            
            // Buscar el email en la base de datos
            $existingUser = $this->userModel->findByEmail($email);
            
            // Si no existe, el email está disponible
            if (!$existingUser) {
                $this->jsonResponse(true, 'El correo electrónico está disponible');
                return;
            }
            
            // Si estamos editando un usuario, verificar si el email pertenece al mismo usuario
            if ($userId && $existingUser['id'] == $userId) {
                $this->jsonResponse(true, 'El correo electrónico es válido');
                return;
            }
            
            // Si llegamos aquí, el email ya está en uso
            throw new Exception('Este correo electrónico ya está registrado');
            
        } catch (Exception $e) {
            $this->jsonResponse(false, $e->getMessage());
        }
    }

    private function validateUserData($data, $isUpdate = false) {
        $validated = [];
        
        // Validar nombre
        if (empty($data['nombre'])) {
            throw new Exception('El nombre es requerido');
        }
        $validated['nombre'] = filter_var($data['nombre'], FILTER_SANITIZE_STRING);

        // Validar email (solo para nuevos usuarios)
        if (!$isUpdate) {
            if (empty($data['email'])) {
                throw new Exception('El correo electrónico es requerido');
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('El correo electrónico no es válido');
            }
            $validated['email'] = $data['email'];
        }

        // Validar contraseña
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                throw new Exception('La contraseña debe tener al menos 8 caracteres');
            }
            if (!preg_match('/[A-Z]/', $data['password'])) {
                throw new Exception('La contraseña debe contener al menos una mayúscula');
            }
            if (!preg_match('/[a-z]/', $data['password'])) {
                throw new Exception('La contraseña debe contener al menos una minúscula');
            }
            if (!preg_match('/[0-9]/', $data['password'])) {
                throw new Exception('La contraseña debe contener al menos un número');
            }
            if (!preg_match('/[!@#$%^&*]/', $data['password'])) {
                throw new Exception('La contraseña debe contener al menos un carácter especial (!@#$%^&*)');
            }
            if (strpos($data['password'], ' ') !== false) {
                throw new Exception('La contraseña no puede contener espacios');
            }
            $validated['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Validar rol
        if (isset($data['rol_id'])) {
            if ($this->hasPermission('superadministrador')) {
                $validated['rol_id'] = $data['rol_id'];
            } else if ($this->hasPermission('gestionar_usuarios')) {
                // Administradores solo pueden asignar roles inferiores
                $userRole = $_SESSION['user_role'];
                if ($data['rol_id'] >= $userRole) {
                    throw new Exception('No tienes permiso para asignar este rol');
                }
                $validated['rol_id'] = $data['rol_id'];
            } else {
                $validated['rol_id'] = 3; // Rol de usuario normal por defecto
            }
        }

        return $validated;
    }

    private function canModifyUser($targetUser) {
        // Superadmin puede modificar cualquier usuario excepto otros superadmins
        if ($this->hasPermission('superadministrador')) {
            // No puede modificar otros superadmins a menos que sea el único
            if ($targetUser['rol_nombre'] === 'superadministrador' && 
                $targetUser['id'] !== $_SESSION['user_id'] &&
                $this->userModel->countSuperadmins() > 1) {
                return false;
            }
            return true;
        }

        // Un usuario puede modificarse a sí mismo
        if ($targetUser['id'] === $_SESSION['user_id']) {
            return true;
        }

        // Administradores pueden modificar usuarios con roles inferiores
        if ($this->hasPermission('gestionar_usuarios')) {
            $userRoleId = $_SESSION['user_role_id'];
            return $userRoleId < $targetUser['rol_id']; // Menor ID = rol superior
        }

        return false;
    }

    private function canEditUser($user) {
        // Verificar permiso básico
        if (!$this->hasPermission('usuarios') && !$this->hasPermission('gestionar_usuarios')) {
            return false;
        }
        
        // Superadmin puede editar cualquier usuario (con la restricción de otros superadmins)
        if ($this->hasPermission('superadministrador')) {
            if ($user['rol_id'] == 1 && $user['id'] !== $_SESSION['user_id']) {
                return $this->userModel->countSuperadmins() <= 1;
            }
            return true;
        }
        
        // Un usuario puede editarse a sí mismo
        if ($user['id'] == $_SESSION['user_id']) {
            return true;
        }
        
        // Administradores pueden editar usuarios con roles inferiores
        if ($this->hasPermission('gestionar_usuarios')) {
            return $_SESSION['user_role_id'] < $user['rol_id'];
        }
        
        return false;
    }

    private function canDeleteUser($user) {
        // Verificar permiso básico
        if (!$this->hasPermission('usuarios') && !$this->hasPermission('gestionar_usuarios')) {
            return false;
        }
        
        // No se puede eliminar al último superadmin
        if ($user['rol_id'] == 1) {
            if ($this->userModel->countSuperadmins() <= 1) {
                return false;
            }
            // Solo un superadmin puede eliminar a otro superadmin
            if (!$this->hasPermission('superadministrador')) {
                return false;
            }
        }

        // Un usuario no puede eliminarse a sí mismo
        if ($user['id'] == $_SESSION['user_id']) {
            return false;
        }

        // Administradores pueden eliminar usuarios con roles inferiores
        if ($this->hasPermission('gestionar_usuarios')) {
            return $_SESSION['user_role_id'] < $user['rol_id'];
        }
        
        return false;
    }

    private function canToggleUserStatus($user) {
        // Verificar permiso básico
        if (!$this->hasPermission('usuarios') && !$this->hasPermission('gestionar_usuarios')) {
            return false;
        }
        
        // No se puede desactivar al último superadmin activo
        if ($user['rol_id'] == 1 && $user['estado'] == 1) {
            if ($this->userModel->countActiveSuperadmins() <= 1) {
                return false;
            }
            // Solo un superadmin puede desactivar a otro superadmin
            if (!$this->hasPermission('superadministrador')) {
                return false;
            }
        }
        
        // Un usuario no puede desactivarse a sí mismo
        if ($user['id'] == $_SESSION['user_id']) {
            return false;
        }

        // Administradores pueden cambiar el estado de usuarios con roles inferiores
        if ($this->hasPermission('gestionar_usuarios')) {
            return $_SESSION['user_role_id'] < $user['rol_id'];
        }
        
        return false;
    }

    private function jsonResponse($success, $message = '', $data = null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}

// Manejo de solicitudes directas al controlador
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    $controller = new UserController();
    
    $action = $_POST['action'] ?? $_GET['action'] ?? 'index';
    
    switch ($action) {
        case 'create':
            $controller->create();
            break;
        case 'update':
            $controller->update();
            break;
        case 'delete':
            $controller->delete();
            break;
        case 'toggleStatus':
            $controller->toggleStatus();
            break;
        case 'get':
            $controller->get();
            break;
        case 'checkEmail':
            $controller->checkEmail();
            break;
        default:
            $controller->index();
    }
}
?> 