-- Script completo para probar operaciones con roles
-- 1. Crear roles de prueba
INSERT INTO roles (nombre, descripcion, estado, es_predeterminado) 
VALUES 
('Rol de Prueba 1', 'Rol para pruebas de edición', 'activo', 0),
('Rol de Prueba 2', 'Rol para pruebas de eliminación', 'activo', 0);

-- 2. Obtener IDs de los roles creados
SET @rol_editar_id = (SELECT id FROM roles WHERE nombre = 'Rol de Prueba 1');
SET @rol_eliminar_id = (SELECT id FROM roles WHERE nombre = 'Rol de Prueba 2');

-- 3. Asignar permisos a los roles
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT @rol_editar_id, id FROM permisos 
WHERE nombre IN ('ver_usuarios', 'ver_mascotas') 
LIMIT 2;

INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT @rol_eliminar_id, id FROM permisos 
WHERE nombre IN ('gestionar_usuarios', 'gestionar_mascotas') 
LIMIT 2;

-- 4. Verificar roles y permisos antes de las operaciones
SELECT 'Roles y permisos antes de las operaciones' as estado;
SELECT r.id, r.nombre, r.descripcion, r.estado, GROUP_CONCAT(p.nombre) as permisos
FROM roles r
LEFT JOIN rol_permisos rp ON r.id = rp.rol_id
LEFT JOIN permisos p ON rp.permiso_id = p.id
WHERE r.id IN (@rol_editar_id, @rol_eliminar_id)
GROUP BY r.id;

-- 5. Prueba de edición de rol
UPDATE roles 
SET nombre = 'Rol Editado',
    descripcion = 'Descripción actualizada',
    estado = 'inactivo'
WHERE id = @rol_editar_id;

-- 6. Actualizar permisos del rol editado
DELETE FROM rol_permisos WHERE rol_id = @rol_editar_id;

INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT @rol_editar_id, id FROM permisos 
WHERE nombre IN ('gestionar_usuarios', 'gestionar_mascotas', 'ver_reportes') 
LIMIT 3;

-- 7. Verificar cambios después de editar
SELECT 'Estado después de editar' as estado;
SELECT r.id, r.nombre, r.descripcion, r.estado, GROUP_CONCAT(p.nombre) as permisos_actualizados
FROM roles r
LEFT JOIN rol_permisos rp ON r.id = rp.rol_id
LEFT JOIN permisos p ON rp.permiso_id = p.id
WHERE r.id = @rol_editar_id
GROUP BY r.id;

-- 8. Prueba de eliminación de rol
-- Primero verificar si hay usuarios con el rol
SELECT 'Verificación de usuarios con el rol' as estado;
SELECT COUNT(*) as usuarios_con_rol
FROM usuarios
WHERE rol_id = @rol_eliminar_id;

-- Si no hay usuarios, proceder con la eliminación
-- Primero eliminar permisos asociados
DELETE FROM rol_permisos WHERE rol_id = @rol_eliminar_id;

-- Luego eliminar el rol
DELETE FROM roles WHERE id = @rol_eliminar_id;

-- 9. Verificar que el rol fue eliminado
SELECT 'Verificación de eliminación' as estado;
SELECT r.*, GROUP_CONCAT(p.nombre) as permisos_restantes
FROM roles r
LEFT JOIN rol_permisos rp ON r.id = rp.rol_id
LEFT JOIN permisos p ON rp.permiso_id = p.id
WHERE r.id = @rol_eliminar_id
GROUP BY r.id;

-- 10. Limpiar datos de prueba
DELETE FROM rol_permisos WHERE rol_id = @rol_editar_id;
DELETE FROM roles WHERE id = @rol_editar_id; 