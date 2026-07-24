---
description: Proceso estandarizado para la creación de nuevos formatos con persistencia JSON, zonas automáticas y visor de impresión PDF.
---

# Guía Maestra de Creación de Formatos (Skill)

Esta guía documenta el proceso completo para crear formatos premium con persistencia JSON, gestión de zonas y exportación formal a PDF.

> **Módulo de referencia obligatorio**: `template/inspeccion empaque/` — Es el estándar actual del proyecto. Antes de crear cualquier archivo nuevo, revisar su estructura.

## Paso 0 — Contexto (SIEMPRE PRIMERO)
- Leer `/var/www/fmt/README.md` para conocer la arquitectura general.
- El módulo de referencia es `template/inspeccion empaque/`. Replicar su patrón.

## 1. Estructura y Base
- **Carpeta**: Crear `template/[nombre_formato]/` (usar guión bajo en lugar de espacios si es necesario).
- **Archivos a crear**:
  - `[nombre_formato].html` — Formulario de captura.
  - `procesar.php` — Endpoint POST (retorna JSON `{status, message, id}`).
  - `listar_jsons.php` — Lista archivos JSON de la sede activa.
  - `rev_[nombre_formato].php` — Galería de registros.
  - `visor_[nombre_formato].php` — Vista documental/imprimible.

## 2. Formulario HTML — Sistema Visual Cyberpunk
El estándar visual es el sistema "Cyberpunk" definido en `inspeccion_empaque.html`. Usar exactamente estas variables CSS:

```css
:root {
    --bg-color: #0B0E14;
    --panel-bg: #151A22;
    --accent: #00F0FF;
    --accent-glow: rgba(0, 240, 255, 0.4);
    --text-main: #E2E8F0;
    --text-muted: #94A3B8;
    --border-color: #1E293B;
    --input-bg: #0F172A;
    --danger: #FF3366;
    --warning: #FFB000;
}
```

- **Tipografía**: `Barlow` (texto) + `Space Mono` (monospace/código).
- **Componentes clave**: `.section-card`, `.form-grid`, `.form-group`, `.form-control`, `.btn-submit`.
- **Zonas**: NO agregar selector de sede. Se gestiona automáticamente en backend vía `$_SESSION['sede']`.

## 3. Persistencia — Backend PHP + JSON
El `procesar.php` debe seguir este patrón exacto (basado en `inspeccion empaque/procesar.php`):

```php
<?php
include '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

$sede = $_SESSION['sede'];

// Recibir datos como JSON (NO como FormData)
$input_json = file_get_contents("php://input");
$input_array = json_decode($input_json, true);

$nuevo_registro = [
    'id_registro'  => uniqid('[PREFIJO]_'),
    'timestamp'    => date('Y-m-d H:i:s'),
    'usuario_sys'  => $_SESSION['nombre'],
    'sede_sys'     => $sede,
    'datos'        => $input_array
];

// Ruta: archivos/generados/[modulo]/[sede]/[YYYY-MM].json
$base_dir = "../../archivos/generados/[nombre_formato]/";
$sede_dir = $base_dir . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/";
$archivo_json = $sede_dir . strtoupper("[PREFIJO]") . "_" . date('Y-m') . ".json";

if (!file_exists($base_dir)) { mkdir($base_dir, 0777, true); }
if (!file_exists($sede_dir)) { mkdir($sede_dir, 0777, true); }

$datos_existentes = file_exists($archivo_json)
    ? (json_decode(file_get_contents($archivo_json), true) ?: [])
    : [];

$datos_existentes[] = $nuevo_registro;
file_put_contents($archivo_json, json_encode($datos_existentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['status' => 'success', 'id' => $nuevo_registro['id_registro']]);
?>
```

**IMPORTANTE**: El formulario envía datos como `application/json` con `fetch()`, NO como `FormData`.

## 4. Comunicación Frontend → Backend (fetch estándar)

```javascript
fetch('procesar.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(jsonData)
})
.then(r => r.json())
.then(data => {
    if (data.status === 'success') {
        Swal.fire({ title: '¡REGISTRADO!', icon: 'success',
            background: '#151A22', color: '#fff', confirmButtonColor: '#00F0FF'
        }).then(() => window.location.reload());
    }
});
```

## 5. Galería de Revisiones
- **`listar_jsons.php`**: Busca archivos JSON solo en la carpeta de la sede del usuario activo.
- **`rev_[nombre].php`**: Lista registros y abre el visor con `?file=ruta_archivo&id=id_registro`.

## 6. Visor Documental (PDF)
- **NO usar mPDF** (legacy). Usar `jsPDF` + `html2canvas` para exportación visual.
- El visor debe tener encabezado institucional: Logo, Título del formato, Código, Versión, Sede.
- Estilo: tabla formal sobre fondo oscuro, imprimible con `@media print`.

## 7. Registro en Menús
- Añadir botón de ingreso al menú correspondiente: `template/menu_almacen.html` o `menu_seccion_sur.html` (archivos `.html`, no `.php`).
- Añadir botón de galería al archivo de revisiones correspondiente.
