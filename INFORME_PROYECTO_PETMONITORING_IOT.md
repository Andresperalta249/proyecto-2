# INFORME COMPLETO DEL PROYECTO PETMONITORING IOT

## 1. ARQUITECTURA GENERAL DEL SISTEMA

### 1.1 Patrón de Diseño
El proyecto implementa el **patrón MVC (Model-View-Controller)** con las siguientes características:

- **Modelo (M)**: Maneja la lógica de negocio y acceso a datos
- **Vista (V)**: Presentación de la interfaz de usuario
- **Controlador (C)**: Coordina entre el modelo y la vista

### 1.2 Estructura de Directorios
```
proyecto-2/
├── app/controllers/          # Controladores específicos
├── assets/                   # Recursos estáticos (CSS, JS, imágenes)
├── config/                   # Configuración del sistema
├── controllers/              # Controladores principales
├── core/                     # Núcleo del framework
├── logs/                     # Archivos de registro
├── models/                   # Modelos de datos
├── views/                    # Vistas y plantillas
└── index.php                 # Punto de entrada
```

## 2. CLASES PRINCIPALES Y HERENCIA

### 2.1 Clase Base: Controller
**Ubicación**: `core/Controller.php`

**Propósito**: Clase base para todos los controladores del sistema.

**Métodos principales**:
- `__construct()`: Inicializa la base de datos y vista
- `loadModel($model)`: Carga dinámicamente modelos
- `render($view, $data)`: Renderiza vistas con datos
- `jsonResponse($data, $status)`: Responde con JSON
- `validateRequest($required)`: Valida datos de entrada
- `sanitizeInput($data)`: Sanitiza datos de entrada
- `redirect($url)`: Redirige a otra URL

**Características**:
- Manejo de errores centralizado
- Autoloading de modelos
- Validación de datos de entrada
- Respuestas JSON estandarizadas

### 2.2 Clase Base: Model
**Ubicación**: `core/Model.php`

**Propósito**: Clase base para todos los modelos de datos.

**Métodos principales**:
- `__construct()`: Inicializa conexión a base de datos
- `query($sql, $params)`: Ejecuta consultas SQL
- `find($id)`: Busca registro por ID
- `create($data)`: Crea nuevo registro
- `update($id, $data)`: Actualiza registro existente
- `delete($id)`: Elimina registro
- `getLastError()`: Obtiene último error

**Características**:
- Patrón Singleton para conexión a BD
- Manejo de transacciones
- Logging de errores
- Sanitización automática de datos

### 2.3 Clase: Database
**Ubicación**: `core/Database.php`

**Propósito**: Maneja la conexión a la base de datos usando patrón Singleton.

**Métodos principales**:
- `getInstance()`: Obtiene instancia única
- `getConnection()`: Obtiene conexión PDO
- `beginTransaction()`: Inicia transacción
- `commit()`: Confirma transacción
- `rollBack()`: Revierte transacción
- `lastInsertId()`: Obtiene último ID insertado

**Características**:
- Patrón Singleton
- Configuración PDO optimizada
- Manejo de transacciones
- Prevención de clonación

### 2.4 Clase: View
**Ubicación**: `core/View.php`

**Propósito**: Maneja la renderización de vistas y layouts.

**Métodos principales**:
- `setTitle($title)`: Establece título de página
- `setData($key, $value)`: Establece datos para vista
- `setLayout($layout)`: Establece layout
- `render($view)`: Renderiza vista completa
- `partial($view, $data)`: Renderiza vista parcial
- `json($data)`: Responde con JSON
- `redirect($url)`: Redirige a URL

**Características**:
- Sistema de layouts flexible
- Extracción automática de datos
- Soporte para vistas parciales
- Buffer de salida

## 3. CONTROLADORES ESPECÍFICOS

### 3.1 AuthController
**Herencia**: `extends Controller`

**Propósito**: Maneja autenticación y autorización de usuarios.

