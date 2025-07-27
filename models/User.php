<?php
require_once __DIR__ . '/../core/Model.php';

class User extends Model {
    protected $table = 'usuarios';

    public function getAll($filtros = [], $pagina = 1, $alturaPantalla = null) {
        // Calcular registros por página basado en la altura de la pantalla
        $alturaCabecera = 60; // altura aproximada de la cabecera
        $alturaFiltros = 80; // altura aproximada de los filtros
        $alturaPaginacion = 60; // altura aproximada de la paginación
        $alturaFila = 60; // altura aproximada de cada fila
        
        // Si no se proporciona altura, usar un valor por defecto
        if (!$alturaPantalla) {
            $alturaPantalla = 800; // altura por defecto
        }
        
        // Calcular registros por página
        $alturaDisponible = $alturaPantalla - $alturaCabecera - $alturaFiltros - $alturaPaginacion;
        $porPagina = max(1, floor($alturaDisponible / $alturaFila));

        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id_rol 
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['rol_id'])) {
            $sql .= " AND u.rol_id = :rol_id";
            $params[':rol_id'] = $filtros['rol_id'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND u.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }

        // Calcular el offset para la paginación
        $offset = ($pagina - 1) * $porPagina;
        
        // Obtener el total de registros
        $sqlCount = "SELECT COUNT(*) as total FROM usuarios u WHERE 1=1";
        if (!empty($filtros['rol_id'])) {
            $sqlCount .= " AND u.rol_id = :rol_id";
        }
        if (!empty($filtros['estado'])) {
            $sqlCount .= " AND u.estado = :estado";
        }
        
        $total = $this->query($sqlCount, $params)[0]['total'];

        // Agregar límite y offset a la consulta principal
        $sql .= " ORDER BY u.id_usuario DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $porPagina;
        $params[':offset'] = $offset;

        $usuarios = $this->query($sql, $params);

        return [
            'usuarios' => $usuarios,
            'total' => $total,
            'paginas' => ceil($total / $porPagina),
            'pagina_actual' => $pagina,
            'por_pagina' => $porPagina,
            'altura_pantalla' => $alturaPantalla
        ];
    }



    public function buscar($termino, $filtros = []) {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id_rol 
                WHERE 1=1";
        $params = [];

        if (!empty($termino)) {
            $sql .= " AND (u.nombre LIKE :termino OR u.email LIKE :termino)";
            $params[':termino'] = "%$termino%";
        }

        if (!empty($filtros['rol_id'])) {
            $sql .= " AND u.rol_id = :rol_id";
            $params[':rol_id'] = $filtros['rol_id'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND u.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }

        $sql .= " ORDER BY u.id_usuario DESC";
        return $this->query($sql, $params);
    }

    public function findById($id) {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id_rol 
                WHERE u.id_usuario = :id";
        $result = $this->query($sql, [':id' => $id]);
        return $result ? $result[0] : null;
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $this->query($sql, [':email' => $email]);
        if ($stmt && is_array($stmt) && count($stmt) > 0) {
            return $stmt[0];
        }
        return null;
    }

    public function getRoles() {
        $sql = "SELECT * FROM roles ORDER BY nombre";
        return $this->query($sql);
    }

    public function crear($datos) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO usuarios (nombre, email, password, telefono, direccion, rol_id, estado) 
                    VALUES (:nombre, :email, :password, :telefono, :direccion, :rol_id, :estado)";
            
            $params = [
                ':nombre' => $datos['nombre'],
                ':email' => $datos['email'],
                ':password' => password_hash($datos['password'], PASSWORD_DEFAULT),
                ':telefono' => $datos['telefono'],
                ':direccion' => $datos['direccion'],
                ':rol_id' => $datos['rol_id'],
                ':estado' => $datos['estado']
            ];

            $this->query($sql, $params);
            $id = $this->db->lastInsertId();
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Usuario creado correctamente', 'id' => $id];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => 'Error al crear el usuario: ' . $e->getMessage()];
        }
    }

    public function actualizar($datos) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE usuarios SET 
                    nombre = :nombre,
                    telefono = :telefono,
                    direccion = :direccion,
                    rol_id = :rol_id,
                    estado = :estado";

            $params = [
                ':id_usuario' => $datos['id_usuario'],
                ':nombre' => $datos['nombre'],
                ':telefono' => $datos['telefono'],
                ':direccion' => $datos['direccion'],
                ':rol_id' => $datos['rol_id'],
                ':estado' => $datos['estado']
            ];

            if (!empty($datos['password'])) {
                $sql .= ", password = :password";
                $params[':password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id_usuario = :id_usuario";
            $this->query($sql, $params);
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Usuario actualizado correctamente'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => 'Error al actualizar el usuario: ' . $e->getMessage()];
        }
    }

    public function cambiarEstado($id, $estado) {
        try {
            $sql = "UPDATE usuarios SET estado = :estado WHERE id_usuario = :id";
            $this->query($sql, [':id' => $id, ':estado' => $estado]);
            return ['success' => true, 'message' => 'Estado actualizado correctamente'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al cambiar el estado: ' . $e->getMessage()];
        }
    }

    public function cambiarEstadoEnCascada($id, $estado) {
        try {
            $this->db->beginTransaction();

            // Cambiar estado del usuario
            $this->cambiarEstado($id, $estado);

            // Cambiar estado de las mascotas
            $sql = "UPDATE mascotas SET estado = :estado WHERE usuario_id = :id";
            $this->query($sql, [':id' => $id, ':estado' => $estado]);

            // Cambiar estado de los dispositivos asociados a las mascotas
            $sql = "UPDATE dispositivos d 
                    INNER JOIN mascotas m ON d.mascota_id = m.id_mascota 
                    SET d.estado = :estado 
                    WHERE m.usuario_id = :id";
            $this->query($sql, [':id' => $id, ':estado' => $estado]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Estado actualizado correctamente en cascada'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => 'Error al cambiar el estado en cascada: ' . $e->getMessage()];
        }
    }

    public function eliminar($id) {
        try {
            $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
            $this->query($sql, [':id' => $id]);
            return ['success' => true, 'message' => 'Usuario eliminado correctamente'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al eliminar el usuario: ' . $e->getMessage()];
        }
    }

    public function eliminarEnCascada($id) {
        try {
            $this->db->beginTransaction();

            // Obtener IDs de mascotas
            $sql = "SELECT id_mascota FROM mascotas WHERE usuario_id = :id";
            $mascotas = $this->query($sql, [':id' => $id]);
            $mascotaIds = array_column($mascotas, 'id_mascota');

            if (!empty($mascotaIds)) {
                // Eliminar dispositivos asociados a las mascotas
                $sql = "DELETE FROM dispositivos WHERE mascota_id IN (" . implode(',', $mascotaIds) . ")";
                $this->query($sql);

                // Eliminar mascotas
                $sql = "DELETE FROM mascotas WHERE usuario_id = :id";
                $this->query($sql, [':id' => $id]);
            }

            // Eliminar usuario
            $this->eliminar($id);

            $this->db->commit();
            return ['success' => true, 'message' => 'Usuario y registros asociados eliminados correctamente'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => 'Error al eliminar en cascada: ' . $e->getMessage()];
        }
    }

    public function getMascotasAsociadas($id) {
        try {
            $sql = "SELECT * FROM mascotas WHERE usuario_id = :id";
            return $this->query($sql, [':id' => $id]);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getDispositivosAsociados($id) {
        $sql = "SELECT d.* 
                FROM dispositivos d 
                INNER JOIN mascotas m ON d.mascota_id = m.id_mascota 
                WHERE m.usuario_id = :id";
        return $this->query($sql, [':id' => $id]);
    }

    public function getTotalUsuariosNormales() {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE rol_id = 3 AND estado = 'activo'";
        $result = $this->query($sql);
        return $result[0]['total'] ?? 0;
    }

    public function getActiveUsers() {
        $sql = "SELECT * FROM usuarios WHERE estado = 'activo'";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarDispositivosPorMascotas($mascotaIds) {
        if (empty($mascotaIds)) return 0;
        $in = implode(',', array_map('intval', $mascotaIds));
        $sql = "SELECT COUNT(*) as total FROM dispositivos WHERE mascota_id IN ($in)";
        $result = $this->query($sql);
        return $result[0]['total'] ?? 0;
    }
} 