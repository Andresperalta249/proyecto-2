<?php
// test_editar_rol.php

// Iniciar sesión y simular un usuario autenticado con permisos
session_start();
$_SESSION['user_id'] = 1; 
$_SESSION['user'] = ['rol_id' => 3]; // Super Administrador
$_SESSION['permissions'] = ['editar_roles', 'ver_roles']; // Permisos necesarios

// Cargar archivos necesarios para que el entorno de la app funcione
require_once 'core/Database.php';
require_once 'config/config.php';
require_once 'core/Controller.php';

echo "🧪 Iniciando prueba para editar rol...\n\n";

// URL a probar
$url = 'http://localhost/proyecto-2/roles/editar/8';
echo "URL de prueba: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Pasar la cookie de sesión para que el servidor nos reconozca como logueados
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Código de estado HTTP recibido: $httpCode\n";

if ($error) {
    echo "❌ Error de cURL: $error\n";
}

if ($httpCode == 200) {
    echo "\n✅ ¡Prueba exitosa! El servidor respondió con OK (200).\n";
    echo "El formulario para editar roles ahora carga correctamente.\n";
    echo "\nRespuesta del servidor (primeros 200 caracteres):\n";
    echo "--------------------------------------------------\n";
    echo substr(strip_tags($response), 0, 200) . "...\n";
    echo "--------------------------------------------------\n";
} else {
    echo "\n❌ ¡La prueba falló! El servidor respondió con el código de error $httpCode.\n";
    echo "Esto indica que el problema persiste.\n";
} 