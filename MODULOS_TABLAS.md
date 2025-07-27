# 📊 Módulos y Tablas del Sistema IoT Pets

## 🔍 **Resumen de Módulos**

El sistema está organizado en **7 módulos principales**, cada uno con sus respectivos controladores, modelos y vistas.

---

## 📋 **1. Módulo de Autenticación (Auth)**
**Controlador:** `AuthController.php`  
**Modelos:** `UsuarioModel.php`, `User.php`

### 🗃️ **Tablas utilizadas:**
- **`usuarios`** - Gestión de usuarios del sistema
- **`roles`** - Roles de usuario (SuperAdmin, Admin, Usuario)
- **`password_resets`** - Tokens para restablecimiento de contraseñas

### 🔧 **Funcionalidades:**
- Login/Logout
- Registro de usuarios
- Recuperación de contraseñas
- Gestión de sesiones

---

## 👥 **2. Módulo de Usuarios**
**Controlador:** `UsuariosController.php`  
**Modelos:** `UsuarioModel.php`, `User.php`

### 🗃️ **Tablas utilizadas:**
- **`usuarios`** - CRUD completo de usuarios
- **`roles`** - Asociación de roles
- **`mascotas`** - Mascotas asociadas al usuario
- **`dispositivos`** - Dispositivos asociados a las mascotas del usuario

### 🔧 **Funcionalidades:**
- Listado de usuarios
- Crear/Editar/Eliminar usuarios
- Cambiar estado (activo/inactivo)
- Gestión de permisos por rol

---

## 🐕 **3. Módulo de Mascotas**
**Controlador:** `MascotasController.php`  
**Modelos:** `Mascota.php`

### 🗃️ **Tablas utilizadas:**
- **`mascotas`** - CRUD completo de mascotas
- **`usuarios`** - Propietarios de las mascotas
- **`dispositivos`** - Dispositivos asociados a mascotas

### 🔧 **Funcionalidades:**
- Gestión completa de mascotas
- Asociación con propietarios
- Filtros por especie, raza, edad
- Estadísticas por veterinario
- Gestión de imágenes

---

## 📱 **4. Módulo de Dispositivos**
**Controlador:** `DispositivosController.php`  
**Modelos:** `DispositivoModel.php`

### 🗃️ **Tablas utilizadas:**
- **`dispositivos`** - CRUD completo de dispositivos IoT
- **`mascotas`** - Mascotas asociadas a dispositivos
- **`usuarios`** - Propietarios de dispositivos
- **`datos_sensores`** - Datos de ubicación y sensores

### 🔧 **Funcionalidades:**
- Gestión de dispositivos IoT
- Asociación con mascotas
- Monitoreo de ubicación
- Estado de conexión
- Configuración de dispositivos

---

## 📊 **5. Módulo de Monitoreo**
**Controlador:** `MonitorController.php`  
**Modelos:** `DatosSensorModel.php`, `DispositivoModel.php`

### 🗃️ **Tablas utilizadas:**
- **`datos_sensores`** - Datos de sensores en tiempo real
- **`dispositivos`** - Información de dispositivos
- **`mascotas`** - Mascotas monitoreadas

### 🔧 **Funcionalidades:**
- Monitoreo en tiempo real
- Gráficas de datos de sensores
- Ubicación GPS
- Alertas de salud
- Historial de datos

---

## 🎛️ **6. Módulo de Roles y Permisos**
**Controlador:** `RolesController.php`  
**Modelos:** `Rol.php`

### 🗃️ **Tablas utilizadas:**
- **`roles`** - CRUD completo de roles
- **`roles_permisos`** - Permisos asociados a roles
- **`permisos`** - Catálogo de permisos del sistema
- **`usuarios`** - Usuarios asociados a roles

### 🔧 **Funcionalidades:**
- Gestión de roles
- Asignación de permisos
- Validación de usuarios asociados
- Control de acceso

---

## 📈 **7. Módulo de Dashboard**
**Controlador:** `DashboardController.php`  
**Modelos:** `UsuarioModel.php`, `Mascota.php`, `DispositivoModel.php`

### 🗃️ **Tablas utilizadas:**
- **`usuarios`** - Estadísticas de usuarios
- **`mascotas`** - Estadísticas de mascotas
- **`dispositivos`** - Estadísticas de dispositivos
- **`datos_sensores`** - Datos para gráficas

### 🔧 **Funcionalidades:**
- KPIs del sistema
- Gráficas de crecimiento
- Distribución por especies
- Historial de actividad

---

## 📝 **8. Módulo de Logs (Sistema)**
**Modelos:** `Log.php`

### 🗃️ **Tablas utilizadas:**
- **`logs`** - Registro de actividades del sistema

### 🔧 **Funcionalidades:**
- Registro de actividades
- Auditoría de acciones
- Limpieza automática de logs antiguos
- Estadísticas de actividad

---

## 🗄️ **Resumen de Tablas Principales**

| Tabla | Descripción | Módulos que la usan |
|-------|-------------|-------------------|
| `usuarios` | Usuarios del sistema | Auth, Usuarios, Mascotas, Dashboard |
| `roles` | Roles de usuario | Auth, Usuarios, Roles |
| `mascotas` | Mascotas registradas | Mascotas, Dispositivos, Dashboard |
| `dispositivos` | Dispositivos IoT | Dispositivos, Monitor, Dashboard |
| `datos_sensores` | Datos de sensores | Monitor, Dispositivos |
| `logs` | Actividad del sistema | Sistema (Logs) |
| `roles_permisos` | Permisos por rol | Roles |
| `permisos` | Catálogo de permisos | Roles |
| `password_resets` | Recuperación de contraseñas | Auth |

---

## 🔗 **Relaciones entre Módulos**

1. **Auth ↔ Usuarios** - Gestión de autenticación y usuarios
2. **Usuarios ↔ Mascotas** - Propietarios y mascotas
3. **Mascotas ↔ Dispositivos** - Mascotas monitoreadas
4. **Dispositivos ↔ Monitor** - Datos de sensores
5. **Roles ↔ Usuarios** - Control de acceso
6. **Dashboard** - Consolida datos de todos los módulos

---

## 📊 **Estadísticas del Sistema**

- **7 módulos principales**
- **9 tablas de base de datos**
- **8 controladores**
- **8 modelos**
- **Múltiples vistas por módulo**

**El sistema está diseñado con una arquitectura modular que permite escalabilidad y mantenimiento eficiente.** 