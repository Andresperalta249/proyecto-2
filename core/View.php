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

    public function setLayout($layout) {
        $this->layout = $layout;
    }

    public function render($view) {
        // Extraer los datos para que estén disponibles en la vista
        extract($this->data);

        // Iniciar el buffer de salida
        ob_start();

        // Cargar la vista
        $viewPath = 'views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new Exception("Vista no encontrada: {$viewPath}");
        }

        // Obtener el contenido del buffer
        $content = ob_get_clean();

        // Cargar el layout solo si está definido
        if ($this->layout) {
            $layoutPath = 'views/layouts/' . $this->layout . '.php';
            if (file_exists($layoutPath)) {
                require $layoutPath;
            } else {
                throw new Exception("Layout no encontrado: {$layoutPath}");
            }
        } else {
            // Si no hay layout, mostrar solo el contenido
            echo $content;
        }
    }

    public function partial($view, $data = []) {
        // Extraer los datos adicionales
        extract($data);

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