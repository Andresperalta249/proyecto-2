<?php
// Script para convertir el informe markdown a formato Word
// Este script genera un archivo HTML que puede ser abierto en Word

// Leer el archivo markdown
$markdown = file_get_contents('INFORME_PROYECTO_PETMONITORING_IOT.md');

// Función simple para convertir markdown a HTML
function markdownToHtml($markdown) {
    // Convertir encabezados
    $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $markdown);
    $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);
    
    // Convertir listas
    $html = preg_replace('/^- (.*$)/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
    
    // Convertir código
    $html = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $html);
    $html = preg_replace('/`(.*?)`/s', '<code>$1</code>', $html);
    
    // Convertir negritas
    $html = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $html);
    
    // Convertir párrafos
    $html = preg_replace('/\n\n([^<].*)/', '<p>$1</p>', $html);
    
    return $html;
}

// Convertir markdown a HTML
$html = markdownToHtml($markdown);

// Crear el documento HTML completo
$document = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe Proyecto PetMonitoring IoT</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 1in;
            color: #333;
        }
        h1 {
            font-size: 18pt;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        h2 {
            font-size: 16pt;
            font-weight: bold;
            color: #34495e;
            margin-top: 18px;
            margin-bottom: 12px;
        }
        h3 {
            font-size: 14pt;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        p {
            margin-bottom: 10px;
            text-align: justify;
        }
        ul {
            margin-left: 20px;
            margin-bottom: 10px;
        }
        li {
            margin-bottom: 5px;
        }
        code {
            font-family: "Courier New", monospace;
            background-color: #f8f9fa;
            padding: 2px 4px;
            border: 1px solid #e9ecef;
            border-radius: 3px;
        }
        pre {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            overflow-x: auto;
        }
        pre code {
            background: none;
            border: none;
            padding: 0;
        }
        .method-list {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 10px;
            margin: 10px 0;
        }
        .method-list li {
            font-family: "Courier New", monospace;
            font-size: 11pt;
        }
        .characteristics {
            background-color: #e8f5e8;
            border-left: 4px solid #27ae60;
            padding: 10px;
            margin: 10px 0;
        }
        .problem {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 10px 0;
        }
        .conclusion {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 15px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 24pt;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 14pt;
            color: #7f8c8d;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>INFORME COMPLETO DEL PROYECTO</h1>
        <h1>PETMONITORING IOT</h1>
        <p>Sistema de Monitoreo de Mascotas mediante Dispositivos IoT</p>
        <p>Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>
    </div>

    ' . $html . '

    <div class="page-break"></div>
    
    <div class="conclusion">
        <h2>RESUMEN EJECUTIVO</h2>
        <p>Este informe presenta un análisis completo del proyecto PetMonitoring IoT, un sistema web desarrollado en PHP que implementa el patrón MVC para el monitoreo de mascotas mediante dispositivos IoT.</p>
        
        <h3>Puntos Clave:</h3>
        <ul>
            <li><strong>Arquitectura:</strong> Patrón MVC con separación clara de responsabilidades</li>
            <li><strong>Base de Datos:</strong> MySQL con patrón Singleton para conexiones</li>
            <li><strong>Frontend:</strong> Bootstrap 5 con componentes modernos</li>
            <li><strong>Seguridad:</strong> Sistema de roles y permisos granular</li>
            <li><strong>Funcionalidades:</strong> Dashboard, monitor en tiempo real, gestión de dispositivos</li>
        </ul>
        
        <h3>Estado del Proyecto:</h3>
        <p>El proyecto presenta una arquitectura sólida y funcionalidades avanzadas, con un error identificado en el método <code>existeMac()</code> del modelo DispositivoModel que requiere implementación.</p>
    </div>
</body>
</html>';

// Guardar el archivo HTML
file_put_contents('INFORME_PROYECTO_PETMONITORING_IOT.html', $document);

echo "Archivo HTML generado exitosamente: INFORME_PROYECTO_PETMONITORING_IOT.html\n";
echo "Para convertir a Word:\n";
echo "1. Abre el archivo HTML en tu navegador\n";
echo "2. Imprime como PDF o guarda como Word\n";
echo "3. Alternativamente, copia y pega el contenido en Word\n";
?> 