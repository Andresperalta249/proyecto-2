<?php
/**
 * Controlador UsuariosController
 * -----------------------------
 * Controlador para la gestión de usuarios del sistema.
 *
 * Atributos:
 *   - usuarioModel: Modelo para acceder a la tabla de usuarios.
 *   - rolModel: Modelo para acceder a los roles de usuario.
 *
 * Métodos principales:
 *   - indexAction(): Muestra la vista principal de usuarios.
 *   - guardarAction(): Crea o actualiza un usuario.
 *   - eliminarAction(): Elimina un usuario.
 *   - cargarFormularioAction($id): Carga el formulario de crear/editar usuario.
 *   - toggleEstadoAction(): Cambia el estado (activo/inactivo) de un usuario.
 *   - Otros métodos auxiliares para roles, permisos, etc.
 *
 * Relación:
 *   - Usa UsuarioModel y Rol para acceder a la base de datos.
 *   - Interactúa con la vista para mostrar formularios y tablas.
 *
 * Flujo típico:
 *   1. El usuario accede a la página de usuarios (indexAction).
 *   2. Se cargan los usuarios vía AJAX.
 *   3. Al crear/editar, se muestra un formulario y se guardan los datos (guardarAction).
 *   4. El controlador valida, llama al modelo y responde con éxito o error.
 */
require_once 'core/Controller.php';
require_once 'models/UsuarioModel.php';
require_once 'models/Rol.php';

class UsuariosController extends Controller {

