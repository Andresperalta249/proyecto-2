<?php
/**
 * Modelo UsuarioModel
 * -------------------
 * Modelo para acceder y manipular la tabla de usuarios en la base de datos.
 *
 * Hereda de: Model (core/Model.php)
 *
 * Atributos:
 *   - table: Nombre de la tabla ('usuarios')
 *
 * Métodos principales:
 *   - obtenerPorId($id): Obtiene un usuario por su ID.
 *   - obtenerPorEmail($email): Obtiene un usuario por su email.
 *   - obtenerTodos(): Obtiene todos los usuarios.
 *   - guardar($datos): Guarda o actualiza un usuario.
 *   - eliminar($id): Elimina un usuario.
 *
 * Relación:
 *   - Hereda de Model, por lo que puede usar todos los métodos genéricos de acceso a datos.
 *   - Relación con Rol (asignación de roles) y Mascota (propietario de mascotas).
 *
 * Ejemplo de uso:
 *   $usuario = $usuarioModel->obtenerPorId($id);
 *   $usuarios = $usuarioModel->obtenerTodos();
 */
class UsuarioModel extends Model {
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario'; // Clave primaria específica de esta tabla

    public function __construct() {
        parent::__construct();
        
        // Verificar si la tabla existe y tiene datos
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $result = $this->query($sql);
            $count = $result[0]['count'] ?? 0;
        } catch (Exception $e) {
        }
        
        // Verificar estructura de la tabla
        try {
            $sql = "DESCRIBE {$this->table}";
            $structure = $this->query($sql);
        } catch (Exception $e) {
        }
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * @param array $data Los datos del usuario.
     * @return mixed El ID del nuevo registro o false en caso de error.
     */
    public function create($data) {
        // Asegurarse de que el estado esté presente
        $data['estado'] = $data['estado'] ?? 'inactivo';
        
        // Quitar campos que no existen en la tabla o se manejan aparte
        unset($data['confirm_password']);

        return parent::create($data);
    }

