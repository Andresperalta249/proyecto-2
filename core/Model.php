<?php

class Model {
    protected $db;
    protected $table;
    protected $lastError;

    public function __construct() {
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            error_log("Error al inicializar Model: " . $e->getMessage());
            throw $e;
        }
    }

    protected function query($sql, $params = []) {
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en la consulta SQL: " . $e->getMessage() . "\nSQL: " . $sql . "\nParams: " . print_r($params, true));
            throw $e;
        }
    }

    public function find($id) {
        $idField = ($this->table === 'mascotas') ? 'id_mascota' : (($this->table === 'dispositivos') ? 'id_dispositivo' : 'id');
        $sql = "SELECT * FROM {$this->table} WHERE $idField = :$idField";
        $result = $this->query($sql, [":$idField" => $id])->fetch();
        return $result ?: null;
    }

    public function create($data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            
            error_log("SQL de inserción: " . $sql);
            error_log("Datos a insertar: " . print_r($data, true));
            
            $stmt = $this->db->getConnection()->prepare($sql);
            if (!$stmt) {
                $error = $this->db->getConnection()->errorInfo();
                error_log("Error al preparar la consulta: " . print_r($error, true));
                throw new PDOException("Error al preparar la consulta: " . $error[2]);
            }
            
            $result = $stmt->execute($data);
            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("Error al ejecutar la consulta: " . print_r($error, true));
                throw new PDOException("Error al ejecutar la consulta: " . $error[2]);
            }
            
            return $this->db->getConnection()->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error al crear registro: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public function update($id, $data) {
        $setClauses = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "$key = :$key";
        }
        $idField = ($this->table === 'mascotas') ? 'id_mascota' : (($this->table === 'dispositivos') ? 'id_dispositivo' : 'id');
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE $idField = :$idField";
        $data[$idField] = $id;
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Error al actualizar registro: " . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id) {
        $idField = ($this->table === 'mascotas') ? 'id_mascota' : (($this->table === 'dispositivos') ? 'id_dispositivo' : 'id');
        $sql = "DELETE FROM {$this->table} WHERE $idField = :$idField";
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([":$idField" => $id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar registro: " . $e->getMessage());
            throw $e;
        }
    }

    public function getLastError() {
        return $this->lastError;
    }
} 