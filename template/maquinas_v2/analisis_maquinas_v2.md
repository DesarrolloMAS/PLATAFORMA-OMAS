# 📋 Análisis Completo: Módulo `maquinas_v2`

**Ruta:** `/var/www/fmt/template/maquinas_v2/`  
**Sistema:** PLATAFORMA-OMAS · Área de Mantenimiento — Mecánicos  
**Versión:** V2 · Sistema JSON Interconectado  
**Zonas cubiertas:** Bogotá (ZC) · Pasto (ZS)

---

## 1. Propósito General

`maquinas_v2` es un **módulo de verificación periódica de equipos y máquinas industriales** utilizado por el área de Mantenimiento. Permite que los técnicos mecánicos registren, guarden, corrijan y consulten verificaciones de estado y calibración de distintos tipos de máquinas (balanzas, básculas, flowbalancers, imanes, cosedoras y equipos de laboratorio).

A diferencia de la versión anterior (v1), que generaba PDFs físicos, **esta versión persiste todos los registros en archivos JSON** en el servidor, lo cual permite búsqueda, edición (correcciones), eliminación granular y generación de PDF bajo demanda desde el visor.

---

## 2. Árbol de Archivos

```
template/maquinas_v2/
│
├── css/
│   └── maquinas_v2.css          ← Hoja de estilos (Tema Cyberpunk - ámbar)
│
├── config_formularios.json      ← Configuración dinámica de todos los tipos de máquinas
├── maquinas_galeria.json        ← Catálogo de máquinas por zona y grupo (inventario)
│
├── maquinas_menu.php            ← PANTALLA 1: Menú / catálogo visual de máquinas
├── formulario.html              ← PANTALLA 2: Formulario de verificación (dinámico)
├── formulario_correccion.php    ← PUENTE: Pre-carga datos y redirige a formulario para corrección
├── revision_maquinas.php        ← PANTALLA 3: Historial y revisión de registros guardados
├── visor_verificacion.php       ← PANTALLA 4: Visor documental con exportación PDF
│
├── procesar.php                 ← API PHP: Guarda/registra verificaciones (POST JSON)
├── rastreo.php                  ← API PHP: Devuelve última verificación por máquina (GET JSON)
├── listar_registros.php         ← API PHP: Devuelve todos los registros agrupados (GET JSON)
└── eliminar_registro.php        ← API PHP: Elimina un registro específico (POST JSON)
```

**Persistencia en disco:**
```
archivos/generados/maquinas_v2/
└── {tipo_maquina}/
    └── {nombre_grupo}/
        └── {codigo_maquina}.json   ← Array de registros históricos de esa máquina
```

---

## 3. Flujo General del Sistema

```
                    ┌────────────────────────────────────────┐
                    │         maquinas_galeria.json          │
                    │  (inventario: zonas > grupos > códigos)│
                    └────────────────┬───────────────────────┘
                                     │ fetch()
                    ┌────────────────▼───────────────────────┐
                    │          maquinas_menu.php             │
                    │  Carga galería + rastreo.php           │
                    │  Muestra menú con estado verificación  │
                    └────────────────┬───────────────────────┘
                                     │ Click en máquina
                    ┌────────────────▼───────────────────────┐
                    │           formulario.html              │
                    │  ?tipo=&codigo=&maquina=               │
                    │  Carga config_formularios.json         │
                    │  Renderiza campos dinámicamente        │
                    └──────┬─────────────────────────────────┘
                           │                    │
                    [Guardar Borrador]    [Generar Verificación]
                           │                    │
                    ┌──────▼────────────────────▼─────────────┐
                    │             procesar.php                │
                    │  Escribe JSON en archivos/generados/    │
                    │  estado: "borrador" | "verificado"      │
                    └─────────────────────────────────────────┘
                                     │ Redirige a menú
                    ┌────────────────▼───────────────────────┐
                    │        revision_maquinas.php           │
                    │  fetch(listar_registros.php)           │
                    │  Lista todos los registros históricos  │
                    └──────┬─────────────────────────────────┘
                           │                    │
                    [📂 Ver]              [✏️ Corregir]
                           │                    │
         ┌─────────────────▼──┐   ┌────────────▼───────────────────┐
         │ visor_verificacion │   │   formulario_correccion.php    │
         │ Muestra el doc.    │   │   Precarga sessionStorage      │
         │ Exporta PDF        │   │   Redirige → formulario.html   │
         └────────────────────┘   │   con corrige_id=              │
                                  └────────────────────────────────┘
```

