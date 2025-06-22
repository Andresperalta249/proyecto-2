<?php

class DatosSensor extends Model {
    protected $table = 'datos_sensores';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Obtiene los datos recientes de un dispositivo en un rango de horas
     */
    public function getUltimosDatos($dispositivoId, $horas = 24) {
        try {
            $sql = "SELECT fecha, bpm, temperatura, bateria, latitude AS latitud, longitude AS longitud
                    FROM {$this->table}
                    WHERE dispositivo_id = :dispositivo_id
                    AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR)
                    ORDER BY fecha ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':dispositivo_id' => $dispositivoId,
                ':horas' => $horas
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getUltimosDatos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la ruta de un dispositivo en un rango de horas
     */
    public function getRuta($dispositivoId, $horas) {
        try {
            $sql = "SELECT latitude, longitude, fecha 
                    FROM {$this->table} 
                    WHERE dispositivo_id = :dispositivo_id 
                    AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR)
                    ORDER BY fecha ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':dispositivo_id' => $dispositivoId,
                ':horas' => $horas
            ]);
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!$result) {
                error_log("No se encontró ruta para el dispositivo ID: " . $dispositivoId);
                return [];
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error en getRuta: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene datos para una gráfica específica
     */
    public function getDatosGrafica($dispositivoId, $tipo, $horas) {
        try {
            $campo = $this->getCampoPorTipo($tipo);
            if (!$campo) {
                error_log("Tipo de dato no válido: " . $tipo);
                return [];
            }

            $sql = "SELECT fecha, {$campo} as valor 
                    FROM {$this->table} 
                    WHERE dispositivo_id = :dispositivo_id 
                    AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR)
                    ORDER BY fecha ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':dispositivo_id' => $dispositivoId,
                ':horas' => $horas
            ]);
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!$result) {
                error_log("No se encontraron datos para la gráfica del dispositivo ID: " . $dispositivoId);
                return [];
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error en getDatosGrafica: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas de un tipo de dato específico
     */
    public function getEstadisticas($dispositivoId, $tipo, $horas) {
        $campo = $this->getCampoPorTipo($tipo);
        if (!$campo) {
            return null;
        }

        $sql = "SELECT 
                    MIN({$campo}) as minimo,
                    MAX({$campo}) as maximo,
                    AVG({$campo}) as promedio
                FROM {$this->table} 
                WHERE dispositivo_id = :dispositivo_id 
                AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR)";
        
        return $this->query($sql, [
            ':dispositivo_id' => $dispositivoId,
            ':horas' => $horas
        ])->fetch();
    }

    /**
     * Obtiene el nombre del campo según el tipo de dato
     */
    private function getCampoPorTipo($tipo) {
        $campos = [
            'temperatura' => 'temperatura',
            'ritmoCardiaco' => 'bpm',
            'bateria' => 'bateria'
        ];

        return $campos[$tipo] ?? null;
    }

    /**
     * Obtiene la última ubicación registrada de un dispositivo
     */
    public function getUltimaUbicacion($dispositivoId) {
        try {
            $sql = "SELECT latitude AS latitud, longitude AS longitud, fecha 
                    FROM {$this->table} 
                    WHERE dispositivo_id = :dispositivo_id 
                    AND latitude IS NOT NULL 
                    AND longitude IS NOT NULL 
                    ORDER BY fecha DESC 
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':dispositivo_id' => $dispositivoId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                error_log("No se encontró ubicación para el dispositivo ID: " . $dispositivoId);
                return null;
            }
            return $result;
        } catch (Exception $e) {
            error_log("Error en getUltimaUbicacion: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene los datos recientes de un dispositivo en un rango de horas
     */
    public function getDatosPorDispositivo($dispositivoId, $horas = 24) {
        try {
            $sql = "SELECT 
                        fecha,
                        temperatura,
                        bpm as ritmo_cardiaco,
                        bateria,
                        latitude as latitud,
                        longitude as longitud
                    FROM {$this->table}
                    WHERE dispositivo_id = :dispositivo_id
                    AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR)
                    ORDER BY fecha ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':dispositivo_id' => $dispositivoId,
                ':horas' => $horas
            ]);
            
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Datos obtenidos para dispositivo {$dispositivoId}: " . print_r($datos, true));
            return $datos;
        } catch (Exception $e) {
            error_log("Error en getDatosPorDispositivo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Búsqueda avanzada y paginada de registros históricos
     */
    public function buscarRegistrosAvanzado($usuario_id = null, $mascota_id = null, $mac = null, $page = 1, $perPage = 20, $fecha_inicio = null, $fecha_fin = null) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];
        $join = '';

        $join .= ' INNER JOIN dispositivos d ON d.id_dispositivo = datos_sensores.dispositivo_id';
        $join .= ' INNER JOIN mascotas m ON d.mascota_id = m.id_mascota';
        $join .= ' INNER JOIN usuarios u ON m.usuario_id = u.id_usuario';
        if ($usuario_id) {
            $where[] = 'd.usuario_id = :usuario_id';
            $params[':usuario_id'] = $usuario_id;
        }
        if ($mascota_id) {
            $where[] = 'd.mascota_id = :mascota_id';
            $params[':mascota_id'] = $mascota_id;
        }
        if ($mac) {
            $where[] = 'd.mac LIKE :mac';
            $params[':mac'] = '%' . $mac . '%';
        }
        if ($fecha_inicio && $fecha_fin) {
            $where[] = 'datos_sensores.fecha BETWEEN :fecha_inicio AND :fecha_fin';
            $params[':fecha_inicio'] = $fecha_inicio . ' 00:00:00';
            $params[':fecha_fin'] = $fecha_fin . ' 23:59:59';
        }
        $whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $sql = "SELECT datos_sensores.*, d.mac, d.mascota_id, m.nombre AS mascota_nombre, u.nombre AS dueno_nombre
                FROM datos_sensores
                $join
                $whereSQL
                ORDER BY datos_sensores.fecha DESC
                LIMIT :offset, :perPage";
        error_log('[DEBUG] SQL: ' . $sql);
        error_log('[DEBUG] PARAMS: ' . json_encode($params));
        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log('[DEBUG] DATA: ' . json_encode($data));
        // Total para paginación
        $sqlCount = "SELECT COUNT(*) as total FROM datos_sensores $join $whereSQL";
        $stmtCount = $this->db->getConnection()->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        // Formatear datos para la tabla
        $result = [];
        foreach ($data as $r) {
            error_log('[DEBUG] MAC EN REGISTRO: ' . json_encode($r['mac']));
            $result[] = [
                'fecha_hora' => $r['fecha'],
                'temperatura' => $r['temperatura'],
                'ritmo_cardiaco' => $r['bpm'],
                'ubicacion' => ($r['latitude'] && $r['longitude']) ? ($r['latitude'] . ', ' . $r['longitude']) : '',
                'bateria' => $r['bateria'],
                'latitud' => $r['latitude'],
                'longitud' => $r['longitude'],
                'mascota_nombre' => $r['mascota_nombre'],
                'dueno_nombre' => $r['dueno_nombre'],
                'mac' => $r['mac'],
            ];
        }
        error_log('[DEBUG] RESULT: ' . json_encode($result));
        return [
            'data' => $result,
            'total' => (int)$total,
            'page' => (int)$page,
            'perPage' => (int)$perPage
        ];
    }

    /**
     * Devuelve la última ubicación de cada mascota con nombre, dueño y MAC
     */
    public function obtenerUltimasUbicacionesMascotas() {
        $sql = "SELECT m.id_mascota, m.nombre AS mascota_nombre, u.nombre AS dueno_nombre, d.mac,
                        ds.latitude, ds.longitude, ds.fecha
                FROM mascotas m
                INNER JOIN usuarios u ON m.usuario_id = u.id_usuario
                INNER JOIN dispositivos d ON d.mascota_id = m.id_mascota
                INNER JOIN (
                    SELECT dispositivo_id, MAX(fecha) AS max_fecha
                    FROM datos_sensores
                    WHERE latitude IS NOT NULL AND longitude IS NOT NULL
                    GROUP BY dispositivo_id
                ) ult ON ult.dispositivo_id = d.id_dispositivo
                INNER JOIN datos_sensores ds ON ds.dispositivo_id = d.id_dispositivo AND ds.fecha = ult.max_fecha
                WHERE ds.latitude IS NOT NULL AND ds.longitude IS NOT NULL
                ORDER BY ds.fecha DESC";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
} 