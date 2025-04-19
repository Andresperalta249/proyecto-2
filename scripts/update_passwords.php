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
    
    // Nueva contraseña que vamos a establecer para todos los usuarios
    $newPassword = "123456";
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Actualizar las contraseñas
    $stmt = $conn->prepare("UPDATE usuarios SET password = :password");
    $stmt->execute(['password' => $hashedPassword]);
    
    echo "Contraseñas actualizadas exitosamente!\n";
    
    // Mostrar usuarios para verificar
    $stmt = $conn->query("SELECT id, nombre, email FROM usuarios");
    echo "\nUsuarios actualizados:\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . " | Nombre: " . $row['nombre'] . " | Email: " . $row['email'] . "\n";
    }
    
    // Probar inicio de sesión con el primer usuario
    $stmt = $conn->query("SELECT * FROM usuarios LIMIT 1");
    $testUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testUser) {
        // Verificar si la contraseña coincide
        if (password_verify($newPassword, $testUser['password'])) {
            echo "\nPrueba de inicio de sesión exitosa para el usuario: " . $testUser['email'] . "\n";
            echo "La nueva contraseña '123456' funciona correctamente.\n";
        } else {
            echo "\nError en la prueba de inicio de sesión.\n";
        }
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 