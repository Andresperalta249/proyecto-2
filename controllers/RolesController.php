<?php

class RolesController extends Controller
{
    private $rolModel;

    public function __construct()
    {
        parent::__construct();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->rolModel = $this->loadModel('Rol');
    }

    public function indexAction()
    {
        if (!verificarPermiso('ver_roles')) {
            $this->view->render('errors/403');
            return;
        }

        $this->view->setLayout('main');
        $this->view->setData('titulo', 'Gestión de Roles');
        $this->view->setData('subtitulo', 'Administración de roles y permisos del sistema.');
        $this->view->render('roles/index');
    }

    public function listarAction()
    {
        if (!$this->isPostRequest() || !verificarPermiso('ver_roles')) {
            return $this->jsonResponse(['error' => 'Acceso denegado'], 403);
        }

        try {
            $draw = $_POST['draw'] ?? 1;
            $start = $_POST['start'] ?? 0;
            $length = $_POST['length'] ?? 10;
            $searchValue = $_POST['search']['value'] ?? '';
            
            $orderColumnIndex = $_POST['order'][0]['column'] ?? 0;
            $orderColumnName = $_POST['columns'][$orderColumnIndex]['data'] ?? 'id_rol';
            $orderDir = $_POST['order'][0]['dir'] ?? 'asc';
            
            $allowedColumns = ['id_rol', 'nombre', 'descripcion', 'estado'];
            if (!in_array($orderColumnName, $allowedColumns)) {
                $orderColumnName = 'id_rol';
            }

            $result = $this->rolModel->getPaginated($start, $length, $searchValue, $orderColumnName, $orderDir);
            
            $data = array_map(function($rol) {
                $rol['is_protected'] = in_array($rol['id_rol'], [1, 2, 3]);
                return $rol;
            }, $result['data']);

            $this->jsonResponse([
                'draw' => intval($draw),
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $data
            ]);
        } catch (Exception $e) {
            error_log('Error en RolesController::listarAction: ' . $e->getMessage());
            $this->jsonResponse(['error' => 'Ocurrió un error en el servidor.'], 500);
        }
    }

    public function crearAction()
    {
        if (!verificarPermiso('crear_roles')) {
            echo '<div class="alert alert-danger">No tienes permiso para crear roles.</div>';
            return;
        }

        $data = [
            'rol' => null,
            'permisos' => $this->rolModel->getPermisos()
        ];
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        $view = new View();
        if ($isAjax) {
            extract($data);
            require ROOT_PATH . '/views/roles/form.php';
        } else {
            $view->render('roles/form', $data, false);
        }
    }

    public function editarAction($id)
    {
        if (in_array($id, [1, 2, 3])) {
            echo '<div class="alert alert-warning text-center"><strong>Acción denegada:</strong> Este rol está protegido y no puede ser editado.</div>';
            return;
        }

        if (!verificarPermiso('editar_roles')) {
            echo '<div class="alert alert-danger">No tienes permiso para editar roles.</div>';
            return;
        }

        $rol = $this->rolModel->find($id, 'id_rol');
        if (!$rol) {
            echo '<div class="alert alert-danger">Rol no encontrado.</div>';
            return;
        }

        $permisosAsignados = $this->rolModel->getPermisosPorRol($id);
        $rol['permiso_ids'] = array_column($permisosAsignados, 'id_permiso');
        $data = [
            'rol' => $rol,
            'permisos' => $this->rolModel->getPermisos()
        ];
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        $view = new View();
        if ($isAjax) {
            extract($data);
            require ROOT_PATH . '/views/roles/form.php';
        } else {
            $view->render('roles/form', $data, false);
        }
    }
    