---

## 4. Archivos de Configuración (JSON)

### 4.1 `maquinas_galeria.json` — Inventario de Máquinas

Define el **catálogo completo de máquinas** organizadas por:
- **tipo** (clave que conecta con `config_formularios.json`)
- **grupo** (nombre de la carpeta física / nombre visual del subgrupo)
- **códigos** (array de identificadores individuales de cada máquina)

| Tipo | Grupos | Cantidad de equipos |
|------|--------|---------------------|
| `puntada` | PUNTADA_BOG | 7 cosedoras |
| `equipos` | EQUIPOS_BOG, EQUIPOS_ZS | 22 + 11 equipos de laboratorio |
| `camionera` | CAMIONERA_BOG, CAMIONERA_PAS | 1 + 1 básculas camioneras |
| `bascula` | BASCULA_PASTO, BASCULA_BOGOTA | 6 + 4 básculas |
| `balanzas` | BALANZAS_ZC, BALANZAS_ZS | 5 + 6 balanzas |
| `flowbalancer` | FLOWBALANCER_PASTO, FLOWBALANCER_BOGOTA | 8 + 14 flowbalancers |
| `iman` | IMAN_BOG, IMAN_PAST | 11 + 4 imanes |

**Formato de código:** `NOMBRE_MAQUINA_CODIGOINTERNO` — permite una ruta de archivo predecible y normalizable.

---

### 4.2 `config_formularios.json` — Configuración Dinámica de Formularios

Este archivo es el **corazón configuración del sistema**: define qué campos, tablas y secciones se renderizan para cada tipo de máquina. Tiene una estructura por tipo:

```json
{
  "tipo_maquina": {
    "titulo": "...",
    "imagen_generica": "/fmt/img/...",
    "campos_estado": [...],
    "bloques_calibracion": [...],   // opcional
    "campos_extra": {...},          // opcional (ej: flowbalancer)
    "escalas_lectura": {...},       // opcional (ej: balanzas)
    "rangos_emp": {...},            // opcional (ej: balanzas, básculas, camionera)
    "observaciones": true
  }
}
```

#### Tipos de máquinas configurados:

| Clave | Título | Características especiales |
|-------|--------|-----------------------------|
| `balanzas` | Verificación de Balanza | Escala de lectura + Rangos EMP (NTC 2031) + 1 bloque de calibración (6 puntos) |
| `bascula` | Verificación de Báscula | Rangos EMP (NTC 2031) + 1 bloque de calibración (6 puntos) |
| `camionera` | Verificación de Báscula Camionera | Rangos EMP + 2 bloques de calibración: pesas patrón (8 celdas) + vehículo (4 posiciones) |
| `flowbalancer` | Verificación de Flowbalancer | Campos extra (Masas Patrón WT/ZERO) sin bloques de calibración tabulares |
| `iman` | Verificación de Imán | Solo campos de estado (sin calibración) |
| `puntada` | Verificación de Cosedora | Solo 3 campos de estado básicos |
| `equipos` | Verificación de Equipos de Laboratorio | 10 campos de estado con opciones N/A |

#### Estructura de `campos_estado`
```json
[
  { "name": "estado_general", "label": "...", "type": "text", "placeholder": "..." },
  { "name": "estado_limpieza", "label": "...", "type": "select", "options": ["Cumple","No Cumple"] }
]
```
- **type**: `text`, `textarea`, `select`, `number`
- **options**: array de opciones para selects

#### Estructura de `bloques_calibracion`
```json
{
  "titulo": "Verificación de Calibración",
  "nota": "Texto informativo...",
  "campo_base": { "name": "peso_utilizado", "label": "...", "placeholder": "..." },
  "puntos": ["P1", "P2", ...],
  "prefijo_punto": "p",
  "campos_por_punto": [
    { "sufijo": "peso_indicador", "label": "Peso indicador", "calculado": false },
    { "sufijo": "diferencia", "label": "Diferencia", "calculado": true }
  ],
  "campo_resultado": { "name": "verificacion_masas", "label": "¿Cumple?", "options": ["No","Si"] }
}
```

> El campo con `"calculado": true` se **calcula automáticamente** en el formulario: `diferencia = peso_utilizado - peso_indicador`.

