<?php
/**
 * Modelo DispositivoModel
 * ----------------------
 * Modelo para acceder y manipular la tabla de dispositivos en la base de datos.
 *
 * Hereda de: Model (core/Model.php)
 *
 * Atributos:
 *   - table: Nombre de la tabla ('dispositivos')
 *   - primaryKey: Clave primaria ('id_dispositivo')
 *
 * Métodos principales:
 *   - getDispositivoById($id): Obtiene un dispositivo y su mascota asociada.
 *   - getDispositivosByUsuario($usuarioId): Dispositivos de un usuario.
 *   - Métodos CRUD heredados: create, update, delete, find.
 *   - Métodos personalizados para consultas específicas (mascotas, ubicaciones, etc.).
 *
 * Relación:
 *   - Hereda de Model, por lo que puede usar todos los métodos genéricos de acceso a datos.
 *   - Es usado por DispositivosController para gestionar dispositivos.
 *
 * Ejemplo de uso:
 *   $dispositivo = $dispositivoModel->find($id);
 *   $dispositivoModel->update($id, $data);
 */
require_once __DIR__ . '/../core/Model.php';
class DispositivoModel extends Model {
    protected $table = 'dispositivos';
    protected $primaryKey = 'id_dispositivo';

    public function __construct() {
        parent::__construct();
    }

    public function getDispositivoById($id) {
        $sql = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado,
                        d.usuario_id, d.mascota_id,
                        m.nombre as nombre_mascota, m.especie as especie_mascota 
                 FROM {$this->table} d 
                 LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                 WHERE d.id_dispositivo = :id";
        $result = $this->query($sql, [':id' => $id]);
        return $result ? $result[0] : null;
    }

    public function getDispositivosByUsuario($usuarioId) {
        $sql = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado,
                        d.usuario_id, d.mascota_id,
                        m.nombre as nombre_mascota, m.especie as especie_mascota 
                 FROM {$this->table} d 
                 LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                 WHERE d.usuario_id = :usuario_id";
        return $this->query($sql, [':usuario_id' => $usuarioId]);
    }

    public function getUltimaUbicacion($dispositivoId) {
        $sql = "SELECT latitud, longitud, fecha 
                 FROM datos_sensores 
                 WHERE dispositivo_id = :dispositivo_id 
                 ORDER BY fecha DESC 
                 LIMIT 1";
        $result = $this->query($sql, [':dispositivo_id' => $dispositivoId]);
        return $result ? $result[0] : null;
    }

    public function getUltimosDatos($dispositivoId, $horas = 24) {
        $sql = "SELECT temperatura, ritmo_cardiaco, bateria, latitud, longitud, fecha 
                 FROM datos_sensores 
                 WHERE dispositivo_id = :dispositivo_id 
                 AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR) 
                 ORDER BY fecha DESC";
        return $this->query($sql, [':dispositivo_id' => $dispositivoId, ':horas' => $horas]);
    }

    public function getRuta($dispositivoId, $horas = 24) {
        $sql = "SELECT latitud, longitud, fecha 
                 FROM datos_sensores 
                 WHERE dispositivo_id = :dispositivo_id 
                 AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR) 
                 ORDER BY fecha ASC";
        return $this->query($sql, [':dispositivo_id' => $dispositivoId, ':horas' => $horas]);
    }

    public function getDispositivosWithMascotas($usuarioId) {
        $sql = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado,
                        d.usuario_id, d.mascota_id,
                        u.nombre as usuario_nombre, u.email as usuario_email,
                        m.nombre as nombre_mascota, m.especie as especie_mascota 
                 FROM {$this->table} d 
                 LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario 
                 LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                 WHERE d.usuario_id = :usuario_id";
        return $this->query($sql, [':usuario_id' => $usuarioId]);
    }

    public function getTodosDispositivosConMascotas() {
        $sql = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado,
                        d.usuario_id, d.mascota_id,
                        u.nombre as usuario_nombre, u.email as usuario_email,
                        m.nombre as nombre_mascota, m.especie as especie_mascota 
                 FROM {$this->table} d 
                 LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario 
                 LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                 ORDER BY d.id_dispositivo DESC";
        return $this->query($sql);
    }

    public function filtrarDispositivos($filtros, $usuarioId = null) {
        $sql = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado,
                        d.usuario_id, d.mascota_id,
                        u.nombre as usuario_nombre, u.email as usuario_email,
                        m.nombre as nombre_mascota, m.especie as especie_mascota 
                 FROM {$this->table} d 
                 LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario 
                 LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                 WHERE 1=1";
        $params = [];
        if ($usuarioId) {
            $sql .= " AND d.usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (d.nombre LIKE :busqueda OR d.mac LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND d.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND d.usuario_id = :filtro_usuario_id";
            $params[':filtro_usuario_id'] = $filtros['usuario_id'];
        }
        if (!empty($filtros['mascota_id'])) {
            $sql .= " AND d.mascota_id = :mascota_id";
            $params[':mascota_id'] = $filtros['mascota_id'];
        }
        $sql .= " ORDER BY d.id_dispositivo DESC";
        return $this->query($sql, $params);
    }

    public function existeMac($mac, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE mac = :mac";
        $params = [':mac' => $mac];
        if ($excludeId) {
            $sql .= " AND id_dispositivo != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        $result = $this->query($sql, $params);
        return $result ? $result[0]['COUNT(*)'] > 0 : false;
    }

    public function getDispositivosDisponibles() {
        $sql = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado 
                 FROM {$this->table} d 
                 WHERE d.mascota_id IS NULL AND d.estado = 'activo'";
        return $this->query($sql);
    }

    public function getDispositivosByMascota($mascotaId) {
        $sql = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado 
                 FROM {$this->table} d 
                 WHERE d.mascota_id = :mascota_id";
        return $this->query($sql, [':mascota_id' => $mascotaId]);
    }

    public function getTotalDispositivos() {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $result = $this->query($sql);
        return $result ? $result[0]['COUNT(*)'] : 0;
    }

    public function getTotalConectados() {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE estado = 'activo'";
        $result = $this->query($sql);
        return $result ? $result[0]['COUNT(*)'] : 0;
    }

    public function desasociarDeMascota($mascotaId) {
        $sql = "UPDATE {$this->table} SET mascota_id = NULL WHERE mascota_id = :mascota_id";
        return $this->query($sql, [':mascota_id' => $mascotaId]);
    }

    // Métodos para compatibilidad con APIController, si los necesitas puedes mantenerlos
    public function getAllWithDetails() {
        $sql = "SELECT d.id_dispositivo as id, d.nombre, d.mac, d.estado,
                        d.usuario_id, d.mascota_id,
                        u.nombre as dueño,
                        CASE WHEN d.mascota_id IS NULL THEN 1 ELSE 0 END as disponible,
                        m.nombre as mascota
                 FROM {$this->table} d 
                 LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario 
                 LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                 ORDER BY d.id_dispositivo DESC";
        return $this->query($sql);
    }

    public function getById($id) {
        return $this->getDispositivoById($id);
    }
} 