---
description: Proceso estandarizado para conectar un módulo existente (nuevo o legacy) con la Central Documental — galería, subida a SharePoint y buscador híbrido.
---

# Guía Maestra de Integración con Central Documental (Skill)

Esta guía documenta cómo conectar cualquier módulo del sistema (que ya guarda
sus registros en JSON, según `crear-formato.md`) con el **HUB Maestría
Documental** (`template/central_documental/`), para que sus archivos queden
**procesados** (PDF), **enviados** (SharePoint), **revisados** (galería) y
**filtrados** (buscador híbrido local+SharePoint).

> **Caso de referencia real**: la integración de `maquinas_v2` (2026-07),
> el primer módulo con persistencia **anidada** (`tipo/grupo/codigo.json`) en
> vez de un JSON plano por sede/mes. Sirve como plantilla para cualquier
> módulo con estructura no trivial.

## Paso 0 — Diagnóstico previo (SIEMPRE PRIMERO)

Antes de tocar código, verifica:
1. **¿El módulo ya tiene tarjeta en `hub_reportes.php`?** No asumas que
   "aparece en `galeria_unificada.php`" significa que es alcanzable — hoy
   (2026-07) la mayoría de entradas del `$modulosMap` de `galeria_unificada.php`
   (hseq, termohigrometros, liberaciones, mantenimiento, los `ver_*` legacy,
   etc.) **no tienen tarjeta** en `hub_reportes.php` y solo se acceden
   escribiendo la URL a mano. Si tu módulo debe ser usable de verdad, el paso
   5 (tarjeta en el hub) NO es opcional.
2. **¿La persistencia es plana o anidada?** Un JSON por sede/mes (como
   `molienda`, `empaque_v2`) encaja directo en los tipos `json` / `json_daily`
   ya soportados. Una estructura con más niveles (como
   `maquinas_v2/{tipo}/{grupo}/{codigo}.json`) necesita un `tipo` de escaneo
   nuevo en `galeria_unificada.php` (ver Paso 1).
3. **¿Cada archivo local es "un documento" o "un histórico acumulado"?** Si
   un mismo archivo JSON contiene múltiples registros históricos (como el
   `codigo.json` de una máquina, que acumula todas sus verificaciones), la
   galería debe listar **un ítem por registro**, no por archivo — y la subida
   a SharePoint debe aislar solo el registro seleccionado (ver Paso 2).

## 1. Galería Unificada — `template/central_documental/galeria_unificada.php`

Añade una entrada en `$modulosMap` (línea ~24):
```php
'mi_modulo' => ['nombre' => 'Nombre Visible', 'ruta' => '../../archivos/generados/mi_modulo/', 'tipo' => 'json'],
```
Tipos ya soportados: `json` (un archivo = un documento), `json_daily`
(un archivo mensual, un ítem por registro con campo `fecha`), `excel`/`text`
(archivos crudos), y `sede_scoped: true` si la ruta usa `{SEDE}`.

Si tu estructura es anidada (más de un nivel de carpetas antes de llegar al
JSON), agrega un `elseif ($mod['tipo'] === 'mi_tipo_nested')` en el bucle de
escaneo (línea ~86-121) que recorra los niveles y arme un ítem por registro
individual, con los campos extra necesarios para identificarlo sin ambigüedad
(ver el caso `maquinas_nested` como plantilla). Esos campos extra deben:
- añadirse como `data-*` en el checkbox `.rec-check` del item-card (~línea 437),
  condicionados a que existan (`isset($it['campo_extra'])`), para no romper
  los demás módulos;
- propagarse en `getSelectedFilesInfo()` (JS) al objeto que se envía a
  `sharepoint_upload.php`.

## 2. Subida a SharePoint — `template/central_documental/sharepoint_upload.php`

Agrega un `case 'mi_modulo':` al switch de la ETAPA A (línea ~92-138):
- Si el módulo tiene un **visor HTML** (como todos los del estándar nuevo),
  arma `$urlToRender` apuntando a su `visor_*.php` y deja que Puppeteer
  (`generate_pdf_headless.js`) lo convierta a PDF — es el mismo mecanismo
  para todos los módulos, no hay que tocar Puppeteer.
