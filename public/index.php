<?php
session_start();
require_once '../config/database.php';
require_once '../core/Router.php';
require_once '../core/Controller.php';

// Autoload de clases
spl_autoload_register(function ($class) {
    $directories = ['controllers', 'models', 'core'];
    foreach ($directories as $directory) {
        $file = "../{$directory}/{$class}.php";
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Crear instancia del router
$router = new Router();

// Definir rutas
$router->addRoute('GET', '/', 'HomeController', 'index');
$router->addRoute('GET', '/login', 'AuthController', 'showLogin');
$router->addRoute('POST', '/login', 'AuthController', 'login');
$router->addRoute('GET', '/register', 'AuthController', 'showRegister');
$router->addRoute('POST', '/register', 'AuthController', 'register');
$router->addRoute('GET', '/dashboard', 'DashboardController', 'index');

// Despachar la ruta
$router->dispatch();
?> 