<?php
class DispositivoModel {
    private $db;
    private $table = 'dispositivos';
    private $lastError;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDispositivoById($id) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado,
                            d.usuario_id, d.mascota_id,
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
                            u.nombre as usuario_nombre, u.email as usuario_email,
                            m.nombre as nombre_mascota, m.especie as especie_mascota 
                     FROM {$this->table} d 
                     LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario 
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

    public function getTodosDispositivosConMascotas() {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado,
                            d.usuario_id, d.mascota_id,
                            u.nombre as usuario_nombre, u.email as usuario_email,
                            m.nombre as nombre_mascota, m.especie as especie_mascota 
                     FROM {$this->table} d 
                     LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     ORDER BY d.id_dispositivo DESC";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getTodosDispositivosConMascotas: " . $e->getMessage());
            return [];
        }
    }

    public function filtrarDispositivos($filtros, $usuarioId = null) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado,
                            d.usuario_id, d.mascota_id,
                            u.nombre as usuario_nombre, u.email as usuario_email,
                            m.nombre as nombre_mascota, m.especie as especie_mascota 
                     FROM {$this->table} d 
                     LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     WHERE 1=1";
            
            $params = [];
            
            if ($usuarioId) {
                $query .= " AND d.usuario_id = :usuario_id";
                $params[':usuario_id'] = $usuarioId;
            }
            
            if (!empty($filtros['busqueda'])) {
                $query .= " AND (d.nombre LIKE :busqueda OR d.mac LIKE :busqueda)";
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }
            
            if (!empty($filtros['estado'])) {
                $query .= " AND d.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }
            
            if (!empty($filtros['usuario_id'])) {
                $query .= " AND d.usuario_id = :filtro_usuario_id";
                $params[':filtro_usuario_id'] = $filtros['usuario_id'];
            }
            
            if (!empty($filtros['mascota_id'])) {
                $query .= " AND d.mascota_id = :mascota_id";
                $params[':mascota_id'] = $filtros['mascota_id'];
            }
            
            $query .= " ORDER BY d.id_dispositivo DESC";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::filtrarDispositivos: " . $e->getMessage());
            return [];
        }
    }

    public function existeMac($mac, $excludeId = null) {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table} WHERE mac = :mac";
            $params = [':mac' => $mac];
            
            if ($excludeId) {
                $query .= " AND id_dispositivo != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::existeMac: " . $e->getMessage());
            return false;
        }
    }

    public function createDispositivo($data) {
        try {
            // Validar campos requeridos
            if (empty($data['nombre']) || empty($data['mac']) || empty($data['estado'])) {
                error_log("Error en DispositivoModel::createDispositivo: Campos requeridos faltantes");
                $this->lastError = "Los campos nombre, mac y estado son requeridos";
                return false;
            }
            
            // Asignar valores por defecto para campos opcionales
            $usuario_id = isset($data['usuario_id']) && !empty($data['usuario_id']) ? $data['usuario_id'] : null;
            // mascota_id puede ser NULL cuando no hay mascota asignada
            $mascota_id = isset($data['mascota_id']) && !empty($data['mascota_id']) ? (int)$data['mascota_id'] : null;
            
            $query = "INSERT INTO {$this->table} (nombre, mac, estado, usuario_id, mascota_id, creado_en) 
                     VALUES (:nombre, :mac, :estado, :usuario_id, :mascota_id, NOW())";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':mac', $data['mac']);
            $stmt->bindParam(':estado', $data['estado']);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(':mascota_id', $mascota_id, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Error en DispositivoModel::createDispositivo: " . print_r($stmt->errorInfo(), true));
                $this->lastError = implode(', ', $stmt->errorInfo());
                return false;
            }
            
            error_log("Dispositivo creado exitosamente: " . $data['nombre']);
            return $result;
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::createDispositivo: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function updateDispositivo($id, $data) {
        try {
            // Construir consulta dinámicamente basada en los campos proporcionados
            $setParts = [];
            $params = [':id' => $id];
            
            if (isset($data['nombre'])) {
                $setParts[] = "nombre = :nombre";
                $params[':nombre'] = $data['nombre'];
            }
            
            if (isset($data['mac'])) {
                $setParts[] = "mac = :mac";
                $params[':mac'] = $data['mac'];
            }
            
            if (isset($data['estado'])) {
                $setParts[] = "estado = :estado";
                $params[':estado'] = $data['estado'];
            }
            
            if (isset($data['usuario_id'])) {
                $setParts[] = "usuario_id = :usuario_id";
                $params[':usuario_id'] = $data['usuario_id'];
            }
            
            if (isset($data['mascota_id'])) {
                $setParts[] = "mascota_id = :mascota_id";
                // mascota_id puede ser NULL cuando no hay mascota asignada
                $params[':mascota_id'] = !empty($data['mascota_id']) ? (int)$data['mascota_id'] : null;
            }
            
            // No actualizar fecha ya que no existe columna actualizado_en
            // $setParts[] = "actualizado_en = NOW()";
            
            if (empty($setParts)) {
                error_log("Error en DispositivoModel::updateDispositivo: No hay campos para actualizar");
                return false;
            }
            
            $query = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id_dispositivo = :id";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $result = $stmt->execute($params);
            
            if (!$result) {
                error_log("Error en DispositivoModel::updateDispositivo: " . print_r($stmt->errorInfo(), true));
                $this->lastError = implode(', ', $stmt->errorInfo());
                return false;
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::updateDispositivo: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function deleteDispositivo($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id_dispositivo = :id";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::deleteDispositivo: " . $e->getMessage());
            return false;
        }
    }

    public function getDispositivosDisponibles() {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado 
                     FROM {$this->table} d 
                     WHERE d.mascota_id IS NULL AND d.estado = 'activo'";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivosDisponibles: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosByMascota($mascotaId) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado 
                     FROM {$this->table} d 
                     WHERE d.mascota_id = :mascota_id";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':mascota_id', $mascotaId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivosByMascota: " . $e->getMessage());
            return [];
        }
    }

    public function getLastError() {
        return $this->lastError ?? 'Error desconocido';
    }

    public function getTotalDispositivos() {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table}";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getTotalDispositivos: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalConectados() {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table} WHERE estado = 'activo'";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getTotalConectados: " . $e->getMessage());
            return 0;
        }
    }

    public function desasociarDeMascota($mascotaId) {
        try {
            $query = "UPDATE {$this->table} SET mascota_id = NULL WHERE mascota_id = :mascota_id";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':mascota_id', $mascotaId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::desasociarDeMascota: " . $e->getMessage());
            return false;
        }
    }

    // ===== MÉTODOS PARA COMPATIBILIDAD CON APICONTROLLER =====

    public function getAllWithDetails() {
        try {
            $query = "SELECT d.id_dispositivo as id, d.nombre, d.mac, d.estado,
                            d.usuario_id, d.mascota_id,
                            u.nombre as dueño,
                            CASE WHEN d.mascota_id IS NULL THEN 1 ELSE 0 END as disponible,
                            m.nombre as mascota
                     FROM {$this->table} d 
                     LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     ORDER BY d.id_dispositivo DESC";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getAllWithDetails: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        return $this->getDispositivoById($id);
    }

    public function create($data) {
        return $this->createDispositivo($data);
    }

    public function update($id, $data) {
        return $this->updateDispositivo($id, $data);
    }

    public function delete($id) {
        return $this->deleteDispositivo($id);
    }
} 