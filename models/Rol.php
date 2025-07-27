<?php
require_once __DIR__ . '/../core/Model.php';
class Rol extends Model {
    protected $table = 'roles';
    
    // Roles del sistema que tienen control total
    private $rolesSistema = [1, 2, 3]; // Super Admin, Admin, Moderador
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Verifica si un rol es un rol del sistema (con control total)
     * @param int $id_rol ID del rol
     * @return bool True si es rol del sistema
     */
    public function esRolSistema($id_rol) {
        return in_array($id_rol, $this->rolesSistema);
    }
    
    /**
     * Verifica si un rol puede gestionar otros roles
     * @param int $id_rol ID del rol
     * @return bool True si puede gestionar roles
     */
    public function puedeGestionarRoles($id_rol) {
        return $this->esRolSistema($id_rol);
    }
    
    /**
     * Obtiene todos los roles
     * @return array Array con los roles
     */
    public function getAll() {
        try {
            $sql = "SELECT r.*, GROUP_CONCAT(DISTINCT p.nombre) as permisos, 
                           GROUP_CONCAT(DISTINCT p.id_permiso) as permiso_ids
                    FROM roles r
                    LEFT JOIN roles_permisos rp ON r.id_rol = rp.rol_id
                    LEFT JOIN permisos p ON rp.permiso_id = p.id_permiso
                    GROUP BY r.id_rol
                    ORDER BY r.id_rol";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar los permisos
            foreach ($roles as &$rol) {
                $rol['permisos'] = $rol['permisos'] ? explode(',', $rol['permisos']) : [];
                $rol['permiso_ids'] = $rol['permiso_ids'] ? explode(',', $rol['permiso_ids']) : [];
                // Agregar información de si es rol del sistema
                $rol['es_rol_sistema'] = $this->esRolSistema($rol['id_rol']);
                $rol['puede_gestionar_roles'] = $this->puedeGestionarRoles($rol['id_rol']);
            }
            
            return is_array($roles) ? $roles : [];
        } catch (PDOException $e) {
            error_log("Error en getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene roles con filtros de búsqueda
     * @param string $search Término de búsqueda
     * @param string $estado Filtro de estado
     * @return array Array con los roles filtrados
     */
    public function getAllWithSearch($search = '', $estado = '') {
        try {
            $sql = "SELECT r.*, GROUP_CONCAT(DISTINCT p.nombre) as permisos, 
                           GROUP_CONCAT(DISTINCT p.id_permiso) as permiso_ids
                    FROM roles r
                    LEFT JOIN roles_permisos rp ON r.id_rol = rp.rol_id
                    LEFT JOIN permisos p ON rp.permiso_id = p.id_permiso";
            
            $whereConditions = [];
            $params = [];
            
            // Filtro de búsqueda
            if (!empty($search)) {
                $whereConditions[] = "(r.nombre LIKE ? OR r.descripcion LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            // Filtro de estado
            if (!empty($estado)) {
                $whereConditions[] = "r.estado = ?";
                $params[] = $estado;
            }
            
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(' AND ', $whereConditions);
            }
            
            $sql .= " GROUP BY r.id_rol ORDER BY r.id_rol";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar los permisos
            foreach ($roles as &$rol) {
                $rol['permisos'] = $rol['permisos'] ? explode(',', $rol['permisos']) : [];
                $rol['permiso_ids'] = $rol['permiso_ids'] ? explode(',', $rol['permiso_ids']) : [];
                // Agregar información de si es rol del sistema
                $rol['es_rol_sistema'] = $this->esRolSistema($rol['id_rol']);
                $rol['puede_gestionar_roles'] = $this->puedeGestionarRoles($rol['id_rol']);
            }
            
            return is_array($roles) ? $roles : [];
        } catch (PDOException $e) {
            error_log("Error en getAllWithSearch: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene un rol por su ID
     * @param int $id_rol ID del rol
     * @return array Datos del rol
     */
    public function getById($id_rol) {
        try {
            $sql = "SELECT r.*, GROUP_CONCAT(DISTINCT p.nombre) as permisos,
                           GROUP_CONCAT(DISTINCT p.id_permiso) as permiso_ids
                    FROM roles r
                    LEFT JOIN roles_permisos rp ON r.id_rol = rp.rol_id
                    LEFT JOIN permisos p ON rp.permiso_id = p.id_permiso
                    WHERE r.id_rol = ?
                    GROUP BY r.id_rol";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id_rol]);
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($rol) {
                $rol['permisos'] = $rol['permisos'] ? explode(',', $rol['permisos']) : [];
                $rol['permiso_ids'] = $rol['permiso_ids'] ? explode(',', $rol['permiso_ids']) : [];
                // Agregar información de si es rol del sistema
                $rol['es_rol_sistema'] = $this->esRolSistema($rol['id_rol']);
                $rol['puede_gestionar_roles'] = $this->puedeGestionarRoles($rol['id_rol']);
            }
            
            return is_array($rol) ? $rol : [];
        } catch (PDOException $e) {
            error_log("Error en getById: " . $e->getMessage());
            return [];
        }
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
    public function update($id, $data) {
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
     * @param int $id_rol ID del rol
     * @return bool True si se eliminó correctamente
     */
    public function delete($id_rol) {
        try {
            $this->db->getConnection()->beginTransaction();
            // Validar que el rol existe
            $rol = $this->getById($id_rol);
            if (!$rol) {
                throw new Exception('Rol no encontrado');
            }
            // Validar que no sea un rol protegido del sistema
            if ($this->esRolSistema($id_rol)) {
                throw new Exception('No se puede eliminar un rol del sistema');
            }
            // Validar que no tenga usuarios asociados
            $sql = "SELECT COUNT(*) FROM usuarios WHERE rol_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id_rol]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('No se puede eliminar un rol que tiene usuarios asociados');
            }
            // Eliminar permisos
            $sql = "DELETE FROM roles_permisos WHERE rol_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id_rol]);
            // Eliminar rol usando la columna correcta
            $sql = "DELETE FROM roles WHERE id_rol = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id_rol]);
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
     * Obtiene todos los permisos disponibles
     * @return array Array con los permisos
     */
    public function getPermisos() {
        $sql = "SELECT id_permiso, nombre, descripcion FROM permisos WHERE estado = 'activo' ORDER BY nombre";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            // Validar que el rol existe
            $rol = $this->getById($id_rol);
            if (!$rol) {
                throw new Exception('Rol no encontrado');
            }
            
            // Validar que no sea un rol protegido del sistema
            if ($this->esRolSistema($id_rol)) {
                throw new Exception('No se puede modificar un rol del sistema');
            }
            
            $sql = "UPDATE roles SET estado = ? WHERE id_rol = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([$estado, $id_rol]);
        } catch (Exception $e) {
            error_log("Error en cambiarEstado: " . $e->getMessage());
            return false;
        }
    }
    
    public function getPermisosPorRol($id_rol) {
        $sql = "SELECT p.id_permiso, p.nombre, p.descripcion
                FROM permisos p
                INNER JOIN roles_permisos rp ON p.id_permiso = rp.permiso_id
                WHERE rp.rol_id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$id_rol]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 