# Estructura Estándar para Tablas en el Sistema

## 1. Contenedor General
```html
<div class="container-fluid">
    <h1 class="mb-4">Título de la Página</h1>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <!-- Formulario de búsqueda -->
            <!-- Tabla -->
        </div>
    </div>
</div>
```

---

## 2. Formulario de Búsqueda
```html
<form class="row g-2 mb-3" id="formBuscar[Entidad]">
    <div class="col-md-4">
        <input type="text" class="form-control" name="nombre" placeholder="Buscar por nombre...">
    </div>
    <div class="col-md-3">
        <select class="form-select" name="filtro1">
            <option value="">Todos</option>
            <!-- Opciones -->
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" name="filtro2">
            <option value="">Todos</option>
            <!-- Opciones -->
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-search"></i> Buscar
        </button>
    </div>
</form>
```

---

## 3. Tabla Estándar
```html
<div class="table-responsive">
    <table class="tabla-app" id="tabla[Entidad]">
        <thead>
            <tr>
                <!-- Encabezados -->
            </tr>
        </thead>
        <tbody>
            <!-- Filas -->
        </tbody>
    </table>
</div>
```

---

## 4. Columna Estado con Switch
```php
<td>
    <div class="form-check form-switch d-flex align-items-center mb-0">
        <input class="form-check-input cambiar-estado <?= $item['estado'] === 'inactivo' ? 'switch-inactivo' : '' ?>"
            type="checkbox"
            data-id="<?= $item['id'] ?>"
            <?= $item['estado'] === 'activo' ? 'checked' : '' ?> >
        <label class="form-check-label ms-2">
            <?= ucfirst($item['estado']) ?>
        </label>
    </div>
</td>
```
- Cuando el estado es inactivo, el switch debe tener la clase `switch-inactivo` para mostrarse gris.
- El CSS para `.switch-inactivo` debe estar en `app.css`:
```css
.switch-inactivo {
    background-color: #e5e7eb !important;
    border-color: #d1d5db !important;
}
```

---

## 5. Botón Flotante de Crear
```php
<?php if ($puedeCrear ?? true): ?>
<button class="fab-crear" id="btnNuevo[Entidad]Flotante">
    <i class="fas fa-plus"></i>
    <span class="fab-text">Agregar [Entidad]</span>
</button>
<?php endif; ?>
```

---

## 6. Búsqueda AJAX (JS)
```js
$('#formBuscar[Entidad]').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    $.ajax({
        url: '/proyecto-2/[entidad]/tabla?' + formData,
        type: 'GET',
        success: function(data) {
            $('#tabla[Entidad] tbody').html(data);
        },
        error: function() {
            alert('No se pudo recargar la tabla.');
        }
    });
});
```

---

## 7. CSS Centralizado
- Todos los estilos reutilizables (tabla, botón flotante, etc.) deben estar en `assets/css/app.css`.
- No debe haber estilos embebidos en los archivos PHP.

---

## 8. Experiencia Visual
- Padding, márgenes y colores deben ser idénticos en todas las vistas.
- El usuario debe tener la misma experiencia visual y funcional en todas las tablas.

---

**¡Usa esta guía como referencia para crear o revisar cualquier vista de tablas en el sistema!** 