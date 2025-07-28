<?php
class DispositivoModel {
    private $db;
    private $table = 'dispositivos';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDispositivoById($id) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado,
                            d.usuario_id, d.mascota_id, d.creado_en,
                            m.nombre as nombre_mascota, m.especie as especie_mascota 
                     FROM {$this->table} d 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     WHERE d.id_dispositivo = :id";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivoById: " . $e->getMessage());
            return false;
        }
    }

    public function getDispositivosByUsuario($usuarioId) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado,
                            d.usuario_id, d.mascota_id,
                            m.nombre as nombre_mascota, m.especie as especie_mascota 
                     FROM {$this->table} d 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     WHERE d.usuario_id = :usuario_id";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivosByUsuario: " . $e->getMessage());
            return [];
        }
    }

    public function getUltimaUbicacion($dispositivoId) {
        try {
            $query = "SELECT latitud, longitud, fecha 
                     FROM datos_sensores 
                     WHERE dispositivo_id = :dispositivo_id 
                     ORDER BY fecha DESC 
                     LIMIT 1";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getUltimaUbicacion: " . $e->getMessage());
            return false;
        }
    }

    public function getUltimosDatos($dispositivoId, $horas = 24) {
        try {
            $query = "SELECT temperatura, ritmo_cardiaco, bateria, latitud, longitud, fecha 
                     FROM datos_sensores 
                     WHERE dispositivo_id = :dispositivo_id 
                     AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR) 
                     ORDER BY fecha DESC";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->bindParam(':horas', $horas, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getUltimosDatos: " . $e->getMessage());
            return [];
        }
    }

    public function getRuta($dispositivoId, $horas = 24) {
        try {
            $query = "SELECT latitud, longitud, fecha 
                     FROM datos_sensores 
                     WHERE dispositivo_id = :dispositivo_id 
                     AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR) 
                     ORDER BY fecha ASC";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->bindParam(':horas', $horas, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getRuta: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosWithMascotas($usuarioId) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado,
                            d.usuario_id, d.mascota_id,
                            m.nombre as nombre_mascota, m.especie as especie_mascota 
                     FROM {$this->table} d 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     WHERE d.usuario_id = :usuario_id";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivosWithMascotas: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalDispositivos() {
        try {
            $query = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getTotalDispositivos: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalConectados() {
        try {
            $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE estado = 'conectado'";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getTotalConectados: " . $e->getMessage());
            return 0;
        }
    }

    public function getTodosDispositivosConMascotas() {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado, d.bateria,
                            d.usuario_id, d.mascota_id,
                            m.nombre as mascota_nombre, 
                            u.nombre as usuario_nombre
                     FROM {$this->table} d 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario 
                     ORDER BY d.id_dispositivo DESC";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getTodosDispositivosConMascotas: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosPaginados($offset, $limit) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado, d.bateria,
                            d.usuario_id, d.mascota_id,
                            m.nombre as mascota_nombre, 
                            u.nombre as usuario_nombre
                     FROM {$this->table} d 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario 
                     ORDER BY d.id_dispositivo DESC
                     LIMIT :offset, :limit";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivosPaginados: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosByUsuarioPaginados($usuarioId, $offset, $limit) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado, d.bateria,
                            d.usuario_id, d.mascota_id,
                            m.nombre as mascota_nombre, 
                            u.nombre as usuario_nombre
                     FROM {$this->table} d 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario 
                     WHERE d.usuario_id = :usuario_id
                     ORDER BY d.id_dispositivo DESC
                     LIMIT :offset, :limit";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivosByUsuarioPaginados: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalDispositivosByUsuario($usuarioId) {
        try {
            $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE usuario_id = :usuario_id";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getTotalDispositivosByUsuario: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene dispositivos con filtros avanzados
     */
    public function getDispositivosFiltrados($usuarioId, $propietarioId = null, $mascotaId = null, $mac = '', $soloActivos = false) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado, d.bateria,
                            d.usuario_id, d.mascota_id,
                            m.nombre as mascota_nombre, m.especie as mascota_especie,
                            u.nombre as usuario_nombre,
                            ds.latitude, ds.longitude, ds.fecha as ultima_fecha,
                            ds.temperatura, ds.bpm, ds.bateria as bateria_sensor
                     FROM {$this->table} d 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario 
                     LEFT JOIN datos_sensores ds ON d.id_dispositivo = ds.dispositivo_id 
                     AND ds.fecha = (SELECT MAX(fecha) FROM datos_sensores WHERE dispositivo_id = d.id_dispositivo)";
            
            $conditions = [];
            $params = [];

            // Verificar permisos
            if (!function_exists('verificarPermiso') || !verificarPermiso('ver_todos_dispositivos')) {
                $conditions[] = "d.usuario_id = :usuario_id";
                $params[':usuario_id'] = $usuarioId;
            }

            // Filtro por propietario
            if ($propietarioId) {
                $conditions[] = "d.usuario_id = :propietario_id";
                $params[':propietario_id'] = $propietarioId;
            }

            // Filtro por mascota
            if ($mascotaId) {
                $conditions[] = "d.mascota_id = :mascota_id";
                $params[':mascota_id'] = $mascotaId;
            }

            // Filtro por MAC
            if (!empty($mac)) {
                $conditions[] = "(d.mac LIKE :mac_inicio OR d.mac LIKE :mac_fin)";
                $params[':mac_inicio'] = $mac . '%';
                $params[':mac_fin'] = '%' . $mac;
            }

            // Filtro solo activos
            if ($soloActivos) {
                $conditions[] = "d.estado = 'activo'";
            }

            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }

            $query .= " ORDER BY d.id_dispositivo DESC";
            
            $stmt = $this->db->getConnection()->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivosFiltrados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene lista de propietarios para filtros
     */
    public function getPropietariosDispositivos($usuarioId) {
        try {
            $query = "SELECT DISTINCT u.id_usuario, u.nombre 
                     FROM usuarios u 
                     INNER JOIN {$this->table} d ON u.id_usuario = d.usuario_id";
            
            $params = [];
            if (!function_exists('verificarPermiso') || !verificarPermiso('ver_todos_dispositivos')) {
                $query .= " WHERE d.usuario_id = :usuario_id";
                $params[':usuario_id'] = $usuarioId;
            }
            
            $query .= " ORDER BY u.nombre";
            
            $stmt = $this->db->getConnection()->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getPropietariosDispositivos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene mascotas por propietario para filtros
     */
    public function getMascotasPorPropietario($usuarioId, $propietarioId = null) {
        try {
            $query = "SELECT m.id_mascota, m.nombre, m.especie 
                     FROM mascotas m 
                     INNER JOIN {$this->table} d ON m.id_mascota = d.mascota_id";
            
            $conditions = [];
            $params = [];

            if (!function_exists('verificarPermiso') || !verificarPermiso('ver_todos_dispositivos')) {
                $conditions[] = "d.usuario_id = :usuario_id";
                $params[':usuario_id'] = $usuarioId;
            }

            if ($propietarioId) {
                $conditions[] = "d.usuario_id = :propietario_id";
                $params[':propietario_id'] = $propietarioId;
            }

            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $query .= " ORDER BY m.nombre";
            
            $stmt = $this->db->getConnection()->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getMascotasPorPropietario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene dispositivos asociados a una mascota específica
     */
    public function getDispositivosByMascota($mascotaId) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado, d.usuario_id, d.mascota_id
                     FROM {$this->table} d 
                     WHERE d.mascota_id = :mascota_id";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':mascota_id', $mascotaId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivosByMascota: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza un dispositivo
     */
    public function updateDispositivo($id, $data) {
        try {
            $setClauses = [];
            $params = [':id' => $id];
            
            foreach ($data as $key => $value) {
                $setClauses[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE id_dispositivo = :id";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::updateDispositivo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene dispositivos disponibles (sin mascota asignada)
     */
    public function getDispositivosDisponibles() {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado, d.usuario_id, d.mascota_id
                     FROM {$this->table} d 
                     WHERE d.mascota_id IS NULL OR d.mascota_id = 0
                     ORDER BY d.nombre";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivosDisponibles: " . $e->getMessage());
            return [];
        }
    }

    public function existeMac($mac, $excludeId = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE mac = :mac";
            $params = [':mac' => $mac];
            
            if ($excludeId) {
                $query .= " AND id_dispositivo != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::existeMac: " . $e->getMessage());
            return false;
        }
    }

    public function createDispositivo($data) {
        try {
            $sql = "INSERT INTO {$this->table} (nombre, mac, estado, usuario_id, mascota_id, creado_en) 
                    VALUES (:nombre, :mac, :estado, :usuario_id, :mascota_id, NOW())";
            
            $params = [
                ':nombre' => $data['nombre'],
                ':mac' => $data['mac'],
                ':estado' => $data['estado'],
                ':usuario_id' => $data['user_id'] ?? null,
                ':mascota_id' => $data['mascota_id'] ?? null
            ];
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                return $this->db->getConnection()->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::createDispositivo: " . $e->getMessage());
            return false;
        }
    }

    public function deleteDispositivo($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id_dispositivo = :id";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::deleteDispositivo: " . $e->getMessage());
            return false;
        }
    }
} 