# 📊 Tablas UI del Sistema IoT Pets

## 🔍 **Resumen de Tablas para Usuario**

El sistema tiene **5 módulos principales** que muestran tablas de datos al usuario para gestionar la información.

---

## 👥 **1. Módulo de Usuarios**
**Vista:** `views/usuarios/index.php`  
**Controlador:** `UsuariosController.php`

### 📋 **Tabla de Usuarios:**
```html
<table class="table table-striped table-hover">
```

### 🗂️ **Columnas mostradas:**
- **ID** - Identificador único
- **Nombre** - Nombre completo del usuario
- **Email** - Correo electrónico
- **Rol** - Rol del usuario (con badge de color)
- **Estado** - Switch activo/inactivo
- **Acciones** - Botones editar/eliminar

### 🔧 **Funcionalidades:**
- ✅ Paginación
- ✅ Búsqueda por nombre/email
- ✅ Filtro por rol
- ✅ Cambio de estado con switch
- ✅ Modal para crear/editar
- ✅ Botón flotante para agregar

---

## 🐕 **2. Módulo de Mascotas**
**Vista:** `views/mascotas/index.php`  
**Controlador:** `MascotasController.php`

### 📋 **Tabla de Mascotas:**
```html
<table class="tabla-app" id="tablaMascotas">
```

### 🗂️ **Columnas mostradas:**
- **ID** - Identificador único
- **Nombre** - Nombre de la mascota
- **Especie** - Tipo de animal
- **Tamaño** - Tamaño de la mascota
- **Género** - Masculino/Femenino
- **Propietario** - Dueño de la mascota
- **Edad** - Cálculo automático desde fecha de nacimiento
- **Estado** - Switch activo/inactivo
- **Acciones** - Editar/Eliminar/Monitor

### 🔧 **Funcionalidades:**
- ✅ Búsqueda por nombre, especie, propietario
- ✅ Filtros avanzados
- ✅ Cambio de estado con switch
- ✅ Botón de monitor (si tiene dispositivo)
- ✅ Modal para crear/editar
- ✅ Botón flotante para agregar

---

## 📱 **3. Módulo de Dispositivos**
**Vista:** `views/dispositivos/index.php`  
**Controlador:** `DispositivosController.php`

### 📋 **Tabla de Dispositivos:**
```html
<table class="tabla-app" id="tablaDispositivos">
```

### 🗂️ **Columnas mostradas:**
- **ID** - Identificador único
- **Nombre** - Nombre del dispositivo
- **MAC** - Dirección MAC del dispositivo
- **Dueño** - Propietario del dispositivo
- **Disponible** - Estado de disponibilidad
- **Estado** - Estado del dispositivo
- **Batería** - Porcentaje de batería
- **Mascota** - Mascota asociada
- **Última Lectura** - Última actualización

### 🔧 **Funcionalidades:**
- ✅ Paginación
- ✅ Búsqueda por nombre/MAC
- ✅ Filtros por estado/disponibilidad
- ✅ Indicadores visuales de estado
- ✅ Modal para gestionar dispositivo
- ✅ Botón flotante para agregar

---

## 🎛️ **4. Módulo de Roles**
**Vista:** `views/roles/index.php`  
**Controlador:** `RolesController.php`

### 📋 **Tabla de Roles:**
```html
<table class="table table-hover">
```

### 🗂️ **Columnas mostradas:**
- **ID** - Identificador único
- **Nombre** - Nombre del rol
- **Descripción** - Descripción del rol
- **Estado** - Badge activo/inactivo
- **Acciones** - Botones editar/eliminar

### 🔧 **Funcionalidades:**
- ✅ Búsqueda por nombre
- ✅ Filtro por estado
- ✅ Carga dinámica por AJAX
- ✅ Modal para crear/editar
- ✅ Gestión de permisos
- ✅ Botón flotante para agregar

---

## 📊 **5. Módulo de Monitor**
**Vista:** `views/monitor/device.php`  
**Controlador:** `MonitorController.php`

### 📋 **Tabla de Registros de Sensores:**
```html
<table class="table table-hover" id="tablaRegistros">
```

### 🗂️ **Columnas mostradas:**
- **Hora** - Timestamp de la lectura
- **Temperatura** - Temperatura registrada
- **Ritmo Cardíaco** - Frecuencia cardíaca
- **Batería** - Nivel de batería
- **Ubicación** - Coordenadas GPS

### 🔧 **Funcionalidades:**
- ✅ Datos en tiempo real
- ✅ Actualización automática
- ✅ Gráficas asociadas
- ✅ Mapa de ubicación
- ✅ Filtros por tiempo

---

## 🎨 **Estilos y Clases CSS**

### **Clases principales utilizadas:**
- `table` - Bootstrap table base
- `table-striped` - Filas alternadas
- `table-hover` - Efecto hover
- `tabla-app` - Clase personalizada del sistema
- `table-responsive` - Responsive design

### **Componentes comunes:**
- **Switches** - Para cambio de estado
- **Badges** - Para mostrar estados
- **Botones de acción** - Editar/Eliminar/Monitor
- **Modales** - Para formularios
- **Botones flotantes** - Para agregar elementos

---

## 🔄 **Funcionalidades Comunes**

### **Búsqueda y Filtros:**
- ✅ Búsqueda por texto
- ✅ Filtros por estado
- ✅ Filtros por tipo/categoría
- ✅ Búsqueda en tiempo real

### **Paginación:**
- ✅ Navegación entre páginas
- ✅ Información de registros
- ✅ Límite configurable

### **Acciones:**
- ✅ Crear nuevos registros
- ✅ Editar registros existentes
- ✅ Eliminar registros
- ✅ Cambiar estado
- ✅ Ver detalles

### **Responsive:**
- ✅ Diseño adaptable
- ✅ Scroll horizontal en móviles
- ✅ Botones adaptativos

---

## 📱 **Experiencia de Usuario**

### **Consistencia Visual:**
- ✅ Mismo estilo en todas las tablas
- ✅ Colores estandarizados
- ✅ Iconografía consistente
- ✅ Espaciado uniforme

### **Interactividad:**
- ✅ Feedback visual inmediato
- ✅ Confirmaciones para acciones críticas
- ✅ Tooltips informativos
- ✅ Estados de carga

### **Accesibilidad:**
- ✅ Contraste adecuado
- ✅ Navegación por teclado
- ✅ Etiquetas descriptivas
- ✅ Estructura semántica

---

## 📊 **Estadísticas de Tablas**

- **5 módulos con tablas**
- **5 tablas principales**
- **4 tablas con CRUD completo**
- **1 tabla de monitoreo en tiempo real**
- **Todas con búsqueda y filtros**
- **Todas con paginación (excepto monitor)**
- **Todas con modales para formularios**

**El sistema proporciona una experiencia de usuario consistente y funcional para la gestión de todos los datos principales.** 