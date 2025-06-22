<?php
class MonitorController extends Controller {
    private $dispositivoModel;
    private $mascotaModel;
    private $datosSensorModel;

    public function __construct() {
        parent::__construct();
        $this->dispositivoModel = new DispositivoModel();
        $this->mascotaModel = new Mascota();
        $this->datosSensorModel = new DatosSensorModel();
    }

    public function indexAction() {
        if (!verificarPermiso('ver_monitor')) {
            $this->view->render('errors/403');
            return;
        }

        $this->view->setLayout('main');
        $this->view->setData('titulo', 'Monitor de Dispositivos');
        $this->view->setData('subtitulo', 'Selecciona un dispositivo para ver su estado en tiempo real.');

        if (function_exists('verificarPermiso') && verificarPermiso('ver_todos_dispositivo')) {
            $dispositivos = $this->dispositivoModel->getTodosDispositivosConMascotas();
        } else {
            $dispositivos = $this->dispositivoModel->getDispositivosWithMascotas($_SESSION['user_id']);
        }

        $this->view->render('monitor/index', [
            'dispositivos' => $dispositivos,
        ]);
    }

    public function deviceAction($id = null) {
        if (!verificarPermiso('ver_monitor_dispositivo')) {
            $this->view->render('errors/403');
            return;
        }

        if (!$id) {
            $this->view->render('errors/404', ['mensaje' => 'ID de dispositivo no proporcionado.']);
            return;
        }

        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        if (!$dispositivo) {
            $this->view->render('errors/404', ['mensaje' => 'Dispositivo no encontrado.']);
            return;
        }

        if (!verificarPermiso('ver_todos_dispositivo') && $dispositivo['usuario_id'] != $_SESSION['user_id']) {
            $this->view->render('errors/403', ['mensaje' => 'No tienes permiso para ver este dispositivo.']);
            return;
        }

        $this->view->setLayout('main');
        $this->view->setData('titulo', 'Monitor: ' . htmlspecialchars($dispositivo['nombre']));
        $this->view->setData('subtitulo', 'Visualizando datos en tiempo real para el dispositivo ' . htmlspecialchars($dispositivo['mac']));
        $this->view->render('monitor/device', ['dispositivo' => $dispositivo]);
    }

    public function getDatosAction($id = null) {
        header('Content-Type: application/json');
        
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'ID de dispositivo no proporcionado'
            ]);
            return;
        }

        // Verificar sesión y permisos
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'No autorizado'
            ]);
            return;
        }

        // Verificar si el dispositivo pertenece al usuario
        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        if (!$dispositivo || $dispositivo['usuario_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Acceso denegado'
            ]);
            return;
        }

        try {
            $horas = isset($_GET['horas']) ? (int)$_GET['horas'] : 24;
            $datos = $this->datosSensorModel->getDatosPorDispositivo($id, $horas);
            $ubicacion = $this->dispositivoModel->getUltimaUbicacion($id);
            
            error_log("Datos obtenidos para dispositivo {$id}: " . print_r($datos, true));
            error_log("Ubicación obtenida para dispositivo {$id}: " . print_r($ubicacion, true));
            
            echo json_encode([
                'success' => true,
                'datos' => $datos,
                'ubicacion' => $ubicacion
            ]);
        } catch (Exception $e) {
            error_log("Error en getDatosAction: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener los datos: ' . $e->getMessage()
            ]);
        }
    }

    public function getRutaAction($id = null) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de dispositivo no proporcionado']);
            return;
        }

        // Verificar sesión y permisos
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        // Verificar si el dispositivo pertenece al usuario
        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        if (!$dispositivo || $dispositivo['usuario_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            return;
        }

        try {
            $horas = isset($_GET['horas']) ? (int)$_GET['horas'] : 24;
            $ruta = $this->dispositivoModel->getRuta($id, $horas);

            echo json_encode([
                'success' => true,
                'ruta' => $ruta
            ]);
        } catch (Exception $e) {
            error_log("Error en getRutaAction: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener la ruta']);
        }
    }

    public function getGraficaAction($id = null) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de dispositivo no proporcionado']);
            return;
        }

        // Verificar sesión y permisos
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        // Verificar si el dispositivo pertenece al usuario
        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        if (!$dispositivo || $dispositivo['usuario_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            return;
        }

        try {
            $horas = isset($_GET['horas']) ? (int)$_GET['horas'] : 24;
            $datos = $this->datosSensorModel->getDatosParaGrafica($id, $horas);
            
            echo json_encode([
                'success' => true,
                'datos' => $datos
            ]);
        } catch (Exception $e) {
            error_log("Error en getGraficaAction: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener los datos para la gráfica']);
        }
    }

    /**
     * Obtiene la última ubicación de un dispositivo
     */
    public function getUltimaUbicacionAction($id) {
        header('Content-Type: application/json');
        try {
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de dispositivo no proporcionado']);
                return;
            }

            // Verificar sesión y permisos
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }

            // Verificar si el dispositivo pertenece al usuario o si tiene permiso para ver todos
            $dispositivo = $this->dispositivoModel->getDispositivoById($id);
            if (!(function_exists('verificarPermiso') && verificarPermiso('ver_todos_dispositivo'))) {
                if ($dispositivo['usuario_id'] != $_SESSION['user_id']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Acceso denegado']);
                    return;
                }
            }

            $datosSensor = new DatosSensorModel();
            $ultimaUbicacion = $datosSensor->getUltimaUbicacion($id);

            if (!$ultimaUbicacion || !isset($ultimaUbicacion['latitud']) || !isset($ultimaUbicacion['longitud'])) {
                http_response_code(404);
                echo json_encode(['error' => 'No se encontró ubicación para el dispositivo', 'latitud' => null, 'longitud' => null, 'fecha' => null]);
                return;
            }

            echo json_encode([
                'latitud' => (float)$ultimaUbicacion['latitud'],
                'longitud' => (float)$ultimaUbicacion['longitud'],
                'fecha' => $ultimaUbicacion['fecha']
            ]);
        } catch (Exception $e) {
            error_log("Error en getUltimaUbicacion: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage(), 'latitud' => null, 'longitud' => null, 'fecha' => null]);
        }
    }

    /**
     * Obtiene los últimos datos de un dispositivo
     */
    public function getUltimosDatosAction($id) {
        try {
            if (!$id) {
                throw new Exception('ID de dispositivo no proporcionado');
            }

            // Verificar sesión y permisos
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }

            // Verificar si el dispositivo pertenece al usuario o si tiene permiso para ver todos
            $dispositivo = $this->dispositivoModel->getDispositivoById($id);
            if (!(function_exists('verificarPermiso') && verificarPermiso('ver_todos_dispositivo'))) {
                if ($dispositivo['usuario_id'] != $_SESSION['user_id']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Acceso denegado']);
                    return;
                }
            }

            $horas = isset($_GET['horas']) ? intval($_GET['horas']) : 24;
            $datosSensor = new DatosSensorModel();
            $ultimosDatos = $datosSensor->getUltimosDatos($id, $horas);
            $ubicacion = $datosSensor->getUltimaUbicacion($id);

            // Asegurar que sensores siempre sea un array
            $sensores = is_array($ultimosDatos) ? $ultimosDatos : [];

            $response = [
                'ubicacion' => $ubicacion ?: null,
                'sensores' => $sensores
            ];

            header('Content-Type: application/json');
            echo json_encode($response);
        } catch (Exception $e) {
            error_log("Error en getUltimosDatos: " . $e->getMessage());
            http_response_code(404);
            echo json_encode([
                'ubicacion' => null,
                'sensores' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    private function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
}