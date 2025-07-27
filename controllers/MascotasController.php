<?php

class MascotasController extends Controller {
    private $mascotaModel;
    private $dispositivoModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->mascotaModel = $this->loadModel('Mascota');
        $this->dispositivoModel = $this->loadModel('DispositivoModel');
        $this->logModel = $this->loadModel('Log');
        // Removido notificacionModel ya que no existe el modelo Notificacion
    }

    public function indexAction() {
        try {
            $propietario_id = $_SESSION['user_id'];
            $puedeVerTodos = verificarPermiso('ver_todos_mascotas');
            
            // Cargar usuarios para el select de propietarios
            $usuariosModel = $this->loadModel('User');
            $usuarios = $usuariosModel->getActiveUsers();
            
            if ($puedeVerTodos) {
                $mascotas = $this->mascotaModel->getMascotasConDispositivo();
            } else {
                $mascotas = $this->mascotaModel->getMascotasConDispositivo(); // Para todos, ya que el método filtra por usuario si es necesario
            }
            error_log('Mascotas obtenidas: ' . print_r($mascotas, true));
            
            $title = 'Administrador de mascotas';
            $content = $this->render('mascotas/index', [
                'mascotas' => $mascotas,
                'usuarios' => $usuarios
            ]);
            $GLOBALS['content'] = $content;
            $GLOBALS['title'] = $title;
            $GLOBALS['menuActivo'] = 'mascotas';
            require_once 'views/layouts/main.php';
        } catch (Exception $e) {
            $title = 'Error';
            $content = $this->render('mascotas/index', [
                'error' => $e->getMessage(),
                'usuarios' => []
            ]);
            $GLOBALS['content'] = $content;
            $GLOBALS['title'] = $title;
            $GLOBALS['menuActivo'] = 'mascotas';
            require_once 'views/layouts/main.php';
        }
    }

    public function createAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateRequest(['nombre', 'especie', 'tamano', 'fecha_nacimiento']);
            $data['propietario_id'] = $_SESSION['propietario_id'];

            // Validar especie permitida
            $especiesPermitidas = ['perro', 'gato'];
            if (!in_array(strtolower($data['especie']), $especiesPermitidas)) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Solo se permiten especies Perro o Gato.'
                ], 400);
            }

            // Validar que todos los campos estén completos
            foreach (['nombre', 'especie', 'tamano', 'fecha_nacimiento'] as $campo) {
                if (empty($data[$campo])) {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Todos los campos son obligatorios.'
                    ], 400);
                }
            }

            // Validar nombre único por usuario
            $existe = $this->mascotaModel->findAll([
                'propietario_id' => $_SESSION['propietario_id'],
                'nombre' => $data['nombre']
            ]);
            if ($existe) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Ya existe una mascota con ese nombre.'
                ], 400);
            }

            // Permisos para propietario y estado
            $puedeAsignarPropietario = in_array('gestionar_mascotas', $_SESSION['permissions'] ?? []);
            if ($puedeAsignarPropietario && isset($_POST['propietario_id'])) {
                $data['propietario_id'] = $_POST['propietario_id'];
            }
            if (isset($_POST['estado'])) {
                $data['estado'] = $_POST['estado'];
            }

            if ($this->mascotaModel->createMascota($data)) {
                $this->logModel->crearLog($_SESSION['propietario_id'], 'Creación de mascota: ' . $data['nombre']);
                // Notificación removida ya que no existe el modelo Notificacion
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Mascota registrada correctamente',
                    'redirect' => BASE_URL . 'mascotas'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Error al registrar la mascota'
                ], 500);
            }
        }

        // Obtener usuarios activos para el select de propietario
        $usuariosModel = $this->loadModel('User');
        $usuarios = $usuariosModel->getActiveUsers();
        $title = 'Nueva Mascota';
        $content = $this->render('mascotas/edit_modal', ['usuarios' => $usuarios]);
        require_once 'views/layouts/main.php';
    }

    public function editAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        $mascota = $this->mascotaModel->findById($id);
        $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]);
        $puedeEditarTodas = function_exists('verificarPermiso') ? verificarPermiso('editar_todas_mascotas') : false;
        if (!$mascota) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Mascota no encontrada'
            ], 404);
        }
        if (!$esAdmin && !$puedeEditarTodas && $mascota['propietario_id'] != $_SESSION['propietario_id']) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No tienes permisos para editar esta mascota porque no eres el propietario.'
            ], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'especie' => $_POST['especie'] ?? '',
                'tamano' => $_POST['tamano'] ?? '',
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
                'genero' => $_POST['genero'] ?? ''
            ];

            // Solo permitir cambiar propietario y estado si tiene permisos
            if ($esAdmin || $puedeEditarTodas) {
                if (isset($_POST['propietario_id'])) {
                    $data['propietario_id'] = $_POST['propietario_id'];
                }
                if (isset($_POST['estado'])) {
                    $data['estado'] = $_POST['estado'];
                }
            }

            if ($this->mascotaModel->updateMascota($id, $data)) {
                $this->logModel->crearLog($_SESSION['propietario_id'], 'Actualización de mascota: ' . $data['nombre']);
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Mascota actualizada correctamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Error al actualizar la mascota'
                ], 500);
            }
        }
    }

    public function deleteAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        $mascota = $this->mascotaModel->findById($id);
        $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]);
        $puedeEliminarTodas = function_exists('verificarPermiso') ? verificarPermiso('eliminar_todas_mascotas') : false;
        if (!$mascota) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Mascota no encontrada'
            ], 404);
        }
        if (!$esAdmin && !$puedeEliminarTodas && $mascota['propietario_id'] != $_SESSION['propietario_id']) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No tienes permisos para eliminar esta mascota porque no eres el propietario.'
            ], 403);
        }

        // Liberar dispositivos asociados y eliminar sus datos
        $dispositivos = $this->dispositivoModel->getDispositivosByMascota($id);
        foreach ($dispositivos as $dispositivo) {
            // Liberar el dispositivo
            $this->dispositivoModel->updateDispositivo($dispositivo['id'], ['mascota_id' => null]);
            // Eliminar datos del dispositivo (asumiendo método en modelo)
            if (method_exists($this->dispositivoModel, 'eliminarDatosDispositivo')) {
                $this->dispositivoModel->eliminarDatosDispositivo($dispositivo['id']);
            }
        }

        if ($this->mascotaModel->deleteMascota($id)) {
            $this->logModel->crearLog($_SESSION['propietario_id'], 'Eliminación de mascota: ' . $mascota['nombre']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Mascota "' . $mascota['nombre'] . '" eliminada correctamente'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error al eliminar la mascota'
            ], 500);
        }
    }

    public function viewAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            redirect('auth/login');
        }

        // Verificar que la mascota pertenezca al usuario
        $mascota = $this->mascotaModel->findById($id);
        if (!$mascota || $mascota['propietario_id'] != $_SESSION['propietario_id']) {
            redirect('mascotas');
        }

        // Obtener dispositivos asignados
        $dispositivos = $this->dispositivoModel->getDispositivosByMascota($id);
        
        $title = 'Detalles de Mascota';
        $content = $this->render('mascotas/view', [
            'mascota' => $mascota,
            'dispositivos' => $dispositivos
        ]);
        require_once 'views/layouts/main.php';
    }

    public function guardarAction() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Acceso denegado'
            ], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'especie' => $_POST['especie'] ?? '',
                'tamano' => $_POST['tamano'] ?? '',
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
                'usuario_id' => $_SESSION['propietario_id']
            ];
            if (isset($_POST['genero'])) {
                $data['genero'] = $_POST['genero'];
            }
            if (isset($_POST['propietario_id'])) {
                $data['propietario_id'] = $_POST['propietario_id'];
            }
            if (isset($_POST['estado'])) {
                $data['estado'] = $_POST['estado'];
            }

            if ($this->mascotaModel->createMascota($data)) {
                $this->logModel->crearLog($_SESSION['propietario_id'], 'Creación de mascota: ' . $data['nombre']);
                $this->jsonResponse([
                    'status' => 'success',
                    'message' => 'Mascota guardada exitosamente'
                ]);
            } else {
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Error al guardar la mascota'
                ]);
            }
        }
    }

    public function crearAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }
        $usuariosModel = $this->loadModel('User');
        $usuarios = $usuariosModel->getActiveUsers();
        // Renderizar solo el formulario sin el layout
        echo $this->render('mascotas/edit_modal', ['usuarios' => $usuarios], true);
        exit;
    }

    private function handleImageUpload($file) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        $uploadDir = 'uploads/mascotas/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $uploadFile = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            return $filename;
        }

        return false;
    }

    private function handleDocumentUpload($file) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        $uploadDir = 'uploads/documentos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $uploadFile = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            return $filename;
        }

        return false;
    }

    private function deleteImage($filename) {
        $filepath = 'uploads/mascotas/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    public function tablaAction() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            exit;
        }

        // Verificar si es admin/superadmin y tiene permiso para ver todas las mascotas
        $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1, 2]);
        $tienePermiso = function_exists('verificarPermiso') ? verificarPermiso('ver_todas_mascotas') : false;

        $filtros = [
            'nombre' => $_GET['nombre'] ?? '',
            'especie' => strtolower($_GET['especie'] ?? ''),
            'estado' => $_GET['estado'] ?? '',
            'busqueda' => $_GET['busqueda'] ?? '',
            'offset' => isset($_GET['offset']) ? intval($_GET['offset']) : 0,
            'limit' => isset($_GET['limit']) ? intval($_GET['limit']) : 20
        ];
        if (!($esAdmin && $tienePermiso)) {
            $filtros['usuario_id'] = $_SESSION['propietario_id'];
        } else if (isset($_GET['usuario_id'])) {
            $filtros['usuario_id'] = $_GET['usuario_id'];
        }

        // Limpieza estricta de filtros para evitar conflicto de parámetros
        if (!empty($filtros['busqueda'])) {
            unset($filtros['nombre'], $filtros['especie']);
            unset($_GET['nombre'], $_GET['especie']);
        }

        $mascotas = $this->mascotaModel->getMascotasFiltradas($filtros);
        if ($mascotas === false) {
            error_log('Error en consulta SQL: getMascotasFiltradas devolvió false');
            $mascotas = [];
        }
        
        // Obtener usuarios activos para mostrar información del propietario
        $usuariosModel = $this->loadModel('User');
        $usuarios = $usuariosModel->getActiveUsers();
        
        // Agregar información del propietario a cada mascota
        foreach ($mascotas as &$mascota) {
            $propietario = $usuariosModel->getUsuarioById($mascota['propietario_id']);
            $mascota['propietario'] = $propietario ? $propietario['nombre'] : 'Sin propietario';
            
            // Validar si cada mascota tiene dispositivo asociado
            $dispositivos = $this->dispositivoModel->getDispositivosByMascota($mascota['id']);
            $mascota['tiene_dispositivo'] = !empty($dispositivos);
        }
        unset($mascota);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'mascotas' => $mascotas,
            'usuarios' => $usuarios,
            'permisos' => [
                'editar_cualquiera' => in_array('editar_cualquier_mascota', $_SESSION['permissions'] ?? []),
                'editar_propias' => in_array('editar_mascotas', $_SESSION['permissions'] ?? []),
                'eliminar' => in_array('eliminar_mascotas', $_SESSION['permissions'] ?? []),
                'propietario_id' => $_SESSION['propietario_id'] ?? null
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function cambiarEstadoAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }
        $mascota = $this->mascotaModel->findById($id);
        $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]);
        $puedeEditarCualquiera = in_array('editar_cualquier_mascota', $_SESSION['permissions'] ?? []);
        $puedeEditarPropias = in_array('editar_mascotas', $_SESSION['permissions'] ?? []);
        if (
            !$mascota ||
            (
                !$esAdmin &&
                !$puedeEditarCualquiera &&
                !($puedeEditarPropias && $mascota['propietario_id'] == $_SESSION['propietario_id'])
            )
        ) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Mascota no encontrada'
            ], 404);
        }
        $nuevoEstado = $_POST['estado'] ?? '';
        if (!in_array($nuevoEstado, ['activo', 'inactivo'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Estado no válido'
            ], 400);
        }
        if ($this->mascotaModel->updateMascota($id, ['estado' => $nuevoEstado])) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No se pudo actualizar el estado'
            ], 500);
        }
    }

    public function editarModalAction($id = null) {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Debes iniciar sesión para realizar esta acción.'
            ]);
            exit;
        }

        // Obtener el ID de la mascota de la URL o del GET
        $id = $id ?? $_GET['id'] ?? null;
        $usuariosModel = $this->loadModel('User');
        $usuarios = $usuariosModel->getActiveUsers();

        if (!$id) {
            // Crear nueva mascota: renderizar el formulario vacío
            echo $this->render('mascotas/edit_modal', [
                'mascota' => [],
                'usuarios' => $usuarios
            ], true);
            exit;
        }

        $mascota = $this->mascotaModel->findById($id);
        if (!$mascota) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Mascota no encontrada.'
            ]);
            exit;
        }

        // Verificar permisos
        $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]);
        $puedeEditarCualquiera = in_array('editar_cualquier_mascota', $_SESSION['permissions'] ?? []);
        $puedeEditarPropias = in_array('editar_mascotas', $_SESSION['permissions'] ?? []);

        if (!$esAdmin && !$puedeEditarCualquiera && !($puedeEditarPropias && $mascota['propietario_id'] == $_SESSION['propietario_id'])) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'No tienes permisos para editar esta mascota.'
            ]);
            exit;
        }

        $dispositivos = $this->dispositivoModel->getDispositivosByMascota($id);

        // Renderizar el formulario de edición
        echo $this->render('mascotas/edit_modal', [
            'mascota' => $mascota,
            'dispositivos' => $dispositivos,
            'usuarios' => $usuarios
        ], true);
        exit;
    }

    public function obtenerPorUsuarioAction($usuario_id = null) {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
            return;
        }

        // Verificar permisos
        if ($usuario_id != $_SESSION['propietario_id'] && !verificarPermiso('ver_todos_dispositivo')) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No tiene permiso para ver las mascotas de otros usuarios'
            ], 403);
            return;
        }

        // Solo mascotas sin dispositivo
        $mascotas = $this->mascotaModel->getMascotasSinDispositivos($usuario_id);
        $this->jsonResponse([
            'success' => true,
            'data' => $mascotas
        ]);
    }

    public function buscarAction() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['mascotas' => []]);
            exit;
        }
        $termino = isset($_GET['termino']) ? trim($_GET['termino']) : '';
        $mascotas = [];
        $soloPropias = !verificarPermiso('ver_todos_mascotas');
        if ($termino === '') {
            if ($soloPropias) {
                $mascotas = $this->mascotaModel->getMascotasByUser($_SESSION['user_id']);
            } else {
                $mascotas = $this->mascotaModel->findAll();
            }
        } else {
            $mascotas = $this->mascotaModel->buscarMascotasPorTermino($termino, $_SESSION['user_id'], $soloPropias);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['mascotas' => $mascotas], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function getAction($id = null) {
        if ($id) {
            $mascota = $this->mascotaModel->findById($id);
            $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]);
            $puedeEditarTodas = function_exists('verificarPermiso') ? verificarPermiso('editar_todas_mascotas') : false;
            $puedeEliminarTodas = function_exists('verificarPermiso') ? verificarPermiso('eliminar_todas_mascotas') : false;
            error_log('ID recibido en getAction: ' . $id);
            error_log('Usuario logueado: ' . print_r($_SESSION, true));
            error_log('Es admin: ' . ($esAdmin ? 'SI' : 'NO'));
            error_log('Permiso editar_todas_mascotas: ' . ($puedeEditarTodas ? 'SI' : 'NO'));
            error_log('Permiso eliminar_todas_mascotas: ' . ($puedeEliminarTodas ? 'SI' : 'NO'));
            error_log('Mascota encontrada: ' . print_r($mascota, true));
            if (!$mascota) {
                echo json_encode(['success' => false, 'error' => 'Mascota no encontrada']);
                return;
            }
            if (!$esAdmin && !$puedeEditarTodas && !$puedeEliminarTodas && $mascota['propietario_id'] != $_SESSION['propietario_id']) {
                echo json_encode(['success' => false, 'error' => 'No tienes permisos para ver esta mascota.']);
                return;
            }
            // Obtener dispositivos asociados
            $dispositivos = $this->dispositivoModel->getDispositivosByMascota($id);
            $mascota['dispositivos'] = $dispositivos;
            echo json_encode(['success' => true, 'mascota' => $mascota]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
        }
    }
}
?> 