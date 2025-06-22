<?php
require_once __DIR__ . '/../core/Model.php';

class Log extends Model {
    protected $table = 'ultimo_acceso';

    public function __construct() {
        parent::__construct();
    }

    public function crearLog($usuario_id, $accion) {
        $data = [
            'id_usuario' => $usuario_id,
            'accion' => $accion,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A'
        ];

        try {
            $this->create($data);
        } catch (Exception $e) {
            error_log("Error en crearLog: " . $e->getMessage());
        }
    }
}
?>