#### `escalas_lectura` y `rangos_emp`
Mapas `{ "CODIGO_MAQUINA": "texto descriptivo" }` que muestran información técnica específica de cada equipo (consultados por código en tiempo de ejecución).

---

## 5. Análisis Detallado por Archivo

### 5.1 `maquinas_menu.php` — Menú Principal

**Propósito:** Punto de entrada del módulo. Muestra todas las máquinas del catálogo organizadas por zona y grupo, con su estado de verificación.

**Tecnologías:** PHP (solo para cabeceras/errores), HTML, JS vanilla, Fetch API.

**Flujo JavaScript:**
1. `DOMContentLoaded` → lanza dos `fetch()` en paralelo (`Promise.all`):
   - `maquinas_galeria.json` — catálogo de máquinas
   - `rastreo.php` — fechas de última verificación
2. Itera sobre las **zonas** del catálogo
3. Por cada zona crea un acordeón desplegable (`zona-container`)
4. Por cada grupo dentro de la zona, muestra imagen + nombre del grupo
5. Por cada código de máquina:
   - Busca en `rastreo.php` si existe una verificación reciente
   - Si existe → clase `verificada` (verde ✅) + fecha
   - Si no → clase `no-verificada` (ámbar 🛠️) + "Sin verificación"
6. El enlace lleva a: `formulario.html?tipo=ZONA&codigo=CODIGO&maquina=GRUPO`

**Función `menuDesplegable(zonaId, headerEl)`:**
- Toggle de clase `visible` en el contenedor de zona
- Rota el ícono `▶` a `▼` al expandir

---

### 5.2 `formulario.html` — Formulario de Verificación Dinámico

**Propósito:** Formulario completamente dinámico que se adapta al tipo de máquina seleccionada.

**Parámetros URL:**
| Parámetro | Descripción |
|-----------|-------------|
| `tipo` | Tipo de máquina (`balanzas`, `bascula`, etc.) |
| `codigo` | Código único de la máquina |
| `maquina` | Nombre del grupo/modelo |
| `corrige_id` | ID del registro a corregir (si es corrección) |

**Funciones JavaScript clave:**

| Función | Propósito |
|---------|-----------|
| `cargarFormulario()` | Fetch de `config_formularios.json` → renderiza HTML según tipo |
| `renderCampo(campo, full)` | Renderiza un campo individual (input/select/textarea) |
| `renderSeccionEstado(cfg, n)` | Renderiza la sección "Verificación de Estado" (sección 01) |
| `renderBloqueCalibracion(bloque, i, n)` | Renderiza una tabla de calibración con filas por punto de medición |
| `renderCamposExtra(extra, n)` | Renderiza campos adicionales (ej: Masas Patrón del flowbalancer) |
| `renderEscalaLectura(cfg)` | Muestra la escala de lectura de la máquina específica (si existe) |
| `renderRangosEmp(cfg)` | Muestra los rangos EMP según NTC 2031 (si existe) |
| `renderObservaciones(n)` | Sección de observaciones (textarea) |
| `activarCalculoAutomatico()` | Enlaza listeners: recalcula `diferencia = base - indicador` en tiempo real |
| `aplicarPrecarga()` | Si viene de corrección, llena campos desde `sessionStorage` |
| `recolectarDatos()` | Recopila todos los `[name]` del formulario como objeto `{campo: valor}` |
| `enviar(accion)` | POST a `procesar.php` con `accion: "guardar"` o `accion: "registrar"` |

**Numeración de secciones:**
Las secciones se numeran automáticamente con padding `01`, `02`, `03`, etc. según el orden:
1. Verificación de Estado y Funcionamiento → siempre primero
2. Bloques de calibración → uno por cada elemento en `bloques_calibracion`
3. Campos extra → si existe `campos_extra`
4. Escala de lectura / Rangos EMP → informativo, sin número
5. Observaciones → siempre al final

**Modo corrección:**
- Si `corrige_id` está presente en la URL, se muestra el campo "Código de Orden de Trabajo" (obligatorio)
- Los datos del registro original se precargaron en `sessionStorage` por `formulario_correccion.php`

---

### 5.3 `formulario_correccion.php` — Puente de Corrección

**Propósito:** Actúa como **intermediario stateful** entre `revision_maquinas.php` y `formulario.html` para el flujo de corrección.

