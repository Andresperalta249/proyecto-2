<?php
class Controller {
    protected $db;
    protected $view;
    protected $model;

    public function __construct() {
        // Cargar la configuración primero
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(__DIR__));
        }
        
        // Cargar la configuración
        if (!defined('DB_HOST')) {
            require_once ROOT_PATH . '/config/config.php';
        }
        
        // Cargar la clase Database
        require_once ROOT_PATH . '/core/Database.php';
        
        // Inicializar la base de datos
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            error_log("Error al inicializar la base de datos: " . $e->getMessage());
            throw new Exception("Error al inicializar la base de datos");
        }

        // Inicializar la vista
        require_once ROOT_PATH . '/core/View.php';
        $this->view = new View();
    }

    protected function loadModel($model) {
        $modelFile = ROOT_PATH . '/models/' . $model . '.php';
        if (!file_exists($modelFile)) {
            error_log("Modelo no encontrado: " . $modelFile);
            throw new Exception("Modelo no encontrado: " . $model);
        }
        require_once $modelFile;
        return new $model();
    }

    protected function render($view, $data = []) {
        $viewFile = ROOT_PATH . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            error_log("Vista no encontrada: " . $viewFile);
            throw new Exception("Vista no encontrada: " . $view);
        }
        extract($data);
        ob_start();
        require_once $viewFile;
        return ob_get_clean();
    }

    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    protected function validateRequest($required = []) {
        // Si hay datos en $_POST, úsalos
        $data = $_POST;
        // Si no hay datos en $_POST, intenta obtenerlos del cuerpo JSON
        if (empty($data)) {
            $data = json_decode(file_get_contents('php://input'), true);
        }
        if (!$data) {
            $this->jsonResponse(['error' => 'Datos inválidos'], 400);
        }

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->jsonResponse(['error' => "El campo {$field} es requerido"], 400);
            }
        }
        return $data;
    }

    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(strip_tags($data));
    }

    protected function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }
}
?> 