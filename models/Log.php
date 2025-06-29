<?php
/**
 * Modelo Log
 * ----------
 * Modelo para registrar y consultar logs del sistema.
 *
 * Hereda de: Model (core/Model.php)
 *
 * Atributos:
 *   - table: Nombre de la tabla ('logs')
 *
 * Métodos principales:
 *   - registrar($mensaje, $tipo): Guarda un nuevo log.
 *   - obtenerTodos(): Obtiene todos los logs.
 *   - buscarPorTipo($tipo): Busca logs por tipo.
 *
 * Relación:
 *   - Hereda de Model, por lo que puede usar todos los métodos genéricos de acceso a datos.
 *   - Es usado por controladores para registrar eventos y errores.
 *
 * Ejemplo de uso:
 *   $logModel->registrar('Usuario creado', 'info');
 */
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