**Proceso:**
1. Recibe GET: `?tipo=&maquina=&codigo=&id=`
2. Abre el archivo JSON de la máquina correspondiente
3. Localiza el registro por `id_registro`
4. Extrae los `datos` del registro (eliminando las claves de identificación)
5. Construye un payload `{ corrige_id, datos }` y lo serializa como JSON
6. Inyecta ese JSON en `sessionStorage` del navegador via script
7. Redirige inmediatamente a `formulario.html?...&corrige_id=ID`

**Mecanismo sessionStorage:**
```javascript
sessionStorage.setItem('maquinas_v2_precarga', JSON.stringify({corrige_id, datos}));
window.location.replace(url_formulario);
```
Esto garantiza que los datos se transfieran de PHP a HTML (cross-file) sin exponerlos en la URL.

---

### 5.4 `procesar.php` — API de Guardado

**Propósito:** Endpoint PHP que persiste verificaciones y borradores en archivos JSON.

**Método:** POST · Content-Type: `application/json`

**Validaciones:**
- Cuerpo JSON válido (400 si no)
- `tipo_maquina`, `codigo_maquina`, `nombre_maquina` presentes (400 si falta alguno)
- Si `corrige_id` existe, `codigo_orden` debe estar presente (400 si no)

**Función `sanear_ruta($valor)`:**
```php
preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($valor)))
```
Normaliza cualquier string a caracteres seguros para usar como nombre de archivo/carpeta.

**Estructura de la ruta de archivo:**
```
archivos/generados/maquinas_v2/
  └── {tipo_maquina}/          ← balanzas/, bascula/, etc.
      └── {nombre_maquina}/    ← BALANZAS_ZC/, etc.
          └── {codigo}.json   ← ANALITICA220G_LABBOGBAL01.json
```

**Estructura del registro guardado:**
```json
{
  "id_registro": "MAQV2_6871a3f4e8c2a",
  "timestamp": "2026-07-16 10:23:45",
  "usuario_sys": "Juan Pérez",
  "tipo_registro": "verificacion",  // o "correccion"
  "estado": "verificado",           // o "borrador"
  "corrige_id": null,               // o "MAQV2_..." si es corrección
  "codigo_orden": null,             // código OT para correcciones
  "datos": {
    "tipo_maquina": "balanzas",
    "codigo_maquina": "ANALITICA220G_LABBOGBAL01",
    "nombre_maquina": "BALANZAS_ZC",
    "estado_general": "Buen estado",
    "estado_limpieza": "Cumple",
    "nivelada": "Cumple",
    // ... todos los campos del formulario
    "p1_peso_indicador": "220.01",
    "p1_diferencia": "-0.01",
    // ...
    "verificacion_masas": "Si",
    "observaciones": "Sin novedades"
  }
}
```

> El archivo JSON de cada máquina es un **array acumulativo** de todos sus registros históricos.

---

### 5.5 `rastreo.php` — API de Estado de Verificación

**Propósito:** Devuelve, para cada máquina con registros, la **fecha de su última verificación formal** (excluyendo borradores).

**Método:** GET · Respuesta: JSON

**Lógica:**
1. Escanea `archivos/generados/maquinas_v2/` recursivamente (tipo → grupo → archivo)
2. Por cada archivo `.json`, lee todos los registros
3. Filtra: ignora los que tienen `estado === 'borrador'`
4. Selecciona el registro con el `timestamp` más reciente (el "vigente")
5. Devuelve la fecha `YYYY-MM-DD` de ese registro

**Respuesta ejemplo:**
```json
{
  "balanzas": {
    "ANALITICA220G_LABBOGBAL01": {
      "codigos": [
        { "codigo": "ANALITICA220G_LABBOGBAL01", "ultima_verificacion": "2026-07-10" }
      ]
    }
  }
}
```

> Este endpoint es **público** (no requiere sesión) porque el menú debe cargarlo sin autenticación previa para mostrar el estado visual.

---

### 5.6 `listar_registros.php` — API de Historial Completo

**Propósito:** Devuelve el historial **completo de todos los registros** de todas las máquinas, ordenados del más reciente al más antiguo.

**Método:** GET · Respuesta: JSON · **Requiere sesión autenticada**

