<?php
require_once __DIR__ . '/../core/Model.php';

class Rol extends Model {
    protected $table = 'roles';
    
    public function __construct() {
        parent::__construct();
    }

    public function getAll() {
        $sql = "SELECT r.*, (SELECT COUNT(*) FROM usuarios u WHERE u.rol_id = r.id_rol) as usuarios_count 
                FROM {$this->table} r ORDER BY r.id_rol ASC";
        return $this->query($sql);
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id_rol = :id";
        return $this->query($sql, [':id' => $id]);
    }

    public function getPermisosPorRol($rol_id) {
        $sql = "SELECT p.id_permiso, p.nombre 
                FROM permisos p
                JOIN roles_permisos rp ON p.id_permiso = rp.permiso_id
                WHERE rp.rol_id = :rol_id";
        return $this->query($sql, [':rol_id' => $rol_id], true);
    }
    
    public function getPermisos() {
        $sql = "SELECT * FROM permisos";
        return $this->query($sql, [], true);
    }

    public function getPaginated($start, $length, $searchValue, $orderColumn, $orderDir)
    {
        $query = "SELECT 
                    r.id_rol, 
                    r.nombre, 
                    r.descripcion, 
                    r.estado,
                    (SELECT COUNT(*) FROM usuarios u WHERE u.rol_id = r.id_rol) as usuarios_count,
                    (SELECT COUNT(*) FROM roles_permisos rp WHERE rp.rol_id = r.id_rol) as permisos_count,
                    (SELECT GROUP_CONCAT(p.nombre SEPARATOR ', ') 
                     FROM roles_permisos rp 
                     JOIN permisos p ON rp.permiso_id = p.id_permiso 
                     WHERE rp.rol_id = r.id_rol) as permisos_lista
                  FROM {$this->table} r";

        $params = [];
        $whereClause = '';
        if (!empty($searchValue)) {
            $whereClause = " WHERE r.nombre LIKE :searchNombre OR r.descripcion LIKE :searchDesc";
            $params[':searchNombre'] = "%$searchValue%";
            $params[':searchDesc'] = "%$searchValue%";
        }

        // Conteo total de registros
        $totalQuery = "SELECT COUNT(*) as total FROM {$this->table}";
        $totalRecords = $this->query($totalQuery)[0]['total'];
        
        // Conteo de registros filtrados
        $totalFiltered = $totalRecords;
        if (!empty($whereClause)) {
            $totalFilteredQuery = "SELECT COUNT(*) as total FROM {$this->table} r" . $whereClause;
            $totalFiltered = $this->query($totalFilteredQuery, $params)[0]['total'];
        }

        $query .= $whereClause . " ORDER BY {$orderColumn} {$orderDir} LIMIT " . (int)$length . " OFFSET " . (int)$start;
        
        $results = $this->query($query, $params);

        return [
            'data' => $results,
            'recordsTotal' => (int)$totalRecords,
            'recordsFiltered' => (int)$totalFiltered
        ];
    }

    /**
     * Crea un nuevo rol
     * @param array $data Datos del rol
     * @return bool True si se creó correctamente
     */
    public function create($data) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Validar nombre único
            if ($this->nombreExiste($data['nombre'])) {
                throw new Exception('Ya existe un rol con ese nombre');
            }
            
