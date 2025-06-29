<?php
/**
 * Controlador DispositivosController
 * ----------------------------------
 * Controlador principal para la gestión de dispositivos IoT.
 *
 * Atributos:
 *   - dispositivoModel: Modelo para acceder a la tabla de dispositivos.
 *   - mascotaModel: Modelo para acceder a la tabla de mascotas.
 *   - logModel: Modelo para registrar logs de acciones.
 *
 * Métodos principales:
 *   - indexAction(): Muestra la vista principal de dispositivos.
 *   - obtenerDispositivosAction(): Devuelve la lista de dispositivos (para DataTable).
 *   - guardarAction(): Crea o actualiza un dispositivo según los datos recibidos.
 *   - eliminarAction(): Elimina un dispositivo.
 *   - formularioAction($id): Carga el formulario de crear/editar dispositivo.
 *   - obtenerMascotasDisponiblesAction(): Devuelve mascotas disponibles para asignar a un dispositivo.
 *   - Otros métodos auxiliares para cambiar estado, filtrar, etc.
 *
 * Relación:
 *   - Usa los modelos DispositivoModel y Mascota para acceder a la base de datos.
 *   - Interactúa con la vista para mostrar formularios y tablas.
 *
 * Flujo típico:
 *   1. El usuario accede a la página de dispositivos (indexAction).
 *   2. Se cargan los dispositivos vía AJAX (obtenerDispositivosAction).
 *   3. Al crear/editar, se muestra un formulario (formularioAction) y se guardan los datos (guardarAction).
 *   4. El controlador valida, llama al modelo y responde con éxito o error.
 */
