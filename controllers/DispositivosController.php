<?php
class DispositivosController extends Controller {
    private $dispositivoModel;
    private $mascotaModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->dispositivoModel = $this->loadModel('DispositivoModel');
        $this->mascotaModel = $this->loadModel('Mascota');
        $this->logModel = $this->loadModel('Log');
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }
        $user_id = $_SESSION['user_id'];
        $puedeVerTodos = verificarPermiso('ver_todos_dispositivo');
        
        // Obtener todos los dispositivos sin paginación
        $dispositivos = $this->dispositivoModel->getTodosDispositivosConMascotas();
        $totalDispositivos = count($dispositivos);
        // Filtrar solo usuarios con rol 'Usuario'
        $userModel = $this->loadModel('User');
        $resultado = $userModel->getAll();
        $usuarios = array_filter($resultado['usuarios'], function($u) {
            return isset($u['rol_nombre']) && strtolower($u['rol_nombre']) === 'usuario';
        });
        $mascotas = $this->mascotaModel->findAll();
        $title = 'Administrador de dispositivos';
        $content = $this->render('dispositivos/index', [
            'dispositivos' => $dispositivos,
            'usuarios' => $usuarios,
            'mascotas' => $mascotas,
            'totalDispositivos' => $totalDispositivos
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
        $resultado = $userModel->getAll();
        $usuarios = $resultado['usuarios'] ?? [];
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
                    'user_id' => null  // Por defecto sin usuario asignado
                ];
                
                // Validar usuario_id si se proporciona
                if (!empty($_POST['usuario_id'])) {
                    $usuario = $userModel->findById($_POST['usuario_id']);
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

    // AJAX: Eliminar dispositivo
    public function deleteAjaxAction($id = null) {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->jsonResponse(['success' => false, 'error' => 'Acceso denegado'], 403);
                return;
            }

            if (!$id) {
                $this->jsonResponse(['success' => false, 'error' => 'ID de dispositivo requerido'], 400);
                return;
            }

            // Verificar que el dispositivo existe
            $dispositivo = $this->dispositivoModel->getDispositivoById($id);
            if (!$dispositivo) {
                $this->jsonResponse(['success' => false, 'error' => 'Dispositivo no encontrado'], 404);
                return;
            }

            // Verificar permisos
            $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2,3]);
            $tienePermisoEliminar = verificarPermiso('eliminar_dispositivos');
            
            if (!$esAdmin && !$tienePermisoEliminar) {
                $this->jsonResponse(['success' => false, 'error' => 'No tienes permisos para eliminar dispositivos'], 403);
                return;
            }

            // Eliminar el dispositivo
            if ($this->dispositivoModel->deleteDispositivo($id)) {
                $this->logModel->crearLog($_SESSION['user_id'], 'Eliminación de dispositivo: ' . $dispositivo['nombre']);
                $this->jsonResponse(['success' => true, 'message' => 'Dispositivo eliminado correctamente']);
            } else {
                $this->jsonResponse(['success' => false, 'error' => 'Error al eliminar el dispositivo'], 500);
            }

        } catch (Exception $e) {
            error_log("Error en deleteAjaxAction: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }

    public function cambiarEstadoAction() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
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

    public function obtenerMascotasSinDispositivoAction($usuario_id) {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
            return;
        }
        // Validar que el usuario consultado sea de rol 'Usuario'
        $userModel = $this->loadModel('User');
        $usuario = $userModel->findById($usuario_id);
        if (!$usuario || strtolower($usuario['rol_nombre']) !== 'usuario') {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Usuario no válido'
            ]);
            return;
        }
        $mascotas = $this->mascotaModel->getMascotasSinDispositivos($usuario_id);
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
            'mascota_id' => $_POST['mascota_id'] ?? ''
        ];
        $puedeVerTodos = verificarPermiso('ver_todos_dispositivo');
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
        try {
            // Debug temporal
            error_log("DEBUG obtenerDetallesAction - SESSION: " . print_r($_SESSION, true));
            error_log("DEBUG obtenerDetallesAction - ID recibido: " . $id);
            
            if (!isset($_SESSION['user_id']) || !$id) {
                error_log("DEBUG obtenerDetallesAction - Acceso denegado: user_id=" . ($_SESSION['user_id'] ?? 'NO SET') . ", id=" . $id);
                echo json_encode([
                    'success' => false,
                    'error' => 'Acceso denegado'
                ]);
                return;
            }

            $dispositivo = $this->dispositivoModel->getDispositivoById($id);
            error_log("DEBUG obtenerDetallesAction - Dispositivo encontrado: " . print_r($dispositivo, true));
            
            if (!$dispositivo) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Dispositivo no encontrado'
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $dispositivo['id_dispositivo'],
                    'nombre' => $dispositivo['nombre'],
                    'mac' => $dispositivo['mac'],
                    'estado' => $dispositivo['estado'],
                    'bateria' => 'N/A',
                    'ultima_lectura' => $dispositivo['creado_en'],
                    'usuario_nombre' => 'Usuario ID: ' . $dispositivo['usuario_id'],
                    'mascota_nombre' => $dispositivo['mascota_id'] ? 'Mascota ID: ' . $dispositivo['mascota_id'] : null,
                    'fecha_asignacion' => $dispositivo['creado_en']
                ]
            ]);
        } catch (Exception $e) {
            error_log("Error en obtenerDetallesAction: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }

    // AJAX: Actualizar dispositivo
    public function updateAction() {
        error_log('SESSION EN UPDATE: ' . print_r($_SESSION, true));
        error_log('POST DATA EN UPDATE: ' . print_r($_POST, true));
        
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }

        try {
            $data = $this->validateRequest(['id_dispositivo', 'nombre', 'mac', 'estado']);
            error_log('DATA VALIDADA EN UPDATE: ' . print_r($data, true));
            
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
            error_log('DISPOSITIVO ENCONTRADO EN UPDATE: ' . print_r($dispositivo, true));
            
            // Verificación de permisos
            $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2,3]); // Incluir Super Admin (rol 3)
            $tienePermisoEditar = verificarPermiso('editar_dispositivos');
            
            error_log('DEBUG PERMISOS - esAdmin: ' . ($esAdmin ? 'true' : 'false') . ', tienePermisoEditar: ' . ($tienePermisoEditar ? 'true' : 'false'));
            
            if (!$dispositivo) {
                $this->jsonResponse(['success' => false, 'error' => 'Dispositivo no encontrado'], 404);
                return;
            }
            
            // Permitir si es admin o tiene permiso de editar dispositivos
            if (!$esAdmin && !$tienePermisoEditar) {
                $this->jsonResponse(['success' => false, 'error' => 'No tienes permisos para editar dispositivos'], 403);
                return;
            }

            if ($this->dispositivoModel->updateDispositivo($data['id_dispositivo'], $data)) {
                $this->logModel->crearLog($_SESSION['user_id'], 'Actualización de dispositivo: ' . $data['nombre']);
                $this->jsonResponse(['success' => true, 'message' => 'Dispositivo actualizado correctamente']);
            } else {
                $this->jsonResponse(['success' => false, 'error' => 'Error al actualizar el dispositivo'], 500);
            }
        } catch (Exception $e) {
            error_log('ERROR EN UPDATE ACTION: ' . $e->getMessage());
            error_log('STACK TRACE: ' . $e->getTraceAsString());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
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
        // Actualizar el dispositivo con el nuevo usuario y mascota
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
}
?> 