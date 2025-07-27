<?php

class MonitorController extends Controller {
    private $dispositivoModel;
    private $datosSensorModel;

    public function __construct() {
        parent::__construct();
        $this->dispositivoModel = new DispositivoModel();
        $this->datosSensorModel = new DatosSensor();
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        if (function_exists('verificarPermiso') && !verificarPermiso('ver_monitor')) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $this->view->setTitle('Monitor de Dispositivos');
        $this->view->setData('menuActivo', 'monitor');
        $this->view->render('monitor/index');
    }

    public function deviceAction($id = null) {
        if (!$id) {
            header('Location: ' . BASE_URL . 'monitor');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        
        if (!$dispositivo) {
            header('Location: ' . BASE_URL . 'monitor');
            exit;
        }

        if (!(function_exists('verificarPermiso') && verificarPermiso('ver_todos_dispositivos'))) {
            if ($dispositivo['usuario_id'] != $_SESSION['user_id']) {
                header('Location: ' . BASE_URL . 'monitor');
                exit;
            }
        }

        $this->view->setTitle('Monitor de Dispositivo');
        $this->view->setData('dispositivo', $dispositivo);
        $this->view->setData('menuActivo', 'monitor');
        $this->view->render('monitor/device');
    }

    public function getDatosAction($id = null) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de dispositivo no proporcionado']);
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        if (!$dispositivo || $dispositivo['usuario_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            return;
        }

        try {
            $horas = isset($_GET['horas']) ? (int)$_GET['horas'] : 24;
            $datos = $this->datosSensorModel->getDatosPorDispositivo($id, $horas);
            echo json_encode(['success' => true, 'data' => $datos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener datos']);
        }
    }

    public function getDatosFiltradosAction() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        try {
            $propietario = $_GET['propietario'] ?? '';
            $mascota = $_GET['mascota'] ?? '';
            $mac = $_GET['mac'] ?? '';
            $soloActivos = isset($_GET['soloActivos']) && $_GET['soloActivos'] === 'true';

            $dispositivos = $this->dispositivoModel->getDispositivosFiltrados(
                $propietario, $mascota, $mac, $soloActivos
            );

            echo json_encode(['success' => true, 'data' => $dispositivos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener dispositivos filtrados']);
        }
    }

    public function getPropietariosAction() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        try {
            $propietarios = $this->dispositivoModel->getPropietariosDispositivos();
            echo json_encode(['success' => true, 'data' => $propietarios]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener propietarios']);
        }
    }

    public function getMascotasPorPropietarioAction() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        try {
            $propietario = $_GET['propietario'] ?? '';
            $mascotas = $this->dispositivoModel->getMascotasPorPropietario($propietario);
            echo json_encode(['success' => true, 'data' => $mascotas]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener mascotas']);
        }
    }

    public function getDatosTablaAction() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        try {
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $porPagina = isset($_GET['porPagina']) ? (int)$_GET['porPagina'] : 10;
            $dispositivoId = $_GET['dispositivoId'] ?? null;

            $datos = $this->datosSensorModel->getDatosTabla($pagina, $porPagina, $dispositivoId);
            echo json_encode(['success' => true, 'data' => $datos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener datos de tabla']);
        }
    }

    public function testAction() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        if (function_exists('verificarPermiso') && !verificarPermiso('ver_monitor')) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $this->view->setTitle('Test Monitor');
        $this->view->setData('menuActivo', 'monitor');
        $this->view->render('monitor/test');
    }
} 