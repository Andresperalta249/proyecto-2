<?php
class Database {
    private $host = "localhost";
    private $db_name = "mascotas_iot";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            error_log("[TEST-DB] Intentando conectar a la base de datos");
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
            error_log("[TEST-DB] Conexión exitosa");
        } catch(PDOException $exception) {
            error_log("[ERROR-DB] Error de conexión: " . $exception->getMessage());
            error_log("[ERROR-DB] Stack trace: " . $exception->getTraceAsString());
            throw new Exception("Error de conexión a la base de datos: " . $exception->getMessage());
        }

        return $this->conn;
    }

    public function query($sql, $params = []) {
        try {
            error_log("[TEST-DB] Ejecutando query: " . $sql);
            error_log("[TEST-DB] Parámetros: " . print_r($params, true));
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            error_log("[TEST-DB] Query ejecutado exitosamente");
            return $stmt;
        } catch(PDOException $e) {
            error_log("[ERROR-DB] Error en query: " . $e->getMessage());
            error_log("[ERROR-DB] SQL: " . $sql);
            error_log("[ERROR-DB] Parámetros: " . print_r($params, true));
            throw $e;
        }
    }
}
?> 