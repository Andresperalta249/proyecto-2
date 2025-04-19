<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = 'usuarios';

    public function __construct($database) {
        $this->conn = $database->getConnection();
    }

    public function getAllUsers($filters = [], $start = 0, $length = 10) {
        try {
            $sql = "SELECT u.*, r.nombre as rol_nombre 
                    FROM {$this->table} u 
                    LEFT JOIN roles r ON u.rol_id = r.id 
                    WHERE 1=1";
            $params = [];

            // Aplicar filtros
            if (!empty($filters['rol'])) {
                $sql .= " AND u.rol_id = ?";
                $params[] = $filters['rol'];
            }

            if (isset($filters['estado']) && $filters['estado'] !== '') {
                $sql .= " AND u.estado = ?";
                $params[] = $filters['estado'] === '1' ? 'activo' : 'inactivo';
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (u.nombre LIKE ? OR u.email LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY u.id DESC";

            // Agregar LIMIT para paginación
            if ($length > 0) {
                $sql .= " LIMIT ?, ?";
                $params[] = (int)$start;
                $params[] = (int)$length;
            }

            error_log("SQL Query: " . $sql);
            error_log("SQL Params: " . json_encode($params));

            $stmt = $this->conn->prepare($sql);
            
            // Bind parameters
            if (!empty($params)) {
                foreach ($params as $i => $param) {
                    $stmt->bindValue($i + 1, $param);
                }
            }
            
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Usuarios encontrados: " . count($users));
            return $users;

        } catch (PDOException $e) {
            error_log("Error en User::getAllUsers: " . $e->getMessage());
            error_log("SQL Query: " . $sql);
            error_log("SQL Params: " . json_encode($params));
            throw new Exception("Error al obtener usuarios: " . $e->getMessage());
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en User::getById: " . $e->getMessage());
            throw new Exception("Error al obtener usuario");
        }
    }

    public function findByEmail($email) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en User::findByEmail: " . $e->getMessage());
            throw new Exception("Error al buscar usuario por email");
        }
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO {$this->table} (nombre, email, password, rol_id, estado, created_at) 
                    VALUES (?, ?, ?, ?, 'activo', NOW())";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['nombre'],
                $data['email'],
                $data['password'],
                $data['rol_id'] ?? 3 // rol por defecto: usuario normal
            ]);
        } catch (PDOException $e) {
            error_log("Error en User::create: " . $e->getMessage());
            throw new Exception("Error al crear usuario");
        }
    }

    public function update($id, $data) {
        try {
            $updates = [];
            $params = [];

            // Construir la consulta dinámicamente
            if (isset($data['nombre'])) {
                $updates[] = "nombre = ?";
                $params[] = $data['nombre'];
            }

            if (isset($data['password'])) {
                $updates[] = "password = ?";
                $params[] = $data['password'];
            }

            if (isset($data['rol_id'])) {
                $updates[] = "rol_id = ?";
                $params[] = $data['rol_id'];
            }

            if (empty($updates)) {
                return false;
            }

            $updates[] = "updated_at = NOW()";
            $params[] = $id;

            $sql = "UPDATE {$this->table} SET " . implode(", ", $updates) . " WHERE id = ?";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error en User::update: " . $e->getMessage());
            throw new Exception("Error al actualizar usuario");
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en User::delete: " . $e->getMessage());
            throw new Exception("Error al eliminar usuario");
        }
    }

    public function updateStatus($id, $status) {
        try {
            // Validar el estado
            if (!in_array($status, ['activo', 'inactivo'])) {
                throw new Exception("Estado no válido");
            }

            // Validar que el usuario existe
            $user = $this->getById($id);
            if (!$user) {
                throw new Exception("Usuario no encontrado");
            }

            $stmt = $this->conn->prepare("UPDATE {$this->table} SET estado = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$status, $id]);

            if (!$result) {
                throw new Exception("Error al actualizar el estado");
            }

            // Verificar si realmente se actualizó algún registro
            if ($stmt->rowCount() === 0) {
                throw new Exception("No se pudo actualizar el estado del usuario");
            }

            return true;
        } catch (PDOException $e) {
            error_log("Error en User::updateStatus: " . $e->getMessage());
            throw new Exception("Error al actualizar estado del usuario: " . $e->getMessage());
        }
    }

    public function countSuperadmins() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE rol_id = 1");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en User::countSuperadmins: " . $e->getMessage());
            throw new Exception("Error al contar superadministradores");
        }
    }

    public function countActiveSuperadmins() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE rol_id = 1 AND estado = 'activo'");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en User::countActiveSuperadmins: " . $e->getMessage());
            throw new Exception("Error al contar superadministradores activos");
        }
    }

    public function authenticate($email, $password) {
        try {
            $user = $this->findByEmail($email);
            
            if (!$user || $user['estado'] !== 'activo') {
                return false;
            }

            if (password_verify($password, $user['password'])) {
                // Registrar el acceso
                $this->logAccess($user['id']);
                unset($user['password']);
                return $user;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error en User::authenticate: " . $e->getMessage());
            throw new Exception("Error en la autenticación");
        }
    }

    private function logAccess($userId) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO historial_accesos (usuario_id, created_at) VALUES (?, NOW())");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Error en User::logAccess: " . $e->getMessage());
            // No lanzamos excepción aquí para no interrumpir el login
        }
    }

    public function getTotalUsers() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table}");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en User::getTotalUsers: " . $e->getMessage());
            throw new Exception("Error al contar usuarios totales");
        }
    }

    public function getTotalFilteredUsers($filters = []) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} u WHERE 1=1";
            $params = [];

            // Aplicar filtros
            if (!empty($filters['rol'])) {
                $sql .= " AND u.rol_id = ?";
                $params[] = $filters['rol'];
            }

            if (isset($filters['estado']) && $filters['estado'] !== '') {
                $sql .= " AND u.estado = ?";
                $params[] = $filters['estado'] === '1' ? 'activo' : 'inactivo';
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (u.nombre LIKE ? OR u.email LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $stmt = $this->conn->prepare($sql);
            
            if (!empty($params)) {
                foreach ($params as $i => $param) {
                    $stmt->bindValue($i + 1, $param);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en User::getTotalFilteredUsers: " . $e->getMessage());
            throw new Exception("Error al contar usuarios filtrados");
        }
    }

    /**
     * Obtiene la fecha del último acceso del usuario
     * @param int $userId ID del usuario
     * @return string|null Fecha del último acceso formateada o null si no hay registros
     */
    public function getLastAccess($userId) {
        try {
            $sql = "SELECT created_at FROM historial_accesos 
                    WHERE usuario_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Formatear la fecha
                $date = new DateTime($result['created_at']);
                return $date->format('d/m/Y H:i');
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error en User::getLastAccess: " . $e->getMessage());
            return null;
        }
    }
}
?> 