<?php

class MascotasController extends Controller {
    private $mascotaModel;
    private $logModel;
    private $usuarioModel;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación para todo el controlador
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        $this->mascotaModel = $this->loadModel('Mascota');
        $this->logModel = $this->loadModel('Log');
        $this->usuarioModel = $this->loadModel('UsuarioModel');
    }

    public function indexAction() {
        if (!verificarPermiso('ver_mascotas')) {
            $this->view->render('errors/403');
            return;
        }

        $this->view->setLayout('main');
        $this->view->setData('titulo', 'Gestión de Mascotas');
        $this->view->setData('subtitulo', 'Administra y consulta la información de las mascotas.');
        
        $propietario_id = $_SESSION['user_id'];
        $puedeVerTodos = verificarPermiso('ver_todos_mascotas');
        
        $usuariosModel = $this->loadModel('User');
        $usuarios = $usuariosModel->getActiveUsers();
        
        if ($puedeVerTodos) {
            $mascotas = $this->mascotaModel->getMascotasConDispositivo();
        } else {
            $mascotas = $this->mascotaModel->getMascotasConDispositivo();
        }
        
        $this->view->render('mascotas/index', [
            'mascotas' => $mascotas,
            'usuarios' => $usuarios
        ]);
    }

    public function obtenerMascotasAction() {
        if (!$this->isPostRequest() || !verificarPermiso('ver_mascotas')) {
            $this->jsonResponse(['error' => 'Acceso no autorizado'], 403);
            return;
        }

        $params = $_POST;
        $propietarioId = verificarPermiso('ver_todos_mascotas') ? null : $_SESSION['user_id'];
        
        $response = $this->mascotaModel->getPaginatedMascotas($params, $propietarioId);
        
        $this->jsonResponse($response);
    }

    public function cargarFormularioAction($id = null) {
        $permisoRequerido = $id ? 'editar_mascotas' : 'crear_mascotas';
        if (!verificarPermiso($permisoRequerido)) {
            $this->view->render('partials/modal_error', ['mensaje' => 'No tienes permiso para realizar esta acción.']);
            return;
        }

        $mascota = null;
        if ($id) {
            try {
                $mascota = $this->mascotaModel->findById($id);
                if (!$mascota) {
                    $this->view->render('partials/modal_error', ['mensaje' => 'Mascota no encontrada.']);
                    return;
                }
                
                // Verificar permisos para editar esta mascota específica
                if (!verificarPermiso('ver_todas_mascotas') && $mascota['usuario_id'] != $_SESSION['user_id']) {
                    $this->view->render('partials/modal_error', ['mensaje' => 'No tienes permiso para editar esta mascota.']);
                    return;
                }
            } catch (Exception $e) {
                error_log('Error al cargar mascota: ' . $e->getMessage());
                $this->view->render('partials/modal_error', ['mensaje' => 'Error al cargar los datos de la mascota.']);
                return;
            }
        }
        
        try {
            $usuarioModel = $this->loadModel('UsuarioModel');
            $usuarios = $usuarioModel->getAll();

            // Determinar si puede asignar propietario: necesita AMBOS permisos
            $puedeAsignarPropietario = verificarPermiso('ver_todas_mascotas') && verificarPermiso('crear_mascotas');
            
            $this->view->render('mascotas/form', [
                'mascota' => $mascota,
                'usuarios' => $usuarios,
                'puedeAsignarPropietario' => $puedeAsignarPropietario
            ], false);
        } catch (Exception $e) {
            error_log('Error en cargarFormularioAction: ' . $e->getMessage());
            $this->view->render('partials/modal_error', ['mensaje' => 'Error al cargar el formulario.']);
        }
    }

    public function guardarAction() {
        if (!$this->isPostRequest()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
    
        $id = $_POST['id_mascota'] ?? null;
        $permiso = $id ? 'editar_mascotas' : 'crear_mascotas';
        if (!verificarPermiso($permiso)) {
            return $this->jsonResponse(['success' => false, 'message' => 'No tienes permiso para realizar esta acción.'], 403);
        }
    
        // Recoger los datos correctos del formulario
        $data = [
            'nombre' => trim($_POST['nombre']),
            'especie' => trim($_POST['especie']),
            'tamano' => trim($_POST['tamano']),
            'genero' => $_POST['genero'],
            'fecha_nacimiento' => empty($_POST['fecha_nacimiento']) ? null : $_POST['fecha_nacimiento'],
        ];
    
        // Asignar propietario: necesita AMBOS permisos ver_todas_mascotas Y crear_mascotas
        $puedeAsignarPropietario = verificarPermiso('ver_todas_mascotas') && verificarPermiso('crear_mascotas');
        
        if ($puedeAsignarPropietario) {
            // Si puede asignar propietario, es obligatorio que lo especifique
            if (empty($_POST['usuario_id'])) {
                return $this->jsonResponse([
                    'success' => false, 
                    'message' => 'El campo propietario es obligatorio cuando se tienen permisos para asignar propietarios.'
                ], 400);
            }
            $data['usuario_id'] = $_POST['usuario_id'];
        } elseif (!$id) { 
            // Si no puede asignar propietario, se asigna a sí mismo al crear
            $data['usuario_id'] = $_SESSION['user_id'];
        }
    
        try {
            if ($id) {
                // Usamos el método específico del modelo que filtra campos
                $this->mascotaModel->updateMascota($id, $data);
                $message = 'Mascota actualizada correctamente.';
            } else {
                // Añadimos el estado por defecto al crear
                $data['estado'] = 'activo';
                // Usamos el método específico del modelo que filtra campos
                $this->mascotaModel->createMascota($data);
                $message = 'Mascota creada correctamente.';
            }
            $this->jsonResponse(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            error_log('Error en MascotasController::guardarAction - ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Ocurrió un error al guardar la mascota.'], 500);
        }
    }

    public function toggleEstadoAction() {
        if (!verificarPermiso('editar_mascotas')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Acción no permitida.'], 403);
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $estado_str = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING); // 'true' o 'false'

        if ($id === false || $estado_str === null) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Datos incompletos.'], 400);
        }

        // Convertir el estado del switch ('true'/'false') directamente al estado de la BD ('activo'/'inactivo')
        $nuevo_estado = ($estado_str === 'true') ? 'activo' : 'inactivo';

        // Intentamos actualizar. Si la BD ya tiene ese estado, rowCount() será 0 y el modelo devolverá false.
        // En ese caso, no es un error, simplemente no había nada que cambiar.
        if ($this->mascotaModel->updateMascota($id, ['estado' => $nuevo_estado])) {
            $this->jsonResponse(['status' => 'success', 'message' => 'Estado actualizado con éxito.']);
        } else {
            // Verificamos si realmente hubo un error o si simplemente no hubo cambios.
            // Para ello, leemos el estado actual de la mascota.
            $mascota_actual = $this->mascotaModel->findById($id);
            if ($mascota_actual && $mascota_actual['estado'] === $nuevo_estado) {
                // No hubo error, el estado ya era el correcto.
                $this->jsonResponse(['status' => 'success', 'message' => 'El estado ya estaba actualizado.']);
            } else {
                // Hubo un error real al intentar actualizar.
                $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo actualizar el estado.'], 500);
            }
        }
    }

    public function eliminarAction($id) {
        if (!$this->isPostRequest() || !verificarPermiso('eliminar_mascotas')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Acceso no autorizado.'], 403);
        }

        $mascota = $this->mascotaModel->find($id, 'id_mascota');
        if (!$mascota) {
            return $this->jsonResponse(['success' => false, 'message' => 'Mascota no encontrada.'], 404);
        }

        if (!verificarPermiso('ver_todos_mascotas') && $mascota['usuario_id'] != $_SESSION['user_id']) {
             return $this->jsonResponse(['success' => false, 'message' => 'No tienes permiso para eliminar esta mascota.'], 403);
        }
        
        try {
            $this->mascotaModel->delete($id, 'id_mascota');
            $this->jsonResponse(['success' => true, 'message' => 'Mascota eliminada correctamente.']);
        } catch (Exception $e) {
            error_log('Error en MascotasController::eliminarAction - ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar la mascota.'], 500);
        }
    }

    private function isPostRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
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
        if (!verificarPermiso('editar_mascotas')) {
            $this->jsonResponse(['success' => false, 'error' => 'No tiene permiso para editar mascotas.'], 403);
            return;
        }

        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $mascota = $this->mascotaModel->findById($id);
        if (!$mascota) {
            $this->jsonResponse(['success' => false, 'error' => 'Mascota no encontrada'], 404);
            return;
        }

        // Un admin o superadmin puede editar cualquier mascota. Un usuario normal, solo las suyas.
        if (!verificarPermiso('ver_todos_mascotas') && $mascota['propietario_id'] != $_SESSION['user_id']) {
            $this->jsonResponse(['success' => false, 'error' => 'No tiene permisos para editar esta mascota.'], 403);
            return;
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
            if (verificarPermiso('editar_mascotas')) {
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
        if (!verificarPermiso('eliminar_mascotas')) {
            $this->jsonResponse(['success' => false, 'error' => 'No tiene permiso para eliminar mascotas.'], 403);
            return;
        }

        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $mascota = $this->mascotaModel->findById($id);
        if (!$mascota) {
            $this->jsonResponse(['success' => false, 'error' => 'Mascota no encontrada'], 404);
            return;
        }

        // Un admin o superadmin puede eliminar cualquier mascota. Un usuario normal, solo las suyas.
        if (!verificarPermiso('ver_todos_mascotas') && $mascota['propietario_id'] != $_SESSION['user_id']) {
            $this->jsonResponse(['success' => false, 'error' => 'No tiene permisos para eliminar esta mascota.'], 403);
            return;
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

        $title = 'Detalles de Mascota';
        $content = $this->render('mascotas/view', [
            'mascota' => $mascota
        ]);
        require_once 'views/layouts/main.php';
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

        // Parámetros de DataTables
        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $searchValue = $_POST['search']['value'] ?? '';
        $order = $_POST['order'] ?? [];
        $columns = $_POST['columns'] ?? [];

        $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1, 2]);
        $tienePermisoVerTodas = function_exists('verificarPermiso') ? verificarPermiso('ver_todos_mascotas') : false;

        $usuario_id = null;
        if (!($esAdmin && $tienePermisoVerTodas)) {
            $usuario_id = $_SESSION['user_id']; // O propietario_id, según tu lógica de sesión
        }

        $paramsDataTable = [
            'draw' => $draw,
            'start' => $start,
            'length' => $length,
            'search' => ['value' => $searchValue],
            'order' => $order,
            'columns' => $columns
        ];

        $data = $this->mascotaModel->getMascotasFiltradas($paramsDataTable, $usuario_id, ($esAdmin && $tienePermisoVerTodas));

        // Asegurarse de que los datos estén en el formato correcto para DataTables
        // DataTables espera 'draw', 'recordsTotal', 'recordsFiltered', 'data'
        
        // No es necesario añadir el propietario_nombre aquí si ya se hace en el modelo
        // No es necesario añadir 'tiene_dispositivo' aquí si ya se hace en el modelo

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
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
        
        // Un usuario puede cambiar el estado de una mascota si:
        // 1. Tiene permiso para editar cualquier mascota, O
        // 2. Tiene permiso para editar mascotas Y la mascota le pertenece
        $puedeEditarCualquiera = verificarPermiso('editar_cualquier_mascota');
        $puedeEditarPropias = verificarPermiso('editar_mascotas') && $mascota['propietario_id'] == $_SESSION['propietario_id'];
        
        if (!$mascota || (!$puedeEditarCualquiera && !$puedeEditarPropias)) {
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
        // Un usuario puede editar una mascota si:
        // 1. Tiene permiso para editar cualquier mascota, O
        // 2. Tiene permiso para editar mascotas Y la mascota le pertenece
        $puedeEditarCualquiera = verificarPermiso('editar_cualquier_mascota');
        $puedeEditarPropias = verificarPermiso('editar_mascotas') && $mascota['propietario_id'] == $_SESSION['propietario_id'];

        if (!$puedeEditarCualquiera && !$puedeEditarPropias) {
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
            
            // Un usuario puede ver una mascota si:
            // 1. Tiene permiso para editar cualquier mascota, O
            // 2. Tiene permiso para eliminar cualquier mascota, O
            // 3. La mascota le pertenece
            $puedeEditarTodas = verificarPermiso('editar_cualquier_mascota');
            $puedeEliminarTodas = verificarPermiso('eliminar_cualquier_mascota');
            $esPropietario = $mascota['propietario_id'] == $_SESSION['propietario_id'];
            
            error_log('ID recibido en getAction: ' . $id);
            error_log('Usuario logueado: ' . print_r($_SESSION, true));
            error_log('Permiso editar_cualquier_mascota: ' . ($puedeEditarTodas ? 'SI' : 'NO'));
            error_log('Permiso eliminar_cualquier_mascota: ' . ($puedeEliminarTodas ? 'SI' : 'NO'));
            error_log('Es propietario: ' . ($esPropietario ? 'SI' : 'NO'));
            error_log('Mascota encontrada: ' . print_r($mascota, true));
            
            if (!$mascota) {
                echo json_encode(['success' => false, 'error' => 'Mascota no encontrada']);
                return;
            }
            
            if (!$puedeEditarTodas && !$puedeEliminarTodas && !$esPropietario) {
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

    public function listarAction()
    {
        // Verificar permisos si es necesario
        if (!verificarPermiso('ver_mascotas')) {
            $this->jsonResponse(['error' => 'Acceso no autorizado'], 403);
            return;
        }

        $puedeVerTodos = verificarPermiso('ver_todas_mascotas');
        $usuarioId = $puedeVerTodos ? null : $_SESSION['user_id'];
        
        $conditions = [];
        if (!$puedeVerTodos) {
            $conditions['usuario_id'] = $usuarioId;
        }

        $mascotas = $this->mascotaModel->findAll($conditions);

        // Formatear los datos para DataTables
        $data = [];
        foreach ($mascotas as $mascota) {
            $rowData = $mascota;
            // Formatear la fecha, manejando valores nulos o inválidos
            if (!empty($mascota['fecha_nacimiento']) && $mascota['fecha_nacimiento'] !== '0000-00-00') {
                $rowData['fecha_nacimiento'] = date('d/m/Y', strtotime($mascota['fecha_nacimiento']));
            } else {
                $rowData['fecha_nacimiento'] = '-';
            }
            $data[] = $rowData;
        }

        $this->jsonResponse(['data' => $data]);
    }
}
?> 