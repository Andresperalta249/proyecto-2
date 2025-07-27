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
} 