class DispositivosController extends Controller {
    private $dispositivoModel;
    private $mascotaModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->dispositivoModel = new DispositivoModel();
        $this->mascotaModel = $this->loadModel('Mascota');
        $this->logModel = $this->loadModel('Log');
    }

    public function indexAction() {
        if (!verificarPermiso('ver_dispositivos')) {
            $this->view->render('errors/403');
            return;
        }
        
        $this->view->setLayout('main');
        $this->view->render('dispositivos/index');
    }

    public function obtenerDispositivosAction() {
        if (!verificarPermiso('ver_dispositivos')) {
            $this->jsonResponse(['error' => 'No tienes permiso'], 403);
            return;
        }

        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search = $_POST['search']['value'] ?? '';

        $user_id = $_SESSION['user_id'];
        $puedeVerTodos = verificarPermiso('ver_todos_dispositivos');
        
        // Obtener dispositivos según permisos
        if ($puedeVerTodos) {
            $dispositivos = $this->dispositivoModel->getTodosDispositivosConMascotas();
        } else {
            $dispositivos = $this->dispositivoModel->getDispositivosWithMascotas($user_id);
        }

        // Filtrar por búsqueda si es necesario
        if (!empty($search)) {
            $dispositivos = array_filter($dispositivos, function($dispositivo) use ($search) {
                return stripos($dispositivo['nombre'], $search) !== false || 
                       stripos($dispositivo['mac'], $search) !== false ||
                       stripos($dispositivo['usuario_nombre'], $search) !== false ||
                       stripos($dispositivo['nombre_mascota'], $search) !== false;
            });
        }

        $totalRecords = count($dispositivos);
        $recordsFiltered = $totalRecords;
        
        // Aplicar paginación
        $dispositivos = array_slice($dispositivos, $start, $length);

        $data = [];
        $canEditGlobal = verificarPermiso('editar_dispositivos');
        $canDeleteGlobal = verificarPermiso('eliminar_dispositivos');

        foreach ($dispositivos as $dispositivo) {
            $canEdit = $canEditGlobal;
            $canDelete = $canDeleteGlobal;

            // Regla: Solo puede editar/eliminar dispositivos propios si no tiene permiso global
            if (!$puedeVerTodos && $dispositivo['usuario_id'] != $user_id) {
                $canEdit = false;
                $canDelete = false;
            }

            $data[] = [
                'id' => $dispositivo['id_dispositivo'],
                'nombre' => htmlspecialchars($dispositivo['nombre']),
                'mac' => htmlspecialchars($dispositivo['mac']),
                'usuario' => htmlspecialchars($dispositivo['usuario_nombre'] ?? ''),
                'disponible' => empty($dispositivo['nombre_mascota']) ? 'Disponible' : 'Asignado',
                'estado' => $dispositivo['estado'],
                'mascota' => htmlspecialchars($dispositivo['nombre_mascota'] ?? ''),
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

    public function createAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        $userModel = new UsuarioModel();
        $usuarios = $userModel->getActiveUsers();
        $mascotaModel = $this->mascotaModel;
        $mascotas = $mascotaModel->findAll();

        // Si es AJAX, solo devuelve el formulario
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        if ($isAjax) {
            $dispositivo = null;
            if (!isset($usuarios)) $usuarios = [];
            if (!isset($mascotas)) $mascotas = [];
            require ROOT_PATH . '/views/dispositivos/form.php';
            return;
        }

        // Si no es AJAX, mostrar 404
        header('HTTP/1.0 404 Not Found');
        echo 'Página no encontrada';
        exit;
    }

    public function editAction($id = null) {
        if (!verificarPermiso('editar_dispositivos')) {
            $this->view->render('errors/403');
            return;
        }

        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        if (!$dispositivo) {
            $_SESSION['error'] = 'Dispositivo no encontrado';
            redirect('dispositivos');
        }

        // Obtener mascotas sin dispositivo + la mascota actualmente asignada
        $mascotas = $this->mascotaModel->getMascotasSinDispositivos($_SESSION['user_id']);
        if ($dispositivo['mascota_id']) {
            $mascotaActual = $this->mascotaModel->findById($dispositivo['mascota_id']);
            if ($mascotaActual) {
                $mascotas[] = $mascotaActual;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateRequest(['nombre', 'mac', 'estado']);
            // Validar formato de MAC
            if (!preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $data['mac'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Formato de MAC inválido'
                ], 400);
                return;
            }
            // Validar unicidad de MAC (excluyendo el dispositivo actual)
            if ($this->dispositivoModel->existeMac($data['mac'], $id)) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'La MAC ya está registrada'
                ], 400);
                return;
            }

            if ($this->dispositivoModel->updateDispositivo($id, $data)) {
                $this->logModel->crearLog($_SESSION['user_id'], 'Actualización de dispositivo: ' . $data['nombre']);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Dispositivo actualizado correctamente',
                    'redirect' => BASE_URL . 'dispositivos'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Error al actualizar el dispositivo'
                ], 500);
            }
        }

        $title = 'Editar Dispositivo';
        $content = $this->view->render('dispositivos/edit', [
            'dispositivo' => $dispositivo,
            'mascotas' => $mascotas
        ], true);
        $GLOBALS['content'] = $content;
        $GLOBALS['title'] = $title;
        $GLOBALS['menuActivo'] = 'dispositivos';
        require_once 'views/layouts/main.php';
    }

    public function deleteAction($id = null) {
        if (!verificarPermiso('eliminar_dispositivos')) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No tienes permiso para eliminar dispositivos'
            ], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lógica para procesar la eliminación
            if ($this->dispositivoModel->deleteDispositivo($id)) {
                $this->logModel->crearLog($_SESSION['user_id'], 'Eliminó el dispositivo ID: ' . $id);
                $_SESSION['success'] = 'Dispositivo eliminado correctamente';
                redirect('dispositivos');
            } else {
                $_SESSION['error'] = 'Error al eliminar el dispositivo';
                redirect('dispositivos/delete/' . $id);
            }
            return;
        }

        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        if (!$dispositivo) {
            $_SESSION['error'] = 'Dispositivo no encontrado';
            redirect('dispositivos');
        }

        $title = 'Eliminar Dispositivo';
        $content = $this->view->render('dispositivos/delete', [
            'dispositivo' => $dispositivo
        ], true);
        $GLOBALS['content'] = $content;
        $GLOBALS['title'] = $title;
        $GLOBALS['menuActivo'] = 'dispositivos';
        require_once 'views/layouts/main.php';
    }

    public function cambiarEstadoAction() {
        if (!verificarPermiso('editar_dispositivos')) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No tienes permiso para cambiar el estado'
            ], 403);
            return;
        }

        if (!$this->isPostRequest()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405);
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$id || !$estado) {
            return $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos.'], 400);
        }
        
        // Aquí podrías validar que el estado sea uno de los permitidos
        $estadosPermitidos = ['activo', 'inactivo', 'mantenimiento'];
        if (!in_array($estado, $estadosPermitidos)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Estado no válido.'], 400);
        }

        try {
            $resultado = $this->dispositivoModel->updateDispositivo($id, ['estado' => $estado]);
            if ($resultado) {
                $this->jsonResponse(['success' => true, 'message' => 'Estado actualizado correctamente.']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'No se pudo actualizar el estado.'], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Error interno al actualizar.'], 500);
        }
    }

    public function obtenerMascotasSinDispositivoAction($propietario_id) {
        if (!isset($_SESSION['user_id']) || !verificarPermiso('editar_dispositivos')) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No tienes permiso para realizar esta acción'
            ], 403);
            return;
        }
        // Validar que el usuario consultado sea de rol 'Usuario'
        $userModel = new UsuarioModel();
        $usuario = $userModel->getUsuarioById($propietario_id);
        if (!$usuario) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Usuario no válido'
            ]);
            return;
        }
        
        try {
            // Obtener mascotas del usuario específico que no tengan dispositivo asignado
            $mascotas = $this->mascotaModel->getMascotasSinDispositivos($propietario_id);
            $this->jsonResponse([
                'success' => true,
                'data' => $mascotas
            ]);
        } catch (Exception $e) {
            error_log('Error al obtener mascotas sin dispositivo: ' . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error al cargar las mascotas'
            ]);
        }
    }

    public function obtenerMascotasDisponiblesAction($propietario_id, $dispositivo_id = null) {
        error_log("DEBUG: obtenerMascotasDisponiblesAction llamado con propietario_id: $propietario_id, dispositivo_id: $dispositivo_id");
        error_log("DEBUG: Método HTTP: " . $_SERVER['REQUEST_METHOD']);
        error_log("DEBUG: Headers: " . print_r(getallheaders(), true));
        
        if (!isset($_SESSION['user_id']) || !verificarPermiso('editar_dispositivos')) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No tienes permiso para realizar esta acción'
            ], 403);
            return;
        }
        
        // Validar que el usuario consultado sea válido
        $userModel = new UsuarioModel();
        $usuario = $userModel->getUsuarioById($propietario_id);
        if (!$usuario) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Usuario no válido'
            ]);
            return;
        }
        
        try {
            // Obtener mascotas del usuario específico que no tengan dispositivo asignado
            $mascotas = $this->mascotaModel->getMascotasSinDispositivos($propietario_id);
            error_log("DEBUG: Mascotas sin dispositivo obtenidas: " . count($mascotas));
            
            // Si se está editando un dispositivo, incluir también la mascota actualmente asignada
            if ($dispositivo_id) {
                $dispositivo = $this->dispositivoModel->getDispositivoById($dispositivo_id);
                error_log("DEBUG: Dispositivo encontrado: " . ($dispositivo ? 'SÍ' : 'NO'));
                if ($dispositivo) {
                    error_log("DEBUG: Dispositivo mascota_id: " . ($dispositivo['mascota_id'] ?? 'NULL'));
                }
                
                if ($dispositivo && $dispositivo['mascota_id']) {
                    $mascotaActual = $this->mascotaModel->findById($dispositivo['mascota_id']);
                    error_log("DEBUG: Mascota actual encontrada: " . ($mascotaActual ? 'SÍ' : 'NO'));
                    if ($mascotaActual) {
                        error_log("DEBUG: Mascota actual: " . $mascotaActual['nombre']);
                        // Quitar duplicados por id
                        $mascotas = array_filter($mascotas, function($m) use ($mascotaActual) {
                            return $m['id_mascota'] != $mascotaActual['id_mascota'];
                        });
                        // Insertar al principio para que aparezca primero
                        array_unshift($mascotas, $mascotaActual);
                        error_log("DEBUG: Mascota actual agregada al principio");
                    }
                }
            }
            
            error_log("DEBUG: Total de mascotas a devolver: " . count($mascotas));
            $this->jsonResponse([
                'success' => true,
                'data' => $mascotas
            ]);
        } catch (Exception $e) {
            error_log('Error al obtener mascotas disponibles: ' . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error al cargar las mascotas'
            ]);
        }
    }

    public function obtenerDispositivosDisponiblesAction() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
            return;
        }
        $dispositivos = $this->dispositivoModel->getDispositivosDisponibles();
        $this->jsonResponse($dispositivos);
    }

    public function filtrarAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        $filtros = [
            'busqueda' => $_POST['busqueda'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            'usuario_id' => $_POST['usuario_id'] ?? '',
            'mascota_id' => $_POST['mascota_id'] ?? ''
        ];
        $puedeVerTodos = verificarPermiso('ver_todos_dispositivos');
        $dispositivos = $this->dispositivoModel->filtrarDispositivos($filtros, $puedeVerTodos ? null : $_SESSION['user_id']);
        ob_start();
        if (!empty($dispositivos)) {
            foreach ($dispositivos as $dispositivo) {
                include __DIR__ . '/../views/dispositivos/_fila.php';
            }
        }
        $html = ob_get_clean();
        $success = !empty($dispositivos);
        $this->jsonResponse(['success' => $success, 'html' => $html]);
    }

    // AJAX: Verificar si una MAC ya existe
    public function verificarMacAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mac'])) {
            $mac = $_POST['mac'];
            $exists = $this->dispositivoModel->existeMac($mac);
            $this->jsonResponse(['exists' => $exists]);
        } else {
            $this->jsonResponse(['exists' => false]);
        }
    }

    // AJAX: Obtener detalles del dispositivo
    public function obtenerDetallesAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
            return;
        }

        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        if (!$dispositivo) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Dispositivo no encontrado'
            ], 404);
            return;
        }

        // Verificar permisos
        if ($dispositivo['usuario_id'] != $_SESSION['user_id'] && !verificarPermiso('ver_todos_dispositivos')) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No tiene permiso para ver este dispositivo'
            ], 403);
            return;
        }

        // Obtener datos adicionales
        $userModel = new UsuarioModel();
        $usuario = $userModel->getUsuarioById($dispositivo['usuario_id']);
        
        $mascota = null;
        if ($dispositivo['mascota_id']) {
            $mascotaModel = $this->loadModel('Mascota');
            $mascota = $mascotaModel->findById($dispositivo['mascota_id']);
        }

        $this->jsonResponse([
            'success' => true,
            'data' => [
                'id_dispositivo' => $dispositivo['id_dispositivo'],
                'nombre' => $dispositivo['nombre'],
                'mac' => $dispositivo['mac'],
                'estado' => $dispositivo['estado'],
                'usuario_id' => $dispositivo['usuario_id'],
                'mascota_id' => $dispositivo['mascota_id'],
                'usuario_nombre' => $usuario ? $usuario['nombre'] : null,
                'nombre_mascota' => $mascota ? $mascota['nombre'] : null,
                'fecha_asignacion' => $dispositivo['creado_en'] ?? null
            ]
        ]);
    }

    // AJAX: Actualizar dispositivo
    public function updateAction() {
        if (!verificarPermiso('editar_dispositivos')) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No tienes permiso para editar dispositivos'
            ], 403);
            return;
        }

        error_log('SESSION EN UPDATE: ' . print_r($_SESSION, true));
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }

        $data = $this->validateRequest(['id_dispositivo', 'nombre', 'mac', 'estado']);
        
        // Validar formato de MAC
        if (!preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $data['mac'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Formato de MAC inválido'], 400);
            return;
        }

        // Validar unicidad de MAC
        if ($this->dispositivoModel->existeMac($data['mac'], $data['id_dispositivo'])) {
            $this->jsonResponse(['success' => false, 'error' => 'La MAC ya está registrada'], 400);
            return;
        }

        // Verificar permisos
        $dispositivo = $this->dispositivoModel->getDispositivoById($data['id_dispositivo']);
        
        // Un usuario puede editar un dispositivo si:
        // 1. Tiene permiso para ver todos los dispositivos, O
        // 2. Tiene permiso para editar dispositivos Y el dispositivo le pertenece
        $puedeEditarTodos = verificarPermiso('ver_todos_dispositivos');
        $puedeEditarPropios = verificarPermiso('editar_dispositivos') && $dispositivo['usuario_id'] == $_SESSION['user_id'];
        
        if (!$dispositivo || (!$puedeEditarTodos && !$puedeEditarPropios)) {
            $this->jsonResponse(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        if ($this->dispositivoModel->updateDispositivo($data['id_dispositivo'], $data)) {
            $this->logModel->crearLog($_SESSION['user_id'], 'Actualización de dispositivo: ' . $data['nombre']);
            $this->jsonResponse(['success' => true, 'message' => 'Dispositivo actualizado correctamente']);
        } else {
            $this->jsonResponse(['success' => false, 'error' => 'Error al actualizar el dispositivo'], 500);
        }
    }

    public function asignarAction() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        $dispositivo_id = $_POST['dispositivo_id'] ?? null;
        $usuario_id = $_POST['usuario_id'] ?? null;
        $mascota_id = $_POST['mascota_id'] ?? null;
        if (!$dispositivo_id || !$usuario_id) {
            $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos'], 400);
            return;
        }
        $dispositivo = $this->dispositivoModel->getDispositivoById($dispositivo_id);
        if (!$dispositivo) {
            $this->jsonResponse(['success' => false, 'error' => 'Dispositivo no encontrado'], 404);
            return;
        }
        // Si se selecciona mascota, validar que no tenga ya un dispositivo
        if ($mascota_id) {
            $dispositivosMascota = $this->dispositivoModel->getDispositivosByMascota($mascota_id);
            if (!empty($dispositivosMascota)) {
                $this->jsonResponse(['success' => false, 'error' => 'La mascota seleccionada ya tiene un dispositivo asignado.'], 400);
                return;
            }
        }
        // Actualizar el dispositivo con el nuevo propietario y mascota
        $dataUpdate = [
            'usuario_id' => $usuario_id,
            'mascota_id' => $mascota_id ?: null
        ];
        if ($this->dispositivoModel->updateDispositivo($dispositivo_id, $dataUpdate)) {
            $this->logModel->crearLog($_SESSION['user_id'], 'Reasignación de dispositivo ID: ' . $dispositivo_id . ' a usuario ID: ' . $usuario_id . ' y mascota ID: ' . ($mascota_id ?: 'Ninguna'));
            $this->jsonResponse(['success' => true, 'message' => 'Dispositivo reasignado correctamente.']);
        } else {
            $this->jsonResponse(['success' => false, 'error' => 'No se pudo reasignar el dispositivo.'], 500);
        }
    }

    private function generarIdentificador() {
        return 'DEV-' . strtoupper(uniqid());
    }

    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    protected function validateRequest($required = []) {
        // Si hay datos en $_POST, úsalos
        $data = $_POST;
        // Si no hay datos en $_POST, intenta obtenerlos del cuerpo JSON
        if (empty($data)) {
            $data = json_decode(file_get_contents('php://input'), true);
        }
        if (!$data) {
            $this->jsonResponse(['error' => 'Datos inválidos'], 400);
        }

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->jsonResponse(['error' => "El campo {$field} es requerido"], 400);
            }
        }
        return $data;
    }

    private function isPostRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public function guardarAction() {
        if (!$this->isPostRequest()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $id = $_POST['id_dispositivo'] ?? null;
        $permiso = $id ? 'editar_dispositivos' : 'crear_dispositivos';
        if (!verificarPermiso($permiso)) {
            return $this->jsonResponse(['success' => false, 'message' => 'No tienes permiso para realizar esta acción.'], 403);
        }

        // Log de datos recibidos para debugging
        error_log('DispositivosController::guardarAction - Datos recibidos: ' . print_r($_POST, true));

        // Validar campos requeridos
        if (empty($_POST['nombre']) || empty($_POST['mac']) || empty($_POST['estado'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Los campos nombre, MAC y estado son obligatorios.'], 400);
        }

        // Recoger los datos del formulario
        $data = [
            'nombre' => trim($_POST['nombre']),
            'mac' => trim($_POST['mac']),
            'estado' => $_POST['estado']
        ];

        // Manejar campos opcionales
        if (!empty($_POST['usuario_id'])) {
            $data['usuario_id'] = $_POST['usuario_id'];
        } elseif (!$id) {
            // Solo asignar usuario por defecto al crear
            $data['usuario_id'] = $_SESSION['user_id'];
        }

        // mascota_id puede ser NULL cuando no hay mascota asignada
        $data['mascota_id'] = !empty($_POST['mascota_id']) ? (int)$_POST['mascota_id'] : null;

        // Validar formato de MAC
        if (!preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $data['mac'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Formato de MAC inválido'], 400);
        }

        // Validar unicidad de MAC
        if ($this->dispositivoModel->existeMac($data['mac'], $id)) {
            return $this->jsonResponse(['success' => false, 'message' => 'La dirección MAC ya está registrada'], 400);
        }

        try {
            error_log('DispositivosController::guardarAction - Datos a guardar: ' . print_r($data, true));
            
            if ($id) {
                // Actualizar dispositivo existente
                $resultado = $this->dispositivoModel->updateDispositivo($id, $data);
                $message = 'Dispositivo actualizado correctamente.';
                error_log('Resultado de updateDispositivo: ' . ($resultado ? 'éxito' : 'fallo'));
            } else {
                // Crear nuevo dispositivo
                $resultado = $this->dispositivoModel->createDispositivo($data);
                $message = 'Dispositivo creado correctamente.';
                error_log('Resultado de createDispositivo: ' . ($resultado ? 'éxito' : 'fallo'));
            }

            if ($resultado) {
                // Log de éxito
                $this->logModel->crearLog($_SESSION['user_id'], ($id ? 'Actualización' : 'Creación') . ' de dispositivo: ' . $data['nombre']);
                $this->jsonResponse(['success' => true, 'message' => $message]);
            } else {
                $error = $this->dispositivoModel->getLastError();
                error_log('Error al guardar dispositivo: ' . $error);
                $this->jsonResponse(['success' => false, 'message' => 'Error al guardar el dispositivo: ' . $error], 500);
            }
        } catch (Exception $e) {
            error_log('Excepción en DispositivosController::guardarAction - ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Ocurrió un error al guardar el dispositivo: ' . $e->getMessage()], 500);
        }
    }

    public function toggleEstadoAction() {
        if (!verificarPermiso('editar_dispositivos')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Acción no permitida.'], 403);
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $estado_str = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);

        if ($id === false || $estado_str === null) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Datos incompletos.'], 400);
        }

        // Convertir el estado del switch ('true'/'false') al estado de la BD ('activo'/'inactivo')
        $nuevo_estado = ($estado_str === 'true') ? 'activo' : 'inactivo';

        try {
            if ($this->dispositivoModel->updateDispositivo($id, ['estado' => $nuevo_estado])) {
                $this->jsonResponse(['status' => 'success', 'message' => 'Estado actualizado con éxito.']);
            } else {
                $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo actualizar el estado.'], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Error al actualizar el estado.'], 500);
        }
    }

    public function eliminarAction() {
        if (!verificarPermiso('eliminar_dispositivos')) {
            $this->jsonResponse(['success' => false, 'message' => 'No tienes permisos para eliminar dispositivos'], 403);
            return;
        }

        if (!$this->isPostRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de dispositivo requerido'], 400);
            return;
        }

        try {
            // Verificar que el dispositivo existe
            $dispositivo = $this->dispositivoModel->getDispositivoById($id);
            if (!$dispositivo) {
                $this->jsonResponse(['success' => false, 'message' => 'Dispositivo no encontrado'], 404);
                return;
            }

            // Verificar permisos específicos
            $user_id = $_SESSION['user_id'];
            $puedeVerTodos = verificarPermiso('ver_todos_dispositivos');
            
            if (!$puedeVerTodos && $dispositivo['usuario_id'] != $user_id) {
                $this->jsonResponse(['success' => false, 'message' => 'No tienes permisos para eliminar este dispositivo'], 403);
                return;
            }

            // Eliminar el dispositivo
            $success = $this->dispositivoModel->delete($id, 'id_dispositivo');
            if ($success) {
                $this->logModel->crearLog($user_id, 'Eliminación de dispositivo: ' . $dispositivo['nombre']);
                $this->jsonResponse(['success' => true, 'message' => 'Dispositivo eliminado correctamente']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar el dispositivo'], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar dispositivo: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    public function formularioAction($id = null) {
        error_log("DEBUG: formularioAction llamado con ID: " . ($id ?? 'null'));
        
        $permiso = $id ? 'editar_dispositivos' : 'crear_dispositivos';
        if (!verificarPermiso($permiso)) {
            $this->view->render('partials/modal_error', ['mensaje' => 'No tienes permiso para realizar esta acción.'], false);
            return;
        }
        $userModel = new UsuarioModel();
        $usuarios = $userModel->getActiveUsers();
        $dispositivo = null;
        $mascotas = [];
        if ($id) {
            $dispositivo = $this->dispositivoModel->getDispositivoById($id);
            error_log("DEBUG: Dispositivo encontrado: " . ($dispositivo ? 'SÍ' : 'NO'));
            if ($dispositivo) {
                error_log("DEBUG: Dispositivo datos: " . print_r($dispositivo, true));
            }
            
            if (!$dispositivo) {
                $this->view->render('partials/modal_error', ['mensaje' => 'Dispositivo no encontrado.'], false);
                return;
            }
            if ($dispositivo['usuario_id']) {
                $mascotas = $this->mascotaModel->getMascotasSinDispositivos($dispositivo['usuario_id']);
                if ($dispositivo['mascota_id']) {
                    $mascotaActual = $this->mascotaModel->findById($dispositivo['mascota_id']);
                    if ($mascotaActual) {
                        // Quitar duplicados por id
                        $mascotas = array_filter($mascotas, function($m) use ($mascotaActual) {
                            return $m['id_mascota'] != $mascotaActual['id_mascota'];
                        });
                        // Insertar al principio
                        array_unshift($mascotas, $mascotaActual);
                    }
                }
            }
        } else {
            // Nuevo: traer todas las mascotas
            $mascotas = $this->mascotaModel->findAll();
        }
        require ROOT_PATH . '/views/dispositivos/form.php';
    }
}
?> 