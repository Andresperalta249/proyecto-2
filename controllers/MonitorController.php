<?php
class MonitorController extends Controller {
    private $dispositivoModel;
    private $mascotaModel;
    private $datosSensorModel;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación para todo el controlador
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }
        
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
        $this->view->setData('titulo', 'Monitor IoT');
        $this->view->setData('subtitulo', 'Monitor IoT - Consulta datos históricos, ubicaciones y filtra por dispositivos y mascotas.');

        // Determinar permisos para filtros
        $puedeVerTodasMascotas = verificarPermiso('ver_todas_mascotas');
        $puedeVerMascotas = verificarPermiso('ver_mascotas');
        
        $this->view->render('monitor/index', [
            'puedeVerTodasMascotas' => $puedeVerTodasMascotas,
            'puedeVerMascotas' => $puedeVerMascotas,
            'usuarioActual' => $_SESSION['user_id']
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
            echo json_encode(['success' => false, 'error' => 'ID de dispositivo no proporcionado']);
            return;
        }

        // Verificar si el dispositivo pertenece al usuario o tiene permisos para ver todos
        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        if (!verificarPermiso('ver_todos_dispositivo') && $dispositivo['usuario_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
            return;
        }

        try {
            $horas = isset($_GET['horas']) ? (int)$_GET['horas'] : 24;
            $datos = $this->datosSensorModel->getDatosPorDispositivo($id, $horas);
            $ubicacion = $this->datosSensorModel->getUltimaUbicacion($id);
            
            echo json_encode([
                'success' => true,
                'datos' => $datos,
                'ubicacion' => $ubicacion
            ]);
        } catch (Exception $e) {
            error_log("Error en getDatosAction: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al obtener los datos']);
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

    // ========================================
    // FUNCIONALIDAD DE REPORTES INTEGRADA
    // ========================================

    public function getPropietariosAction() {
        header('Content-Type: application/json');
        try {
            $q = $_GET['q'] ?? '';
            require_once 'models/UsuarioModel.php';
            $userModel = new UsuarioModel();
            
            // Filtrar propietarios según permisos
            if (verificarPermiso('ver_todas_mascotas')) {
                // Puede ver todas las mascotas - mostrar todos los usuarios
                $propietarios = $userModel->getAll();
            } else if (verificarPermiso('ver_mascotas')) {
                // Solo puede ver sus propias mascotas - mostrar solo él mismo
                $propietarios = [$userModel->findById($_SESSION['user_id'])];
            } else {
                echo json_encode(['results' => []]);
                exit;
            }
            
            $result = [];
            foreach ($propietarios as $u) {
                if ($u && (empty($q) || stripos($u['nombre'], $q) !== false || stripos($u['email'], $q) !== false)) {
                    $result[] = [
                        'id' => $u['id_usuario'],
                        'text' => $u['nombre'] . ' (' . $u['email'] . ')'
                    ];
                }
            }
            echo json_encode(['results' => $result]);
        } catch (Exception $e) {
            error_log('Error en getPropietariosAction: ' . $e->getMessage());
            echo json_encode(['results' => []]);
        }
        exit;
    }

    public function getMascotasPorPropietarioAction() {
        header('Content-Type: application/json');
        $usuario_id = $_GET['usuario_id'] ?? null;
        if (!$usuario_id) { echo json_encode(['results'=>[]]); exit; }
        $mascotas = $this->mascotaModel->findAll(['usuario_id' => $usuario_id]);
        $result = array_map(function($m) {
            return [
                'id' => $m['id_mascota'],
                'text' => $m['nombre'] . ' (' . $m['especie'] . ')'
            ];
        }, $mascotas);
        echo json_encode(['results' => $result]);
        exit;
    }

    public function getMacsAction() {
        header('Content-Type: application/json');
        try {
            $q = $_GET['q'] ?? '';
            $dispositivos = $this->dispositivoModel->getTodosDispositivosConMascotas();
            
            $result = [];
            foreach ($dispositivos as $d) {
                if (empty($q) || stripos($d['mac'], $q) !== false) {
                    $result[] = [
                        'id' => $d['mac'],
                        'text' => $d['mac'] . ' (' . $d['nombre'] . ')'
                    ];
                }
            }
            echo json_encode(['results' => $result]);
        } catch (Exception $e) {
            error_log('Error en getMacsAction: ' . $e->getMessage());
            echo json_encode(['results' => []]);
        }
        exit;
    }

    public function getRegistrosAction() {
        header('Content-Type: application/json');
        try {
            // Procesar parámetros correctamente: convertir strings vacíos a null
            $usuario_id = !empty($_GET['usuario_id']) ? $_GET['usuario_id'] : null;
            $mascota_id = !empty($_GET['mascota_id']) ? $_GET['mascota_id'] : null;
            $mac = !empty($_GET['mac']) ? $_GET['mac'] : null;
            
            // Convertir string boolean a boolean real
            $mostrarTodos = ($_GET['mostrar_todos'] ?? 'false') === 'true';
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = min(50, intval($_GET['perPage'] ?? 20));
            
            $fecha_inicio = !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fecha_fin = !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            
            // Log para debug (remover en producción)
            error_log("DEBUG getRegistros - usuario_id: " . ($usuario_id ?? 'null') . ", mascota_id: " . ($mascota_id ?? 'null') . ", mac: " . ($mac ?? 'null') . ", mostrarTodos: " . ($mostrarTodos ? 'true' : 'false'));
            
            // SIEMPRE aplicar filtros de permisos apropiados
            if ($mostrarTodos || (!$usuario_id && !$mascota_id && !$mac)) {
                error_log("DEBUG: Entrando en rama principal de filtros");
                
                // Si se solicita mostrar todos O si no hay filtros específicos, aplicar permisos
                if (verificarPermiso('ver_todas_mascotas')) {
                    error_log("DEBUG: Usuario puede ver TODAS las mascotas");
                    // Puede ver todas las mascotas del sistema - no filtrar por usuario
                    // Mantener usuario_id, mascota_id y mac tal como vienen
                } else if (verificarPermiso('ver_mascotas')) {
                    error_log("DEBUG: Usuario solo puede ver SUS mascotas");
                    // Solo puede ver sus propias mascotas - filtrar por usuario actual
                    if (!$usuario_id) {
                        $usuario_id = $_SESSION['user_id'];
                        error_log("DEBUG: Asignando usuario_id = " . $_SESSION['user_id']);
                    } else if ($usuario_id != $_SESSION['user_id']) {
                        error_log("DEBUG: Intenta ver datos de otro usuario sin permiso");
                        // Intenta ver datos de otro usuario sin permiso
                        echo json_encode(['data' => [], 'total' => 0, 'page' => 1, 'totalPages' => 0]);
                        exit;
                    }
                } else {
                    error_log("DEBUG: No tiene permisos para ver datos");
                    // No tiene permisos para ver datos
                    echo json_encode(['data' => [], 'total' => 0, 'page' => 1, 'totalPages' => 0]);
                    exit;
                }
            } else {
                error_log("DEBUG: Hay filtros específicos");
                // Hay filtros específicos - verificar permisos
                if (!verificarPermiso('ver_todas_mascotas')) {
                    if ($usuario_id && $usuario_id != $_SESSION['user_id']) {
                        error_log("DEBUG: Intenta filtrar por otro usuario sin permiso");
                        // Intenta filtrar por otro usuario sin permiso
                        echo json_encode(['data' => [], 'total' => 0, 'page' => 1, 'totalPages' => 0]);
                        exit;
                    }
                    // Si no especifica usuario, usar el actual
                    if (!$usuario_id) {
                        $usuario_id = $_SESSION['user_id'];
                        error_log("DEBUG: Asignando usuario_id = " . $_SESSION['user_id'] . " (filtros específicos)");
                    }
                }
            }
            
            error_log("DEBUG: Parámetros finales - usuario_id: " . ($usuario_id ?? 'null') . ", mascota_id: " . ($mascota_id ?? 'null') . ", mac: " . ($mac ?? 'null'));
            
            $result = $this->datosSensorModel->buscarRegistrosAvanzado($usuario_id, $mascota_id, $mac, $page, $perPage, $fecha_inicio, $fecha_fin);
            
            error_log("DEBUG: Resultado - total: " . $result['total'] . ", registros: " . count($result['data']));
            
            echo json_encode($result);
        } catch (Exception $e) {
            error_log('Error en getRegistrosAction: ' . $e->getMessage());
            echo json_encode(['data' => [], 'total' => 0, 'page' => 1, 'totalPages' => 0]);
        }
        exit;
    }

    public function exportarCsvAction() {
        try {
            $usuario_id = $_GET['usuario_id'] ?? null;
            $mascota_id = $_GET['mascota_id'] ?? null;
            $mac = $_GET['mac'] ?? null;
            
            $result = $this->datosSensorModel->buscarRegistrosAvanzado($usuario_id, $mascota_id, $mac, 1, 10000);
            $registros = $result['data'] ?? [];
            
            // Exportar como CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="reporte_monitoreo_iot.csv"');
            $out = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fputs($out, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            fputcsv($out, ['Fecha y hora','Temperatura','Ritmo cardíaco','Ubicación','Batería']);
            foreach($registros as $r) {
                fputcsv($out, [
                    $r['fecha_hora'] ?? '',
                    $r['temperatura'] ?? '',
                    $r['ritmo_cardiaco'] ?? '',
                    $r['ubicacion'] ?? '',
                    $r['bateria'] ?? ''
                ]);
            }
            fclose($out);
        } catch (Exception $e) {
            error_log('Error en exportarCsvAction: ' . $e->getMessage());
            echo 'Error al exportar datos';
        }
        exit;
    }

    public function getUltimasUbicacionesAction() {
        header('Content-Type: application/json');
        try {
            $result = $this->datosSensorModel->obtenerUltimasUbicacionesMascotas();
            echo json_encode($result);
        } catch (Exception $e) {
            error_log('Error en getUltimasUbicacionesAction: ' . $e->getMessage());
            echo json_encode([]);
        }
        exit;
    }
}