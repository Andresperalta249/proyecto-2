<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $host = 'localhost';
    $dbname = 'mascotas_iot';
    $username = 'root';
    $password = '';

    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conexión exitosa a la base de datos!\n";
    
    // Mostrar todas las bases de datos
    $stmt = $conn->query("SHOW DATABASES");
    echo "\nBases de datos disponibles:\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['Database'] . "\n";
    }
    
    // Mostrar todas las tablas de mascotas_iot
    $stmt = $conn->query("SHOW TABLES FROM mascotas_iot");
    echo "\nTablas en mascotas_iot:\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['Tables_in_mascotas_iot'] . "\n";
    }

} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
    echo "Código de error: " . $e->getCode() . "\n";
}
?> 