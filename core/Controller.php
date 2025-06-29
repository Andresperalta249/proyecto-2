<?php
class Controller {
    protected $db;
    protected $view;

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

    protected function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    /**
     * Sanitiza una entrada de texto para prevenir inyección de código
     * @param string $input El texto a sanitizar
     * @return string El texto sanitizado
     */
    protected function sanitizeInput($input) {
        if (is_string($input)) {
            // Eliminar espacios en blanco al inicio y final
            $input = trim($input);
            // Convertir caracteres especiales en entidades HTML
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            // Eliminar caracteres de control
            $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);
        }
        return $input;
    }

    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Valida y sanitiza los datos de la petición
     * @param array $requiredFields Campos requeridos
     * @return array|false Datos validados o false si hay error
     */
    protected function validateRequest($requiredFields = []) {
        $data = [];
        $errors = [];

        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $errors[] = "El campo {$field} es requerido";
            } else {
                $data[$field] = $this->sanitizeInput($_POST[$field]);
            }
        }

        if (!empty($errors)) {
            $this->jsonResponse([
                'success' => false,
                'error' => implode(', ', $errors)
            ], 400);
            return false;
        }

        return $data;
    }
}
?> 