            // Insertar rol
            $sql = "INSERT INTO roles (nombre, descripcion, estado) VALUES (?, ?, ?)";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['descripcion'],
                $data['estado']
            ]);
            
            $id_rol = $this->db->getConnection()->lastInsertId();
            
            // Asignar permisos
            if (!empty($data['permisos'])) {
                $sql = "INSERT INTO roles_permisos (rol_id, permiso_id) VALUES (?, ?)";
                $stmt = $this->db->getConnection()->prepare($sql);
                
                foreach ($data['permisos'] as $id_permiso) {
                    $stmt->execute([$id_rol, $id_permiso]);
                }
            }
            
            $this->db->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza un rol existente
     * @param int $id ID del rol
     * @param array $data Datos del rol
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $data, $idField = null) {
        try {
            $this->db->getConnection()->beginTransaction();
            // Validar que el rol existe
            $rol = $this->getById($id);
            if (!$rol) {
                throw new Exception('Rol no encontrado');
            }
            // Validar nombre único
            if ($this->nombreExiste($data['nombre'], $id)) {
                throw new Exception('Ya existe un rol con ese nombre');
            }
            // Actualizar rol
            $sql = "UPDATE roles SET nombre = ?, descripcion = ?, estado = ? WHERE id_rol = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['descripcion'],
                $data['estado'],
                $id
            ]);
            // Actualizar permisos
            $sql = "DELETE FROM roles_permisos WHERE rol_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id]);
            if (!empty($data['permisos'])) {
                $sql = "INSERT INTO roles_permisos (rol_id, permiso_id) VALUES (?, ?)";
                $stmt = $this->db->getConnection()->prepare($sql);
                foreach ($data['permisos'] as $id_permiso) {
                    $stmt->execute([$id, $id_permiso]);
                }
            }
            $this->db->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina un rol
     * @param int $id ID del rol
     * @return bool True si se eliminó correctamente
     */
    public function delete($id, $idField = null) {
        try {
            $this->db->getConnection()->beginTransaction();
            // Validar que el rol existe
            $rol = $this->getById($id);
            if (!$rol) {
                throw new Exception('Rol no encontrado');
            }
            // Validar que no sea un rol protegido
            if ($id <= 3) {
                throw new Exception('No se puede eliminar un rol protegido');
            }
            // Validar que no tenga usuarios asociados
            $sql = "SELECT COUNT(*) FROM usuarios WHERE rol_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('No se puede eliminar un rol que tiene usuarios asociados');
            }
            // Eliminar permisos
            $sql = "DELETE FROM roles_permisos WHERE rol_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id]);
            // Eliminar rol usando la columna correcta
            $sql = "DELETE FROM roles WHERE id_rol = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id]);
            $this->db->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica si un nombre de rol ya existe
     * @param string $nombre Nombre del rol
     * @param int $excluir_id ID del rol a excluir (para actualizaciones)
     * @return bool True si el nombre ya existe
     */
    private function nombreExiste($nombre, $excluir_id = null) {
        try {
            $sql = "SELECT COUNT(*) FROM roles WHERE nombre = ?";
            $params = [$nombre];
            
            if ($excluir_id) {
                $sql .= " AND id_rol != ?";
                $params[] = $excluir_id;
            }
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en nombreExiste: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cuenta los usuarios asociados a un rol
     * @param int $id_rol
     * @return int
     */
    public function countUsuariosAsociados($id_rol) {
        try {
            $sql = "SELECT COUNT(*) FROM usuarios WHERE rol_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id_rol]);
            
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error en countUsuariosAsociados: " . $e->getMessage());
            throw new Exception('Error al contar usuarios asociados');
        }
    }
    
    /**
     * Quita el rol a todos los usuarios asociados (deja rol_id en NULL)
     * @param int $id_rol
     * @return void
     */
    public function quitarRolAUsuarios($id_rol) {
        $sql = "UPDATE usuarios SET rol_id = NULL WHERE rol_id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$id_rol]);
    }
    
    /**
     * Cambia el estado de un rol
     * @param int $id_rol ID del rol
     * @param string $estado Nuevo estado del rol
     * @return bool True si se actualizó correctamente
     */
    public function cambiarEstado($id_rol, $estado) {
        try {
            $sql = "UPDATE {$this->table} SET estado = :estado WHERE id_rol = :id_rol";
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([':estado' => $estado, ':id_rol' => $id_rol]);
        } catch (Exception $e) {
            error_log("Error al cambiar estado del rol: " . $e->getMessage());
            return false;
        }
    }
} 