**Métodos principales**:
- `loginAction()`: Procesa inicio de sesión
- `registerAction()`: Registra nuevos usuarios
- `resetPasswordAction($token)`: Restablece contraseña
- `forgotPasswordAction()`: Solicita restablecimiento
- `logoutAction()`: Cierra sesión
- `getRolNombre($rolId)`: Obtiene nombre del rol
- `validatePassword($password)`: Valida contraseña

**Características**:
- Validación de credenciales
- Hashing seguro de contraseñas
- Manejo de tokens de recuperación
- Logging detallado de eventos

### 3.2 DashboardController
**Herencia**: `extends Controller`

**Propósito**: Maneja el dashboard principal del sistema.

**Métodos principales**:
- `indexAction()`: Muestra dashboard principal
- `getKPIDataAction()`: Obtiene datos KPI
- `getDistribucionEspeciesAction()`: Obtiene distribución de especies
- `getHistorialUsuariosAction()`: Obtiene historial de usuarios
- `sendJsonResponse($data)`: Envía respuesta JSON
- `sendJsonError($message, $code)`: Envía error JSON

**Características**:
- Cálculo dinámico de KPIs
- Gráficas en tiempo real
- Paginación inteligente
- Manejo de errores robusto

### 3.3 DispositivosController
**Herencia**: `extends Controller`

**Propósito**: Gestiona dispositivos IoT del sistema.

**Métodos principales**:
- `indexAction()`: Lista dispositivos
- `createAction()`: Crea nuevo dispositivo
- `editAction($id)`: Edita dispositivo existente
- `deleteAction($id)`: Elimina dispositivo
- `deleteAjaxAction($id)`: Elimina vía AJAX
- `cambiarEstadoAction()`: Cambia estado de dispositivo
- `asignarAction()`: Asigna dispositivo a usuario/mascota
- `filtrarAction()`: Filtra dispositivos
- `verificarMacAction()`: Verifica unicidad de MAC
- `obtenerDetallesAction($id)`: Obtiene detalles de dispositivo
- `updateAction()`: Actualiza dispositivo vía AJAX

**Características**:
- Validación de formato MAC
- Verificación de unicidad
- Asignación dinámica
- Filtros avanzados
- Logging de acciones

### 3.4 MonitorController
**Herencia**: `extends Controller`

**Propósito**: Maneja el monitoreo en tiempo real de dispositivos.

**Métodos principales**:
- `indexAction()`: Muestra monitor principal
- `getDatosFiltradosAction()`: Obtiene datos filtrados
- `getPropietariosAction()`: Obtiene lista de propietarios
- `getMascotasPorPropietarioAction()`: Obtiene mascotas por propietario
- `getDatosTablaAction()`: Obtiene datos para tabla
- `testSimpleAction()`: Vista de prueba

**Características**:
- Filtros en tiempo real
- Datos geográficos
- Gráficas interactivas
- Permisos granulares

## 4. MODELOS DE DATOS

### 4.1 User (UsuarioModel)
**Herencia**: `extends Model`
**Tabla**: `usuarios`

**Métodos principales**:
- `getAll($filtros, $pagina, $alturaPantalla)`: Obtiene usuarios paginados
- `buscar($termino, $filtros)`: Busca usuarios
- `findById($id)`: Busca por ID
- `findByEmail($email)`: Busca por email
- `crear($datos)`: Crea usuario
- `actualizar($datos)`: Actualiza usuario
- `cambiarEstado($id, $estado)`: Cambia estado
- `cambiarEstadoEnCascada($id, $estado)`: Cambia estado en cascada
- `eliminar($id)`: Elimina usuario
- `eliminarEnCascada($id)`: Elimina en cascada
- `getMascotasAsociadas($id)`: Obtiene mascotas del usuario
- `getDispositivosAsociados($id)`: Obtiene dispositivos del usuario
- `getTotalUsuariosNormales()`: Cuenta usuarios normales
- `getActiveUsers()`: Obtiene usuarios activos

**Características**:
- Paginación dinámica
- Búsqueda avanzada
- Operaciones en cascada
- Validación de datos

