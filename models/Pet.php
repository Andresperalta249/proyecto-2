<?php
require_once __DIR__ . '/../config/database.php';

class Pet {
    private $conn;
    private $table = 'mascotas';

    public function __construct($database) {
        $this->conn = $database->getConnection();
    }

    public function getUserPetsCount($userId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE usuario_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return intval($result['count']);
        } catch (PDOException $e) {
            error_log("Error en Pet::getUserPetsCount: " . $e->getMessage());
            return 0;
        }
    }

    public function getAllByUser($userId) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE usuario_id = ? ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Pet::getAllByUser: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Pet::getById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cuenta el número de mascotas activas de un usuario
     * @param int $userId ID del usuario
     * @return int Número de mascotas
     */
    public function countPetsByUser($userId) {
        try {
            $sql = "SELECT COUNT(*) FROM mascotas WHERE usuario_id = ? AND estado = 'activo'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Pet::countPetsByUser: " . $e->getMessage());
            return 0;
        }
    }
} 