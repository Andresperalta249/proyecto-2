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
     * Obtiene datos para tabla en tiempo real
     */
    public function getDatosTabla($usuarioId, $dispositivoId = null, $limite = 50, $pagina = 1) {
        try {
            $offset = ($pagina - 1) * $limite;
            
            $query = "SELECT ds.id, ds.dispositivo_id, ds.fecha, ds.latitude, ds.longitude, 
                            ds.altitude, ds.speed, ds.bpm, ds.temperatura, ds.bateria,
                            d.nombre as dispositivo_nombre, d.mac,
                            m.nombre as mascota_nombre, m.especie as mascota_especie,
                            u.nombre as usuario_nombre
                     FROM {$this->table} ds
                     INNER JOIN dispositivos d ON ds.dispositivo_id = d.id_dispositivo
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
                     LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario";
            
            $conditions = [];
            $params = [];

            // Verificar permisos
            if (!function_exists('verificarPermiso') || !verificarPermiso('ver_todos_dispositivos')) {
                $conditions[] = "d.usuario_id = :usuario_id";
                $params[':usuario_id'] = $usuarioId;
            }

            // Filtro por dispositivo específico
            if ($dispositivoId) {
                $conditions[] = "ds.dispositivo_id = :dispositivo_id";
                $params[':dispositivo_id'] = $dispositivoId;
            }

            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }

            $query .= " ORDER BY ds.fecha DESC LIMIT :limite OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getDatosTabla: " . $e->getMessage());
            return [];
        }
    }
} 