### 4.2 DispositivoModel
**Herencia**: No hereda de Model (implementación independiente)
**Tabla**: `dispositivos`

**Métodos principales**:
- `getDispositivoById($id)`: Obtiene dispositivo por ID
- `getDispositivosByUsuario($usuarioId)`: Obtiene dispositivos de usuario
- `getUltimaUbicacion($dispositivoId)`: Obtiene última ubicación
- `getUltimosDatos($dispositivoId, $horas)`: Obtiene datos recientes
- `getRuta($dispositivoId, $horas)`: Obtiene ruta del dispositivo
- `getTodosDispositivosConMascotas()`: Obtiene todos con mascotas
- `getTotalDispositivos()`: Cuenta total de dispositivos
- `getTotalConectados()`: Cuenta dispositivos conectados
- `getDispositivosFiltrados()`: Obtiene con filtros
- `createDispositivo($data)`: Crea dispositivo
- `updateDispositivo($id, $data)`: Actualiza dispositivo
- `deleteDispositivo($id)`: Elimina dispositivo
- `existeMac($mac, $excludeId)`: Verifica unicidad de MAC
- `getDispositivosDisponibles()`: Obtiene dispositivos disponibles

**Características**:
- Manejo de ubicación GPS
- Filtros avanzados
- Validación de MAC
- Operaciones geográficas

### 4.3 Mascota
**Herencia**: `extends Model`
**Tabla**: `mascotas`

**Métodos principales**:
- `getMascotasByUser($usuario_id)`: Obtiene mascotas de usuario
- `createMascota($data)`: Crea mascota
- `updateMascota($id, $data)`: Actualiza mascota
- `deleteMascota($id)`: Elimina mascota
- `getEstadisticas($usuario_id)`: Obtiene estadísticas
- `getMascotasConDispositivos($usuario_id)`: Obtiene con dispositivos
- `getMascotasSinDispositivos($usuario_id)`: Obtiene sin dispositivos

- `getEstadisticasAvanzadas($usuario_id)`: Estadísticas avanzadas
- `buscarMascotasPorTermino($termino, $userId, $soloPropias)`: Búsqueda
- `getDistribucionEspecies()`: Distribución por especies
- `getTotalRegistradas()`: Cuenta total de mascotas

**Características**:
- Cálculo de edades
- Estadísticas avanzadas
- Filtros por especie/raza


### 4.4 DatosSensor
**Herencia**: `extends Model`
**Tabla**: `datos_sensores`

**Métodos principales**:
- `getUltimosDatos($dispositivoId, $horas)`: Obtiene datos recientes
- `getRuta($dispositivoId, $horas)`: Obtiene ruta
- `getDatosGrafica($dispositivoId, $tipo, $horas)`: Datos para gráficas
- `getEstadisticas($dispositivoId, $tipo, $horas)`: Estadísticas
- `getUltimaUbicacion($dispositivoId)`: Última ubicación
- `getDatosTabla($usuarioId, $dispositivoId, $limite, $pagina)`: Datos para tabla
- `getCampoPorTipo($tipo)`: Mapea tipos a campos

**Características**:
- Datos en tiempo real
- Gráficas dinámicas
- Estadísticas por tipo
- Manejo de coordenadas GPS

## 5. SISTEMA DE AUTOLOADING

### 5.1 Autoload.php
**Ubicación**: `core/Autoload.php`

**Funcionalidad**:
- Registra función de autoloading
- Busca clases en múltiples directorios
- Soporte para namespaces
- Logging de errores de carga

**Directorios de búsqueda**:
- `/controllers/`
- `/models/`
- `/core/`
- `/config/`

## 6. FUNCIONES DE UTILIDAD

### 6.1 functions.php
**Ubicación**: `core/functions.php`

