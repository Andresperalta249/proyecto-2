<?php
require_once 'config/config.php';
require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Verificar si ya existe un usuario admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = 'admin@test.com'");
    $stmt->execute();
    $existe = $stmt->fetchColumn();
    
    if ($existe > 0) {
        echo "El usuario admin ya existe.\n";
        exit;
    }
    
    // Crear rol admin si no existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE nombre = 'Administrador'");
    $stmt->execute();
    $rolExiste = $stmt->fetchColumn();
    
    if ($rolExiste == 0) {
        $stmt = $pdo->prepare("INSERT INTO roles (nombre, descripcion, estado) VALUES ('Administrador', 'Rol con todos los permisos', 'activo')");
        $stmt->execute();
        $rolId = $pdo->lastInsertId();
        echo "Rol Administrador creado con ID: $rolId\n";
    } else {
        $stmt = $pdo->prepare("SELECT id_rol FROM roles WHERE nombre = 'Administrador'");
        $stmt->execute();
        $rolId = $stmt->fetchColumn();
        echo "Rol Administrador ya existe con ID: $rolId\n";
    }
    
    // Crear usuario admin
    $password = password_hash('Admin123!', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol_id, estado) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Administrador', 'admin@test.com', $password, $rolId, 'activo']);
    $userId = $pdo->lastInsertId();
    
    echo "Usuario admin creado con ID: $userId\n";
    echo "Email: admin@test.com\n";
    echo "Password: Admin123!\n";
    
    // Crear permisos básicos si no existen
    $permisos = [
        'ver_dispositivos',
        'crear_dispositivos', 
        'editar_dispositivos',
        'eliminar_dispositivos',
        'ver_todos_dispositivo',
        'ver_mascotas',
        'crear_mascotas',
        'editar_mascotas', 
        'eliminar_mascotas',
        'ver_usuarios',
        'crear_usuarios',
        'editar_usuarios',
        'eliminar_usuarios'
    ];
    
    foreach ($permisos as $permiso) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM permisos WHERE codigo = ?");
        $stmt->execute([$permiso]);
        $permisoExiste = $stmt->fetchColumn();
        
        if ($permisoExiste == 0) {
            $stmt = $pdo->prepare("INSERT INTO permisos (nombre, codigo, descripcion, estado) VALUES (?, ?, ?, ?)");
            $stmt->execute([ucfirst(str_replace('_', ' ', $permiso)), $permiso, 'Permiso para ' . $permiso, 'activo']);
            $permisoId = $pdo->lastInsertId();
            echo "Permiso $permiso creado con ID: $permisoId\n";
        } else {
            $stmt = $pdo->prepare("SELECT id_permiso FROM permisos WHERE codigo = ?");
            $stmt->execute([$permiso]);
            $permisoId = $stmt->fetchColumn();
        }
        
        // Asignar permiso al rol admin
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles_permisos WHERE rol_id = ? AND permiso_id = ?");
        $stmt->execute([$rolId, $permisoId]);
        $asignacionExiste = $stmt->fetchColumn();
        
        if ($asignacionExiste == 0) {
            $stmt = $pdo->prepare("INSERT INTO roles_permisos (rol_id, permiso_id) VALUES (?, ?)");
            $stmt->execute([$rolId, $permisoId]);
            echo "Permiso $permiso asignado al rol Administrador\n";
        }
    }
    
    echo "\n¡Usuario de prueba creado exitosamente!\n";
    echo "Puedes iniciar sesión con:\n";
    echo "Email: admin@test.com\n";
    echo "Password: Admin123!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 