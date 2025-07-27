# Guía de Tipografía - PetMonitoring IoT

## Sistema de Tipografía Estandarizado

Este documento describe el sistema de tipografía estandarizado implementado en el proyecto PetMonitoring IoT.

### Archivos Principales

- **`assets/css/typography.css`** - Sistema de variables CSS y clases utilitarias
- **`assets/css/auth.css`** - Estilos de autenticación actualizados
- **`assets/css/dashboard.css`** - Estilos del dashboard actualizados
- **`assets/css/device-monitor.css`** - Estilos del monitor de dispositivos actualizados
- **`assets/css/errors.css`** - Estilos de páginas de error actualizados

### Variables CSS Principales

#### Fuentes
```css
--font-family-primary: 'Segoe UI', Arial, sans-serif;
--font-family-secondary: 'Inter', 'Segoe UI', Arial, sans-serif;
```

#### Tamaños Base
```css
--font-size-base: 15px;
--font-size-xs: 0.75rem;    /* 11.25px */
--font-size-sm: 0.875rem;   /* 13.125px */
--font-size-md: 1rem;       /* 15px */
--font-size-lg: 1.125rem;   /* 16.875px */
--font-size-xl: 1.25rem;    /* 18.75px */
--font-size-2xl: 1.5rem;    /* 22.5px */
--font-size-3xl: 1.875rem;  /* 28.125px */
--font-size-4xl: 2.25rem;   /* 33.75px */
--font-size-5xl: 3rem;      /* 45px */
```

#### Pesos de Fuente
```css
--font-weight-light: 300;
--font-weight-normal: 400;
--font-weight-medium: 500;
--font-weight-semibold: 600;
--font-weight-bold: 700;
--font-weight-extrabold: 800;
```

### Jerarquía de Tipografía

#### Títulos
- **Heading 1**: `var(--heading-1-size)` - Títulos principales
- **Heading 2**: `var(--heading-2-size)` - Subtítulos importantes
- **Heading 3**: `var(--heading-3-size)` - Encabezados de sección
- **Heading 4**: `var(--heading-4-size)` - Encabezados menores

#### Texto del Cuerpo
- **Body Large**: `var(--body-large-size)` - Texto destacado
- **Body Normal**: `var(--body-normal-size)` - Texto base
- **Body Small**: `var(--body-small-size)` - Texto secundario
- **Body XS**: `var(--body-xs-size)` - Texto muy pequeño

#### Elementos de Interfaz
- **UI Label**: `var(--ui-label-size)` - Etiquetas de formularios
- **UI Button**: `var(--ui-button-size)` - Texto de botones
- **UI Input**: `var(--ui-input-size)` - Texto de campos de entrada
- **UI Caption**: `var(--ui-caption-size)` - Texto de ayuda y captions

### Clases Utilitarias

#### Títulos
```html
<h1 class="heading-1">Título Principal</h1>
<h2 class="heading-2">Subtítulo</h2>
<h3 class="heading-3">Encabezado de Sección</h3>
<h4 class="heading-4">Encabezado Menor</h4>
```

#### Texto del Cuerpo
```html
<p class="body-large">Texto destacado</p>
<p class="body-normal">Texto base</p>
<p class="body-small">Texto secundario</p>
<span class="body-xs">Texto muy pequeño</span>
```

#### Elementos de Interfaz
```html
<label class="ui-label">Etiqueta</label>
<button class="ui-button">Botón</button>
<input class="ui-input" type="text">
<span class="ui-caption">Texto de ayuda</span>
```

### Uso en CSS

#### Variables Directas
```css
.mi-clase {
    font-family: var(--font-family-primary);
    font-size: var(--heading-2-size);
    font-weight: var(--font-weight-semibold);
    line-height: var(--line-height-tight);
}
```

#### Clases Utilitarias
```css
.mi-clase {
    /* Aplicar clase utilitaria */
    @extend .heading-2;
}
```

### Responsive Design

El sistema se adapta automáticamente a diferentes tamaños de pantalla:

- **Desktop**: `--font-size-base: 15px`
- **Tablet (≤768px)**: `--font-size-base: 14px`
- **Mobile (≤480px)**: `--font-size-base: 13px`

### Integración con Chart.js

Las gráficas de Chart.js utilizan las variables de tipografía:

```javascript
Chart.defaults.font.family = getComputedStyle(document.documentElement)
    .getPropertyValue('--font-family-primary');
Chart.defaults.font.size = parseInt(getComputedStyle(document.documentElement)
    .getPropertyValue('--font-size-sm'));
```

### Beneficios del Sistema

1. **Consistencia**: Todos los elementos usan la misma tipografía
2. **Mantenibilidad**: Cambios centralizados en un archivo
3. **Escalabilidad**: Fácil adaptación a diferentes dispositivos
4. **Accesibilidad**: Mejor legibilidad y contraste
5. **Performance**: Menos código CSS repetido

### Migración Completada

✅ **Archivos actualizados:**
- `assets/css/auth.css`
- `assets/css/dashboard.css`
- `assets/css/device-monitor.css`
- `assets/css/errors.css`
- `views/layouts/main.php`
- `views/auth/login.php`
- `views/auth/forgot-password.php`
- `views/monitor/device.php`

### Próximos Pasos

1. Aplicar las clases utilitarias en los archivos HTML/PHP
2. Revisar y actualizar cualquier CSS personalizado restante
3. Implementar pruebas de accesibilidad
4. Documentar casos de uso específicos

---

**Nota**: Este sistema de tipografía es la base para mantener consistencia visual en todo el proyecto PetMonitoring IoT. 