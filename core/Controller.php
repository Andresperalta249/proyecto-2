<?php
class Controller {
    protected function view($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }

    protected function model($model) {
        require_once "models/{$model}.php";
        return new $model();
    }

    protected function redirect($url) {
        header("Location: {$url}");
        exit();
    }
}
?> 