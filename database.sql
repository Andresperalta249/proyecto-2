-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS mascotas_iot;
USE mascotas_iot;

-- Tabla de roles
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    es_predeterminado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

-- Tabla de permisos
CREATE TABLE permisos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

-- Tabla de roles_permisos
CREATE TABLE rol_permisos (
    rol_id INT,
    permiso_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id),
    FOREIGN KEY (permiso_id) REFERENCES permisos(id)
);

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- Tabla de mascotas
CREATE TABLE mascotas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    especie VARCHAR(50) NOT NULL,
    raza VARCHAR(50),
    fecha_nacimiento DATE,
    usuario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de dispositivos
CREATE TABLE dispositivos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    codigo_identificacion VARCHAR(50) NOT NULL UNIQUE,
    tipo VARCHAR(50) NOT NULL,
    mascota_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    FOREIGN KEY (mascota_id) REFERENCES mascotas(id)
);

-- Tabla de historial_monitor
CREATE TABLE historial_monitor (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dispositivo_id INT,
    temperatura DECIMAL(5,2),
    ritmo_cardiaco INT,
    latitud DECIMAL(10,8),
    longitud DECIMAL(11,8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id)
);

-- Tabla de password_resets
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (email) REFERENCES usuarios(email)
);

-- Limpiar datos existentes
TRUNCATE TABLE rol_permisos;
DELETE FROM permisos;
DELETE FROM roles;

-- Insertar roles predeterminados
INSERT INTO roles (nombre, descripcion, es_predeterminado) VALUES
('Superadministrador', 'Control total del sistema incluyendo roles predeterminados', TRUE),
('Administrador', 'Gestión general del sistema y roles personalizados', TRUE),
('Usuario', 'Acceso básico al sistema', TRUE);

-- Insertar permisos actualizados
INSERT INTO permisos (nombre, descripcion, icono) VALUES
('gestionar_usuarios', 'Crear, editar, eliminar usuarios', '👥'),
('ver_usuarios', 'Solo visualizar la lista de usuarios', '👀'),
('gestionar_mascotas', 'Agregar, editar, eliminar mascotas', '🐾'),
('ver_mascotas', 'Ver fichas de mascotas', '📄'),
('gestionar_dispositivos', 'Vincular dispositivos IoT, configurar y eliminar', '📡'),
('ver_dispositivos', 'Solo visualizar dispositivos activos', '🔍'),
('ver_reportes', 'Acceder a reportes de salud o actividad', '📊'),
('exportar_reportes', 'Descargar reportes en PDF, Excel, etc', '🧾'),
('gestionar_configuracion', 'Cambiar parámetros globales del sistema', '⚙️'),
('gestionar_roles', 'Crear, editar o anular roles personalizados', '🔐'),
('asignar_roles', 'Asignar roles a usuarios', '🏷️'),
('roles', 'Acceder al módulo de roles', '👥');

-- Asignar todos los permisos al Superadministrador
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT 1, id FROM permisos;

-- Asignar permisos al Administrador
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT 2, id FROM permisos 
WHERE nombre IN (
    'gestionar_usuarios', 'ver_usuarios',
    'gestionar_mascotas', 'ver_mascotas',
    'gestionar_dispositivos', 'ver_dispositivos',
    'ver_reportes', 'exportar_reportes',
    'gestionar_configuracion', 'gestionar_roles',
    'asignar_roles', 'roles'
);

-- Asignar permisos básicos al Usuario
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT 3, id FROM permisos 
WHERE nombre IN (
    'ver_mascotas',
    'ver_dispositivos',
    'ver_reportes'
);

-- Insertar usuarios de prueba (password: 123456)
INSERT INTO usuarios (nombre, email, password, rol_id, estado) VALUES
('Administrador Principal', 'admin@mascotasiot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'activo'),
('Supervisor General', 'supervisor@mascotasiot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'activo'),
('Usuario Demo', 'usuario@mascotasiot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'activo');

-- Insertar mascotas de prueba
INSERT INTO mascotas (nombre, especie, raza, fecha_nacimiento, usuario_id, estado) VALUES
('Max', 'Perro', 'Labrador', '2020-05-15', 3, 'activo'),
('Luna', 'Gato', 'Siamés', '2021-03-10', 3, 'activo'),
('Rocky', 'Perro', 'Pastor Alemán', '2019-12-01', 3, 'activo');

-- Insertar dispositivos de prueba
INSERT INTO dispositivos (nombre, codigo_identificacion, tipo, mascota_id, estado) VALUES
('Collar Max', 'DISP001', 'Collar Inteligente', 1, 'activo'),
('Collar Luna', 'DISP002', 'Collar Inteligente', 2, 'activo'),
('Collar Rocky', 'DISP003', 'Collar Inteligente', 3, 'activo');

-- Insertar datos de monitoreo de prueba
INSERT INTO historial_monitor (dispositivo_id, temperatura, ritmo_cardiaco, latitud, longitud) VALUES
(1, 38.5, 85, 19.4326, -99.1332),
(1, 38.2, 90, 19.4326, -99.1332),
(2, 38.7, 95, 19.4326, -99.1332),
(2, 38.4, 88, 19.4326, -99.1332),
(3, 38.6, 92, 19.4326, -99.1332),
(3, 38.3, 87, 19.4326, -99.1332);

-- Credenciales de acceso:
-- Superadmin: admin@mascotasiot.com / 123456
-- Admin: supervisor@mascotasiot.com / 123456
-- Usuario: usuario@mascotasiot.com / 123456 