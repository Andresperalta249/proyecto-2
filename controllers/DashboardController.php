<?php
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/models/DispositivoModel.php';
require_once ROOT_PATH . '/models/Mascota.php';
require_once ROOT_PATH . '/models/User.php';

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
        try {
            $totalConectados = $this->dispositivoModel->getTotalConectados();
            $totalDispositivos = $this->dispositivoModel->getTotalDispositivos();
            $data = [
                'totalDispositivos' => [
                    'conectados' => $totalConectados,
                    'total' => $totalDispositivos
                ],
                'totalMascotas' => $this->mascotaModel->getTotalRegistradas(),
                'distribucionEspecies' => $this->mascotaModel->getDistribucionEspecies()
            ];
            $content = $this->render('dashboard/index', $data);
            $GLOBALS['content'] = $content;
            $GLOBALS['title'] = 'Dashboard';
            $GLOBALS['menuActivo'] = 'dashboard';
            require_once 'views/layouts/main.php';
        } catch (Exception $e) {
            error_log("Error en DashboardController::indexAction: " . $e->getMessage());
            echo '<h1>Error 500</h1><p>Error al cargar el dashboard.</p>';
        }
    }

    public function getKPIDataAction() {
        error_log('Entrando a getKPIDataAction');
        try {
            $userModel = new User();
            
            // Validar que los modelos estén disponibles
            if (!$this->dispositivoModel || !$this->mascotaModel) {
                throw new Exception('Error de inicialización de modelos');
            }

            // Obtener datos con validación
            $usuarios_registrados = $userModel->getTotalUsuariosNormales();
            if ($usuarios_registrados === false) {
                throw new Exception('Error al obtener total de usuarios');
            }

            $dispositivos_conectados = $this->dispositivoModel->getTotalConectados();
            if ($dispositivos_conectados === false) {
                throw new Exception('Error al obtener dispositivos conectados');
            }

            $dispositivos_total = $this->dispositivoModel->getTotalDispositivos();
            if ($dispositivos_total === false) {
                throw new Exception('Error al obtener total de dispositivos');
            }

            $mascotas_total = $this->mascotaModel->getTotalRegistradas();
            if ($mascotas_total === false) {
                throw new Exception('Error al obtener total de mascotas');
            }

            $especies = $this->mascotaModel->getDistribucionEspecies();
            if ($especies === false) {
                throw new Exception('Error al obtener distribución de especies');
            }

            $response = [
                'dispositivos' => [
                    'conectados' => (int)$dispositivos_conectados,
                    'total' => (int)$dispositivos_total
                ],
                'mascotas' => (int)$mascotas_total,
                'especies' => $especies,
                'usuarios_registrados' => (int)$usuarios_registrados
            ];

            $this->sendJsonResponse($response);
        } catch (Exception $e) {
            error_log("Error en DashboardController::getKPIDataAction: " . $e->getMessage());
            $this->sendJsonError('Error al obtener datos KPI: ' . $e->getMessage());
        }
    }



    public function getDistribucionEspeciesAction() {
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

                // Agregar datos de prueba para visualización
                if (empty($registros)) {
                    foreach ($fechas as $fecha) {
                        $registros[] = [
                            'fecha' => $fecha,
                            'usuarios' => 1,
                            'mascotas' => 1
                        ];
                    }
                }
                
                $this->sendJsonResponse($registros);
            } catch (PDOException $e) {
                error_log("Error SQL en getHistorialUsuariosAction: " . $e->getMessage());
                throw new Exception("Error en la base de datos: " . $e->getMessage());
            }
        } catch (Exception $e) {
            error_log("Error en getHistorialUsuariosAction: " . $e->getMessage());
            $this->sendJsonError('Error al obtener historial de usuarios y mascotas: ' . $e->getMessage());
        }
    }

    private function sendJsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function sendJsonError($message, $code = 500) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
} 