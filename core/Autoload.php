<?php
spl_autoload_register(function ($class) {
    // Directorios donde buscar las clases
    $directories = [
        ROOT_PATH . '/controllers/',
        ROOT_PATH . '/models/',
        ROOT_PATH . '/core/',
        ROOT_PATH . '/config/'
    ];
    
    // Buscar la clase en cada directorio
    foreach ($directories as $directory) {
        // Convertir el nombre de la clase a formato de archivo
        $file = $directory . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Si no se encuentra la clase, registrar el error
    error_log("Clase no encontrada: " . $class);
});
?> 