**Funciones principales**:
- `verificarCualquierPermiso($permisos)`: Verifica al menos un permiso
- `verificarTodosPermisos($permisos)`: Verifica todos los permisos
- `obtenerRolUsuario()`: Obtiene nombre del rol
- `obtenerRolId()`: Obtiene ID del rol
- `esAdministrador()`: Verifica si es administrador
- `esSuperAdministrador()`: Verifica si es super admin
- `formatearFecha($fecha, $formato)`: Formatea fechas
- `generarTokenCSRF()`: Genera token CSRF
- `verificarTokenCSRF($token)`: Verifica token CSRF
- `sanitizarTexto($texto)`: Sanitiza texto
- `redirigir($url)`: Redirige a URL
- `mostrarError($mensaje, $url)`: Muestra error
- `mostrarExito($mensaje, $url)`: Muestra éxito

## 7. CONFIGURACIÓN DEL SISTEMA

### 7.1 config.php
**Ubicación**: `config/config.php`

**Configuraciones principales**:
- **Base de datos**: Host, nombre, usuario, contraseña
- **Aplicación**: Nombre, URL, versión, email
- **Correo**: Configuración SMTP
- **Archivos**: Directorios, tamaños máximos
- **Sesión**: Configuración de sesiones
- **Seguridad**: Costos de hash, tokens, JWT
- **Zona horaria**: America/Mexico_City

**Funciones de configuración**:
- `redirect($path)`: Redirección
- `isAuthenticated()`: Verifica autenticación
- `requireAuth()`: Requiere autenticación
- `getCurrentUser()`: Obtiene usuario actual
- `checkPermission($permission)`: Verifica permisos
- `verificarPermiso($permission)`: Alias para permisos
- `csrf_token()`: Genera token CSRF
- `verify_csrf_token($token)`: Verifica token CSRF
- `validatePassword($password)`: Valida contraseña

## 8. PUNTO DE ENTRADA

### 8.1 index.php
**Funcionalidades principales**:
- Configuración de errores y logging
- Routing dinámico basado en URL
- Middleware de autenticación
- Middleware de permisos
- Manejo de errores centralizado
- Conversión de rutas (kebab-case a camelCase)

**Flujo de procesamiento**:
1. Configuración inicial
2. Parsing de URL
3. Determinación de controlador/acción
4. Verificación de autenticación
5. Verificación de permisos
6. Carga de controlador
7. Ejecución de acción
8. Manejo de errores

## 9. SISTEMA DE VISTAS

### 9.1 Layout Principal
**Ubicación**: `views/layouts/main.php`

**Características**:
- Sidebar moderno y responsive
- Sistema de navegación dinámico
- Menús basados en permisos
- Integración con Bootstrap 5
- Iconos Font Awesome
- Gráficas Chart.js
- Mapas Leaflet
- DataTables para tablas
- SweetAlert2 para modales

**Componentes principales**:
- Header con información de usuario
- Sidebar con navegación
- Área de contenido principal
- Scripts y estilos integrados
- Sistema de mensajes

## 10. SEGURIDAD Y PERMISOS

### 10.1 Sistema de Roles
- **Super Administrador**: Acceso total
- **Administrador**: Gestión de usuarios y dispositivos
- **Usuario**: Gestión de sus propias mascotas

### 10.2 Permisos Implementados
- `ver_dashboard`: Acceso al dashboard
- `ver_usuarios`: Gestión de usuarios
- `ver_roles`: Gestión de roles
- `ver_mascotas`: Gestión de mascotas
- `ver_dispositivos`: Gestión de dispositivos
- `ver_monitor`: Acceso al monitor
- `ver_todos_dispositivos`: Ver todos los dispositivos
- `editar_dispositivos`: Editar dispositivos
- `eliminar_dispositivos`: Eliminar dispositivos

## 11. CARACTERÍSTICAS TÉCNICAS

### 11.1 Base de Datos
- **Motor**: MySQL
- **Patrón**: Singleton para conexiones
- **Transacciones**: Soporte completo
- **Prepared Statements**: Para seguridad
- **Logging**: Errores detallados

### 11.2 Frontend
- **Framework**: Bootstrap 5
- **JavaScript**: jQuery + Vanilla JS
- **Gráficas**: Chart.js
- **Mapas**: Leaflet
- **Tablas**: DataTables
- **Modales**: SweetAlert2
- **Iconos**: Font Awesome + Bootstrap Icons

