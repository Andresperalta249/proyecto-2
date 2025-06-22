<?php
require_once 'core/Controller.php';
require_once 'models/DispositivoModel.php';
require_once 'models/Mascota.php';
require_once 'models/User.php';

class DashboardController extends Controller {
    private $dispositivoModel;
    private $mascotaModel;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            header('Location: /proyecto-2/auth/login');
            exit;
        }

        $this->dispositivoModel = new DispositivoModel();
        $this->mascotaModel = new Mascota();
    }

    public function indexAction() {
        // Verificar permisos para ver dashboard
        if (!verificarPermiso('ver_dashboard')) {
            error_log("[DEBUG] Usuario sin permiso ver_dashboard, redirigiendo a monitor");
            // Redirigir a monitor si no tiene permisos de dashboard
            header('Location: /proyecto-2/monitor');
            exit;
        }

        $this->view->setLayout('main');
        $this->view->setData('titulo', 'Dashboard');
        $this->view->setData('subtitulo', 'Resumen general del sistema.');

        try {
            $data = [
                'totalDispositivos' => [
                    'conectados' => $this->dispositivoModel->getTotalConectados(),
                    'total' => $this->dispositivoModel->getTotalDispositivos()
                ],
                'totalMascotas' => $this->mascotaModel->getTotalRegistradas(),
                'totalUsuarios' => (new User())->getTotalUsuariosNormales(),
                'distribucionEspecies' => $this->mascotaModel->getDistribucionEspecies()
            ];
            $this->view->render('dashboard/index', $data);
        } catch (Exception $e) {
            error_log("Error en DashboardController::indexAction: " . $e->getMessage());
            $this->view->render('errors/500', ['error' => $e->getMessage()]);
        }
    }

    public function getKPIDataAction() {
        // Verificar permisos para ver dashboard
        if (!verificarPermiso('ver_dashboard')) {
            $this->sendJsonError('No tienes permiso para ver el dashboard', 403);
            return;
        }

        try {
            $userModel = new User();
            if (!$this->dispositivoModel || !$this->mascotaModel) {
                throw new Exception('Error de inicialización de modelos');
            }
            
            // Log de depuración
            error_log("=== DEBUG DASHBOARD KPI ===");
            
            $usuarios_registrados = $userModel->getTotalUsuariosNormales();
            error_log("Usuarios registrados: " . $usuarios_registrados);
            if ($usuarios_registrados === false) {
                throw new Exception('Error al obtener total de usuarios');
            }
            
            $dispositivos_conectados = $this->dispositivoModel->getTotalConectados();
            error_log("Dispositivos conectados: " . $dispositivos_conectados);
            if ($dispositivos_conectados === false) {
                throw new Exception('Error al obtener dispositivos conectados');
            }
            
            $dispositivos_total = $this->dispositivoModel->getTotalDispositivos();
            error_log("Total dispositivos: " . $dispositivos_total);
            if ($dispositivos_total === false) {
                throw new Exception('Error al obtener total de dispositivos');
            }
            
            $mascotas_total = $this->mascotaModel->getTotalRegistradas();
            error_log("Total mascotas: " . $mascotas_total);
            if ($mascotas_total === false) {
                throw new Exception('Error al obtener total de mascotas');
            }
            
            $especies = $this->mascotaModel->getDistribucionEspecies();
            error_log("Especies: " . print_r($especies, true));
            if ($especies === false) {
                throw new Exception('Error al obtener distribución de especies');
            }
            
            error_log("=== FIN DEBUG DASHBOARD KPI ===");
            
            $response = [
                'dispositivos' => [
                    'conectados' => (int)$dispositivos_conectados,
                    'total' => (int)$dispositivos_total
                ],
                'mascotas' => (int)$mascotas_total,
                'totalMascotas' => (int)$mascotas_total,
                'totalDispositivos' => [
                    'conectados' => (int)$dispositivos_conectados,
                    'total' => (int)$dispositivos_total
                ],
                'totalUsuarios' => (int)$usuarios_registrados,
                'especies' => $especies
            ];
            $this->sendJsonResponse($response);
        } catch (Exception $e) {
            error_log("Error en DashboardController::getKPIDataAction: " . $e->getMessage());
            $this->sendJsonError('Error al obtener datos KPI: ' . $e->getMessage());
        }
    }

    public function getDistribucionEspeciesAction() {
        // Verificar permisos para ver dashboard
        if (!verificarPermiso('ver_dashboard')) {
            $this->sendJsonError('No tienes permiso para ver el dashboard', 403);
            return;
        }

        try {
            $distribucion = $this->mascotaModel->getDistribucionEspecies();
            if ($distribucion === false) {
                throw new Exception('Error al obtener distribución de especies');
            }
            $this->sendJsonResponse($distribucion);
        } catch (Exception $e) {
            error_log("Error en DashboardController::getDistribucionEspeciesAction: " . $e->getMessage());
            $this->sendJsonError('Error al obtener distribución de especies: ' . $e->getMessage());
        }
    }

    public function getHistorialUsuariosAction() {
        // Verificar permisos para ver dashboard
        if (!verificarPermiso('ver_dashboard')) {
            $this->sendJsonError('No tienes permiso para ver el dashboard', 403);
            return;
        }

        try {
            $dias = isset($_GET['dias']) ? (int)$_GET['dias'] : 7;
            $dias = max(1, min($dias, 30)); // Limitar entre 1 y 30 días
            
            // Obtener las fechas de los últimos N días
            $fechas = [];
            for ($i = $dias - 1; $i >= 0; $i--) {
                $fechas[] = date('Y-m-d', strtotime("-$i days"));
            }
            $desde = $fechas[0];
            $hasta = $fechas[count($fechas)-1];
            
            // Consulta para usuarios y mascotas
            $sql = "SELECT 
                    fecha,
                    SUM(usuarios) as usuarios,
                    SUM(mascotas) as mascotas
                FROM (
                    SELECT DATE(creado_en) as fecha, COUNT(*) as usuarios, 0 as mascotas
                    FROM usuarios 
                    WHERE DATE(creado_en) BETWEEN ? AND ?
                    GROUP BY DATE(creado_en)
                    UNION ALL
                    SELECT DATE(creado_en) as fecha, 0 as usuarios, COUNT(*) as mascotas
                    FROM mascotas 
                    WHERE DATE(creado_en) BETWEEN ? AND ?
                    GROUP BY DATE(creado_en)
                ) as combined
                GROUP BY fecha
                ORDER BY fecha";
            
            try {
                $stmt = $this->db->getConnection()->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta SQL");
                }
                
                // Vincular los parámetros en el orden correcto
                $stmt->bindValue(1, $desde, PDO::PARAM_STR);
                $stmt->bindValue(2, $hasta, PDO::PARAM_STR);
                $stmt->bindValue(3, $desde, PDO::PARAM_STR);
                $stmt->bindValue(4, $hasta, PDO::PARAM_STR);
                
                if (!$stmt->execute()) {
                    throw new Exception("Error al ejecutar la consulta SQL: " . implode(" ", $stmt->errorInfo()));
                }
                
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($result === false) {
                    throw new Exception("Error al obtener los resultados");
                }

                // Asegurar que todos los días tengan un valor, incluso si es 0
                $registros = [];
                foreach ($fechas as $fecha) {
                    $encontrado = false;
                    foreach ($result as $row) {
                        if ($row['fecha'] === $fecha) {
                            $registros[] = [
                                'fecha' => $fecha,
                                'usuarios' => (int)$row['usuarios'],
                                'mascotas' => (int)$row['mascotas']
                            ];
                            $encontrado = true;
                            break;
                        }
                    }
                    if (!$encontrado) {
                        $registros[] = [
                            'fecha' => $fecha,
                            'usuarios' => 0,
                            'mascotas' => 0
                        ];
                    }
                }
                
                $this->sendJsonResponse($registros);
            } catch (Exception $e) {
                throw new Exception("Error en la consulta de historial: " . $e->getMessage());
            }
        } catch (Exception $e) {
            error_log("Error en DashboardController::getHistorialUsuariosAction: " . $e->getMessage());
            $this->sendJsonError('Error al obtener historial de usuarios: ' . $e->getMessage());
        }
    }

    private function sendJsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
    }

    private function sendJsonError($message, $code = 500) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
    }
} 