    public function guardarAction()
    {
        if (!$this->isPostRequest()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $id = $_POST['id_rol'] ?? null;

        if ($id && in_array($id, [1, 2, 3])) {
            return $this->jsonResponse(['success' => false, 'message' => 'No se puede modificar un rol protegido.'], 403);
        }

        $isEdit = !empty($id);

        if (($isEdit && !verificarPermiso('editar_roles')) || (!$isEdit && !verificarPermiso('crear_roles'))) {
            return $this->jsonResponse(['success' => false, 'message' => 'No tienes permiso para realizar esta acción.'], 403);
        }

        try {
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $permisos = $_POST['permisos'] ?? [];

            $errores = [];

            // Validaciones backend
            if (!$nombre) {
                $errores[] = 'El nombre del rol es obligatorio.';
            }
            if (!$descripcion) {
                $errores[] = 'La descripción es obligatoria.';
            }
            if (empty($permisos)) {
                $errores[] = 'Debes seleccionar al menos un permiso.';
            }
            $rolExistente = $this->rolModel->getRolByNombre($nombre);
            if ($rolExistente && (!$id || $rolExistente['id_rol'] != $id)) {
                $errores[] = 'El nombre del rol ya existe.';
            }

            if (!empty($errores)) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode(['success' => false, 'errores' => $errores]);
                    exit;
                } else {
                    // Renderizar la vista con errores
                    $data = compact('nombre', 'descripcion', 'permisos', 'errores');
                    $this->view->render('roles/form', $data);
                    return;
                }
            }

            if ($isEdit) {
                $success = $this->rolModel->update($id, ['nombre' => $nombre, 'descripcion' => $descripcion, 'permisos' => $permisos]);
                $message = 'Rol actualizado correctamente.';
            } else {
                $success = $this->rolModel->create(['nombre' => $nombre, 'descripcion' => $descripcion, 'permisos' => $permisos]);
                $message = 'Rol creado correctamente.';
            }

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => $message]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al guardar el rol. Verifique que el nombre no esté duplicado.']);
            }
        } catch (Exception $e) {
             error_log('Error en RolesController::guardarAction: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Ocurrió un error en el servidor.'], 500);
        }
    }

    public function eliminarAction()
    {
        if (!$this->isPostRequest() || !verificarPermiso('eliminar_roles')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }

        try {
            $id = filter_var($_POST['id_rol'] ?? null, FILTER_VALIDATE_INT);

            if (!$id) {
                return $this->jsonResponse(['success' => false, 'message' => 'ID de rol inválido.'], 400);
            }

            if ($id <= 3) {
                return $this->jsonResponse(['success' => false, 'message' => 'No se puede eliminar un rol protegido.'], 403);
            }
            
            if ($this->rolModel->delete($id)) {
                $this->jsonResponse(['success' => true, 'message' => 'Rol eliminado correctamente.']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'No se pudo eliminar el rol. Es posible que tenga usuarios asociados.']);
            }
        } catch (Exception $e) {
            error_log('Error en RolesController::eliminarAction: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Ocurrió un error en el servidor.'], 500);
        }
    }

    public function toggleEstadoAction()
    {
        if (!$this->isPostRequest() || !verificarPermiso('editar_roles')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$id || !$estado || !in_array($estado, ['activo', 'inactivo'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Datos inválidos.'], 400);
        }

        if ($id <= 3) {
            return $this->jsonResponse(['success' => false, 'message' => 'No se puede cambiar el estado de un rol protegido.'], 403);
        }

        try {
            if ($this->rolModel->cambiarEstado($id, $estado)) {
                $this->jsonResponse(['success' => true, 'message' => 'Estado actualizado.']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'No se pudo actualizar el estado.'], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar el estado.'], 500);
        }
    }

    public function validarNombreAction() {
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $idRol = isset($_POST['id_rol']) ? intval($_POST['id_rol']) : 0;
        $rolExistente = $this->rolModel->getRolByNombre($nombre);
        if ($rolExistente && (!$idRol || $rolExistente['id_rol'] != $idRol)) {
            echo json_encode(['success' => false, 'error' => 'El nombre del rol ya existe.']);
            exit;
        }
        echo json_encode(['success' => true]);
        exit;
    }

    private function isPostRequest()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
} 