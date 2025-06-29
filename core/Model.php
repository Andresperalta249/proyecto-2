<?php
/**
 * Clase base Model
 * ----------------
 * Esta clase es la base para todos los modelos del sistema. Permite realizar operaciones CRUD (crear, leer, actualizar, eliminar) sobre la base de datos de forma genérica.
 *
 * Atributos:
 *   - db: Conexión a la base de datos (PDO)
 *   - table: Nombre de la tabla asociada al modelo
 *   - lastError: Último error ocurrido (string)
 *   - primaryKey: Clave primaria de la tabla (por defecto 'id')
 *
 * Métodos principales:
 *   - query($sql, $params): Ejecuta una consulta SQL y devuelve los resultados
 *   - find($id, $idField): Busca un registro por su ID
 *   - create($data): Inserta un nuevo registro
 *   - update($id, $data, $idField): Actualiza un registro existente
 *   - delete($id, $idField): Elimina un registro
 *   - getLastError(): Devuelve el último error
 *
 * Relación:
 *   - Es heredada por todos los modelos de entidades (UsuarioModel, Mascota, DispositivoModel, etc.)
 */
class Model {
    protected $db;
    protected $table;
    protected $lastError;
    protected $primaryKey = 'id';

    public function __construct() {
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function query($sql, $params = []) {
        try {
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare($sql);
            if (!$stmt) {
                $errorInfo = $connection->errorInfo();
                throw new PDOException("Error al preparar statement: " . $errorInfo[2]);
            }
            $executeResult = $stmt->execute($params);
            if (!$executeResult) {
                $errorInfo = $stmt->errorInfo();
                throw new PDOException("Error al ejecutar statement: " . $errorInfo[2]);
            }
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log('Error en Model::query: ' . $e->getMessage());
            return [];
        }
    }

    public function find($id, $idField = null) {
        $idField = $idField ?? $this->primaryKey;
        $sql = "SELECT * FROM {$this->table} WHERE `$idField` = :idValue";
        $params = [":idValue" => $id];
        try {
            $result = $this->query($sql, $params);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function create($data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $stmt = $this->db->getConnection()->prepare($sql);
            if (!$stmt) {
                $error = $this->db->getConnection()->errorInfo();
                throw new PDOException("Error al preparar la consulta: " . $error[2]);
            }
            $result = $stmt->execute($data);
            if (!$result) {
                $error = $stmt->errorInfo();
                throw new PDOException("Error al ejecutar la consulta: " . $error[2]);
            }
            return $this->db->getConnection()->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            throw $e;
        }
    }

    public function update($id, $data, $idField = null) {
        $setClauses = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "`$key` = :$key";
        }
        $idField = $idField ?? $this->primaryKey;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE `$idField` = :idValue";
        $params = $data;
        $params['idValue'] = $id;
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function delete($id, $idField = null) {
        $idField = $idField ?? $this->primaryKey;
        $sql = "DELETE FROM {$this->table} WHERE `$idField` = :idValue";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([":idValue" => $id]);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function getLastError() {
        return $this->lastError;
    }
} 