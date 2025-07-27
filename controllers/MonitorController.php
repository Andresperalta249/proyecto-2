<?php
class MonitorController extends Controller {
    private $dispositivoModel;
    private $mascotaModel;
    private $datosSensorModel;

    public function __construct() {
        parent::__construct();
        $this->dispositivoModel = new DispositivoModel();
        $this->mascotaModel = new Mascota();
        $this->datosSensorModel = new DatosSensor();
    }

    public function indexAction() {
        error_log("=== INICIO indexAction ===");
        error_log("Sesión actual: " . print_r($_SESSION, true));
        
        if (!isset($_SESSION['user_id'])) {
            error_log("Error: No hay sesión de usuario activa");
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        // Verificar permiso para acceder al monitor
        if (function_exists('verificarPermiso') && !verificarPermiso('ver_monitor')) {
            error_log("Error: Usuario no tiene permiso para ver monitor");
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        if (function_exists('verificarPermiso') && verificarPermiso('ver_todos_dispositivos')) {
            $dispositivos = $this->dispositivoModel->getTodosDispositivosConMascotas();
        } else {
            $dispositivos = $this->dispositivoModel->getDispositivosWithMascotas($_SESSION['user_id']);
        }
        error_log("Dispositivos obtenidos: " . print_r($dispositivos, true));
        
        $this->view->setTitle('Monitor de Dispositivos');
        $this->view->setData('dispositivos', $dispositivos);
        $this->view->setData('menuActivo', 'monitor');
        error_log("=== FIN indexAction - Renderizando vista ===");
        $this->view->render('monitor/index');
    }

    public function deviceAction($id = null) {
        error_log("=== INICIO deviceAction ===");
        error_log("ID recibido: " . ($id ?? 'null'));
        error_log("Sesión actual: " . print_r($_SESSION, true));
        
        if (!$id) {
            error_log("Error: ID de dispositivo no proporcionado");
            header('Location: ' . BASE_URL . 'monitor');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            error_log("Error: No hay sesión de usuario activa");
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $dispositivoModel = new DispositivoModel();
        $dispositivo = $dispositivoModel->getDispositivoById($id);
        
        error_log("Dispositivo encontrado: " . print_r($dispositivo, true));
        
        if (!$dispositivo) {
            error_log("Error: Dispositivo no encontrado");
            header('Location: ' . BASE_URL . 'monitor');
            exit;
        }

        if (!(function_exists('verificarPermiso') && verificarPermiso('ver_todos_dispositivos'))) {
            if ($dispositivo['usuario_id'] != $_SESSION['user_id']) {
                error_log("Error: Intento de acceso no autorizado");
                header('Location: ' . BASE_URL . 'monitor');
                exit;
            }
        }

        $this->view->setTitle('Monitor de Dispositivo');
        $this->view->setData('dispositivo', $dispositivo);
        $content = $this->render('monitor/device', ['dispositivo' => $dispositivo]);
        $GLOBALS['content'] = $content;
        $GLOBALS['title'] = 'Administrador de monitor';
        $GLOBALS['menuActivo'] = 'monitor';
        require_once 'views/layouts/main.php';
    }

    public function getDatosAction($id = null) {
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
            $datos = $this->datosSensorModel->getDatosPorDispositivo($id, $horas);
            $ubicacion = $this->dispositivoModel->getUltimaUbicacion($id);
            
            echo json_encode([
                'success' => true,
                'datos' => $datos,
                'ubicacion' => $ubicacion
            ]);
        } catch (Exception $e) {
            error_log("Error en getDatosAction: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener los datos']);
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
            if (!(function_exists('verificarPermiso') && verificarPermiso('ver_todos_dispositivos'))) {
                if ($dispositivo['usuario_id'] != $_SESSION['user_id']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Acceso denegado']);
                    return;
                }
            }

            $datosSensor = new DatosSensor();
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
            if (!(function_exists('verificarPermiso') && verificarPermiso('ver_todos_dispositivos'))) {
                if ($dispositivo['usuario_id'] != $_SESSION['user_id']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Acceso denegado']);
                    return;
                }
            }

            $horas = isset($_GET['horas']) ? intval($_GET['horas']) : 24;
            $datosSensor = new DatosSensor();
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
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Obtiene dispositivos con filtros avanzados
     */
    public function getDatosFiltradosAction() {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->jsonResponse(['error' => 'No autorizado'], 401);
                return;
            }

            $propietarioId = isset($_GET['propietario_id']) ? intval($_GET['propietario_id']) : null;
            $mascotaId = isset($_GET['mascota_id']) ? intval($_GET['mascota_id']) : null;
            $mac = isset($_GET['mac']) ? trim($_GET['mac']) : '';
            $soloActivos = isset($_GET['solo_activos']) && $_GET['solo_activos'] === 'true';

            // Obtener dispositivos filtrados
            $dispositivos = $this->dispositivoModel->getDispositivosFiltrados(
                $_SESSION['user_id'],
                $propietarioId,
                $mascotaId,
                $mac,
                $soloActivos
            );

            $this->jsonResponse(['success' => true, 'dispositivos' => $dispositivos]);
        } catch (Exception $e) {
            error_log("Error en getDatosFiltrados: " . $e->getMessage());
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene lista de propietarios para filtros
     */
    public function getPropietariosAction() {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->jsonResponse(['error' => 'No autorizado'], 401);
                return;
            }

            $propietarios = $this->dispositivoModel->getPropietariosDispositivos($_SESSION['user_id']);
            $this->jsonResponse(['success' => true, 'propietarios' => $propietarios]);
        } catch (Exception $e) {
            error_log("Error en getPropietarios: " . $e->getMessage());
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene mascotas por propietario para filtros
     */
    public function getMascotasPorPropietarioAction() {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->jsonResponse(['error' => 'No autorizado'], 401);
                return;
            }

            $propietarioId = isset($_GET['propietario_id']) ? intval($_GET['propietario_id']) : null;
            $mascotas = $this->dispositivoModel->getMascotasPorPropietario($_SESSION['user_id'], $propietarioId);
            $this->jsonResponse(['success' => true, 'mascotas' => $mascotas]);
        } catch (Exception $e) {
            error_log("Error en getMascotasPorPropietario: " . $e->getMessage());
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene datos de sensores para tabla en tiempo real
     */
    public function getDatosTablaAction() {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->jsonResponse(['error' => 'No autorizado'], 401);
                return;
            }

            $dispositivoId = isset($_GET['dispositivo_id']) ? intval($_GET['dispositivo_id']) : null;
            $limite = isset($_GET['limite']) ? intval($_GET['limite']) : 50;
            $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;

            $datosSensor = new DatosSensor();
            $datos = $datosSensor->getDatosTabla($_SESSION['user_id'], $dispositivoId, $limite, $pagina);

            $this->jsonResponse(['success' => true, 'datos' => $datos]);
        } catch (Exception $e) {
            error_log("Error en getDatosTabla: " . $e->getMessage());
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Página de prueba para debug
     */
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
