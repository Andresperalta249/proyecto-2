<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        error_log("[DEBUG Database::__construct] Iniciando conexión a la base de datos");
        error_log("[DEBUG Database::__construct] Host: " . DB_HOST);
        error_log("[DEBUG Database::__construct] Database: " . DB_NAME);
        error_log("[DEBUG Database::__construct] User: " . DB_USER);
        
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
            error_log("[DEBUG Database::__construct] DSN: $dsn");
            
            $this->connection = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            error_log("[DEBUG Database::__construct] Conexión a la base de datos establecida correctamente");
            
            // Probar la conexión
            $testQuery = $this->connection->query("SELECT 1");
            $testResult = $testQuery->fetch();
            error_log("[DEBUG Database::__construct] Prueba de conexión exitosa: " . print_r($testResult, true));
        } catch (PDOException $e) {
            error_log("[ERROR Database::__construct] Error de conexión a la base de datos: " . $e->getMessage());
            error_log("[ERROR Database::__construct] Código de error: " . $e->getCode());
            error_log("[ERROR Database::__construct] Stack trace: " . $e->getTraceAsString());
            throw new Exception("Error de conexión a la base de datos");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en la consulta SQL: " . $e->getMessage());
            throw new Exception("Error en la consulta SQL");
        }
    }

    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    // Prevenir la clonación del objeto
    private function __clone() {}

    // Prevenir la deserialización del objeto
    public function __wakeup() {
        throw new Exception("No se puede deserializar una instancia de singleton");
    }
} 