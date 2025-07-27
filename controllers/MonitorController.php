<?php

class MonitorController extends Controller {
    private $dispositivoModel;

    public function __construct() {
        parent::__construct();
        $this->dispositivoModel = new DispositivoModel();
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

        // Obtener dispositivos según permisos con datos de ubicación
        if (function_exists('verificarPermiso') && verificarPermiso('ver_todos_dispositivos')) {
            $dispositivos = $this->dispositivoModel->getDispositivosFiltrados($_SESSION['user_id']);
        } else {
            $dispositivos = $this->dispositivoModel->getDispositivosFiltrados($_SESSION['user_id']);
        }

        $this->view->setTitle('Monitor de Dispositivos');
        $this->view->setData('dispositivos', $dispositivos);
        $this->view->setData('menuActivo', 'monitor');
        $this->view->render('monitor/index');
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
                $_SESSION['user_id'], $propietario, $mascota, $mac, $soloActivos
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
            $propietarios = $this->dispositivoModel->getPropietariosDispositivos($_SESSION['user_id']);
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
            $mascotas = $this->dispositivoModel->getMascotasPorPropietario($_SESSION['user_id'], $propietario);
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

            $datosSensorModel = new DatosSensor();
            $datos = $datosSensorModel->getDatosTabla($pagina, $porPagina, $dispositivoId);
            echo json_encode(['success' => true, 'data' => $datos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener datos de tabla']);
        }
    }

    public function testSimpleAction() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $this->view->setTitle('Test Simple');
        $this->view->setData('menuActivo', 'monitor');
        $this->view->render('monitor/test-simple');
    }
} 