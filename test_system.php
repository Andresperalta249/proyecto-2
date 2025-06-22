<?php
// Script de prueba para verificar que todos los endpoints funcionen
session_start();

// Simular un usuario logueado para las pruebas
$_SESSION['user_id'] = 1;
$_SESSION['user'] = [
    'id' => 1,
    'nombre' => 'Usuario Test',
    'email' => 'test@test.com',
    'rol_id' => 1
];
$_SESSION['rol_nombre'] = 'Administrador';

echo "<h1>🧪 PRUEBAS DEL SISTEMA</h1>";
echo "<h2>Verificando endpoints y funcionalidades</h2>";

// Función para hacer peticiones HTTP
function testEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'response' => $response,
        'success' => $httpCode >= 200 && $httpCode < 300
    ];
}

// Lista de endpoints a probar
$endpoints = [
    // Páginas principales
    ['url' => 'http://localhost/proyecto-2/dispositivos', 'name' => 'Página Dispositivos'],
    ['url' => 'http://localhost/proyecto-2/mascotas', 'name' => 'Página Mascotas'],
    ['url' => 'http://localhost/proyecto-2/usuarios', 'name' => 'Página Usuarios'],
    ['url' => 'http://localhost/proyecto-2/roles', 'name' => 'Página Roles'],
    
    // Endpoints AJAX
    ['url' => 'http://localhost/proyecto-2/dispositivos/obtenerDispositivos', 'method' => 'POST', 'name' => 'Obtener Dispositivos'],
    ['url' => 'http://localhost/proyecto-2/mascotas/obtenerMascotas', 'method' => 'POST', 'name' => 'Obtener Mascotas'],
    ['url' => 'http://localhost/proyecto-2/usuarios/obtenerUsuarios', 'method' => 'POST', 'name' => 'Obtener Usuarios'],
    ['url' => 'http://localhost/proyecto-2/roles/listar', 'method' => 'POST', 'name' => 'Listar Roles'],
    
    // Formularios
    ['url' => 'http://localhost/proyecto-2/dispositivos/cargarFormulario', 'name' => 'Formulario Dispositivos'],
    ['url' => 'http://localhost/proyecto-2/mascotas/cargarFormulario', 'name' => 'Formulario Mascotas'],
    ['url' => 'http://localhost/proyecto-2/usuarios/cargarFormulario', 'name' => 'Formulario Usuarios'],
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Endpoint</th><th>Método</th><th>Estado</th><th>Código HTTP</th><th>Respuesta</th></tr>";

foreach ($endpoints as $endpoint) {
    $url = $endpoint['url'];
    $method = $endpoint['method'] ?? 'GET';
    $name = $endpoint['name'];
    
    $result = testEndpoint($url, $method);
    
    $status = $result['success'] ? '✅ OK' : '❌ ERROR';
    $color = $result['success'] ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td><strong>$name</strong></td>";
    echo "<td>$method</td>";
    echo "<td style='color: $color;'>$status</td>";
    echo "<td>{$result['code']}</td>";
    echo "<td>" . substr($result['response'], 0, 100) . "...</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>📊 Resumen de Pruebas</h2>";
echo "<p>✅ = Funciona correctamente</p>";
echo "<p>❌ = Error o no funciona</p>";

echo "<h2>🔧 Próximos Pasos</h2>";
echo "<p>1. Revisar los endpoints que muestran ❌</p>";
echo "<p>2. Verificar los logs de error</p>";
echo "<p>3. Probar formularios y botones manualmente</p>";
?> 