- **Flujo bilateral** (cuando el archivo local es un histórico acumulado, no
  un documento suelto): extrae solo el registro/subconjunto relevante del
  `$ruta` original, escríbelo en un JSON atómico temporal
  (`dirname($ruta) . "/Prefijo_{clave}_{fecha}.json"`), y en la ETAPA C
  (línea ~207-265) extiende la condición bilateral (hoy solo cubre
  `molienda_v2`) para que también suba y luego borre ese JSON atómico.
- El nombre del PDF/JSON atómico **debe incluir `YYYY-MM-DD`** — el uploader
  usa esa fecha (regex en `uploader_selective.js`) para decidir en qué
  carpeta de mes cae en SharePoint.

## 3. Mapeo de carpeta — `template/uploader_selective.js`

Añade tu carpeta local al `folderMap` (línea ~48):
```js
'mi_modulo': 'Nombre Legible En SharePoint',
```
Sin esta entrada, el uploader sube igual pero con el nombre técnico de la
carpeta local en vez de uno legible.

## 4. Buscador híbrido (opcional pero recomendado) — `sp_reader.php`

Si el módulo tiene un identificador natural para buscar (lote, código,
número de orden), agrega:
- una función `localXxxByYyy()` que escanee `archivos/generados/` en PHP puro;
- una acción nueva (`if ($action === 'search_mi_modulo_by_x') { ... }`) que
  haga la búsqueda local, luego la búsqueda en SharePoint vía
  `runSpReader('list'|'read', ruta)`, y fusione con `mergeByFile()` o
  `mergeTurnos()` (o una variante) marcando `source: local|sharepoint|both`.
- **Cuidado con el timeout de 60s** (`ini_set('max_execution_time', 60)`):
  cada llamada a `sp_reader.js` es un proceso Node + auth MSAL (~1-2s). Dos
  formas de acotar el barrido, según qué buscas:
  - **Por identificador único** (código, lote): resuelve primero la ubicación
    exacta por config/catálogo local (sin red) y limita el barrido de
    SharePoint a esa única carpeta × N meses — igual que
    `search_molienda_by_lote` / `search_maquinas_by_codigo`.
  - **Por fecha, cuando el archivo/PDF subido lleva la fecha en el nombre**
    (patrón `Prefijo_{clave}_{fecha}_{id}.{pdf,json}` del Paso 2): la fecha ya
    fija el mes exacto (no hay que probar varios), así que el barrido es
    **listar** (no leer) cada carpeta relevante de ese único mes y filtrar
    por nombre de archivo — solo se hace `read` sobre los que ya calzaron por
    nombre. Con esta técnica, `search_maquinas_by_fecha` recorre las ~13
    carpetas tipo×grupo con 1 `list` cada una, sin necesidad de leer el
    contenido de archivos que no coincidan. Antes de descartar una búsqueda
    por fecha por "muy cara", confirma si el nombre de archivo ya trae la
    fecha — si es así, casi siempre es viable.

## 5. Tarjeta y UI en el Hub — `hub_reportes.php`

- Agrega el módulo a `$categorias` (línea ~5-13) — sin esto, el módulo queda
  "integrado" pero invisible, el mismo problema del Paso 0.
- Si agregaste buscador (Paso 4), añade: una pestaña más en `.search-tabs`,
  su `search-form-panel`, una sección de resultados
  (`xxxHeader`/`spXxxStatusLine`/`spXxxResultsGrid` — copiar el patrón de
  Empaque/Bulto), las funciones JS `xxxSearch()` / `fetchXxxByYyy()` /
  `renderXxxCard()`, y registrar el tab nuevo en `switchTab()` y `clearResults()`.

## Checklist final

- [ ] Entrada en `$modulosMap` de `galeria_unificada.php` (+ tipo de escaneo si aplica)
- [ ] `case` en `sharepoint_upload.php` (+ flujo bilateral si el archivo es un histórico)
- [ ] Entrada en `folderMap` de `uploader_selective.js`
- [ ] Acción de búsqueda en `sp_reader.php` (opcional)
- [ ] Tarjeta en `$categorias` de `hub_reportes.php` — **sin esto no es alcanzable**
- [ ] Tab + resultados + JS en `hub_reportes.php` si hay buscador
- [ ] `php -l` sobre los 3 archivos PHP tocados y `node --check` sobre `uploader_selective.js`
- [ ] Nunca ejecutar una subida real de prueba contra SharePoint sin que el usuario lo pida explícitamente (sube archivos reales a producción)
