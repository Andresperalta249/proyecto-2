<?php
class DispositivosController extends Controller {
    private $dispositivoModel;
    private $mascotaModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->dispositivoModel = $this->loadModel('Dispositivo');
        $this->mascotaModel = $this->loadModel('Mascota');
        $this->logModel = $this->loadModel('Log');
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }
        $user_id = $_SESSION['user_id'];
        $puedeVerTodos = verificarPermiso('ver_todos_dispositivo');
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        if ($puedeVerTodos) {
            $dispositivos = $this->dispositivoModel->getDispositivosPaginados($offset, $perPage);
            $totalDispositivos = $this->dispositivoModel->getTotalDispositivos();
        } else {
            // Para usuarios normales, solo sus dispositivos
            $sql = "SELECT d.*, m.nombre as mascota_nombre, u.nombre as propietario_nombre
                    FROM dispositivos d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    LEFT JOIN usuarios u ON d.propietario_id = u.id
                    WHERE d.propietario_id = :user_id
                    ORDER BY d.ultima_conexion DESC
                    LIMIT :offset, :limit";
            $stmt = Database::getInstance()->getConnection()->prepare($sql);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
            $stmt->execute();
            $dispositivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalDispositivos = $this->dispositivoModel->getTotalDispositivos();
        }
        // Optimización: obtener todas las últimas lecturas en una sola consulta
        $ids = array_column($dispositivos, 'id_dispositivo');
        $ultimasLecturas = $this->dispositivoModel->getUltimasLecturasPorDispositivos($ids);
        foreach ($dispositivos as &$dispositivo) {
            $dispositivo['ultima_lectura'] = $ultimasLecturas[$dispositivo['id_dispositivo']] ?? null;
        }
        unset($dispositivo);
        // Filtrar solo usuarios con rol 'Usuario'
        $userModel = $this->loadModel('User');
        $usuarios = array_filter($userModel->getAll(), function($u) {
            return isset($u['rol_nombre']) && strtolower($u['rol_nombre']) === 'usuario';
        });
        $mascotas = $this->mascotaModel->findAll();
        $title = 'Administrador de dispositivos';
        $content = $this->render('dispositivos/index', [
            'dispositivos' => $dispositivos,
            'usuarios' => $usuarios,
            'mascotas' => $mascotas,
            'totalDispositivos' => $totalDispositivos,
            'perPage' => $perPage
        ]);
        $GLOBALS['content'] = $content;
        $GLOBALS['title'] = $title;
        $GLOBALS['menuActivo'] = 'dispositivos';
        require_once 'views/layouts/main.php';
    }

    public function createAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        $userModel = $this->loadModel('User');
        $usuarios = $userModel->getUsuarios();
        $mascotaModel = $this->mascotaModel;
        $mascotas = $mascotaModel->findAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Log de los datos recibidos
                error_log('Datos POST recibidos: ' . print_r($_POST, true));
                
                // Validar campos requeridos
                $requiredFields = ['nombre', 'mac', 'estado'];
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field])) {
                        $this->jsonResponse([
                            'success' => false,
                            'error' => "El campo {$field} es requerido"
                        ], 400);
                        return;
                    }
                }
                
                $data = [
                    'nombre' => $_POST['nombre'],
                    'mac' => $_POST['mac'],
                    'estado' => $_POST['estado'],
                    'user_id' => $_SESSION['user_id']
                ];
                
                // Validar usuario_id si se proporciona
                if (!empty($_POST['usuario_id'])) {
                    $usuario = $userModel->getUsuarioById($_POST['usuario_id']);
                    if (!$usuario) {
                        $this->jsonResponse([
                            'success' => false,
                            'error' => 'Usuario no válido'
                        ], 400);
                        return;
                    }
                    $data['user_id'] = $_POST['usuario_id'];
                }
                
                // Validar mascota_id si se proporciona
                if (!empty($_POST['mascota_id'])) {
                    $mascota = $mascotaModel->findById($_POST['mascota_id']);
                    if (!$mascota) {
                        $this->jsonResponse([
                            'success' => false,
                            'error' => 'Mascota no válida'
                        ], 400);
                        return;
                    }
                    $data['mascota_id'] = $_POST['mascota_id'];
                }
                
                // Log de los datos validados
                error_log('Datos validados: ' . print_r($data, true));
                
                // Validar formato de MAC
                if (!preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $data['mac'])) {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Formato de MAC inválido'
                    ], 400);
                    return;
                }
                
                // Validar unicidad de MAC
                if ($this->dispositivoModel->existeMac($data['mac'])) {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'La MAC ya está registrada'
                    ], 400);
                    return;
                }
                
                // Log antes de crear el dispositivo
                error_log('Intentando crear dispositivo con datos: ' . print_r($data, true));
                
                try {
                    $resultado = $this->dispositivoModel->createDispositivo($data);
                    
                    // Log del resultado
                    error_log('Resultado de createDispositivo: ' . ($resultado ? 'éxito' : 'fallo'));
                    
                    if ($resultado) {
                        $this->logModel->crearLog($_SESSION['user_id'], 'Creación de dispositivo: ' . $data['nombre']);
                        $this->jsonResponse([
                            'success' => true,
                            'message' => 'Dispositivo registrado correctamente',
                            'redirect' => BASE_URL . 'dispositivos'
                        ]);
                    } else {
                        $error = $this->dispositivoModel->getLastError();
                        error_log('Error al crear dispositivo: ' . $error);
                        $this->jsonResponse([
                            'success' => false,
                            'error' => 'Error al registrar el dispositivo: ' . $error
                        ], 500);
                    }
                } catch (PDOException $e) {
                    error_log('Error PDO al crear dispositivo: ' . $e->getMessage());
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Error de base de datos: ' . $e->getMessage()
                    ], 500);
                }
            } catch (Exception $e) {
                error_log('Excepción en createAction: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Error interno del servidor: ' . $e->getMessage()
                ], 500);
            }
        }

        $title = 'Nuevo Dispositivo';
        $content = $this->render('dispositivos/create', [
            'usuarios' => $usuarios,
            'mascotas' => $mascotas
        ]);
        $GLOBALS['content'] = $content;
        $GLOBALS['title'] = $title;
        $GLOBALS['menuActivo'] = 'dispositivos';
        require_once 'views/layouts/main.php';
    }

    public function editAction($id = null) {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
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
                $this->logModel->crearLog($_SESSION['propietario_id'], 'Actualización de dispositivo: ' . $data['nombre']);
                
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
        $content = $this->render('dispositivos/edit', [
            'dispositivo' => $dispositivo,
            'mascotas' => $mascotas
        ]);
        $GLOBALS['content'] = $content;
        $GLOBALS['title'] = $title;
        $GLOBALS['menuActivo'] = 'dispositivos';
        require_once 'views/layouts/main.php';
    }

    public function deleteAction($id = null) {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        if (!$dispositivo) {
            $_SESSION['error'] = 'Dispositivo no encontrado';
            redirect('dispositivos');
        }

        $title = 'Eliminar Dispositivo';
        $content = $this->render('dispositivos/delete', [
            'dispositivo' => $dispositivo
        ]);
        $GLOBALS['content'] = $content;
        $GLOBALS['title'] = $title;
        $GLOBALS['menuActivo'] = 'dispositivos';
        require_once 'views/layouts/main.php';
    }

    public function cambiarEstadoAction() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }

            if (!verificarPermiso('cambiar_estado_dispositivos')) {
                throw new Exception('No tiene permiso para cambiar el estado de dispositivos');
            }

            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('ID de dispositivo inválido');
            }

            $estado = $_POST['estado'] ?? '';
            if (!in_array($estado, ['activo', 'inactivo'])) {
                throw new Exception('Estado inválido');
            }

            $dispositivo = $this->dispositivoModel->getDispositivoById($id);
            if (!$dispositivo) {
                throw new Exception('Dispositivo no encontrado');
            }

            // Solo superadmin/admin o dueño pueden cambiar el estado
            $propietarioLogueadoId = $_SESSION['propietario_id'] ?? 0;
            $esSuperAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1;
            if (!$esSuperAdmin && $dispositivo['propietario_id'] != $propietarioLogueadoId) {
                throw new Exception('No puede cambiar el estado de este dispositivo');
            }

            $ok = $this->dispositivoModel->updateDispositivo($id, ['estado' => $estado]);
            if (!$ok) {
                throw new Exception('Error al actualizar el estado');
            }

            $this->logModel->crearLog($_SESSION['user_id'], 'Cambió el estado del dispositivo ID: ' . $id . ' a ' . $estado);
            echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);

        } catch (Exception $e) {
            error_log("Error en cambiarEstadoAction: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function obtenerMascotasSinDispositivoAction($propietario_id) {
        if (!isset($_SESSION['propietario_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
            return;
        }
        // Validar que el usuario consultado sea de rol 'Usuario'
        $userModel = $this->loadModel('User');
        $usuario = $userModel->getUsuarioById($propietario_id);
        if (!$usuario || strtolower($usuario['rol_nombre']) !== 'usuario') {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Usuario no válido'
            ]);
            return;
        }
        $mascotas = $this->mascotaModel->getMascotasSinDispositivos($propietario_id);
        $this->jsonResponse([
            'success' => true,
            'data' => $mascotas
        ]);
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
            'mascota_id' => $_POST['mascota_id'] ?? '',
            'bateria' => $_POST['bateria'] ?? ''
        ];
        $puedeVerTodos = verificarPermiso('ver_todos_dispositivo');
        $dispositivos = $this->dispositivoModel->filtrarDispositivos($filtros, $puedeVerTodos ? null : $_SESSION['propietario_id']);
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
        if ($dispositivo['propietario_id'] != $_SESSION['propietario_id'] && !verificarPermiso('ver_todos_dispositivo')) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No tiene permiso para ver este dispositivo'
            ], 403);
            return;
        }

        // Obtener datos adicionales
        $userModel = $this->loadModel('User');
        $usuario = $userModel->getUsuarioById($dispositivo['propietario_id']);
        
        $mascota = null;
        if ($dispositivo['mascota_id']) {
            $mascotaModel = $this->loadModel('Mascota');
            $mascota = $mascotaModel->findById($dispositivo['mascota_id']);
        }

        $this->jsonResponse([
            'success' => true,
            'data' => [
                'id' => $dispositivo['id_dispositivo'],
                'nombre' => $dispositivo['nombre'],
                'mac' => $dispositivo['mac'],
                'estado' => $dispositivo['estado'],
                'bateria' => $dispositivo['bateria'],
                'ultima_lectura' => $dispositivo['ultima_conexion'],
                'usuario_nombre' => $usuario ? $usuario['nombre'] : null,
                'mascota_nombre' => $mascota ? $mascota['nombre'] : null,
                'fecha_asignacion' => $dispositivo['creado_en']
            ]
        ]);
    }

    // AJAX: Actualizar dispositivo
    public function updateAction() {
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
        $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]);
        if (
            !$dispositivo ||
            (!$esAdmin && !verificarPermiso('editar_todos_dispositivos') && $dispositivo['propietario_id'] != $_SESSION['propietario_id'])
        ) {
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
            'propietario_id' => $usuario_id,
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
}
?> 