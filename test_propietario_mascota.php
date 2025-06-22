<?php
// Test de funcionalidad: Campo propietario en formulario de mascotas
require_once __DIR__ . '/config/config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Test - Campo Propietario en Mascotas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .test-item { margin: 15px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        h1 { color: #333; text-align: center; }
        h2 { color: #495057; border-bottom: 2px solid #dee2e6; padding-bottom: 5px; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        .btn { padding: 8px 15px; margin: 5px; text-decoration: none; border-radius: 4px; display: inline-block; color: white; }
        .btn-primary { background: #007bff; }
        .checkmark { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧪 Test - Campo Propietario en Mascotas</h1>
        <p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>
        
        <h2>📋 Funcionalidad Implementada</h2>
        <div class='success'>
            <strong>✅ NUEVA FUNCIONALIDAD IMPLEMENTADA</strong><br>
            Cuando un usuario tiene los permisos <code>ver_todas_mascotas</code> Y <code>crear_mascotas</code> 
            juntos, el formulario de crear/editar mascota mostrará el campo propietario como <strong>obligatorio</strong>.
        </div>

        <h2>🔧 Cambios Realizados</h2>
        
        <div class='test-item'>
            <h4><span class='checkmark'>✅</span> 1. Controlador MascotasController.php</h4>
            <ul>
                <li><strong>Método cargarFormularioAction:</strong> Cambió la lógica de <code>\$esAdmin</code> a <code>\$puedeAsignarPropietario</code></li>
                <li><strong>Condición:</strong> <code>verificarPermiso('ver_todas_mascotas') && verificarPermiso('crear_mascotas')</code></li>
                <li><strong>Método guardarAction:</strong> Validación obligatoria del campo propietario cuando se tienen ambos permisos</li>
            </ul>
        </div>

        <div class='test-item'>
            <h4><span class='checkmark'>✅</span> 2. Vista form.php</h4>
            <ul>
                <li><strong>Variable cambiada:</strong> <code>\$esAdmin</code> → <code>\$puedeAsignarPropietario</code></li>
                <li><strong>Campo obligatorio:</strong> Añadido asterisco rojo (*) en la etiqueta</li>
                <li><strong>Texto de ayuda:</strong> Mensaje explicativo sobre la obligatoriedad del campo</li>
            </ul>
        </div>

        <div class='test-item'>
            <h4><span class='checkmark'>✅</span> 3. JavaScript mascotas.js</h4>
            <ul>
                <li><strong>Validación frontend:</strong> Verifica que el campo propietario esté seleccionado antes de enviar</li>
                <li><strong>Mensaje de error:</strong> Alerta específica si no se selecciona propietario</li>
                <li><strong>Focus automático:</strong> Enfoca el campo para mejor UX</li>
            </ul>
        </div>

        <h2>🎯 Lógica de Permisos</h2>
        <div class='info'>
            <h4>Escenarios de Uso:</h4>
            <ul>
                <li><strong>Usuario con ambos permisos</strong> (<code>ver_todas_mascotas</code> + <code>crear_mascotas</code>):
                    <br>→ <strong>Ve el campo propietario como OBLIGATORIO</strong></li>
                <li><strong>Usuario solo con</strong> <code>crear_mascotas</code>:
                    <br>→ <strong>NO ve el campo propietario</strong> (se asigna automáticamente a él)</li>
                <li><strong>Usuario sin permisos suficientes:</strong>
                    <br>→ <strong>NO puede crear mascotas</strong></li>
            </ul>
        </div>

        <h2>🧪 Cómo Probar</h2>
        
        <div class='test-item'>
            <h4>1. Crear usuario con ambos permisos</h4>
            <p>Asigna a un usuario los permisos:</p>
            <ul>
                <li><code>ver_todas_mascotas</code></li>
                <li><code>crear_mascotas</code></li>
            </ul>
        </div>

        <div class='test-item'>
            <h4>2. Iniciar sesión con ese usuario</h4>
            <p>Ve a <a href='/proyecto-2/mascotas' class='btn btn-primary' target='_blank'>Módulo Mascotas</a></p>
        </div>

        <div class='test-item'>
            <h4>3. Hacer clic en \"Nueva Mascota\"</h4>
            <p>Debería aparecer:</p>
            <ul>
                <li>✅ Campo \"Propietario\" con asterisco rojo (*)</li>
                <li>✅ Texto \"Campo obligatorio: debe asignar un propietario a la mascota\"</li>
                <li>✅ Lista desplegable con todos los usuarios</li>
            </ul>
        </div>

        <div class='test-item'>
            <h4>4. Intentar guardar sin seleccionar propietario</h4>
            <p>Debería mostrar:</p>
            <ul>
                <li>✅ Alerta: \"Debe seleccionar un propietario para la mascota\"</li>
                <li>✅ El cursor se enfoca en el campo propietario</li>
            </ul>
        </div>

        <div class='test-item'>
            <h4>5. Seleccionar propietario y guardar</h4>
            <p>Debería:</p>
            <ul>
                <li>✅ Guardar correctamente</li>
                <li>✅ Asignar la mascota al propietario seleccionado</li>
                <li>✅ Mostrar la mascota en la tabla</li>
            </ul>
        </div>

        <h2>⚠️ Casos de Prueba Adicionales</h2>
        
        <div class='warning'>
            <h4>Probar también con usuarios que NO tienen ambos permisos:</h4>
            <ul>
                <li><strong>Usuario solo con <code>crear_mascotas</code>:</strong> No debería ver el campo propietario</li>
                <li><strong>Usuario solo con <code>ver_todas_mascotas</code>:</strong> No debería poder crear mascotas</li>
                <li><strong>Usuario sin permisos:</strong> No debería acceder al módulo</li>
            </ul>
        </div>

        <h2>🎉 Estado</h2>
        <div class='success'>
            <strong>FUNCIONALIDAD COMPLETAMENTE IMPLEMENTADA</strong><br>
            El campo propietario ahora es obligatorio cuando el usuario tiene ambos permisos 
            <code>ver_todas_mascotas</code> y <code>crear_mascotas</code> juntos.
        </div>
    </div>
</body>
</html>";
?> 