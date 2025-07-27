<?php
class UsuarioModel extends Model {
    protected $table = 'usuarios';

    public function __construct() {
        parent::__construct();
    }

    public function getUsuarioByEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = :email";
            $result = $this->query($sql, [':email' => $email]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error en getUsuarioByEmail: " . $e->getMessage());
            return null;
        }
    }

    public function crearUsuario($data) {
        try {
            $sql = "INSERT INTO {$this->table} (nombre, email, password, rol_id) 
                    VALUES (:nombre, :email, :password, :rol_id)";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':email' => $data['email'],
                ':password' => $data['password'],
                ':rol_id' => $data['rol_id']
            ]);
            
            return $this->db->getConnection()->lastInsertId();
        } catch (Exception $e) {
            error_log("Error en crearUsuario: " . $e->getMessage());
            return false;
        }
    }

    public function registrarInicioSesion($id_usuario) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET ultimo_acceso = NOW() 
                    WHERE id_usuario = :id_usuario";
            return $this->query($sql, [':id_usuario' => $id_usuario]);
        } catch (Exception $e) {
            error_log("Error en registrarInicioSesion: " . $e->getMessage());
            return false;
        }
    }

    public function update($id_usuario, $data) {
        try {
            $sql = "UPDATE {$this->table} SET ";
            $params = [];
            $updates = [];

            foreach ($data as $key => $value) {
                $updates[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }

            $sql .= implode(', ', $updates);
            $sql .= " WHERE id_usuario = :id_usuario";
            $params[':id_usuario'] = $id_usuario;

            return $this->query($sql, $params);
        } catch (Exception $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id_usuario) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id_usuario = :id_usuario";
            return $this->query($sql, [':id_usuario' => $id_usuario]);
        } catch (Exception $e) {
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }

    public function find($id_usuario) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id_usuario = :id_usuario";
            $result = $this->query($sql, [':id_usuario' => $id_usuario]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error en find: " . $e->getMessage());
            return null;
        }
    }

    public function getAll() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY nombre";
            return $this->query($sql);
        } catch (Exception $e) {
            error_log("Error en getAll: " . $e->getMessage());
            return [];
        }
    }

    public function buscar($termino) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE nombre LIKE :termino 
                    OR email LIKE :termino 
                    ORDER BY nombre";
            return $this->query($sql, [':termino' => "%{$termino}%"]);
        } catch (Exception $e) {
            error_log("Error en buscar: " . $e->getMessage());
            return [];
        }
    }

    public function getPasswordReset($token) {
        try {
            $sql = "SELECT * FROM password_resets 
                    WHERE token = :token 
                    AND expires_at > NOW()";
            $result = $this->query($sql, [':token' => $token]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error en getPasswordReset: " . $e->getMessage());
            return null;
        }
    }

    public function createPasswordReset($id_usuario, $token, $expiresAt) {
        try {
            $sql = "INSERT INTO password_resets (user_id, token, expires_at) 
                    VALUES (:user_id, :token, :expires_at)";
            return $this->query($sql, [
                ':user_id' => $id_usuario,
                ':token' => $token,
                ':expires_at' => $expiresAt
            ]);
        } catch (Exception $e) {
            error_log("Error en createPasswordReset: " . $e->getMessage());
            return false;
        }
    }

    public function deletePasswordReset($token) {
        try {
            $sql = "DELETE FROM password_resets WHERE token = :token";
            return $this->query($sql, [':token' => $token]);
        } catch (Exception $e) {
            error_log("Error en deletePasswordReset: " . $e->getMessage());
            return false;
        }
    }
} 