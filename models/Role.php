<?php
require_once __DIR__ . '/../config/database.php';

class Role {
    private $db;
    private $conn;
    private $table = 'roles';

    public function __construct(Database $database) {
        $this->db = $database;
        $this->conn = $this->db->getConnection();
    }

    public function getAllRoles() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY id ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Role::getAllRoles: " . $e->getMessage());
            throw new Exception("Error al obtener los roles");
        }
    }

    public function getById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Role::getById: " . $e->getMessage());
            throw new Exception("Error al obtener el rol");
        }
    }

    public function getPermissions($roleId) {
        try {
            $sql = "SELECT p.* FROM permisos p 
                    INNER JOIN rol_permisos rp ON p.id = rp.permiso_id 
                    WHERE rp.rol_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$roleId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Role::getPermissions: " . $e->getMessage());
            throw new Exception("Error al obtener los permisos del rol");
        }
    }

    public function getRolePermisos($roleId) {
        return $this->getPermissions($roleId);
    }

    public function getAllPermisos() {
        try {
            $query = "SELECT * FROM permisos WHERE estado = 'activo' ORDER BY id ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Role::getAllPermisos: " . $e->getMessage());
            throw new Exception("Error al obtener los permisos");
        }
    }

    public function create($data, $permisos) {
        try {
            $conn = $this->conn;
            $conn->beginTransaction();

            // Insertar el nuevo rol
            $stmt = $conn->prepare("INSERT INTO {$this->table} (nombre, descripcion, estado, es_predeterminado) VALUES (?, ?, 'activo', 0)");
            $stmt->execute([$data['nombre'], $data['descripcion']]);
            $rolId = $conn->lastInsertId();

            // Insertar los permisos para el rol
            if (!empty($permisos)) {
                $stmt = $conn->prepare("INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)");
                foreach ($permisos as $permisoId) {
                    $stmt->execute([$rolId, $permisoId]);
                }
            }

            $conn->commit();
            return true;
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Error en Role::create: " . $e->getMessage());
            throw new Exception("Error al crear el rol");
        }
    }

    public function getByNombre($nombre) {
        $query = "SELECT * FROM roles WHERE nombre = ? AND estado = 'activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$nombre]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function asignarPermisos($rolId, $permisos) {
        $conn = null;
        try {
            error_log("Iniciando asignación de permisos para rol ID: " . $rolId);
            error_log("Permisos a asignar: " . print_r($permisos, true));
            
            $conn = $this->db->getConnection();
            $conn->beginTransaction();

            // Verificar que el rol existe
            $stmt = $conn->prepare("SELECT id FROM roles WHERE id = ? AND estado = 'activo'");
            $stmt->execute([$rolId]);
            if (!$stmt->fetch()) {
                throw new Exception("El rol especificado no existe o está inactivo");
            }

            // Eliminar permisos existentes
            $stmt = $conn->prepare("DELETE FROM rol_permisos WHERE rol_id = ?");
            if (!$stmt->execute([$rolId])) {
                throw new Exception("Error al eliminar permisos existentes: " . implode(", ", $stmt->errorInfo()));
            }
            error_log("Permisos existentes eliminados");

            // Insertar nuevos permisos
            if (!empty($permisos)) {
                $values = [];
                $params = [];
                foreach ($permisos as $permisoId) {
                    // Verificar que el permiso existe
                    $stmt = $conn->prepare("SELECT id FROM permisos WHERE id = ? AND estado = 'activo'");
                    $stmt->execute([$permisoId]);
                    if (!$stmt->fetch()) {
                        throw new Exception("El permiso ID $permisoId no existe o está inactivo");
                    }
                    
                    $values[] = "(?, ?)";
                    $params[] = $rolId;
                    $params[] = $permisoId;
                }

                $query = "INSERT INTO rol_permisos (rol_id, permiso_id) VALUES " . implode(", ", $values);
                $stmt = $conn->prepare($query);
                if (!$stmt->execute($params)) {
                    throw new Exception("Error al asignar permisos: " . implode(", ", $stmt->errorInfo()));
                }
                error_log("Nuevos permisos asignados exitosamente");
            }

            $conn->commit();
            error_log("Transacción de asignación de permisos completada");
            return true;

        } catch (Exception $e) {
            error_log("Error en Role::asignarPermisos: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
                error_log("Transacción de permisos revertida");
            }
            
            throw $e;
        }
    }

    public function update($id, $data, $permisos) {
        try {
            $conn = $this->conn;
            $conn->beginTransaction();

            // Verificar si es un rol predeterminado
            $stmt = $conn->prepare("SELECT es_predeterminado FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$rol) {
                throw new Exception("El rol no existe");
            }
            
            if ($rol['es_predeterminado']) {
                throw new Exception("No se pueden modificar los roles predeterminados");
            }

            // Actualizar el rol
            $stmt = $conn->prepare("UPDATE {$this->table} SET nombre = ?, descripcion = ? WHERE id = ? AND es_predeterminado = 0");
            $stmt->execute([$data['nombre'], $data['descripcion'], $id]);

            // Eliminar permisos existentes
            $stmt = $conn->prepare("DELETE FROM rol_permisos WHERE rol_id = ?");
            $stmt->execute([$id]);

            // Insertar nuevos permisos
            if (!empty($permisos)) {
                $stmt = $conn->prepare("INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)");
                foreach ($permisos as $permisoId) {
                    $stmt->execute([$id, $permisoId]);
                }
            }

            $conn->commit();
            return true;
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Error en Role::update: " . $e->getMessage());
            throw new Exception("Error al actualizar el rol");
        }
    }

    public function delete($id) {
        try {
            $conn = $this->conn;
            $conn->beginTransaction();

            // Verificar si es un rol predeterminado
            $stmt = $conn->prepare("SELECT es_predeterminado FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$rol) {
                throw new Exception("El rol no existe");
            }
            
            if ($rol['es_predeterminado']) {
                throw new Exception("No se pueden eliminar los roles predeterminados");
            }

            // Verificar si hay usuarios asignados al rol
            $usuariosCount = $this->getUsersCount($id);
            if ($usuariosCount > 0) {
                throw new Exception("No se puede eliminar el rol porque tiene usuarios asignados");
            }

            // Eliminar los permisos asociados al rol
            $stmt = $conn->prepare("DELETE FROM rol_permisos WHERE rol_id = ?");
            $stmt->execute([$id]);

            // Eliminar el rol
            $stmt = $conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);

            $conn->commit();
            return true;
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Error en Role::delete: " . $e->getMessage());
            throw new Exception("Error al eliminar el rol");
        }
    }

    public function getUsersCount($rolId) {
        $query = "SELECT COUNT(*) as count FROM usuarios WHERE rol_id = ? AND estado = 'activo'";
        $result = $this->db->query($query, [$rolId])->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function updateEstado($id, $estado) {
        try {
            // Verificar si es un rol predeterminado
            $stmt = $this->conn->prepare("SELECT es_predeterminado FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$rol) {
                throw new Exception("El rol no existe");
            }
            
            if ($rol['es_predeterminado']) {
                throw new Exception("No se pueden modificar los roles predeterminados");
            }

            // Actualizar el estado
            $query = "UPDATE {$this->table} SET estado = :estado WHERE id = :id AND es_predeterminado = 0";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar el estado del rol");
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en Role::updateEstado: " . $e->getMessage());
            throw new Exception("Error al actualizar el estado del rol");
        }
    }

    public function getUsuariosConRol($roleId) {
        try {
            $query = "SELECT u.* FROM usuarios u WHERE u.rol_id = :role_id AND u.estado = 'activo'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en Role::getUsuariosConRol: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el rol de un usuario específico
     * @param int $userId ID del usuario
     * @return array|false Datos del rol o false si no se encuentra
     */
    public function getUserRole($userId) {
        try {
            $sql = "SELECT r.* FROM roles r 
                    INNER JOIN usuarios u ON u.rol_id = r.id 
                    WHERE u.id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Role::getUserRole: " . $e->getMessage());
            return false;
        }
    }
} 