SISTEMA DE GESTIÓN MASCOTAS IOT
=============================

DESCRIPCIÓN
-----------
Sistema de gestión para mascotas con funcionalidades IoT, incluyendo:
- Gestión de usuarios y roles
- Control de dispositivos IoT
- Monitoreo de mascotas
- Sistema de permisos avanzado

REQUISITOS DEL SISTEMA
---------------------
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache 2.4 o superior
- XAMPP (recomendado para desarrollo)
- Extensión PDO para PHP
- Extensión JSON para PHP

ESTRUCTURA DE DIRECTORIOS
------------------------
/
├── config/              # Configuraciones del sistema
├── controllers/         # Controladores de la aplicación
├── models/             # Modelos de datos
├── public/             # Archivos públicos
│   ├── assets/         # Recursos estáticos
│   │   ├── css/       # Estilos CSS
│   │   ├── js/        # Scripts JavaScript
│   │   └── img/       # Imágenes
│   └── index.php       # Punto de entrada
├── views/              # Vistas de la aplicación
│   ├── auth/          # Vistas de autenticación
│   ├── dashboard/     # Panel principal
│   ├── roles/         # Gestión de roles
│   └── usuarios/      # Gestión de usuarios
└── README.txt         # Este archivo

INSTALACIÓN
-----------
1. Clonar el repositorio en la carpeta htdocs de XAMPP
2. Crear una base de datos MySQL
3. Importar el archivo database.sql
4. Configurar las credenciales en config/database.php
5. Acceder a la aplicación desde el navegador

CONFIGURACIÓN DE LA BASE DE DATOS
--------------------------------
1. Crear una nueva base de datos
2. Importar el archivo database.sql
3. Verificar las credenciales en config/database.php:
   - host: localhost
   - dbname: nombre_de_la_base
   - username: usuario_mysql
   - password: contraseña_mysql

PERMISOS Y ROLES
---------------
El sistema incluye tres roles principales:
1. Superadministrador
   - Acceso total al sistema
   - Gestión de todos los usuarios
   - Configuración del sistema

2. Administrador
   - Gestión de usuarios con roles inferiores
   - Acceso a dispositivos IoT
   - Monitoreo de mascotas

3. Usuario
   - Acceso básico al sistema
   - Gestión de sus propias mascotas
   - Visualización de datos

BACKUP DEL SISTEMA
-----------------
Para realizar un backup completo:

1. Backup de la base de datos:
   mysqldump -u [usuario] -p [nombre_base] > backup_$(date +%Y%m%d).sql

2. Backup de archivos:
   - Copiar toda la estructura de directorios
   - Especial atención a:
     * config/database.php
     * public/assets/
     * views/

3. Frecuencia recomendada:
   - Backup diario de la base de datos
   - Backup semanal de archivos
   - Backup mensual completo

SEGURIDAD
---------
- Todas las contraseñas se almacenan con hash
- Protección contra SQL Injection
- Validación de permisos en cada acción
- Protección de rutas sensibles
- Sanitización de entradas

MANTENIMIENTO
------------
1. Actualizaciones:
   - Mantener PHP y MySQL actualizados
   - Actualizar dependencias regularmente
   - Revisar logs de errores

2. Monitoreo:
   - Verificar espacio en disco
   - Monitorear uso de CPU
   - Revisar logs de acceso

CONTACTO Y SOPORTE
-----------------
Para reportar problemas o solicitar soporte:
- Email: soporte@mascotasiot.com
- Teléfono: +XX XXX XXX XXXX
- Horario de atención: Lunes a Viernes 9:00 - 18:00

VERSIÓN ACTUAL
-------------
Versión: 1.0.0
Última actualización: [Fecha actual] 