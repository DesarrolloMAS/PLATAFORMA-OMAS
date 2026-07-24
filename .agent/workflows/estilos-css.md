---
description: Guía oficial de estilos CSS (Tema "Cyberpunk") para los nuevos formatos del sistema.
---

# Guía de Estilos CSS (Tema Cyberpunk)

Esta guía documenta el estándar visual requerido para todos los nuevos módulos de la PLATAFORMA-OMAS. El diseño utiliza una paleta oscura con acentos en colores neón, creando una experiencia inmersiva y moderna.

## 1. Fuentes Requeridas
Incluir siempre en el `<head>`:
```html
<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
```

## 2. Variables de Color (Root)
Copiar exactamente esta definición en la raíz del CSS:
```css
:root {
    --bg-color: #0B0E14;         /* Fondo principal de la página */
    --panel-bg: #151A22;         /* Fondo de las tarjetas y paneles */
    --accent: #00F0FF;           /* Cyan neón principal (bordes, acentos, botones) */
    --accent-glow: rgba(0, 240, 255, 0.4);
    --accent-hover: #00D1DF;     
    --text-main: #E2E8F0;        /* Texto principal (blanco grisáceo) */
    --text-muted: #94A3B8;       /* Texto secundario/etiquetas */
    --border-color: #1E293B;     /* Bordes sutiles de separación */
    --input-bg: #0F172A;         /* Fondo de los inputs de formulario */
    --danger: #FF3366;           /* Rojo neón para errores/peligro */
    --warning: #FFB000;          /* Naranja para alertas */
    --danger-glow: rgba(255, 51, 102, 0.4);
    
    /* Bordes redondeados estándar */
    --r-lg: 12px;
    --r-md: 8px;
    --r-sm: 4px;
}
```

## 3. Elementos Globales
```css
body {
    font-family: 'Barlow', sans-serif;
    background-color: var(--bg-color);
    color: var(--text-main);
    line-height: 1.5;
    min-height: 100vh;
    padding: 40px 20px;
    /* Patrón de fondo (grid) */
    background-image: 
        linear-gradient(rgba(0, 240, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
    background-size: 30px 30px;
}
```

## 4. Clases y Componentes Obligatorios

### Contenedor Principal y Header
- `.container`: Debe envolver todo el contenido, `max-width: 1000px; margin: 0 auto;`.
- `.header-box`: Tarjeta superior. Debe tener `border-left: 4px solid var(--accent);` y el decorador seudoelemento `::before` con rotación 45deg indicando "V1 JSON".
- `.main-title`: Letras mayúsculas, `font-weight: 700`, color `#fff`.
- `.sub-title`: Color `var(--text-muted)`.

### Formularios y Tarjetas
- `.section-card`: Contenedor para cada grupo de campos. `background: var(--panel-bg); border-radius: var(--r-md);`. Efecto `:hover` que ilumina el borde en cyan.
- `.section-title`: Título de cada tarjeta. Color `var(--accent)`. Soporta un `<span>` interno para numeración (con fuente `Space Mono`).
- `.form-grid`: Grid CSS `grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;`.
- `.form-group`: Envuelve a `label` y `input`. Layout flex column. Usar `.form-group.full` para ocupar todo el ancho.
- `.form-label`: Letra pequeña (`13px`), color `var(--text-muted)`.
- `.form-control`: Inputs, selects y textareas. `background: var(--input-bg); border: 1px solid var(--border-color);`. En estado `:focus`, aplicar un borde cyan y un box-shadow estilo glow.

### Botones
- `.btn-back`: Botón de retorno al menú. Fuente `Space Mono`, diseño minimalista con bordes sutiles.
- `.btn-submit`: Botón principal de guardado. Debe tener el fondo `var(--accent)`, texto del color del fondo oscuro (`var(--bg-color)`), fuente `Space Mono` mayúsculas, y un `box-shadow` resplandeciente (`var(--accent-glow)`). Al hacer `:hover`, el fondo debe cambiar a blanco brillante.

### Detalles Dinámicos
- `.system-status`: Pie de página indicando "SISTEMA JSON INTERCONECTADO". Usa `Space Mono` y color `var(--text-muted)`.
- `.status-dot`: Punto verde intermitente (`#10B981`) con animación `@keyframes pulse` para indicar sistema en línea.

## 5. Referencia Visual
El código CSS completo y funcional, con todas las animaciones y configuraciones precisas de sombras/bordes, siempre debe extraerse directamente del archivo `template/inspeccion empaque/inspeccion_empaque.html`.
