<?php
class Log extends Model {
    protected $table = 'logs';

    public function __construct() {
        parent::__construct();
    }

    public function crearLog($usuarioId, $accion) {
        try {
            $data = [
                'usuario_id' => $usuarioId,
                'accion' => $accion,
                'fecha' => date('Y-m-d H:i:s')
            ];
            return $this->create($data);
        } catch (Exception $e) {
            error_log("Error en crearLog: " . $e->getMessage());
            return false;
        }
    }

    public function getActividadReciente($usuario_id, $limit = 10) {
        try {
            $sql = "SELECT l.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre
                    FROM {$this->table} l
                    LEFT JOIN dispositivos d ON l.dispositivo_id = d.id
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.usuario_id = :usuario_id
                    ORDER BY l.fecha DESC
                    LIMIT :limit";
            $result = $this->query($sql, [
                ':usuario_id' => $usuario_id,
                ':limit' => $limit
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getActividadReciente: " . $e->getMessage());
            return [];
        }
    }

    public function getActividadByFecha($usuarioId, $fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT l.*, u.nombre as usuario_nombre 
                    FROM {$this->table} l 
                    JOIN usuarios u ON l.usuario_id = u.id 
                    WHERE l.usuario_id = ? 
                    AND l.fecha BETWEEN ? AND ? 
                    ORDER BY l.fecha DESC";
            $result = $this->query($sql, [$usuarioId, $fechaInicio, $fechaFin]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getActividadByFecha: " . $e->getMessage());
            return [];
        }
    }

    public function getActividadByTipo($usuarioId, $tipo) {
        try {
            $sql = "SELECT l.*, u.nombre as usuario_nombre 
                    FROM {$this->table} l 
                    JOIN usuarios u ON l.usuario_id = u.id 
                    WHERE l.usuario_id = ? 
                    AND l.accion LIKE ? 
                    ORDER BY l.fecha DESC";
            $result = $this->query($sql, [$usuarioId, "%$tipo%"]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getActividadByTipo: " . $e->getMessage());
            return [];
        }
    }

    public function limpiarLogsAntiguos($dias = 30) {
        try {
            $sql = "DELETE FROM {$this->table} 
                    WHERE fecha < DATE_SUB(NOW(), INTERVAL :dias DAY)";
            return $this->query($sql, [':dias' => $dias]);
        } catch (Exception $e) {
            error_log("Error en limpiarLogsAntiguos: " . $e->getMessage());
            return false;
        }
    }

    public function getEstadisticas($usuario_id) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(DISTINCT dispositivo_id) as dispositivos,
                        COUNT(DISTINCT tipo) as tipos
                    FROM {$this->table} l
                    LEFT JOIN dispositivos d ON l.dispositivo_id = d.id
                    WHERE d.usuario_id = :usuario_id";
            $result = $this->query($sql, [':usuario_id' => $usuario_id]);
            return $result ? $result[0] : [
                'total' => 0,
                'dispositivos' => 0,
                'tipos' => 0
            ];
        } catch (Exception $e) {
            error_log("Error en getEstadisticas: " . $e->getMessage());
            return [
                'total' => 0,
                'dispositivos' => 0,
                'tipos' => 0
            ];
        }
    }

    public function registrarActividad($dispositivo_id, $tipo, $mensaje, $datos = null) {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (dispositivo_id, tipo, mensaje, datos, fecha) 
                    VALUES (:dispositivo_id, :tipo, :mensaje, :datos, NOW())";
            return $this->query($sql, [
                ':dispositivo_id' => $dispositivo_id,
                ':tipo' => $tipo,
                ':mensaje' => $mensaje,
                ':datos' => $datos ? json_encode($datos) : null
            ]);
        } catch (Exception $e) {
            error_log("Error en registrarActividad: " . $e->getMessage());
            return false;
        }
    }

    public function getActividadPorDispositivo($dispositivo_id, $limit = 10) {
        try {
            $sql = "SELECT l.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre
                    FROM {$this->table} l
                    JOIN dispositivos d ON l.dispositivo_id = d.id
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE l.dispositivo_id = :dispositivo_id
                    ORDER BY l.fecha DESC
                    LIMIT :limit";
            $result = $this->query($sql, [
                ':dispositivo_id' => $dispositivo_id,
                ':limit' => $limit
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getActividadPorDispositivo: " . $e->getMessage());
            return [];
        }
    }

    public function getActividadPorMascota($mascota_id, $limit = 10) {
        try {
            $sql = "SELECT l.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre
                    FROM {$this->table} l
                    JOIN dispositivos d ON l.dispositivo_id = d.id
                    JOIN mascotas m ON d.mascota_id = m.id
                    WHERE m.id_mascota = :mascota_id
                    ORDER BY l.fecha DESC
                    LIMIT :limit";
            $result = $this->query($sql, [
                ':mascota_id' => $mascota_id,
                ':limit' => $limit
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getActividadPorMascota: " . $e->getMessage());
            return [];
        }
    }

    public function getActividadPorTipo($usuario_id, $tipo, $limit = 10) {
        try {
            $sql = "SELECT l.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre
                    FROM {$this->table} l
                    JOIN dispositivos d ON l.dispositivo_id = d.id
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.usuario_id = :usuario_id 
                    AND l.tipo = :tipo
                    ORDER BY l.fecha DESC
                    LIMIT :limit";
            $result = $this->query($sql, [
                ':usuario_id' => $usuario_id,
                ':tipo' => $tipo,
                ':limit' => $limit
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getActividadPorTipo: " . $e->getMessage());
            return [];
        }
    }

    public function getActividadPorFecha($usuario_id, $fecha_inicio, $fecha_fin) {
        try {
            $sql = "SELECT l.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre
                    FROM {$this->table} l
                    JOIN dispositivos d ON l.dispositivo_id = d.id
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.usuario_id = :usuario_id 
                    AND l.fecha BETWEEN :fecha_inicio AND :fecha_fin
                    ORDER BY l.fecha DESC";
            $result = $this->query($sql, [
                ':usuario_id' => $usuario_id,
                ':fecha_inicio' => $fecha_inicio,
                ':fecha_fin' => $fecha_fin
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getActividadPorFecha: " . $e->getMessage());
            return [];
        }
    }

    public function getActividadPorTipoConEstadisticas($usuario_id) {
        try {
            $sql = "SELECT 
                        l.tipo,
                        COUNT(*) as total,
                        COUNT(DISTINCT l.dispositivo_id) as dispositivos
                    FROM {$this->table} l
                    LEFT JOIN dispositivos d ON l.dispositivo_id = d.id
                    WHERE d.usuario_id = :usuario_id
                    GROUP BY l.tipo";
            $result = $this->query($sql, [':usuario_id' => $usuario_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getActividadPorTipoConEstadisticas: " . $e->getMessage());
            return [];
        }
    }
}
?> 