**Diferencias con `rastreo.php`:**
| Característica | `rastreo.php` | `listar_registros.php` |
|----------------|---------------|------------------------|
| Autenticación | No requerida | `verificarAutenticacion()` |
| Contenido | Solo última fecha | Todos los registros (resumen) |
| Uso | Menú principal | Pantalla de revisión |
| Incluye borradores | No | Sí |

**Estructura de respuesta:**
```json
{
  "status": "success",
  "zonas": {
    "balanzas": {
      "BALANZAS_ZC": {
        "ANALITICA220G_LABBOGBAL01": {
          "registros": [
            { "id_registro": "...", "timestamp": "...", "usuario_sys": "...",
              "tipo_registro": "verificacion", "estado": "verificado", "corrige_id": null }
          ]
        }
      }
    }
  }
}
```

---

### 5.7 `eliminar_registro.php` — API de Eliminación

**Propósito:** Elimina un registro específico de una máquina por su `id_registro`.

**Método:** POST · Content-Type: `application/json` · **Requiere sesión**

**Payload:**
```json
{
  "tipo": "balanzas",
  "maquina": "BALANZAS_ZC",
  "codigo": "ANALITICA220G_LABBOGBAL01",
  "id": "MAQV2_6871a3f4e8c2a"
}
```

**Comportamiento especial:**
- Si después de eliminar el array queda **vacío**, se elimina el archivo `.json` completo del disco (`unlink()`)
- Si aún quedan registros, se reescribe el archivo con el array filtrado

---

### 5.8 `revision_maquinas.php` — Pantalla de Historial

**Propósito:** Vista administrativa que lista todos los registros, permite verlos, corregirlos y eliminarlos en lote.

**Requiere sesión:** Sí (`verificarAutenticacion()`)

**Funcionalidades:**
1. **Carga automática** de todos los registros via `fetch('listar_registros.php')`
2. **Agrupación visual** por tipo → grupo → código de máquina
3. **Badges de estado** para cada registro:
   - ✅ `Verificado` (verde)
   - 📝 `Borrador` (ámbar)
   - ✏️ `Corrección` (acento naranja)
4. **Código de color** en el borde izquierdo: azul cian para Bogotá, verde esmeralda para Pasto
5. **Acción Ver:** Abre `visor_verificacion.php?tipo=&maquina=&codigo=&id=` en nueva pestaña
6. **Acción Corregir:** Navega a `formulario_correccion.php?...`
7. **Eliminación en lote:** Checkboxes + botón `🗑️ Eliminar` → `Promise.all()` de múltiples DELETE simultáneos
8. **Verificación de sesión periódica:** `setInterval` cada 10 segundos consulta `/template/verificar_sesion.php`

---

### 5.9 `visor_verificacion.php` — Visor Documental con PDF

**Propósito:** Vista de sólo lectura que representa el registro como un **documento formal**, con identidad visual corporativa y posibilidad de exportar PDF.

**Requiere sesión:** Sí

**Parámetros GET:** `tipo`, `maquina`, `codigo`, `id`

**Construcción del documento:**
1. Lee el archivo JSON de la máquina
2. Localiza el registro por `id` (si no lo encuentra, muestra el más reciente)
3. Si el registro es una `correccion`, busca también el registro original para mostrar ambos técnicos
4. Lee `config_formularios.json` para obtener la estructura de campos del tipo
5. Renderiza el documento en HTML estructurado:
   - Logo + encabezado corporativo ("Somos Más que Harina · OPE-ME-FO-002 · Versión 4")
   - Datos del encabezado (código, grupo, fecha, OT si aplica)
   - Técnico (y técnico corrector si aplica)
   - Tabla de verificación de estado
   - Escala de lectura (si aplica)
   - Rangos EMP / NTC 2031 (si aplica)
   - Tablas de calibración (una por bloque)
   - Campos extra (si aplica)
   - Observaciones (si aplica)
6. **Marca de agua corporativa** (logo al 6% de opacidad)

**Exportación PDF (Client-side):**
```javascript
async function exportarPDF() {
    const { jsPDF } = window.jspdf;
    const canvas = await html2canvas(elemento, { scale: 2, useCORS: true });
    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF('p', 'mm', 'a4');
    // Calcula alto proporcional al ancho A4
    pdf.addImage(imgData, 'PNG', 0, 0, anchoPdf, altoPdf);
    pdf.save(`Verificacion_{tipo}_{codigo}_{fecha}.pdf`);
}
```
Librerías: `html2canvas@1.4.1` + `jsPDF@2.5.1` (ambas desde CDN cloudflare).