### 11.3 Backend
- **Lenguaje**: PHP 7.4+
- **Patrón**: MVC
- **Autoloading**: SPL Autoloader
- **Sesiones**: Configuración segura
- **Logging**: Sistema completo
- **Validación**: Input sanitization
- **CSRF**: Protección implementada

## 12. FUNCIONALIDADES PRINCIPALES

### 12.1 Gestión de Usuarios
- Registro y autenticación
- Recuperación de contraseñas
- Gestión de roles y permisos
- Estados de usuario (activo/inactivo)

### 12.2 Gestión de Mascotas
- Registro de mascotas
- Asignación de dispositivos
- Estadísticas por especie
- Historial médico

### 12.3 Gestión de Dispositivos
- Registro de dispositivos IoT
- Validación de MAC addresses
- Asignación a usuarios/mascotas
- Monitoreo de estado

### 12.4 Monitor en Tiempo Real
- Visualización de datos de sensores
- Gráficas dinámicas
- Mapas de ubicación


### 12.5 Dashboard
- KPIs en tiempo real
- Gráficas de distribución
- Historial de actividad
- Estadísticas generales

## 13. LOGGING Y MONITOREO

### 13.1 Sistema de Logs
- **Ubicación**: `/logs/error.log`
- **Niveles**: ERROR, WARNING, INFO, DEBUG
- **Formato**: Timestamp + Nivel + Mensaje + Archivo:Línea
- **Rotación**: Manual por ahora

### 13.2 Monitoreo de Errores
- Error handler personalizado
- Shutdown function para errores fatales
- Logging de excepciones
- Stack traces detallados

## 14. OPTIMIZACIONES Y MEJORAS

### 14.1 Rendimiento
- Autoloading eficiente
- Prepared statements
- Caché de consultas (pendiente)
- Compresión de assets (pendiente)

### 14.2 Seguridad
- Sanitización de inputs
- Validación de datos
- Protección CSRF
- Hashing seguro de contraseñas
- Control de acceso basado en roles

### 14.3 Escalabilidad
- Arquitectura modular
- Separación de responsabilidades
- Configuración centralizada
- Sistema de plugins (pendiente)

## 15. PROBLEMAS IDENTIFICADOS

### 15.1 Error en DispositivoModel
**Problema**: Error en línea 447 de DispositivosController.php
```
Call to undefined method DispositivoModel::existeMac()
```

**Causa**: El método `existeMac()` no está implementado en la clase DispositivoModel.

**Solución**: Implementar el método `existeMac()` en la clase DispositivoModel.

### 15.2 Recomendaciones de Mejora
1. **Implementar método faltante**: Agregar `existeMac()` a DispositivoModel
2. **Mejorar manejo de errores**: Implementar try-catch más robustos
3. **Optimizar consultas**: Agregar índices a la base de datos
4. **Implementar caché**: Reducir consultas repetitivas
5. **Mejorar seguridad**: Implementar rate limiting
6. **Agregar tests**: Implementar pruebas unitarias

## 16. CONCLUSIÓN

El proyecto PetMonitoring IoT presenta una arquitectura sólida basada en el patrón MVC, con una separación clara de responsabilidades y un sistema de permisos bien estructurado. La implementación incluye funcionalidades avanzadas para el monitoreo de mascotas mediante dispositivos IoT, con un frontend moderno y responsive.

**Fortalezas del proyecto**:
- Arquitectura modular y escalable
- Sistema de permisos granular
- Interfaz de usuario moderna
- Logging detallado
- Manejo de errores centralizado

**Áreas de mejora**:
- Implementar método faltante en DispositivoModel
- Agregar pruebas automatizadas
- Optimizar consultas de base de datos
- Implementar sistema de caché
- Mejorar documentación de código

El proyecto demuestra un buen entendimiento de patrones de diseño y mejores prácticas de desarrollo web, proporcionando una base sólida para futuras expansiones y mejoras. 