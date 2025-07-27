<?php
class DatosSensorModel {
    private $db;
    private $table = 'datos_sensores';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDatosPorDispositivo($dispositivoId, $horas = 24) {
        try {
            $query = "SELECT * FROM {$this->table} 
                     WHERE dispositivo_id = :dispositivo_id 
                     AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR) 
                     ORDER BY fecha DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->bindParam(':horas', $horas, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DatosSensorModel::getDatosPorDispositivo: " . $e->getMessage());
            return [];
        }
    }

    public function getUltimosDatos($dispositivoId, $limite = 10) {
        try {
            $query = "SELECT * FROM {$this->table} 
                     WHERE dispositivo_id = :dispositivo_id 
                     ORDER BY fecha DESC 
                     LIMIT :limite";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DatosSensorModel::getUltimosDatos: " . $e->getMessage());
            return [];
        }
    }

    public function insertarDato($datos) {
        try {
            $query = "INSERT INTO {$this->table} 
                     (dispositivo_id, temperatura, ritmo_cardiaco, bateria, latitud, longitud, fecha) 
                     VALUES 
                     (:dispositivo_id, :temperatura, :ritmo_cardiaco, :bateria, :latitud, :longitud, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dispositivo_id', $datos['dispositivo_id'], PDO::PARAM_INT);
            $stmt->bindParam(':temperatura', $datos['temperatura'], PDO::PARAM_STR);
            $stmt->bindParam(':ritmo_cardiaco', $datos['ritmo_cardiaco'], PDO::PARAM_INT);
            $stmt->bindParam(':bateria', $datos['bateria'], PDO::PARAM_INT);
            $stmt->bindParam(':latitud', $datos['latitud'], PDO::PARAM_STR);
            $stmt->bindParam(':longitud', $datos['longitud'], PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en DatosSensorModel::insertarDato: " . $e->getMessage());
            return false;
        }
    }

    public function getDatosParaGrafica($dispositivoId, $horas = 24) {
        try {
            $query = "SELECT temperatura, ritmo_cardiaco, bateria, fecha 
                     FROM {$this->table} 
                     WHERE dispositivo_id = :dispositivo_id 
                     AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR) 
                     ORDER BY fecha ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->bindParam(':horas', $horas, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DatosSensorModel::getDatosParaGrafica: " . $e->getMessage());
            return [];
        }
    }
} 