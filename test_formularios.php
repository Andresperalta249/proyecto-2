<?php
// Script de prueba específico para formularios y botones
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

echo "<h1>🧪 PRUEBAS DE FORMULARIOS Y BOTONES</h1>";
echo "<h2>Verificando funcionalidad completa del sistema</h2>";

// Función para hacer peticiones HTTP
function testEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'response' => $response,
        'error' => $error,
        'success' => $httpCode >= 200 && $httpCode < 300 && empty($error)
    ];
}

// Lista de pruebas específicas
$pruebas = [
    // 1. PÁGINAS PRINCIPALES
    ['url' => 'http://localhost/proyecto-2/dispositivos', 'name' => '📱 Página Dispositivos', 'type' => 'Página Principal'],
    ['url' => 'http://localhost/proyecto-2/mascotas', 'name' => '🐾 Página Mascotas', 'type' => 'Página Principal'],
    ['url' => 'http://localhost/proyecto-2/usuarios', 'name' => '👥 Página Usuarios', 'type' => 'Página Principal'],
    ['url' => 'http://localhost/proyecto-2/roles', 'name' => '🔐 Página Roles', 'type' => 'Página Principal'],
    
    // 2. ENDPOINTS AJAX - OBTENER DATOS
    ['url' => 'http://localhost/proyecto-2/dispositivos/obtenerDispositivos', 'method' => 'POST', 'name' => '📊 Obtener Dispositivos', 'type' => 'AJAX Data'],
    ['url' => 'http://localhost/proyecto-2/mascotas/obtenerMascotas', 'method' => 'POST', 'name' => '📊 Obtener Mascotas', 'type' => 'AJAX Data'],
    ['url' => 'http://localhost/proyecto-2/usuarios/obtenerUsuarios', 'method' => 'POST', 'name' => '📊 Obtener Usuarios', 'type' => 'AJAX Data'],
    ['url' => 'http://localhost/proyecto-2/roles/listar', 'method' => 'POST', 'name' => '📊 Listar Roles', 'type' => 'AJAX Data'],
    
    // 3. FORMULARIOS - CARGAR
    ['url' => 'http://localhost/proyecto-2/dispositivos/cargarFormulario', 'name' => '📝 Formulario Nuevo Dispositivo', 'type' => 'Formulario'],
    ['url' => 'http://localhost/proyecto-2/mascotas/cargarFormulario', 'name' => '📝 Formulario Nueva Mascota', 'type' => 'Formulario'],
    ['url' => 'http://localhost/proyecto-2/usuarios/cargarFormulario', 'name' => '📝 Formulario Nuevo Usuario', 'type' => 'Formulario'],
    
    // 4. FORMULARIOS - EDITAR (con ID)
    ['url' => 'http://localhost/proyecto-2/dispositivos/cargarFormulario/1', 'name' => '✏️ Formulario Editar Dispositivo', 'type' => 'Formulario Edición'],
    ['url' => 'http://localhost/proyecto-2/mascotas/cargarFormulario/1', 'name' => '✏️ Formulario Editar Mascota', 'type' => 'Formulario Edición'],
    ['url' => 'http://localhost/proyecto-2/usuarios/cargarFormulario/1', 'name' => '✏️ Formulario Editar Usuario', 'type' => 'Formulario Edición'],
];

// Agrupar por tipo
$grupos = [];
foreach ($pruebas as $prueba) {
    $tipo = $prueba['type'];
    if (!isset($grupos[$tipo])) {
        $grupos[$tipo] = [];
    }
    $grupos[$tipo][] = $prueba;
}

// Ejecutar pruebas por grupo
foreach ($grupos as $tipo => $pruebas_grupo) {
    echo "<h3>🔍 $tipo</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Endpoint</th><th>Método</th><th>Estado</th><th>Código HTTP</th><th>Respuesta</th>";
    echo "</tr>";
    
    foreach ($pruebas_grupo as $prueba) {
        $url = $prueba['url'];
        $method = $prueba['method'] ?? 'GET';
        $name = $prueba['name'];
        
        $result = testEndpoint($url, $method);
        
        $status = $result['success'] ? '✅ OK' : '❌ ERROR';
        $color = $result['success'] ? 'green' : 'red';
        $response_preview = substr($result['response'], 0, 100);
        if (strlen($result['response']) > 100) {
            $response_preview .= "...";
        }
        
        echo "<tr>";
        echo "<td><strong>$name</strong></td>";
        echo "<td>$method</td>";
        echo "<td style='color: $color; font-weight: bold;'>$status</td>";
        echo "<td>{$result['code']}</td>";
        echo "<td title='" . htmlspecialchars($result['response']) . "'>" . htmlspecialchars($response_preview) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Resumen final
echo "<h2>📊 RESUMEN DE PRUEBAS</h2>";

$total_pruebas = count($pruebas);
$pruebas_exitosas = 0;
$pruebas_fallidas = 0;

foreach ($pruebas as $prueba) {
    $result = testEndpoint($prueba['url'], $prueba['method'] ?? 'GET');
    if ($result['success']) {
        $pruebas_exitosas++;
    } else {
        $pruebas_fallidas++;
    }
}

echo "<div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>📈 Estadísticas</h3>";
echo "<p><strong>Total de pruebas:</strong> $total_pruebas</p>";
echo "<p style='color: green;'><strong>✅ Exitosas:</strong> $pruebas_exitosas</p>";
echo "<p style='color: red;'><strong>❌ Fallidas:</strong> $pruebas_fallidas</p>";
echo "<p><strong>Porcentaje de éxito:</strong> " . round(($pruebas_exitosas / $total_pruebas) * 100, 2) . "%</p>";
echo "</div>";

if ($pruebas_fallidas > 0) {
    echo "<h3>🔧 PRÓXIMOS PASOS PARA CORREGIR ERRORES</h3>";
    echo "<ol>";
    echo "<li>Revisar los logs de error en <code>logs/error.log</code></li>";
    echo "<li>Verificar que todos los controladores tengan los métodos necesarios</li>";
    echo "<li>Comprobar que los modelos estén funcionando correctamente</li>";
    echo "<li>Validar que las vistas se estén renderizando sin errores</li>";
    echo "<li>Probar manualmente los formularios y botones en el navegador</li>";
    echo "</ol>";
} else {
    echo "<h3>🎉 ¡TODAS LAS PRUEBAS PASARON!</h3>";
    echo "<p>El sistema está funcionando correctamente. Todos los formularios y botones deberían funcionar sin problemas.</p>";
}

echo "<h3>🧪 PRUEBAS MANUALES RECOMENDADAS</h3>";
echo "<ul>";
echo "<li>✅ Probar botón 'Nuevo' en cada módulo</li>";
echo "<li>✅ Probar botón 'Editar' en cada módulo</li>";
echo "<li>✅ Probar botón 'Eliminar' en cada módulo</li>";
echo "<li>✅ Probar botón 'Cambiar Estado' en cada módulo</li>";
echo "<li>✅ Probar formularios de creación y edición</li>";
echo "<li>✅ Probar validaciones de formularios</li>";
echo "<li>✅ Probar paginación en las tablas</li>";
echo "<li>✅ Probar búsqueda en las tablas</li>";
echo "</ul>";
?> 