    /**
     * Obtiene un usuario por su dirección de email.
     * @param string $email El email del usuario.
     * @return array|null Los datos del usuario o null si no se encuentra.
     */
    public function getUsuarioByEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = :email";
            $result = $this->query($sql, [':email' => $email]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * @param array $data Los datos del usuario a crear.
     * @return int|false El ID del nuevo usuario o false si falla.
     */
    public function crearUsuario($data) {
        try {
            $sql = "INSERT INTO {$this->table} (nombre, email, password, rol_id, telefono, direccion) 
                    VALUES (:nombre, :email, :password, :rol_id, :telefono, :direccion)";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':email' => $data['email'],
                ':password' => $data['password'],
                ':rol_id' => $data['rol_id'],
                ':telefono' => $data['telefono'] ?? null,
                ':direccion' => $data['direccion'] ?? null
            ]);
            
            return $this->db->getConnection()->lastInsertId();
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Registra la hora del último inicio de sesión de un usuario.
     * @param int $id_usuario El ID del usuario.
     * @return bool True en éxito, false en fallo.
     */
    public function registrarInicioSesion($id_usuario) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET ultimo_acceso = NOW() 
                    WHERE id_usuario = :id_usuario";
            return $this->query($sql, [':id_usuario' => $id_usuario]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Actualiza los datos de un usuario existente.
     * @param int $id El ID del usuario a actualizar.
     * @param array $data Un array asociativo con los campos y nuevos valores.
     * @param string|null $idField El nombre del campo ID (ignorado, se usa la clave primaria del modelo).
     * @return bool True en éxito, false en fallo.
     */
    public function update($id, $data, $idField = null) {
        // Aseguramos que se usa la clave primaria correcta para este modelo
        $idField = 'id_usuario';
        
        try {
            $setClauses = [];
            foreach ($data as $key => $value) {
                $setClauses[] = "`$key` = :$key";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE `$idField` = :idValue";
            $data['idValue'] = $id;

            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute($data);

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Elimina un usuario de la base de datos.
     * @param int $id El ID del usuario a eliminar.
     * @param string|null $idField El nombre del campo ID (ignorado, se usa la clave primaria del modelo).
     * @return bool True si se eliminó, false si no.
     */
    public function delete($id, $idField = null) {
        try {
            // Llama al delete del padre, especificando la clave primaria de este modelo.
            return parent::delete($id, 'id_usuario');
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Busca un usuario por su ID.
     * @param int $id El ID del usuario.
     * @param string|null $idField El nombre del campo ID (ignorado, se usa la clave primaria del modelo).
     * @return array|null Los datos del usuario o null si no se encuentra.
     */
    public function find($id, $idField = null) {
        try {
            $result = parent::find($id, 'id_usuario');
            return $result;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene todos los usuarios del sistema.
     * @return array Un array con todos los usuarios.
     */
    public function getAll() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY nombre";
            return $this->query($sql);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Busca usuarios por un término de búsqueda en nombre o email.
     * @param string $termino El término a buscar.
     * @return array Un array de usuarios que coinciden con la búsqueda.
     */
    public function buscar($termino) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE nombre LIKE :termino 
                    OR email LIKE :termino 
                    ORDER BY nombre";
            return $this->query($sql, [':termino' => "%{$termino}%"]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene una solicitud de reseteo de contraseña por su token.
     * @param string $token El token de reseteo.
     * @return array|null Los datos del reseteo o null si no es válido.
     */
    public function getPasswordReset($token) {
        try {
            $sql = "SELECT * FROM password_resets 
                    WHERE token = :token 
                    AND expires_at > NOW()";
            $result = $this->query($sql, [':token' => $token]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Crea un nuevo registro de reseteo de contraseña.
     * @param int $id_usuario ID del usuario.
     * @param string $token El token generado.
     * @param string $expiresAt La fecha de expiración.
     * @param string|null $createdAt La fecha de creación (opcional, se usará la fecha actual si no se proporciona).
     * @return bool True en éxito, false en fallo.
     */
    public function createPasswordReset($id_usuario, $token, $expiresAt, $createdAt = null) {
        try {
            if (!$createdAt) {
                $createdAt = date('Y-m-d H:i:s');
            }
            $sql = "INSERT INTO password_resets (user_id, token, expires_at, created_at) 
                    VALUES (:user_id, :token, :expires_at, :created_at)";
            return $this->query($sql, [
                ':user_id' => $id_usuario,
                ':token' => $token,
                ':expires_at' => $expiresAt,
                ':created_at' => $createdAt
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Elimina un token de reseteo de contraseña una vez usado.
     * @param string $token El token a eliminar.
     * @return bool True en éxito, false en fallo.
     */
    public function deletePasswordReset($token) {
        try {
            $sql = "DELETE FROM password_resets WHERE token = :token";
            return $this->query($sql, [':token' => $token]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Cuenta el número total de usuarios, opcionalmente filtrando por un término de búsqueda.
     * Usado para la paginación de DataTables.
     * @param string $search El término de búsqueda opcional.
     * @return int El número total de usuarios.
     */
    public function contarUsuarios($search = '') {
        try {
            $sql = "SELECT COUNT(u.id_usuario) as total 
                    FROM {$this->table} u 
                    JOIN roles r ON u.rol_id = r.id_rol";
            $params = [];

            if (!empty($search)) {
                $sql .= " WHERE LOWER(u.nombre) LIKE :search1 
                          OR LOWER(u.email) LIKE :search2 
                          OR LOWER(r.nombre) LIKE :search3";
                $searchValue = "%" . strtolower($search) . "%";
                $params[':search1'] = $searchValue;
                $params[':search2'] = $searchValue;
                $params[':search3'] = $searchValue;
            }

            $result = $this->query($sql, $params);
            return (int) $result[0]['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Obtiene una lista paginada de usuarios para mostrar en DataTables.
     * Une con la tabla de roles para obtener el nombre del rol.
     * @param int $start El registro inicial desde donde empezar a obtener.
     * @param int $length El número de registros a obtener.
     * @param string $search El término de búsqueda opcional.
     * @return array La lista de usuarios.
     */
    public function obtenerUsuariosPaginados($start, $length, $search) {
        try {
            $sql = "SELECT u.id_usuario, u.nombre, u.email, u.telefono, u.direccion, u.rol_id, r.nombre as rol_nombre, u.estado 
                    FROM {$this->table} u 
                    JOIN roles r ON u.rol_id = r.id_rol";
            
            $params = [];
            $searchValue = "%" . strtolower($search) . "%";

            if (!empty($search)) {
                $sql .= " WHERE LOWER(u.nombre) LIKE :search1 
                          OR LOWER(u.email) LIKE :search2 
                          OR LOWER(r.nombre) LIKE :search3";
                $params[':search1'] = $searchValue;
                $params[':search2'] = $searchValue;
                $params[':search3'] = $searchValue;
            }

            $sql .= " ORDER BY u.id_usuario DESC LIMIT " . (int)$start . ", " . (int)$length;
            
            return $this->query($sql, $params);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Verifica si un email ya existe en la base de datos, opcionalmente excluyendo un ID de usuario.
     * @param string $email El email a verificar.
     * @param int|null $id_actual El ID del usuario a excluir de la búsqueda (para actualizaciones).
     * @return bool True si el email existe, false si no.
     */
    public function emailExiste($email, $id_actual = null) {
        try {
            $sql = "SELECT id_usuario FROM {$this->table} WHERE email = :email";
            $params = [':email' => $email];

            if ($id_actual !== null) {
                $sql .= " AND id_usuario != :id_usuario";
                $params[':id_usuario'] = $id_actual;
            }

            $result = $this->query($sql, $params);
            return !empty($result);
        } catch (Exception $e) {
            return false; // Asumir que no existe en caso de error
        }
    }

    /**
     * Actualiza los datos de un usuario específico.
     * @param array $data Los datos del usuario, debe incluir 'id_usuario'.
     * @return bool True si la actualización fue exitosa, false si no.
     */
    public function actualizarUsuario($data) {
        try {
            $sql = "UPDATE {$this->table} SET nombre = :nombre, email = :email, telefono = :telefono, direccion = :direccion, rol_id = :rol_id, estado = :estado";
            $params = [
                ':nombre' => $data['nombre'],
                ':email' => $data['email'],
                ':telefono' => $data['telefono'],
                ':direccion' => $data['direccion'],
                ':rol_id' => $data['rol_id'],
                ':estado' => $data['estado'],
                ':id_usuario' => $data['id_usuario']
            ];

            if (!empty($data['password'])) {
                $sql .= ", password = :password";
                $params[':password'] = $data['password'];
            }

            $sql .= " WHERE id_usuario = :id_usuario";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $result = $stmt->execute($params);
            
            // Verificar si se afectó al menos una fila
            return $result && $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtiene un usuario por su ID.
     * @param int $id El ID del usuario.
     * @return array|null Los datos del usuario o null.
     */
    public function obtenerUsuarioPorId($id) {
        return $this->find($id);
    }

    /**
     * Cambia el estado (activo/inactivo) de un usuario.
     * @param int $id El ID del usuario.
     * @param string $estado El nuevo estado ('activo' or 'inactivo').
     * @return bool True si se actualizó, false si no.
     */
    public function cambiarEstado($id, $estado) {
        $estado_valido = $estado === 'activo' ? 'activo' : 'inactivo';
        return $this->update($id, ['estado' => $estado_valido], 'id_usuario');
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function findAll($conditions = []) {
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                $whereClauses[] = "`$key` = :$key";
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        return $this->query($sql, $conditions);
    }

    public function getActiveUsers()
    {
        return $this->findAll(['estado' => 'activo']);
    }

    public function getUsuarioById($id)
    {
        return $this->find($id, 'id_usuario');
    }

    public function getPermisosDeUsuario($usuario_id) {
        $sql = "SELECT p.nombre 
                FROM usuarios u
                JOIN roles_permisos rp ON u.rol_id = rp.rol_id
                JOIN permisos p ON rp.permiso_id = p.id_permiso
                WHERE u.id_usuario = ? AND p.estado = 'activo'";
        
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$usuario_id]);
            // Devolver un array plano con los códigos de los permisos
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (PDOException $e) {
            return []; // Devolver un array vacío en caso de error
        }
    }

    public function registrarUltimoAcceso($usuario_id) {
        // Este método actualiza la columna 'ultimo_acceso' en la tabla 'usuarios'
        $data = [
            'ultimo_acceso' => date('Y-m-d H:i:s')
        ];
        return $this->update($usuario_id, $data, 'id_usuario');
    }
} 