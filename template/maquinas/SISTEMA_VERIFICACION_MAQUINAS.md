# Sistema de Verificación de Máquinas — Análisis Técnico Completo

> **Ruta base:** `/var/www/fmt/template/maquinas/`  
> **Fecha de análisis:** 2026-07-15  
> **Alcance:** Menú principal, formularios de verificación, persistencia JSON, generación de PDF, rastreo de fechas y gestión de correcciones.

---

## Tabla de Contenidos

1. [Visión General del Sistema](#1-visión-general-del-sistema)
2. [Arquitectura de Archivos](#2-arquitectura-de-archivos)
3. [Fuente de Datos: `maquinas_galeria.json`](#3-fuente-de-datos-maquinas_galeriajson)
4. [Punto de Entrada: `maquinas_menu.php`](#4-punto-de-entrada-maquinas_menúphp)
5. [Motor de Rastreo: `rastreo.php`](#5-motor-de-rastreo-rastréophp)
6. [Formularios de Verificación por Tipo de Máquina](#6-formularios-de-verificación-por-tipo-de-máquina)
   - [6.1 Balanzas (`/balanzas/`)](#61-balanzas-balanzas)
   - [6.2 Básculas (`/bascula/`)](#62-básculas-bascula)
   - [6.3 Camionera (`/camionera/`)](#63-camionera-camionera)
   - [6.4 Flowbalancer (`/flowbalancer/`)](#64-flowbalancer-flowbalancer)
   - [6.5 Imán (`/iman/`)](#65-imán-iman)
   - [6.6 Puntada / Cosedoras (`/puntada/`)](#66-puntada--cosedoras-puntada)
   - [6.7 Equipos de Laboratorio (`/equipos/`)](#67-equipos-de-laboratorio-equipos)
7. [Flujo de Guardado: `maquinasave.php`](#7-flujo-de-guardado-maquinasavephp)
8. [Plantillas de PDF: `creador_html.php` y `creador_html.css`](#8-plantillas-de-pdf-creador_htmlphp-y-creador_htmlcss)
9. [Módulo de Revisión: `revision_maquinas.php`](#9-módulo-de-revisión-revision_maquinasphp)
10. [Rastreo Documental: `rastreo_doc.php`](#10-rastreo-documental-rastreo_docphp)
11. [Módulo de Corrección](#11-módulo-de-corrección)
12. [Estructura de Archivos Generados en Disco](#12-estructura-de-archivos-generados-en-disco)
13. [Bibliotecas PDF Utilizadas](#13-bibliotecas-pdf-utilizadas)
14. [Flujo Completo de Datos (Diagrama)](#14-flujo-completo-de-datos-diagrama)
15. [Notas Técnicas, Inconsistencias y Consideraciones](#15-notas-técnicas-inconsistencias-y-consideraciones)

---

## 1. Visión General del Sistema

El sistema de verificación de máquinas es un módulo del proyecto **FMT (Formatos MAS)** que permite a técnicos de mantenimiento registrar digitalmente las verificaciones periódicas de los equipos y máquinas de la empresa (planta Bogotá — Zona Centro — y planta Pasto — Zona Sur). 

El flujo completo es:

```
Usuario selecciona máquina
        │
        ▼
maquinas_menu.php  ◄── maquinas_galeria.json (catálogo)
        │               rastreo.php (última verificación)
        ▼
Formulario específico por tipo de máquina
(balanzas_zc.php, camionera_bog.php, etc.)
        │
        ▼  POST
maquinasave.php
        │
        ├── JSON de respaldo (datos del formulario)
        └── PDF de verificación  ←── creador_html.php + creador_html.css
                                       (mPDF o TCPDF)
```

Los PDFs y JSONs se almacenan en:
```
/var/www/fmt/archivos/generados/verificaciones/{zona}/{nombre_maquina}/
```

Existe además un módulo de **revisión** (`revision_maquinas.php`) para listar, ver y eliminar los PDFs generados, y un módulo de **corrección** para editar una verificación ya guardada y regenerar su PDF.

---

## 2. Arquitectura de Archivos

```
/var/www/fmt/template/maquinas/
│
├── maquinas_menu.php              ← Menú principal (punto de entrada)
├── maquinas_galeria.json          ← Catálogo de zonas, grupos y códigos
├── rastreo.php                    ← API JSON: última fecha de verificación por máquina
├── rastreo_doc.php                ← API JSON: listado de PDFs existentes por zona/máquina
├── revision_maquinas.php          ← Panel de revisión y gestión de PDFs
├── formulario_correccion.php      ← Formulario HTML de corrección de verificación
├── procesar_correccion.php        ← Procesador de corrección + regeneración de PDF
├── correccion.php                 ← (Legacy) Formulario de corrección anterior
├── debug_log.php                  ← Utilidad de log de errores
│
├── balanzas/
│   ├── balanzas_zc.php            ← Formulario: Balanzas Zona Centro (Bogotá)
│   ├── balanzas_zs.php            ← Formulario: Balanzas Zona Sur (Pasto)
│   ├── maquinasave.php            ← Guardado y generación de PDF (usa mPDF)
│   ├── creador_html.php           ← Plantilla HTML del PDF
│   ├── creador_html.css           ← Estilos del PDF
│   └── carpeta_usuarios.php       ← (Legacy) Guardado por usuario
│
├── bascula/
│   ├── bascula_bogota.php         ← Formulario: Basculas Bogotá
│   ├── bascula_pasto.php          ← Formulario: Basculas Pasto
│   ├── maquinasave.php            ← Guardado con TCPDF
│   ├── creador_html.php           ← Plantilla HTML del PDF
│   ├── creador_html.css           ← Estilos del PDF
│   └── carpeta_usuarios.php
│
├── camionera/
│   ├── camionera_bog.php          ← Formulario: Camionera Bogotá
│   ├── camionera_pas.php          ← Formulario: Camionera Pasto
│   ├── maquinasave.php            ← Guardado con TCPDF
│   ├── creador_html.php
│   ├── creador_html.css
│   └── carpeta_usuarios.php
│
├── flowbalancer/
│   ├── flowbalancer_bogota.php    ← Formulario: Flowbalancer Bogotá
│   ├── flowbalancer_pasto.php     ← Formulario: Flowbalancer Pasto
│   ├── maquinasave.php            ← Guardado con TCPDF
│   ├── creador_html.php           ← Plantilla HTML del PDF (usa mPDF)
│   ├── creador_html.css
│   └── carpeta_usuarios.php
│
├── iman/
│   ├── iman_bog.php               ← Formulario: Imán Bogotá
│   ├── iman_past.php              ← Formulario: Imán Pasto
│   ├── maquinasave.php            ← Guardado con TCPDF
│   ├── creador_html.php
│   ├── creador_html.css
│   └── carpeta_usuarios.php
│
├── puntada/
│   ├── puntada_bog.php            ← Formulario: Cosedoras (Puntada) Bogotá
│   ├── iman_past.php              ← (⚠️ Nombre incorrecto, es puntada Pasto)
│   ├── maquinasave.php            ← Guardado con TCPDF
│   ├── creador_html.php
│   ├── creador_html.css
│   └── carpeta_usuarios.php
│
└── equipos/
    ├── equipos_bog.php            ← Formulario: Otros equipos Bogotá
    ├── equipos_zs.php             ← Formulario: Otros equipos Zona Sur
    ├── maquinasave.php            ← Guardado con TCPDF
    ├── creador_html.php
    ├── creador_html.css
    └── carpeta_usuarios.php
```

---

## 3. Fuente de Datos: `maquinas_galeria.json`

**Ruta:** `/var/www/fmt/template/maquinas/maquinas_galeria.json`

Este archivo JSON es el **catálogo maestro** del sistema. Define la estructura jerárquica de **zona → grupo → lista de códigos**. El menú principal lo consume para construir dinámicamente la interfaz.

### Estructura del JSON

```json
{
  "{zona}": {
    "{grupo_o_modelo}": [
      "{nombre_descriptivo}_{CODIGO_ACTIVO}",
      ...
    ]
  }
}
```

### Zonas y grupos registrados

| Zona          | Grupos                          | Nº Máquinas |
|---------------|---------------------------------|-------------|
| `puntada`     | `PUNTADA_BOG`                   | 7 cosedoras |
| `equipos`     | `EQUIPOS_BOG`, `EQUIPOS_ZS`     | 23 equipos  |
| `camionera`   | `CAMIONERA_BOG`, `CAMIONERA_PAS`| 2 básculas  |
| `bascula`     | `BASCULA_PASTO`, `BASCULA_BOGOTA`| 10 básculas |
| `balanzas`    | `BALANZAS_ZC`, `BALANZAS_ZS`   | 11 balanzas |
| `flowbalancer`| `FLOWBALANCER_PASTO`, `FLOWBALANCER_BOGOTA`| 14 equipos |
| `iman`        | `IMAN_BOG`, `IMAN_PAST`        | 15 imanes   |

### Convención de nombres en el JSON

Cada entrada en la lista de códigos sigue el patrón:
```
{NOMBRE_DESCRIPTIVO}_{CODIGO_ACTIVO}
```
Ejemplo: `"ANALITICA220G_LABBOGBAL01"` → nombre: `ANALITICA220G`, código: `LABBOGBAL01`

Esta convención es **crítica** para el rastreo: el menú separa nombre y código usando el guión bajo (`_`) como delimitador desde el final.

---

## 4. Punto de Entrada: `maquinas_menu.php`

**Ruta:** `/var/www/fmt/template/maquinas/maquinas_menu.php`

### Descripción

Es la **página principal** del sistema de verificación. No tiene autenticación propia (la delegó al sistema padre `redireccion.php`). Muestra un menú desplegable con todas las zonas, grupos y máquinas disponibles, indicando si cada una ha sido verificada recientemente.

### Carga de datos (JavaScript, `DOMContentLoaded`)

Al cargar la página, ejecuta **dos peticiones en paralelo** con `Promise.all()`:

1. **`maquinas_galeria.json`** — catálogo de máquinas
2. **`rastreo.php`** — estado de verificaciones (última fecha)

```javascript
Promise.all([
    fetch("maquinas_galeria.json").then(r => r.json()),
    fetch("rastreo.php").then(r => r.json())
]).then(([zonas, rastreo]) => { ... });
```

### Construcción dinámica del menú

Para cada **zona** en el catálogo:
1. Crea un `<div class="zona-container">` con un `<label>` desplegable
2. Para cada **grupo** dentro de la zona:
   - Crea un `<div class="maquina">` con la imagen del grupo
   - La imagen se busca en: `/fmt/img/MAQUINAS/{zona}/{grupo}.jpeg`; si falla, usa `/fmt/img/default.png`
3. Para cada **código** en el grupo:
   - Normaliza el código para compararlo con los datos de `rastreo.php`
   - Determina la clase visual (`verificada` o `no-verificada`)
   - Muestra la fecha de la última verificación si existe
   - Genera el enlace al formulario específico

### Resolución de la URL del formulario

```javascript
let formatoArchivo = grupo
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")   // quitar tildes
    .toLowerCase()
    .replace(/\s+/g, "_") + ".php";    // espacios → guiones bajos

let urlFormato = `/template/maquinas/${zona.replace(/\s+/g, "_")}/${formatoArchivo}
    ?codigo=${encodeURIComponent(codigo)}
    &maquina=${encodeURIComponent(grupo)}`;
```

**Ejemplo:** zona `balanzas`, grupo `BALANZAS_ZC` → archivo `balanzas_zc.php`

### Función desplegable

```javascript
function menu_desplegable(zonaId) {
    const menu = document.getElementById(zonaId);
    if (menu) {
        menu.style.display = (menu.style.display === "none" || ...) ? "block" : "none";
    }
}
```

### CSS

El menú usa `/css/maquinas_menu.css`. Los estados visuales se aplican con clases:
- `.verificada` → máquina con verificación registrada
- `.no-verificada` → sin verificación o fuera de plazo

---

## 5. Motor de Rastreo: `rastreo.php`

**Ruta:** `/var/www/fmt/template/maquinas/rastreo.php`

### Propósito

Devuelve un JSON con la **última fecha de verificación** de cada máquina, escaneando el sistema de archivos del servidor.

### Algoritmo

1. Escanea el directorio base: `/var/www/fmt/archivos/generados/verificaciones/`
2. Para cada **zona** (subdirectorio):
   - Para cada **máquina** (subdirectorio de zona):
     - Para cada **archivo PDF** encontrado:
       - Extrae el nombre del PDF (sin extensión)
       - Lo divide por `_` para obtener código y fecha

### Parsing del nombre del archivo PDF

**Formato estándar** (≥ 4 partes al separar por `_`):
```
{nombre_maquina}_{codigo}_{fecha_mas_reciente}.pdf
```
- `$fecha = array_pop($partes)` → última parte es la fecha
- `$codigo = array_pop($partes)` → penúltima es el código
- `$nombre_maquina_archivo = array_pop($partes)` → antepenúltima es el nombre
- `$codigo_json = $nombre_maquina_archivo . '_' . $codigo`

**Formato especial Camionera** (detectado cuando la zona es `camionera` y el nombre empieza con `CAMIONERA_`):
```
CAMIONERA_BOG_BOGOTA_{fecha_inicio}_{fecha_fin}.pdf
```
- Se toman las primeras 3 partes como código: `CAMIONERA_BOG_BOGOTA`
- La última parte es la fecha

### Salida JSON

```json
{
  "balanzas": {
    "BALANZAS_ZC_ANALITICA220G": {
      "codigos": [
        {
          "codigo": "BALANZAS_ZC_ANALITICA220G",
          "ultima_verificacion": "2025-06-15"
        }
      ]
    }
  },
  "camionera": { ... }
}
```

El sistema mantiene **solo la fecha más reciente** por máquina (comparación de strings en formato `Y-m-d`).

---

## 6. Formularios de Verificación por Tipo de Máquina

Todos los formularios comparten la misma estructura base:

### Estructura común de los formularios

**Bloque 1 — Precarga de sesión (corrección):**
```php
session_start();
$datos = null;
if (isset($_SESSION['precargar_formulario']) && $_SESSION['precargar_formulario'] === true) {
    $datos = $_SESSION['formulario_cargado'] ?? null;
    unset($_SESSION['formulario_cargado']);
    unset($_SESSION['precargar_formulario']);
}
```
Esto permite que `formulario_correccion.php` pre-rellene el formulario vía sesión.

**Bloque 2 — Inyección de datos precargados (JavaScript):**
```javascript
const datosPrecargados = <?= json_encode($datos, JSON_UNESCAPED_UNICODE) ?>;
window.addEventListener('DOMContentLoaded', () => {
    for (const [campo, valor] of Object.entries(datosPrecargados)) {
        const input = document.querySelector(`[name="${campo}"]`);
        if (input) { input.value = valor; /* o .checked */ }
    }
});
```

**Bloque 3 — Obtención del código desde URL o sesión:**
```php
$codigo_maquina = isset($_GET['codigo']) ? $_GET['codigo'] : "";
$nombre_maquina = isset($_GET['maquina']) ? $_GET['maquina'] : "";
if (!empty($codigo_maquina)) {
    $_SESSION['codigo_maquina'] = $codigo_maquina;
    $_SESSION['nombre_maquina'] = $nombre_maquina;
} else {
    // fallback a sesión
}
```

**Bloque 4 — Campos ocultos comunes del formulario:**
```html
<input type="hidden" name="codigo_maquina" value="...">
<input type="hidden" name="nombre_maquina" value="...">
<input type="hidden" name="tipo_maquina" value="{balanzas|bascula|camionera|flowbalancer|iman|puntada|equipos}">
<input type="hidden" name="formato" value="{nombre_del_formato}">
```

**Bloque 5 — Acciones del formulario:**
- **Botón `submit`** → `"Generar Reporte PDF"` → POST a `maquinasave.php`
- **Botón `guardar`** → `name="accion" value="guardar"` → guarda solo JSON (sin PDF)

### 6.1 Balanzas (`/balanzas/`)

**Archivos:** `balanzas_zc.php` (Zona Centro), `balanzas_zs.php` (Zona Sur)

**Sección 1 — Verificación de Estado y Funcionamiento:**

| Campo | Tipo | Opciones |
|-------|------|---------|
| `estado_general` | `text` | Texto libre |
| `estado_limpieza` | `select` | Cumple / No Cumple |
| `nivelada` | `select` | Cumple / No Cumple |
| `escala` | `select` | Cumple / No Cumple |
| `cargador` | `select` | Cumple / No Cumple |
| `display` | `select` | Cumple / No Cumple |
| `cables_senal` | `select` | Cumple / No Cumple |
| `stickers` | `select` | Cumple / No Cumple / N/A |
| `verificacion_estado` | `select` | No / Si |

**Sección 2 — Verificación de Calibración:**
- Incluye imagen de diagrama de excentricidad
- Peso utilizado (`text`)
- 6 puntos de medición (P1 a P6): `peso_indicador_pN` y `diferencia_pN`
- **Cálculo automático en JS**: diferencia = peso_utilizado − peso_indicador_pN (en tiempo real)
- `verificacion_masas`: Cumple la tolerancia (No / Si)
- `observaciones`: `textarea`

**Característica especial:** El `creador_html.php` de balanzas incluye la función `unidades_balanzas()` que mapea cada modelo al valor de su **escala de lectura** (ej: Analítica 220g → escala 0.02g). Usa comparación insensible a mayúsculas del nombre de carpeta completo.

---

### 6.2 Básculas (`/bascula/`)

**Archivos:** `bascula_bogota.php`, `bascula_pasto.php`

Prácticamente idéntico al formulario de balanzas, con las mismas secciones. Diferencia principal: el `maquinasave.php` de básculas usa **TCPDF** (no mPDF), y la ruta de guardado incluye el código: 
```
/archivos/generados/verificaciones/Bascula/{codigo_maquina}-{nombre_maquina}/
```

**Sección 1 — Verificación de Estado** (campos equivalentes a balanzas):
- `estado_general`, `estado_limpieza`, `estabilidad`, `escala`, `cargador`, `display`, `cables_senal`, `stickers`, `verificacion_estado`

**Sección 2 — Verificación de Calibración** (6 puntos P1–P6):
- Mismo esquema que balanzas con cálculo automático JS

---

### 6.3 Camionera (`/camionera/`)

**Archivos:** `camionera_bog.php`, `camionera_pas.php`

**Sección 1 — Verificación de Estado:**

| Campo | Descripción |
|-------|-------------|
| `estado_general` | Texto libre |
| `Estado de limpieza` | Superficie libre de huecos |
| `Bordes libres de Obstrucciones` | Select Cumple/No cumple |
| `Topes con Holgura` | Select |
| `cargador` | Tapas de acceso |
| `Tarjeta Sumatoria libre de Humedad` | Select |
| `Cables de señal` | Select |
| `Tornilleria Ajustada` | Select |
| `Cojinetes de celda sin desgaste` | Select |
| `Carcamo Limpio` | Select |

**Sección 2 — Verificación de Calibración con Pesas Patrón:**
- 8 celdas (Celda 1–8): indicador + diferencia
- `Verificacion De MASAS`: Cumple/No

**Sección 3 — Verificación con Vehículo:**
- Peso vehículo referencia
- 4 posiciones (Frente, Centro, Atras, Centro): indicador + diferencia
- `cumplimiento vehiculo`: Si/No

> **Nota:** La camionera tiene un formato de nombre especial en el PDF que el `rastreo.php` maneja específicamente (mínimo 5 partes al separar por `_`).

---

### 6.4 Flowbalancer (`/flowbalancer/`)

**Archivos:** `flowbalancer_bogota.php`, `flowbalancer_pasto.php`

**Sección 1 — Estado y Funcionamiento:**

| Campo | Descripción |
|-------|-------------|
| `estado_general` | Texto libre |
| `Estado de limpieza` | Cumple/No cumple |
| `Estabilidad` | Mesa y soportes estables |
| `escala` | Escala de lectura en cero |
| `cargador` | Celda de carga |
| `Display` | Estado del display |
| `Cables de señal` | Select |
| `Membrana de Presion` | Select (específico de flowbalancer) |
| `Verificacion De Estado` | No/Si |

**Sección 2 — Masas Patrón** (específico de flowbalancer):
- `Patron Utilizado`: valor en KG
- `Con masas patron WT`: número
- `Sin masas patron WT`: número
- `Con masas patron ZERO`: número
- `Sin masas patron ZERO`: número
- `Verificacion De MASAS`: No/Si

---

### 6.5 Imán (`/iman/`)

**Archivos:** `iman_bog.php`, `iman_past.php`

El formulario más simple del sistema. Solo tiene verificación de estado (sin sección de calibración).

**Campos:**

| Campo | Descripción |
|-------|-------------|
| `estado_general` | Texto libre |
| `Acople` | Cumple/No cumple |
| `Soporte` | Cumple/No cumple |
| `Manija` | Cumple/No cumple |
| `Estructura` | Cumple/No cumple |
| `Tornilleria Completa y Ajustada` | Cumple/No cumple |
| `Observaciones` | Textarea |

---

### 6.6 Puntada / Cosedoras (`/puntada/`)

**Archivos:** `puntada_bog.php`, `iman_past.php` (⚠️ nombre de archivo incorrecto en el directorio)

Formulario reducido para cosedoras. Solo verificación de estado operativo.

**Campos:**

| Campo | Descripción |
|-------|-------------|
| `estado_general` | Texto libre |
| `Puntada` | Cumple/No cumple |
| `Lubricacion` | Cumple/No cumple |
| `Observaciones` | Textarea |

---

### 6.7 Equipos de Laboratorio (`/equipos/`)

**Archivos:** `equipos_bog.php`, `equipos_zs.php`

Para equipos de laboratorio y metrología. Los campos incluyen aspectos propios de instrumentos de medición.

**Campos:**

| Campo | Descripción |
|-------|-------------|
| `estado_general` | Texto libre |
| `Estado de limpieza` | Cumple/No cumple |
| `Conexion USB` | Cumple/No cumple/N/A |
| `Pulsador` | Cumple/No cumple/N/A |
| `cargador` | Cumple/No cumple/N/A |
| `Display` | Cumple/No cumple/N/A |
| `Bateria` | Cumple/No cumple/N/A |
| `Recipiente` | Cumple/No cumple/N/A |
| `Limpieza Filtro` | Cumple/No cumple/N/A |
| `Verificacion De Estado` | No/Si |
| `Observaciones` | Textarea |

> **Nota:** El `maquinasave.php` de `equipos` usa **TCPDF** directamente, sin pasar por `creador_html.php`. La generación del PDF itera dinámicamente sobre `$_POST` para armar la tabla de resultados.

---

## 7. Flujo de Guardado: `maquinasave.php`

Cada subdirectorio tiene su propio `maquinasave.php`. Todos comparten la lógica siguiente al recibir un `POST`:

### Parámetros recibidos

| Parámetro | Origen | Descripción |
|-----------|--------|-------------|
| `codigo_maquina` | `hidden` | Código del activo |
| `nombre_maquina` | `hidden` | Nombre del grupo/modelo |
| `tipo_maquina` | `hidden` | Subdirectorio del tipo (balanzas, iman, etc.) |
| `zona` | `hidden` (algunos) | Zona (ej: `balanzas`, `equipos`) |
| `formato` | `hidden` | Identificador del formato |
| `accion` | `button[name]` | `"guardar"` o vacío |
| + todos los campos del formulario | `inputs` | Datos de verificación |

### Lógica de guardado (simplificada)

```php
$fecha_actual = date("Y-m-d");
$usuario = $_SESSION['nombre'] ?? 'anonimo';
$ruta_maquina = "/var/www/fmt/archivos/generados/verificaciones/{$zona}/{$nombre_maquina}/";

if (!file_exists($ruta_maquina)) mkdir($ruta_maquina, 0777, true);

$nombre_archivo = "{$nombre_maquina}_{$codigo_maquina}_{$fecha_actual}";
$ruta_pdf  = $ruta_maquina . $nombre_archivo . ".pdf";
$ruta_json = $ruta_maquina . $nombre_archivo . ".json";
```

### Modo "Guardar" (solo JSON, sin PDF)

```php
if ($accion === 'guardar') {
    file_put_contents($ruta_json, json_encode($_POST, JSON_PRETTY_PRINT));
    exit; // No genera PDF, no redirige
}
```

### Modo "Generar PDF" (flujo completo)

1. **Genera el HTML** incluyendo `creador_html.php` con `ob_start()`
2. **Crea el PDF** con mPDF o TCPDF
3. **Guarda el JSON** de respaldo con los datos + usuario + nombre del PDF
4. **Redirige** a `../maquinas_menu.php`

### Diferencias entre `maquinasave.php` según tipo

| Tipo | Librería PDF | Plantilla HTML | Nota especial |
|------|-------------|----------------|---------------|
| `balanzas` | **mPDF** | `creador_html.php` | Marca de agua con logo |
| `bascula` | **TCPDF** | Generación dinámica | Ruta incluye código |
| `camionera` | **TCPDF** | Generación dinámica | — |
| `flowbalancer` | **TCPDF** | Generación dinámica | — |
| `iman` | **TCPDF** | Generación dinámica | — |
| `puntada` | **TCPDF** | Generación dinámica | — |
| `equipos` | **TCPDF** | Generación dinámica | — |

---

## 8. Plantillas de PDF: `creador_html.php` y `creador_html.css`

El tipo **balanzas** y **flowbalancer** tienen `creador_html.php` propio para generar el PDF con mPDF. Los demás tipos usan TCPDF con generación dinámica directamente en `maquinasave.php`.

### `creador_html.php` de **Balanzas** — estructura del documento PDF

```
┌─────────────────────────────────────────┐
│  LOGO  │  "somos más que harina"        │
├─────────────────────────────────────────┤
│         VERIFICACIÓN DE BALANZA         │
│  Código: XXX  │  Nombre: YYY  │  Fecha  │
│  Técnico: AAA                           │
│  [Técnico que revisa: BBB] (si existe)  │
├─────────────────────────────────────────┤
│         [Imagen genérica de balanza]    │
├─────────────────────────────────────────┤
│  VERIFICACIÓN DE ESTADO Y FUNCIONAMIENTO│
│  ┌──────────────────┬────────────────┐  │
│  │ Chequeo          │ Resultado      │  │
│  ├──────────────────┼────────────────┤  │
│  │ Estado general   │ [valor]        │  │
│  │ ...              │ ...            │  │
│  └──────────────────┴────────────────┘  │
├─────────────────────────────────────────┤
│  Escala de lectura: [unidad según modelo]│
├─────────────────────────────────────────┤
│  VERIFICACIÓN DE CALIBRACIÓN            │
│  ┌─────────┬────┬────────┬──────────┐   │
│  │ [img]   │ Pn │ Peso   │ Diferencia│   │
│  │ excent. │... │Indicad.│          │   │
│  └─────────┴────┴────────┴──────────┘   │
│  Peso utilizado: [valor]                │
│  ¿Cumple Tolerancia? [valor]           │
├─────────────────────────────────────────┤
│  OBSERVACIONES                          │
│  [texto libre]                          │
└─────────────────────────────────────────┘
```

### `creador_html.php` de **Flowbalancer** — secciones

1. **Header** (logo empresa + subtítulo)
2. **Datos de la máquina** (código, nombre, fecha, código de orden si existe)
3. **Técnico** y **Técnico que revisa** (si aplica corrección)
4. **Imagen de referencia** del equipo
5. **Tabla: Verificación de Estado y Funcionamiento** (9 filas)
6. **Tabla: Verificación de Masas Patrón** (6 filas: WT y ZERO con/sin masas)
7. **Observaciones**

### Marca de agua (solo mPDF — balanzas)

```php
$mpdf->SetWatermarkImage($logo_empresa, 0.1, [150,150]);
$mpdf->showWatermarkImage = true;
// ... contenido ...
$mpdf->showWatermarkImage = false; // Se desactiva antes de guardar
```

---

## 9. Módulo de Revisión: `revision_maquinas.php`

**Ruta:** `/var/www/fmt/template/maquinas/revision_maquinas.php`

### Propósito

Panel de administración que permite:
- **Ver** todos los PDFs generados, organizados por zona y máquina
- **Seleccionar** múltiples PDFs para eliminar en lote
- **Abrir** un PDF en nueva pestaña
- **Corregir** una verificación (redirige a `formulario_correccion.php`)

### Autenticación

```php
require_once '../sesion.php';
verificarAutenticacion();
```

Además, tiene verificación AJAX de sesión cada 10 segundos:
```javascript
setInterval(function() {
    verificarSesionAjax(function(activa) { ... });
}, 10000);
```

### Carga de datos

Hace fetch a `rastreo_doc.php` para obtener el árbol de PDFs existentes.

### Renderizado

Para cada zona → máquina → PDF:
- Checkbox de selección (`.pdf-checkbox`)
- Icono del tipo de archivo
- Nombre del PDF
- Botón **Ver** → abre `/archivos/generados/verificaciones/{zona}/{maquina}/{pdf}` en nueva pestaña
- Botón **Corregir** → llama a `corregirPDF()` con zona, máquina, archivo

### Diferenciación visual por planta

```javascript
if (maquina.toLowerCase().includes('bog')) colorClass = 'maquina-bogota';
else if (maquina.toLowerCase().includes('past')) colorClass = 'maquina-pasto';
```

### Eliminación de PDFs

```javascript
fetch('/template/eliminar_archivo_maquinas.php', {
    method: 'POST',
    body: formData   // archivos[] con rutas relativas
});
```

> **⚠️ Nota:** El archivo `/template/eliminar_archivo_maquinas.php` es referenciado pero **no se encontró en el sistema de archivos** del proyecto durante este análisis. Podría estar pendiente de implementación o en otra ruta.

---

## 10. Rastreo Documental: `rastreo_doc.php`

**Ruta:** `/var/www/fmt/template/maquinas/rastreo_doc.php`

### Propósito

Similar a `rastreo.php`, pero devuelve la **lista completa de PDFs** (no solo la fecha más reciente). Lo consume `revision_maquinas.php`.

### Algoritmo

```
Escanear /var/www/fmt/archivos/generados/verificaciones/
  └── Para cada zona (directorio)
        └── Para cada máquina (subdirectorio)
              └── Para cada archivo .pdf
                    └── Añadir a $resultado[zona][maquina]['pdfs'][]
```

### Salida JSON

```json
{
  "balanzas": {
    "BALANZAS_ZC": {
      "pdfs": [
        "BALANZAS_ZC_ANALITICA220G_LABBOGBAL01_2025-06-15.pdf",
        "BALANZAS_ZC_ANALITICA220G_LABBOGBAL01_2025-05-12.pdf"
      ]
    }
  }
}
```

---

## 11. Módulo de Corrección

### 11.1 `formulario_correccion.php`

**Ruta:** `/var/www/fmt/template/maquinas/formulario_correccion.php`

Lee el JSON asociado al PDF a corregir y presenta un formulario editable.

**Parámetros GET esperados:**

| Parámetro | Descripción |
|-----------|-------------|
| `zona` | Zona de la máquina |
| `maquina` | Nombre de la carpeta de la máquina |
| `archivo` | Nombre del archivo PDF |
| `formato` | Tipo de máquina (subdirectorio) |
| `fecha` | Fecha del PDF |

**Extrae el JSON correspondiente:**
```
/archivos/generados/verificaciones/{zona}/{maquina}/{archivo_sin_pdf}.json
```

**Genera el formulario dinámicamente** iterando sobre los campos del JSON:
```php
foreach ($datos as $campo => $valor):
    if ($campo === 'archivo_pdf') continue;
    // Muestra input text con valor precargado
endforeach;
```

**Campos adicionales fijos:**
- `tecnico_correccion`: nombre del técnico de la sesión actual
- `codigo_orden`: requerido para registrar la corrección

**Campos ocultos enviados:**
- `json_path`, `zona`, `maquina`, `archivo_pdf`, `formato`, `fecha`

### 11.2 `procesar_correccion.php`

**Ruta:** `/var/www/fmt/template/maquinas/procesar_correccion.php`

Procesa la corrección: mueve los archivos originales a un directorio de respaldo y regenera el PDF.

**Flujo:**

```
1. Validar que existe el código de orden
2. Definir rutas:
   - ruta_maquina = /archivos/generados/verificaciones/{zona}/{maquina}/
   - carpetaCorrecciones = ruta_maquina → reemplazando "generados" por "correcciones"
3. Crear carpeta de correcciones si no existe
4. Mover JSON original → carpeta de correcciones
5. Mover PDF original → carpeta de correcciones
6. Guardar nuevo JSON con los datos de $_POST
7. Incluir {tipo_maquina}/creador_html.php para renderizar HTML
8. Generar nuevo PDF con mPDF (con CSS de {tipo_maquina}/creador_html.css)
9. Actualizar el campo archivo_pdf en el nuevo JSON
10. Redirigir → revision_maquinas.php
```

**Estructura de directorios para correcciones:**
```
/archivos/correcciones/verificaciones/{zona}/{maquina}/
    ├── {original}.pdf    ← copia del PDF anterior
    └── {original}.json   ← copia del JSON anterior
```

**Selección de la plantilla correcta:**
```php
$creador_html_path = __DIR__ . "/{$tipo_maquina}/creador_html.php";
$css_path = __DIR__ . "/{$tipo_maquina}/creador_html.css";
```

> **⚠️ Importante:** `procesar_correccion.php` siempre usa **mPDF** para regenerar el PDF, incluso para tipos que originalmente usaron TCPDF. Esto puede causar diferencias de formato entre el PDF original y el corregido en tipos como `bascula`, `equipos`, etc.

---

## 12. Estructura de Archivos Generados en Disco

```
/var/www/fmt/archivos/
├── generados/
│   └── verificaciones/
│       ├── balanzas/
│       │   └── BALANZAS_ZC/
│       │       ├── BALANZAS_ZC_ANALITICA220G_LABBOGBAL01_2025-06-15.pdf
│       │       └── BALANZAS_ZC_ANALITICA220G_LABBOGBAL01_2025-06-15.json
│       ├── bascula/
│       │   └── BASCULA_BOGOTA_BBG MOGOLLA-EMPBOGBA S02/
│       │       └── ...
│       ├── camionera/
│       │   └── CAMIONERA_BOG/
│       │       └── CAMIONERA_BOG_BOGOTA_{fecha_inicio}_{fecha_fin}.pdf
│       ├── equipos/
│       ├── flowbalancer/
│       ├── iman/
│       └── puntada/
│
└── correcciones/
    └── verificaciones/
        └── {zona}/{maquina}/
            └── {original_antes_de_corregir}.pdf
            └── {original_antes_de_corregir}.json
```

### Convención del nombre de archivo

**Estándar:**
```
{nombre_maquina}_{codigo_maquina}_{YYYY-MM-DD}.pdf
```
Ejemplo: `BALANZAS_ZC_ANALITICA220G_LABBOGBAL01_2025-06-15.pdf`

**Camionera (excepción):**
```
CAMIONERA_BOG_BOGOTA_{fecha_inicio}_{fecha_fin}.pdf
```

---

## 13. Bibliotecas PDF Utilizadas

### mPDF (Balanzas y correcciones)

```php
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_top' => 10,
    'margin_bottom' => 10,
    'margin_left' => 10,
    'margin_right' => 10
]);
$mpdf->SetWatermarkImage($logo_empresa, 0.1, [150,150]);
$mpdf->showWatermarkImage = true;
$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
$mpdf->Output($ruta_pdf, \Mpdf\Output\Destination::FILE);
```

**Capacidades:** Renderizado completo de HTML/CSS, soporte a imágenes, marca de agua, márgenes personalizados.

### TCPDF (Mayoría de tipos de máquina)

Usado mediante la clase `CustomPDF extends TCPDF`, con un método `Header()` personalizado que renderiza el encabezado corporativo:

```php
class CustomPDF extends TCPDF {
    public function Header() {
        if ($this->getPage() == 1) {
            // Tabla HTML con logo + título + código del documento
        }
    }
}
$pdf = new CustomPDF();
$pdf->AddPage();
$pdf->Image($imagen_path, 15, $pdf->GetY(), 80, 50);
$pdf->Cell(80, 8, $campo, 1, 0, 'L');
$pdf->Output($ruta_pdf, 'F');
```

---

## 14. Flujo Completo de Datos (Diagrama)

```
                    ┌─────────────────────┐
                    │  maquinas_galeria   │
                    │  .json              │
                    │  (catálogo maestro) │
                    └──────────┬──────────┘
                               │ fetch()
┌──────────────────┐           ▼
│   rastreo.php    │──► maquinas_menu.php
│  (API última     │    (menú visual con
│   verificación)  │     estados: verde/rojo)
└──────────────────┘           │
                               │ Click en máquina
                               ▼
                    ┌──────────────────────┐
                    │  Formulario PHP       │
                    │  específico por tipo  │
                    │  ?codigo=XXX&maquina=YYY│
                    └──────────┬───────────┘
                               │
               ┌───────────────┴───────────────┐
               │                               │
        [Botón Guardar]               [Botón Generar PDF]
               │                               │
               ▼                               ▼
    maquinasave.php                  maquinasave.php
    accion=guardar                   (sin accion)
               │                               │
               ▼                               ├──► include creador_html.php
    Guarda solo JSON                 │          (balanzas/flowbalancer)
    (borradores)                     │          o generación dinámica
                                     │          (otros tipos)
                                     │
                                     ├──► mPDF o TCPDF
                                     │
                                     ▼
                          ┌─────────────────────┐
                          │ PDF + JSON guardados│
                          │ en /archivos/        │
                          │ generados/           │
                          │ verificaciones/      │
                          │ {zona}/{maquina}/    │
                          └──────────┬──────────┘
                                     │
                                     │ Redirect
                                     ▼
                          maquinas_menu.php
                          (ahora muestra "verificada")

─────────────────────────────────────────────────────

MÓDULO DE GESTIÓN:

revision_maquinas.php
    │
    ├── fetch rastreo_doc.php  ──► lista todos los PDFs
    │
    ├── [Ver] → abre PDF directamente en navegador
    │
    ├── [Corregir] →
    │       formulario_correccion.php
    │           │ Lee JSON asociado
    │           │ Muestra formulario editable
    │           │ POST →
    │           ▼
    │       procesar_correccion.php
    │           │ Mueve originales a /correcciones/
    │           │ Guarda nuevo JSON
    │           │ Regenera PDF con mPDF
    │           │ Redirect →
    │           ▼
    │       revision_maquinas.php
    │
    └── [Eliminar seleccionados] →
            POST a eliminar_archivo_maquinas.php
            (⚠️ archivo pendiente/no encontrado)
```

---

## 15. Notas Técnicas, Inconsistencias y Consideraciones

### ⚠️ Inconsistencias detectadas

1. **Nombre de archivo incorrecto en `/puntada/`:**  
   Existe `iman_past.php` dentro del directorio `puntada/`. Este archivo corresponde al formulario de puntada para Pasto, pero tiene nombre de imán. Puede causar confusión y podría impedir la resolución de URL desde `maquinas_menu.php`.

2. **Mezcla de librerías PDF:**  
   - `balanzas` y `flowbalancer` usan **mPDF** con `creador_html.php`  
   - El resto usa **TCPDF** con generación dinámica  
   - `procesar_correccion.php` **siempre** usa mPDF al corregir, incluso para tipos originalmente con TCPDF → PDFs corregidos pueden verse distintos a los originales.

3. **`carpeta_usuarios.php` con ruta Windows:**  
   ```php
   $ruta_base = "C:/xampp/htdocs/fmt/archivos/formularios_guardados/";
   ```
   Esta es una ruta de desarrollo local (Windows/XAMPP) que **no funciona en producción** Linux. Este archivo parece ser un legacy de una versión anterior del sistema.

4. **`correccion.php` con ruta Windows:**  
   ```php
   $jsonPath = "C:/xampp/htdocs/fmt/archivos/verificaciones/{$formato}/{$jsonFile}";
   ```
   Mismo problema de ruta. Es un módulo legado que ya fue reemplazado por `formulario_correccion.php`.

5. **`eliminar_archivo_maquinas.php` no encontrado:**  
   El archivo es referenciado desde `revision_maquinas.php` pero no existe en el sistema de archivos analizado. La funcionalidad de eliminación masiva de PDFs no está operativa.

6. **`debug_log.php` con ruta Windows:**  
   ```php
   $logDir = "C:/xampp/htdocs/fmt/debug/";
   ```
   El log de debug escribe en una ruta Windows inexistente en el servidor de producción.

7. **Zona hardcodeada en `bascula/maquinasave.php`:**  
   La ruta de guardado usa `Bascula` (con mayúscula) como zona fija:  
   ```php
   $ruta_maquina = "/archivos/generados/verificaciones/Bascula/{$codigo_maquina}-{$nombre_maquina}/";
   ```
   Mientras que `rastreo.php` espera `bascula` (minúscula). Esto puede afectar el rastreo de la última verificación.

8. **`tipo_maquina` vs `zona`:**  
   Algunos formularios envían `tipo_maquina` y otros usan `zona`. El `maquinasave.php` de cada tipo interpreta estos campos de forma distinta. Hay poca uniformidad en la nomenclatura.

### ✅ Aspectos sólidos del diseño

1. **Separación clara por tipo de máquina:** Cada subdirectorio es autónomo con su propio formulario, guardador y plantilla PDF.

2. **Precarga de datos vía sesión para corrección:** El mecanismo de `$_SESSION['precargar_formulario']` es elegante para reutilizar los formularios originales en el flujo de corrección.

3. **JSON de respaldo:** Todo formulario guardado genera automáticamente un `.json` como respaldo, lo que permite correcciones y auditorías posteriores.

4. **Rastreo de fecha automático:** El sistema deduce el estado de verificación escaneando el sistema de archivos, sin depender de base de datos.

5. **Cálculo JS en tiempo real:** En los formularios de balanzas/básculas, la diferencia de calibración se calcula automáticamente al introducir los valores del indicador y el peso utilizado.

6. **Verificación de sesión AJAX:** `revision_maquinas.php` verifica la sesión cada 10 segundos evitando que un usuario con sesión expirada siga viendo el panel de administración.

---

*Documento generado mediante análisis estático del código fuente. Última actualización: 2026-07-15.*