    private $usuarioModel;
    private $rolModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->usuarioModel = new UsuarioModel();
        $this->rolModel = new Rol();
        $this->logModel = $this->loadModel('Log');
    }

    public function indexAction() {
        if (!verificarPermiso('ver_usuarios')) {
            $this->view->render('errors/403');
            return;
        }
        
        $this->view->setLayout('main');
        $this->view->render('usuarios/index');
    }

    public function obtenerUsuariosAction() {
        if (!verificarPermiso('ver_usuarios')) {
            $this->jsonResponse(['error' => 'No tienes permiso'], 403);
            return;
        }

        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search = $_POST['search']['value'] ?? '';

        // Total de registros sin filtrar
        $totalRecords = $this->usuarioModel->contarUsuarios();

        // Total de registros con filtro (si hay búsqueda)
        $recordsFiltered = $totalRecords;
        if (!empty($search)) {
            $recordsFiltered = $this->usuarioModel->contarUsuarios($search);
        }

        $usuarios = $this->usuarioModel->obtenerUsuariosPaginados($start, $length, $search);

        $data = [];
        $currentUserId = $_SESSION['user_id'] ?? null;
        $canEditGlobal = verificarPermiso('editar_usuarios');
        $canDeleteGlobal = verificarPermiso('eliminar_usuarios');

        foreach ($usuarios as $usuario) {
            $canEdit = $canEditGlobal;
            $canDelete = $canDeleteGlobal;

            // Regla: No se puede eliminar al Super Administrador (rol_id 3)
            if ($usuario['rol_id'] == 3) {
                $canDelete = false;
            }

            // Regla: No se puede eliminar a uno mismo
            if ($usuario['id_usuario'] == $currentUserId) {
                $canDelete = false;
            }

            $data[] = [
                'id' => $usuario['id_usuario'],
                'nombre' => htmlspecialchars($usuario['nombre']),
                'email' => htmlspecialchars($usuario['email']),
                'telefono' => htmlspecialchars($usuario['telefono'] ?? ''),
                'rol' => htmlspecialchars($usuario['rol_nombre']),
                'direccion' => htmlspecialchars($usuario['direccion'] ?? ''),
                'estado' => $usuario['estado'],
                'puede_editar' => $canEdit,
                'puede_eliminar' => $canDelete
            ];
        }

        $this->jsonResponse([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function cargarFormularioAction($id = null) {
        // Comentar o eliminar todos los error_log de depuración, deja solo los de error real.
        // error_log("[DEBUG] cargarFormularioAction llamado con ID: " . var_export($id, true));
        $permisoRequerido = $id ? 'editar_usuarios' : 'crear_usuarios';
        if (!verificarPermiso($permisoRequerido)) {
            $modalErrorPath = ROOT_PATH . '/views/partials/modal_error.php';
            if (!file_exists($modalErrorPath)) {
                echo '<div class="alert alert-danger">No tienes permiso para realizar esta acción.</div>';
                return;
            }
            $this->view->render('partials/modal_error', ['mensaje' => 'No tienes permiso para realizar esta acción.'], false);
            return;
        }
        $usuario = null;
        if ($id) {
            $usuario = $this->usuarioModel->find($id);
        }
        $roles = $this->rolModel->getAll();
        $viewData = [
            'usuario' => $usuario,
            'roles' => $roles
        ];
        // Detectar si es petición AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        if ($isAjax) {
            require ROOT_PATH . '/views/usuarios/form.php';
        } else {
            $this->view->render('usuarios/form', $viewData, false);
        }
    }

    public function guardarAction() {
        if (!$this->isPostRequest()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405);
        }

        $id = $_POST['id_usuario'] ?? null;
        $permisoRequerido = $id ? 'editar_usuarios' : 'crear_usuarios';
        if (!verificarPermiso($permisoRequerido)) {
            return $this->jsonResponse(['success' => false, 'message' => 'No tienes permiso para esta acción.'], 403);
        }

        $data = [
            'nombre' => trim($_POST['nombre']),
            'email' => trim($_POST['email']),
            'telefono' => trim($_POST['telefono']),
            'direccion' => trim($_POST['direccion']),
            'rol_id' => (int)$_POST['rol_id'],
            'estado' => $_POST['estado'] ?? 'inactivo',
        ];

        // Validaciones básicas
        if (empty($data['nombre']) || empty($data['email']) || empty($data['rol_id'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Nombre, email y rol son obligatorios.'], 400);
        }

        if ($this->usuarioModel->emailExiste($data['email'], $id)) {
            return $this->jsonResponse(['success' => false, 'message' => 'El email ya está en uso por otro usuario.'], 400);
        }

        if (!empty($_POST['password'])) {
            if ($_POST['password'] !== $_POST['confirm_password']) {
                return $this->jsonResponse(['success' => false, 'message' => 'Las contraseñas no coinciden.'], 400);
            }
            $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }

        try {
            if ($id) {
                // Actualizar
                $this->usuarioModel->update($id, $data);
                $mensaje = 'Usuario actualizado correctamente.';
            } else {
                // Crear
                if (empty($data['password'])) {
                    return $this->jsonResponse(['success' => false, 'message' => 'La contraseña es obligatoria para nuevos usuarios.'], 400);
                }
                $this->usuarioModel->create($data);
                $mensaje = 'Usuario creado correctamente.';
            }
            $this->jsonResponse(['success' => true, 'message' => $mensaje]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Error al guardar el usuario: ' . $e->getMessage()], 500);
        }
    }

    public function toggleEstadoAction() {
        // Comentar o eliminar todos los error_log de depuración, deja solo los de error real.
        // error_log("[DEBUG] toggleEstadoAction llamado");
        // error_log("[DEBUG] POST recibido: " . print_r($_POST, true));

        if (!$this->isPostRequest() || !verificarPermiso('editar_usuarios')) {
            // error_log("[ERROR] Acción no permitida o método no es POST");
            return $this->jsonResponse(['status' => 'error', 'message' => 'Acción no permitida.'], 403);
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // error_log("[DEBUG] ID filtrado: " . var_export($id, true));
        // error_log("[DEBUG] Estado filtrado: " . var_export($estado, true));

        if (!$id || !$estado) {
            // error_log("[ERROR] Datos incompletos: id o estado no válidos");
            return $this->jsonResponse(['status' => 'error', 'message' => 'Datos incompletos.'], 400);
        }

        $resultado = $this->usuarioModel->cambiarEstado($id, $estado);
        // error_log("[DEBUG] Resultado cambiarEstado: " . var_export($resultado, true));

        if ($resultado) {
            // error_log("[INFO] Estado actualizado correctamente para usuario $id");
            $this->jsonResponse(['status' => 'success', 'message' => 'Estado actualizado correctamente.']);
        } else {
            // error_log("[ERROR] No se pudo actualizar el estado para usuario $id");
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo actualizar el estado.'], 500);
        }
    }

    public function eliminarUsuarioAction($id) {
        if (!verificarPermiso('eliminar_usuarios')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'No tienes permiso para eliminar usuarios.'], 403);
        }

        if (!$this->isPostRequest()) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Método no permitido.'], 405);
        }

        if (!$id) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'ID de usuario no proporcionado.'], 400);
        }

        // Verificar que el usuario existe
        $usuario = $this->usuarioModel->obtenerUsuarioPorId($id);
        if (!$usuario) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Usuario no encontrado.'], 404);
        }

        // Verificar que no se elimine a sí mismo
        if ($id == $_SESSION['user_id']) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'No puedes eliminar tu propia cuenta.'], 400);
        }

        // Proteger Super Administrador (rol_id = 3) - nadie puede eliminarlo
        if ($usuario['rol_id'] == 3) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'No se puede eliminar a un Super Administrador.'], 403);
        }

        // Nueva Regla: Un Administrador (rol 1) no puede eliminar a otro Administrador (rol 1) o Super Administrador (rol 3)
        $currentUserRolId = $_SESSION['user']['rol_id'] ?? null;
        if ($currentUserRolId == 1 && ($usuario['rol_id'] == 1 || $usuario['rol_id'] == 3)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Como Administrador, no puedes eliminar a otros administradores o super administradores.'], 403);
        }

        if ($this->usuarioModel->delete($id)) {
            $this->logModel->crearLog($_SESSION['user_id'], 'Eliminación del usuario ID: ' . $id);
            $this->jsonResponse(['status' => 'success', 'message' => 'Usuario eliminado correctamente.']);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'Error al eliminar el usuario.'], 500);
        }
    }

    private function isPostRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}