---

## 6. Estilos: `css/maquinas_v2.css`

**Tema:** Cyberpunk · Variante "Mantenimiento" (acento ámbar `#FF8A00`)  
**Fuentes:** Google Fonts — `Barlow` (cuerpo) + `Space Mono` (monoespaciado / etiquetas)

### Variables CSS principales:
```css
:root {
    --bg-color: #0B0E14;      /* Fondo oscuro casi negro */
    --panel-bg: #151A22;      /* Fondo de tarjetas/paneles */
    --accent: #FF8A00;        /* Ámbar / naranja (color de marca de mantenimiento) */
    --accent-glow: rgba(255, 138, 0, 0.4);
    --accent-hover: #FFB347;
    --text-main: #E2E8F0;     /* Texto principal (gris claro) */
    --text-muted: #94A3B8;    /* Texto secundario (gris medio) */
    --border-color: #1E293B;  /* Bordes sutiles */
    --input-bg: #0F172A;      /* Fondo de inputs */
    --danger: #FF3366;        /* Rojo para errores/eliminar */
    --warning: #FFB000;       /* Ámbar para advertencias/no-verificado */
    --ok: #10B981;            /* Verde para verificado/ok */
}
```

### Componentes clave:
- **`.header-box`**: Panel de encabezado con borde izquierdo ámbar y pseudo-elemento `"V2 JSON"` en diagonal
- **`.badge-mantenimiento`**: Pill/badge con borde y fondo ámbar translúcido
- **`.section-card`**: Tarjeta con hover que ilumina el borde en ámbar
- **`.tabla-calibracion`**: Tabla de calibración con encabezados en ámbar translúcido
- **`.codigo-link.verificada`**: Verde con borde esmeralda (`.ok`)
- **`.codigo-link.no-verificada`**: Ámbar con borde naranja (`.warning`)
- **`.status-dot`**: Punto verde pulsante (animación CSS `@keyframes pulse`)
- **`.visor-doc`**: Fondo blanco para el documento imprimible, con marca de agua
- **`@media print`**: Oculta elementos `.no-print`, fondo blanco

---

## 7. Seguridad y Autenticación

| Archivo | Sesión requerida | Método |
|---------|-----------------|--------|
| `maquinas_menu.php` | No (PHP decorativo) | — |
| `formulario.html` | No explícito | — |
| `procesar.php` | Sí (session via `sesion.php`) | `$_SESSION['nombre']` |
| `rastreo.php` | No | — |
| `listar_registros.php` | Sí | `verificarAutenticacion()` |
| `eliminar_registro.php` | Sí | `verificarAutenticacion()` |
| `revision_maquinas.php` | Sí | `verificarAutenticacion()` |
| `visor_verificacion.php` | Sí | `verificarAutenticacion()` |
| `formulario_correccion.php` | Sí | `verificarAutenticacion()` |

**`sanear_ruta()`** — función de saneamiento presente en `procesar.php`, `eliminar_registro.php`, `formulario_correccion.php` y `visor_verificacion.php`:
```php
function sanear_ruta($valor) {
    return preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($valor)));
}
```
Previene path traversal al construir rutas de archivos desde parámetros externos.

**`htmlspecialchars()`** — usado en `visor_verificacion.php` en todos los valores renderizados al HTML para prevenir XSS.

---

## 8. Librerías Externas

| Librería | Versión | CDN | Uso |
|----------|---------|-----|-----|
| SweetAlert2 | 11 | jsdelivr | Modales de confirmación/error en formulario y revisión |
| html2canvas | 1.4.1 | cloudflare | Captura del DOM para PDF |
| jsPDF | 2.5.1 | cloudflare | Generación del PDF en client-side |
| Google Fonts (Barlow + Space Mono) | — | fonts.googleapis.com | Tipografía |

---

## 9. Nomenclatura de Códigos de Máquina

Los códigos siguen el patrón `DESCRIPCION_CODIGOINTERNO`, por ejemplo:

- `ANALITICA220G_LABBOGBAL01` → Balanza analítica 220g · Lab Bogotá Balanza 01
- `OHAUS6KG_MOLBOGBAL01` → OHAUS 6kg · Molinería Bogotá Balanza 01
- `CAMIONERA_BOG_BOGOTA` → Báscula camionera Bogotá
- `MZAH 12 SILO 201_MOLBOGFLO01` → Flowbalancer MZAH12 Silo 201 · Molinería Bogotá

