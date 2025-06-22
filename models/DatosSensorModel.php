<?php
class DatosSensorModel {
    private $db;
    private $table = 'datos_sensores';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDatosPorDispositivo($dispositivoId, $horas = 24) {
        try {
            $query = "SELECT * FROM {$this->table} 
                     WHERE dispositivo_id = :dispositivo_id 
                     AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR) 
                     ORDER BY fecha DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->bindParam(':horas', $horas, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DatosSensorModel::getDatosPorDispositivo: " . $e->getMessage());
            return [];
        }
    }

    public function getUltimosDatos($dispositivoId, $limite = 10) {
        try {
            $query = "SELECT * FROM {$this->table} 
                     WHERE dispositivo_id = :dispositivo_id 
                     ORDER BY fecha DESC 
                     LIMIT :limite";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DatosSensorModel::getUltimosDatos: " . $e->getMessage());
            return [];
        }
    }

    public function insertarDato($datos) {
        try {
            $query = "INSERT INTO {$this->table} 
                     (dispositivo_id, temperatura, bpm, bateria, latitude, longitude, fecha) 
                     VALUES 
                     (:dispositivo_id, :temperatura, :bpm, :bateria, :latitude, :longitude, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dispositivo_id', $datos['dispositivo_id'], PDO::PARAM_INT);
            $stmt->bindParam(':temperatura', $datos['temperatura'], PDO::PARAM_STR);
            $stmt->bindParam(':bpm', $datos['bpm'], PDO::PARAM_INT);
            $stmt->bindParam(':bateria', $datos['bateria'], PDO::PARAM_INT);
            $stmt->bindParam(':latitude', $datos['latitude'], PDO::PARAM_STR);
            $stmt->bindParam(':longitude', $datos['longitude'], PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en DatosSensorModel::insertarDato: " . $e->getMessage());
            return false;
        }
    }

    public function getDatosParaGrafica($dispositivoId, $horas = 24) {
        try {
            $query = "SELECT temperatura, bpm, bateria, fecha 
                     FROM {$this->table} 
                     WHERE dispositivo_id = :dispositivo_id 
                     AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR) 
                     ORDER BY fecha ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->bindParam(':horas', $horas, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DatosSensorModel::getDatosParaGrafica: " . $e->getMessage());
            return [];
        }
    }

    public function getUltimaUbicacion($dispositivoId) {
        try {
            $query = "SELECT latitude, longitude, fecha 
                     FROM {$this->table} 
                     WHERE dispositivo_id = :dispositivo_id 
                     AND latitude IS NOT NULL 
                     AND longitude IS NOT NULL 
                     ORDER BY fecha DESC 
                     LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DatosSensorModel::getUltimaUbicacion: " . $e->getMessage());
            return null;
        }
    }

    public function buscarRegistrosAvanzado($usuario_id = null, $mascota_id = null, $mac = null, $page = 1, $perPage = 20, $fecha_inicio = null, $fecha_fin = null) {
        try {
            $conditions = [];
            $params = [];
            
            // Construir WHERE clause
            if ($usuario_id) {
                $conditions[] = "m.usuario_id = :usuario_id";
                $params[':usuario_id'] = $usuario_id;
            }
            
            if ($mascota_id) {
                $conditions[] = "m.id_mascota = :mascota_id";
                $params[':mascota_id'] = $mascota_id;
            }
            
            if ($mac) {
                $conditions[] = "d.mac LIKE :mac";
                $params[':mac'] = "%{$mac}%";
            }
            
            if ($fecha_inicio) {
                $conditions[] = "ds.fecha >= :fecha_inicio";
                $params[':fecha_inicio'] = $fecha_inicio . ' 00:00:00';
            }
            
            if ($fecha_fin) {
                $conditions[] = "ds.fecha <= :fecha_fin";
                $params[':fecha_fin'] = $fecha_fin . ' 23:59:59';
            }
            
            $whereClause = '';
            if (!empty($conditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            }
            
            // Consulta para contar total de registros
            $countQuery = "SELECT COUNT(*) as total 
                          FROM {$this->table} ds
                          LEFT JOIN dispositivos d ON ds.dispositivo_id = d.id_dispositivo  
                          LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
                          LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario
                          {$whereClause}";
            
            $stmt = $this->db->prepare($countQuery);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Consulta principal con paginación
            $offset = ($page - 1) * $perPage;
            $dataQuery = "SELECT 
                            ds.fecha as fecha_hora,
                            ds.temperatura,
                            ds.bpm as ritmo_cardiaco,
                            ds.bateria,
                            ds.latitude as latitud,
                            ds.longitude as longitud,
                            CONCAT(ds.latitude, ', ', ds.longitude) as ubicacion,
                            m.nombre as mascota_nombre,
                            u.nombre as dueno_nombre,
                            d.mac,
                            d.nombre as dispositivo_nombre
                          FROM {$this->table} ds
                          LEFT JOIN dispositivos d ON ds.dispositivo_id = d.id_dispositivo  
                          LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
                          LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario
                          {$whereClause}
                          ORDER BY ds.fecha DESC
                          LIMIT :offset, :perPage";
            
            $stmt = $this->db->prepare($dataQuery);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => ceil($total / $perPage)
            ];
            
        } catch (PDOException $e) {
            error_log("Error en DatosSensorModel::buscarRegistrosAvanzado: " . $e->getMessage());
            return [
                'data' => [],
                'total' => 0,
                'page' => 1,
                'perPage' => $perPage,
                'totalPages' => 0
            ];
        }
    }

    public function obtenerUltimasUbicacionesMascotas() {
        try {
            $query = "SELECT DISTINCT
                        m.nombre as mascota_nombre,
                        u.nombre as dueno_nombre,
                        d.mac,
                        ds.latitude,
                        ds.longitude,
                        ds.fecha
                      FROM {$this->table} ds
                      INNER JOIN dispositivos d ON ds.dispositivo_id = d.id_dispositivo
                      INNER JOIN mascotas m ON d.mascota_id = m.id_mascota
                      INNER JOIN usuarios u ON m.usuario_id = u.id_usuario
                      WHERE ds.latitude IS NOT NULL 
                      AND ds.longitude IS NOT NULL
                      AND ds.fecha = (
                          SELECT MAX(ds2.fecha) 
                          FROM {$this->table} ds2 
                          WHERE ds2.dispositivo_id = ds.dispositivo_id 
                          AND ds2.latitude IS NOT NULL 
                          AND ds2.longitude IS NOT NULL
                      )
                      ORDER BY ds.fecha DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DatosSensorModel::obtenerUltimasUbicacionesMascotas: " . $e->getMessage());
            return [];
        }
    }
} 