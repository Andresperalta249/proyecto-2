<?php
class View {
    private $title;
    private $data = [];
    private $layout = 'main';

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setData($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Alias para setData para compatibilidad
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value) {
        $this->setData($key, $value);
    }

    public function setLayout($layout) {
        $this->layout = $layout;
    }

    public function render($view, $data = [], $return = false) {
        // Combinar datos globales con datos específicos de la vista
        $allData = array_merge($this->data, $data);
        extract($allData);

        ob_start();
        
        $viewPath = 'views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            ob_end_clean();
            throw new Exception("Vista no encontrada: {$viewPath}");
        }
        
        $content = ob_get_clean();

        if ($return) {
            return $content;
        }

        // Cargar el layout (solo si no se retorna el contenido)
        $layoutPath = 'views/layouts/' . $this->layout . '.php';
        if (file_exists($layoutPath)) {
            // Pasar el contenido a la variable $content para el layout
            $GLOBALS['content'] = $content;
            require $layoutPath;
        } else {
            throw new Exception("Layout no encontrado: {$layoutPath}");
        }
    }

    public function partial($view, $data = []) {
        extract(array_merge($this->data, $data));

        // Cargar la vista parcial
        $viewPath = 'views/partials/' . $view . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new Exception("Vista parcial no encontrada: {$viewPath}");
        }
    }

    public function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }
} 