Segmentos del código interno:
- `LAB` / `MOL` / `EMP` / `PRE` / `MAN` / `SLI` / `SLA` / `MIC` / `PLI` / `CGR` → área/proceso
- `BOG` / `PAS` → sede (Bogotá / Pasto)
- `BAL` / `BAS` / `FLO` / `IMA` / `COS` / `CAT` / `ANH` / etc. → tipo de equipo
- `01`, `02`, etc. → número secuencial

---

## 10. Ciclo de Vida de un Registro

```
1. NUEVO REGISTRO
   ├── Técnico accede a maquinas_menu.php
   ├── Selecciona una máquina (sin verificación o a reverificar)
   ├── Se abre formulario.html (carga config + renderiza campos)
   ├── Completa el formulario
   ├── [Guardar Borrador] → procesar.php (accion=guardar, estado=borrador)
   │   └── Accesible para continuar después, NO cuenta como verificado en rastreo
   └── [Generar Verificación] → procesar.php (accion=registrar, estado=verificado)
       └── Cuenta como verificación formal, visible en menú como ✅

2. CORRECCIÓN
   ├── Técnico accede a revision_maquinas.php
   ├── Encuentra el registro a corregir → [✏️ Corregir]
   ├── formulario_correccion.php: lee datos originales → los pone en sessionStorage
   ├── Redirige a formulario.html con corrige_id=ORIGINAL_ID
   ├── formulario.html: detecta corrige_id → muestra campo OT (obligatorio)
   ├── Precarga los datos del sessionStorage en todos los campos
   ├── Técnico modifica lo necesario y envía
   └── procesar.php crea NUEVO registro:
       ├── tipo_registro: "correccion"
       ├── corrige_id: "MAQV2_..." (referencia al original)
       └── codigo_orden: "OT-123" (obligatorio)

3. VISUALIZACIÓN / DESCARGA PDF
   ├── revision_maquinas.php → [📂 Ver] → visor_verificacion.php
   ├── Muestra el documento formal con datos del registro
   ├── Si es corrección, muestra tanto el técnico original como el corrector
   └── [⬇️ Descargar PDF] → html2canvas + jsPDF → archivo .pdf

4. ELIMINACIÓN
   ├── revision_maquinas.php → checkbox(es) → [🗑️ Eliminar]
   ├── Confirmación del usuario
   ├── POST a eliminar_registro.php por cada registro seleccionado
   ├── El registro se filtra del array en el JSON
   └── Si el JSON queda vacío → el archivo se elimina del disco
```

---

## 11. Integración con el Sistema Global

- **Autenticación:** Usa `../sesion.php` (relativa al directorio `template/`)  
- **Verificación de sesión activa:** `revision_maquinas.php` llama cada 10s a `/template/verificar_sesion.php`  
- **Imágenes:** `/fmt/img/MAQUINAS/{zona}/{grupo}.jpeg` — imagen genérica por grupo; `/fmt/img/default.png` como fallback  
- **Logo corporativo:** `/fmt/img/logo_empresa.jpeg` (marca de agua + encabezado del visor)  
- **Retorno al sistema global:** Botón `← Volver` enlaza a `../redireccion.php`  
- **Persistencia:** `../../archivos/generados/maquinas_v2/` (relativa a `template/maquinas_v2/`)

---

## 12. Puntos Destacados del Diseño

1. **Sin base de datos:** Toda la persistencia es en archivos JSON planos. Elimina dependencias de DB y simplifica despliegue.
2. **Config-driven:** Agregar un nuevo tipo de máquina solo requiere añadir una entrada en `config_formularios.json` y los códigos en `maquinas_galeria.json`.
3. **Cálculo automático en tiempo real:** Las diferencias de calibración se calculan en el navegador sin enviar al servidor.
4. **PDF client-side:** La generación de PDF no requiere librerías PHP (como mPDF/TCPDF); se hace capturando el DOM en el navegador.
5. **Corrección no destructiva:** Corregir un registro crea uno nuevo que referencia al original, preservando el historial completo.
6. **Transferencia sessionStorage:** El bridge de corrección usa `sessionStorage` para pasar datos entre un archivo PHP y un HTML sin exponerlos en la URL ni requerir servidor.
